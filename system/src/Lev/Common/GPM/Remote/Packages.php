<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Remote;

use Lev\Common\GPM\Common\CachedCollection;

/**
 * Class Packages
 * @package Lev\Common\GPM\Remote
 */
class Packages extends CachedCollection
{
    /**
     * Packages constructor.
     * @param bool $refresh
     * @param callable|null $callback
     */
    public function __construct($refresh = false, $callback = null)
    {
        $items = [
            'plugins' => new Plugins($refresh, $callback),
            'themes' => new Themes($refresh, $callback)
        ];

        parent::__construct($items);
    }
}
