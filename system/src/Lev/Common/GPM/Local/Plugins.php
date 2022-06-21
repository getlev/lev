<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Local;

use Lev\Common\Lev;

/**
 * Class Plugins
 * @package Lev\Common\GPM\Local
 */
class Plugins extends AbstractPackageCollection
{
    /** @var string */
    protected $type = 'plugins';

    /**
     * Local Plugins Constructor
     */
    public function __construct()
    {
        /** @var \Lev\Common\Plugins $plugins */
        $plugins = Lev::instance()['plugins'];

        parent::__construct($plugins->all());
    }
}
