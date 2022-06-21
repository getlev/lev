<?php
namespace Lev\Plugin\Console;

use Lev\Common\Lev;
use Lev\Common\Data\Data;
use Lev\Console\ConsoleCommand;
use Lev\Plugin\Email\Email;
use Lev\Plugin\Email\Utils as EmailUtils;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class TestEmailCommand
 * @package Lev\Grav\Console\Cli\
 */
class TestEmailCommand extends ConsoleCommand
{
    /** @var array */
    protected $options = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('test-email')
            ->setAliases(['testemail'])
            ->addOption(
                'to',
                't',
                InputOption::VALUE_REQUIRED,
                'An email address to send the email to'
            )
            ->addOption(
                'env',
                'e',
                InputOption::VALUE_OPTIONAL,
                'The environment to trigger a specific configuration. For example: localhost, mysite.dev, www.mysite.com'
            )
            ->addOption(
                'subject',
                's',
                InputOption::VALUE_OPTIONAL,
                'A subject for the email'
            )
            ->addOption(
                'body',
                'b',
                InputOption::VALUE_OPTIONAL,
                'The body of the email'
            )
            ->setDescription('Sends a test email using the plugin\'s configuration')
            ->setHelp('The <info>test-email</info> command sends a test email using the plugin\'s configuration');
    }

    /**
     * @return int
     */
    protected function serve()
    {
        // TODO: remove when requiring Lev 1.7+
        if (method_exists($this, 'initializeLev')) {
            $this->initializeThemes();
        }

        $this->output->writeln('');
        $this->output->writeln('<yellow>Current Configuration:</yellow>');
        $this->output->writeln('');

        $lev = Lev::instance();
        $email_config = new Data($lev['config']->get('plugins.email'));
        if ($email_config->get('mailer.smtp.password')) {
            $password = $email_config->get('mailer.smtp.password');
            $obfuscated_password = str_repeat('*', strlen($password) - 2) . substr($password, -2);
            $email_config->set('mailer.smtp.password', $obfuscated_password);
        }

        dump($email_config);

        $this->output->writeln('');

        $lev['Email'] = new Email();

        $to = $this->input->getOption('to') ?: $lev['config']->get('plugins.email.to');
        $subject = $this->input->getOption('subject');
        $body = $this->input->getOption('body');

        if (!$subject) {
            $subject = 'Testing Lev Email Plugin';
        }

        if (!$body) {
            $configuration = print_r($email_config, true);
            $body = $lev['language']->translate(['PLUGIN_EMAIL.TEST_EMAIL_BODY', $configuration]);
        }

        $sent = EmailUtils::sendEmail(['subject'=>$subject, 'body'=>$body, 'to'=>$to]);

        if ($sent) {
            $this->output->writeln("<green>Message sent successfully!</green>");
        } else {
            $this->output->writeln("<red>Problem sending email...</red>");
        }

        return 0;
    }
}
