<?php

/**
 * @package    Lev\Grav\Common\Processors
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Processors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TwigProcessor
 * @package Lev\Common\Processors
 */
class TwigProcessor extends ProcessorBase
{
    /** @var string */
    public $id = 'twig';
    /** @var string */
    public $title = 'Twig';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->startTimer();
        $this->container['twig']->init();
        $this->stopTimer();

        return $handler->handle($request);
    }
}
