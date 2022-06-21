<?php

/**
 * @package    Lev\Grav\Common\User
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\User;

use Lev\Common\Lev;
use Lev\Common\User\DataUser;
use Lev\Common\Flex;
use Lev\Common\User\Interfaces\UserCollectionInterface;
use Lev\Common\User\Interfaces\UserInterface;

if (!defined('LEV_USER_INSTANCE')) {
    throw new \LogicException('User class was called too early!');
}

if (defined('LEV_USER_INSTANCE') && LEV_USER_INSTANCE === 'FLEX') {
    /**
     * @deprecated 1.6 Use $lev['accounts'] instead of static calls. In type hints, please use UserInterface.
     */
    class User extends Flex\Types\Users\UserObject
    {
        /**
         * Load user account.
         *
         * Always creates user object. To check if user exists, use $this->exists().
         *
         * @param string $username
         * @return UserInterface
         * @deprecated 1.6 Use $lev['accounts']->load(...) instead.
         */
        public static function load($username)
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->' . __FUNCTION__ . '() instead', E_USER_DEPRECATED);

            return static::getCollection()->load($username);
        }

        /**
         * Find a user by username, email, etc
         *
         * Always creates user object. To check if user exists, use $this->exists().
         *
         * @param string $query the query to search for
         * @param array $fields the fields to search
         * @return UserInterface
         * @deprecated 1.6 Use $lev['accounts']->find(...) instead.
         */
        public static function find($query, $fields = ['username', 'email'])
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->' . __FUNCTION__ . '() instead', E_USER_DEPRECATED);

            return static::getCollection()->find($query, $fields);
        }

        /**
         * Remove user account.
         *
         * @param string $username
         * @return bool True if the action was performed
         * @deprecated 1.6 Use $lev['accounts']->delete(...) instead.
         */
        public static function remove($username)
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->delete() instead', E_USER_DEPRECATED);

            return static::getCollection()->delete($username);
        }

        /**
         * @return UserCollectionInterface
         */
        protected static function getCollection()
        {
            return Lev::instance()['accounts'];
        }
    }
} else {
    /**
     * @deprecated 1.6 Use $lev['accounts'] instead of static calls. In type hints, use UserInterface.
     */
    class User extends DataUser\User
    {
        /**
         * Load user account.
         *
         * Always creates user object. To check if user exists, use $this->exists().
         *
         * @param string $username
         * @return UserInterface
         * @deprecated 1.6 Use $lev['accounts']->load(...) instead.
         */
        public static function load($username)
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->' . __FUNCTION__ . '() instead', E_USER_DEPRECATED);

            return static::getCollection()->load($username);
        }

        /**
         * Find a user by username, email, etc
         *
         * Always creates user object. To check if user exists, use $this->exists().
         *
         * @param string $query the query to search for
         * @param array $fields the fields to search
         * @return UserInterface
         * @deprecated 1.6 Use $lev['accounts']->find(...) instead.
         */
        public static function find($query, $fields = ['username', 'email'])
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->' . __FUNCTION__ . '() instead', E_USER_DEPRECATED);

            return static::getCollection()->find($query, $fields);
        }

        /**
         * Remove user account.
         *
         * @param string $username
         * @return bool True if the action was performed
         * @deprecated 1.6 Use $lev['accounts']->delete(...) instead.
         */
        public static function remove($username)
        {
            user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Lev 1.6, use $lev[\'accounts\']->delete() instead', E_USER_DEPRECATED);

            return static::getCollection()->delete($username);
        }

        /**
         * @return UserCollectionInterface
         */
        protected static function getCollection()
        {
            return Lev::instance()['accounts'];
        }
    }
}
