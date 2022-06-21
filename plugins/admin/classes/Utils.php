<?php

namespace Lev\Plugin\Admin;

use Lev\Common\Lev;
use Lev\Common\User\Interfaces\UserCollectionInterface;
use Lev\Common\User\Interfaces\UserInterface;

/**
 * Admin utils class
 *
 * @license MIT
 */
class Utils
{
    /**
     * Matches an email to a user
     *
     * @param string $email
     *
     * @return UserInterface
     */
    public static function findUserByEmail(string $email)
    {
        $lev = Lev::instance();

        /** @var UserCollectionInterface $users */
        $users = $lev['accounts'];

        return $users->find($email, ['email']);
    }

    /**
     * Generates a slug of the given string
     *
     * @param string $str
     * @return string
     */
    public static function slug(string $str)
    {
        if (function_exists('transliterator_transliterate')) {
            $str = transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove;', $str);
        } else {
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        }

        $str = strtolower($str);
        $str = preg_replace('/[-\s]+/', '-', $str);
        $str = preg_replace('/[^a-z0-9-]/i', '', $str);
        $str = trim($str, '-');

        return $str;
    }
}
