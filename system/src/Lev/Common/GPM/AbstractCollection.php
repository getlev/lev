<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM;

use Lev\Common\Iterator;

/**
 * Class AbstractCollection
 * @package Lev\Common\GPM
 */
abstract class AbstractCollection extends Iterator
{
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $items = [];

        foreach ($this->items as $name => $package) {
            $items[$name] = $package->toArray();
        }

        return $items;
    }
}
