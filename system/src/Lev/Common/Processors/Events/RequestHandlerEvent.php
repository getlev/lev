<?php

/**
 * @package    Lev\Grav\Common\Processors
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Processors\Events;

use Lev\Framework\RequestHandler\RequestHandler;
use Lev\Framework\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class RequestHandlerEvent
 * @package Lev\Common\Processors\Events
 */
class RequestHandlerEvent extends Event
{
    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->offsetGet('request');
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->getRequest()->getAttribute('route');
    }

    /**
     * @return RequestHandler
     */
    public function getHandler(): RequestHandler
    {
        return $this->offsetGet('handler');
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->offsetGet('response');
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->offsetSet('response', $response);
        $this->stopPropagation();

        return $this;
    }

    /**
     * @param string $name
     * @param MiddlewareInterface $middleware
     * @return RequestHandlerEvent
     */
    public function addMiddleware(string $name, MiddlewareInterface $middleware): self
    {
        /** @var RequestHandler $handler */
        $handler = $this['handler'];
        $handler->addMiddleware($name, $middleware);

        return $this;
    }
}
