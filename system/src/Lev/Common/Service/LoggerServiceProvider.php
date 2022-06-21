<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

/**
 * Class LoggerServiceProvider
 * @package Lev\Common\Service
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['log'] = function ($c) {
            $log = new Logger('lev');

            /** @var UniformResourceLocator $locator */
            $locator = $c['locator'];

            $log_file = $locator->findResource('log://lev.log', true, true);
            $log->pushHandler(new StreamHandler($log_file, Logger::DEBUG));

            return $log;
        };
    }
}
