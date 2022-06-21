<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Lev\Common\Errors\Errors;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ErrorServiceProvider
 * @package Lev\Common\Service
 */
class ErrorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['errors'] = new Errors;
    }
}
