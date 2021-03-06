<?php

namespace Lev\Plugin\Email;

use Lev\Common\Lev;
use Lev\Common\Twig\Twig;
use Lev\Common\Utils as LevUtils;

/**
 * Class Utils
 * @package Lev\Grav\Plugin\Email
 */
class Utils
{
    /**
     * Quick utility method to send an HTML email.
     *
     * @param array<int,mixed> $params
     *
     * @return bool True if the action was performed.
     */
    public static function sendEmail(...$params)
    {
        if (is_array($params[0])) {
            $params = array_shift($params);
        } else {
            $keys = ['subject', 'body', 'to', 'from', 'content_type'];
            $params = LevUtils::arrayCombine($keys, $params);
        }
        
        //Initialize twig if not yet initialized
        /** @var Twig $twig */
        $twig = Lev::instance()['twig']->init();

        /** @var Email $email */
        $email = Lev::instance()['Email'];

        if (empty($params['to']) || empty($params['subject']) || empty($params['body'])) {
            return false;
        }

        $params['body'] = $twig->processTemplate('email/base.html.twig', ['content' => $params['body']]);

        $message = $email->buildMessage($params);

        return $email->send($message);
    }
}
