<?php

/**
 * @package    Lev\Grav\Common
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common;

/**
 * @deprecated 1.4 Use Lev::instance() instead.
 */
trait LevTrait
{
    /** @var Lev */
    protected static $lev;

    /**
     * @return Lev
     * @deprecated 1.4 Use Lev::instance() instead.
     */
    public static function getLev()
    {
        user_error(__TRAIT__ . ' is deprecated since Lev 1.4, use Lev::instance() instead', E_USER_DEPRECATED);

        if (null === self::$lev) {
            self::$lev = Lev::instance();
        }

        return self::$lev;
    }
}
