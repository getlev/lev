<?php
namespace Lev\Plugin;

use Composer\Autoload\ClassLoader;
use Lev\Common\Data\Data;
use Lev\Common\Plugin;
use Lev\Plugin\Email\Email;
use RocketTheme\Toolbox\Event\Event;

class EmailPlugin extends Plugin
{
    /**
     * @var Email
     */
    protected $email;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized'      => ['onPluginsInitialized', 0],
            'onFormProcessed'           => ['onFormProcessed', 0],
            'onTwigTemplatePaths'       => ['onTwigTemplatePaths', 0],
            'onSchedulerInitialized'    => ['onSchedulerInitialized', 0],
            'onAdminSave'               => ['onAdminSave', 0],
        ];
    }

    /**
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize emailing.
     */
    public function onPluginsInitialized()
    {
        $this->email = new Email();

        if ($this->email::enabled()) {
            $this->lev['Email'] = $this->email;
        }
    }

    /**
     * Add twig paths to plugin templates.
     */
    public function onTwigTemplatePaths()
    {
        $twig = $this->lev['twig'];
        $twig->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Force compile during save if admin plugin save
     *
     * @param Event $event
     */
    public function onAdminSave(Event $event)
    {
        /** @var Data $obj */
        $obj = $event['object'];



        if ($obj instanceof Data && $obj->blueprints()->getFilename() === 'email/blueprints') {
            $current_pw = $this->lev['config']->get('plugins.email.mailer.smtp.password');
            $new_pw = $obj->get('mailer.smtp.password');
            if (!empty($current_pw) && empty($new_pw)) {
                $obj->set('mailer.smtp.password', $current_pw);
            }

        }
    }

    /**
     * Send email when processing the form data.
     *
     * @param Event $event
     */
    public function onFormProcessed(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        if (!$this->email->enabled()) {
            return;
        }

        switch ($action) {
            case 'email':
                // Prepare Twig variables
                $vars = array(
                    'form' => $form,
                    'page' => $this->lev['page']
                );

                // Copy files now, we need those.
                // TODO: needs an update
                $form->legacyUploads();
                $form->copyFiles();

                $this->lev->fireEvent('onEmailSend', new Event(['params' => &$params, 'vars' => &$vars]));

                if ($this->isAssocArray($params)) {
                    $this->sendFormEmail($form, $params, $vars);
                } else {
                    foreach ($params as $email) {
                        $this->sendFormEmail($form, $email, $vars);
                    }
                }

                break;
        }
    }

    /**
     * Add index job to Lev Scheduler
     * Requires Lev 1.6.0 - Scheduler
     */
    public function onSchedulerInitialized(Event $e)
    {
        if ($this->config->get('plugins.email.queue.enabled')) {

            /** @var Scheduler $scheduler */
            $scheduler = $e['scheduler'];
            $at = $this->config->get('plugins.email.queue.flush_frequency');
            $logs = 'logs/email-queue.out';
            $job = $scheduler->addFunction('Lev\Plugin\Email\Email::flushQueue', [], 'email-flushqueue');
            $job->at($at);
            $job->output($logs);
            $job->backlink('/plugins/email');
        }
    }

    protected function sendFormEmail($form, $params, $vars)
    {
        // Build message
        $message = $this->email->buildMessage($params, $vars);

        if (isset($params['attachments'])) {
            $filesToAttach = (array)$params['attachments'];
            if ($filesToAttach) foreach ($filesToAttach as $fileToAttach) {
                $filesValues = $form->value($fileToAttach);

                if ($filesValues) foreach($filesValues as $fileValues) {
                    if (isset($fileValues['file'])) {
                        $filename = $fileValues['file'];
                    } else {
                        $filename = LEV_SITE_DIR . '/' . $fileValues['path'];
                    }

                    try {
                        $message->attach(\Swift_Attachment::fromPath($filename));
                    } catch (\Exception $e) {
                        // Log any issues
                        $this->lev['log']->error($e->getMessage());
                    }
                }
            }
        }

        //fire event to apply optional signers
        $this->lev->fireEvent('onEmailMessage', new Event(['message' => $message, 'params' => $params, 'form' => $form]));

        // Send e-mail
        $this->email->send($message);

        //fire event after eMail was sent
        $this->lev->fireEvent('onEmailSent', new Event(['message' => $message, 'params' => $params, 'form' => $form]));
    }

    protected function isAssocArray(array $arr)
    {
        if (array() === $arr) return false;
        $keys = array_keys($arr);
        $index_keys = range(0, count($arr) - 1);
        return $keys !== $index_keys;
    }

}
