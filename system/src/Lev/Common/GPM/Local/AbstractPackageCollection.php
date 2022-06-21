<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Local;

use Lev\Common\GPM\Common\AbstractPackageCollection as BaseCollection;

/**
 * Class AbstractPackageCollection
 * @package Lev\Common\GPM\Local
 */
abstract class AbstractPackageCollection extends BaseCollection
{
    /**
     * AbstractPackageCollection constructor.
     *
     * @param array $items
     */
    public function __construct($items)
    {
        parent::__construct();

        foreach ($items as $name => $data) {
            $data->set('slug', $name);
            $this->items[$name] = new Package($data, $this->type);
        }
    }
}
