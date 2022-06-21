<?php

/**
 * @package    Lev\Grav\Common\Processors
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Processors;

use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Framework\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class RenderProcessor
 * @package Lev\Common\Processors
 */
class RenderProcessor extends ProcessorBase
{
    /** @var string */
    public $id = 'render';
    /** @var string */
    public $title = 'Render';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->startTimer();

        $container = $this->container;
        $output =  $container['output'];

        if ($output instanceof ResponseInterface) {
            return $output;
        }

        /** @var PageInterface $page */
        $page = $this->container['page'];

        // Use internal Lev output.
        $container->output = $output;

        ob_start();

        $event = new Event(['page' => $page, 'output' => &$container->output]);
        $container->fireEvent('onOutputGenerated', $event);

        echo $container->output;

        $html = ob_get_clean();

        // remove any output
        $container->output = '';

        $event = new Event(['page' => $page, 'output' => $html]);
        $this->container->fireEvent('onOutputRendered', $event);

        $this->stopTimer();

        return new Response($page->httpResponseCode(), $page->httpHeaders(), $html);
    }
}
