<?php

/**
 * @package    Lev\Grav\Common\Processors
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Processors;

use RocketTheme\Toolbox\Event\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SchedulerProcessor
 * @package Lev\Common\Processors
 */
class SchedulerProcessor extends ProcessorBase
{
    /** @var string */
    public $id = '_scheduler';
    /** @var string */
    public $title = 'Scheduler';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->startTimer();
        $scheduler = $this->container['scheduler'];
        $this->container->fireEvent('onSchedulerInitialized', new Event(['scheduler' => $scheduler]));
        $this->stopTimer();

        return $handler->handle($request);
    }
}
