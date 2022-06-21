<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Lev\Common\Lev;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TaskServiceProvider
 * @package Lev\Common\Service
 */
class TaskServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['task'] = function (Lev $c) {
            /** @var ServerRequestInterface $request */
            $request = $c['request'];
            $body = $request->getParsedBody();

            $task = $body['task'] ?? $c['uri']->param('task');
            if (null !== $task) {
                $task = filter_var($task, FILTER_SANITIZE_STRING);
            }

            return $task ?: null;
        };

        $container['action'] = function (Lev $c) {
            /** @var ServerRequestInterface $request */
            $request = $c['request'];
            $body = $request->getParsedBody();

            $action = $body['action'] ?? $c['uri']->param('action');
            if (null !== $action) {
                $action = filter_var($action, FILTER_SANITIZE_STRING);
            }

            return $action ?: null;
        };
    }
}
