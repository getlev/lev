<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Lev\Common\Assets;

/**
 * Class AssetsServiceProvider
 * @package Lev\Common\Service
 */
class AssetsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['assets'] = function () {
            return new Assets();
        };
    }
}
