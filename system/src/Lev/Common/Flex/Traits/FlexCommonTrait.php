<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex\Traits;

use Lev\Common\Debugger;
use Lev\Common\Lev;
use Lev\Common\Twig\Twig;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Template;
use Twig\TemplateWrapper;

/**
 * Trait FlexCommonTrait
 * @package Lev\Common\Flex\Traits
 */
trait FlexCommonTrait
{
    /**
     * @param string $layout
     * @return Template|TemplateWrapper
     * @throws LoaderError
     * @throws SyntaxError
     */
    protected function getTemplate($layout)
    {
        $container = $this->getContainer();

        /** @var Twig $twig */
        $twig = $container['twig'];

        try {
            return $twig->twig()->resolveTemplate($this->getTemplatePaths($layout));
        } catch (LoaderError $e) {
            /** @var Debugger $debugger */
            $debugger = Lev::instance()['debugger'];
            $debugger->addException($e);

            return $twig->twig()->resolveTemplate(['flex/404.html.twig']);
        }
    }

    abstract protected function getTemplatePaths(string $layout): array;
    abstract protected function getContainer(): Lev;
}
