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
 * Class Themes
 * @package Lev\Common\GPM\Local
 */
class Themes extends AbstractPackageCollection
{
    /** @var string */
    protected $type = 'themes';

    /**
     * Local Themes Constructor
     */
    public function __construct()
    {
        /** @var \Lev\Common\Themes $themes */
        $themes = Lev::instance()['themes'];

        parent::__construct($themes->all());
    }
}
