<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Local;

use Lev\Common\GPM\Common\CachedCollection;

/**
 * Class Packages
 * @package Lev\Common\GPM\Local
 */
class Packages extends CachedCollection
{
    public function __construct()
    {
        $items = [
            'plugins' => new Plugins(),
            'themes' => new Themes()
        ];

        parent::__construct($items);
    }
}
