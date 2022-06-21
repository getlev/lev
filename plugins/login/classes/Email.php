<?php declare(strict_types=1);

namespace Lev\Plugin\Login;

use Lev\Common\Config\Config;
use Lev\Common\Lev;
use Lev\Common\Language\Language;
use Lev\Common\Page\Pages;
use Lev\Common\User\Interfaces\UserInterface;
use Lev\Common\Utils;
use Lev\Plugin\Login\Invitations\Invitation;
use Psr\Log\LoggerInterface;

class Email
{
    /**
     * @param UserInterface $user
     * @param UserInterface|null $actor
     * @return void
     * @throws \Exception
     */
    public static function sendActivationEmail(UserInterface $user, UserInterface $actor = null): void
    {
        $email = $user->email;
        $token = (string)$user->get('activation_token', '');

        if (!$email || !str_contains($token, '::')) {
            return;
        }

        [$token, $expire] = explode('::', $token, 2);

        try {
            $config = static::getConfig();

            $param_sep = $config->get('system.param_sep', ':');
            $activationRoute = static::getLogin()->getRoute('activate');
            if (!$activationRoute) {
                throw new \RuntimeException('User activation route does not exist!');
            }

            /** @var Pages $pages */
            $pages = Lev::instance()['pages'];
            $activationLink = $pages->url(
                $activationRoute . '/token' . $param_sep . $token . '/username' . $param_sep . $user->username,
                null,
                true
            );

            $context = [
                'activation_link' => $activationLink,
                'expire' => $expire,
            ];

            $params = [
                'to' => $user->email,
            ];

            static::sendEmail('activate', $context, $params, $user, $actor);
        } catch (\Exception $e) {
            static::getLogger()->error($e->getMessage());

            throw $e;
        }
    }


    /**
     * @param UserInterface $user
     * @param UserInterface|null $actor
     * @return void
     * @throws \Exception
     */
    public static function sendResetPasswordEmail(UserInterface $user, UserInterface $actor = null): void
    {
        $email = $user->email;
        $token = (string)$user->get('reset', '');

        if (!$email || !str_contains($token, '::')) {
            return;
        }

        [$token, $expire] = explode('::', $token, 2);

        try {
            $param_sep = static::getConfig()->get('system.param_sep', ':');
            $resetRoute = static::getLogin()->getRoute('reset');
            if (!$resetRoute) {
                throw new \RuntimeException('Password reset route does not exist!');
            }

            /** @var Pages $pages */
            $pages = Lev::instance()['pages'];
            $resetLink = $pages->url(
                "{$resetRoute}/task{$param_sep}login.reset/token{$param_sep}{$token}/user{$param_sep}{$user->username}/nonce{$param_sep}" . Utils::getNonce('reset-form'),
                null,
                true
            );

            $context = [
                'reset_link' => $resetLink,
                'expire' => $expire,
            ];

            $params = [
                'to' => $user->email,
            ];

            static::sendEmail('reset-password', $context, $params, $user, $actor);
        } catch (\Exception $e) {
            static::getLogger()->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param UserInterface $user
     * @param UserInterface|null $actor
     * @return void
     * @throws \Exception
     */
    public static function sendWelcomeEmail(UserInterface $user, UserInterface $actor = null): void
    {
        if (!$user->email) {
            return;
        }

        try {
            $context = [];

            $params = [
                'to' => $user->email,
            ];

            static::sendEmail('welcome', $context, $params, $user, $actor);
        } catch (\Exception $e) {
            static::getLogger()->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param UserInterface $user
     * @param UserInterface|null $actor
     * @return void
     * @throws \Exception
     */
    public static function sendNotificationEmail(UserInterface $user, UserInterface $actor = null): void
    {
        try {
            $to = static::getConfig()->get('plugins.email.to');
            if (!$to) {
                throw new \RuntimeException(static::getLanguage()->translate('PLUGIN_LOGIN.EMAIL_NOT_CONFIGURED'));
            }

            $context = [];

            $params = [
                'to' => $to,
            ];

            static::sendEmail('notification', $context, $params, $user, $actor);
        } catch (\Exception $e) {
            static::getLogger()->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param Invitation $invitation
     * @param string|null $message
     * @param UserInterface|null $actor
     * @return void
     * @throws \Exception
     */
    public static function sendInvitationEmail(Invitation $invitation, string $message = null, UserInterface $actor = null): void
    {
        if (!$invitation->email) {
            return;
        }

        try {
            $config = static::getConfig();
            $param_sep = $config->get('system.param_sep', ':');
            $inviteRoute = static::getLogin()->getRoute('register', true);
            if (!$inviteRoute) {
                throw new \RuntimeException('User registration route does not exist!');
            }

            /** @var Pages $pages */
            $pages = Lev::instance()['pages'];
            $invitationLink = $pages->url("{$inviteRoute}/{$param_sep}{$invitation->token}", null, true);

            $context = [
                'invitation_link' => $invitationLink,
                'invitation' => $invitation,
                'message' => $message,
            ];

            $params = [
                'to' => $invitation->email,
            ];

            static::sendEmail('invite', $context, $params, null, $actor);
        } catch (\Exception $e) {
            static::getLogger()->error($e->getMessage());

            throw $e;
        }
    }

    protected static function sendEmail(string $template, array $context, array $params, UserInterface $user = null, UserInterface $actor = null): void
    {
        $actor = $actor ?? static::getUser();

        $config = static::getConfig();

        // Twig context.
        $context += [
            'actor' => $actor,
            'user' => $user,
            'site_name' => $config->get('site.title', 'Website'),
            'author' => $config->get('site.author.name', ''),
        ];

        $params += [
            'body' => '',
            'template' => "emails/login/{$template}.html.twig",
        ];

        $email = static::getEmail();

        $message = $email->buildMessage($params, $context);

        $failedRecipients = null;
        $email->send($message, $failedRecipients);
        if ($failedRecipients) {
            $language = static::getLanguage();

            throw new \RuntimeException($language->translate(['PLUGIN_LOGIN.FAILED_TO_SEND_EMAILS', implode(', ', $failedRecipients)]));
        }
    }

    /**
     * @return Login
     */
    protected static function getLogin(): Login
    {
        return Lev::instance()['login'];
    }

    /**
     * @return LoggerInterface
     */
    protected static function getLogger(): LoggerInterface
    {
        return Lev::instance()['log'];
    }

    /**
     * @return UserInterface
     */
    protected static function getUser(): UserInterface
    {
        return Lev::instance()['user'];
    }

    /**
     * @return \Lev\Plugin\Email\Email
     */
    protected static function getEmail(): \Lev\Plugin\Email\Email
    {
        return Lev::instance()['Email'];
    }

    /**
     * @return Config
     */
    protected static function getConfig(): Config
    {
        return Lev::instance()['config'];
    }

    /**
     * @return Language
     */
    protected static function getLanguage(): Language
    {
        return Lev::instance()['language'];
    }
}
