<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Twig\Twig;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class OutputServiceProvider
 * @package Lev\Common\Service
 */
class OutputServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['output'] = function ($c) {
            /** @var Twig $twig */
            $twig = $c['twig'];

            /** @var PageInterface $page */
            $page = $c['page'];

            return $twig->processSite($page->templateFormat());
        };
    }
}
