<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Common;

use Lev\Common\Iterator;

/**
 * Class CachedCollection
 * @package Lev\Common\GPM\Common
 */
class CachedCollection extends Iterator
{
    /** @var array */
    protected static $cache = [];

    /**
     * CachedCollection constructor.
     *
     * @param array $items
     */
    public function __construct($items)
    {
        parent::__construct();

        $method = static::class . __METHOD__;

        // local cache to speed things up
        if (!isset(self::$cache[$method])) {
            self::$cache[$method] = $items;
        }

        foreach (self::$cache[$method] as $name => $item) {
            $this->append([$name => $item]);
        }
    }
}
