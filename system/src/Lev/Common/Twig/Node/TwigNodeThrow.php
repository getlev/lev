<?php

/**
 * @package    Lev\Grav\Common\Twig
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Twig\Node;

use LogicException;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Class TwigNodeThrow
 * @package Lev\Common\Twig\Node
 */
class TwigNodeThrow extends Node
{
    /**
     * TwigNodeThrow constructor.
     * @param int $code
     * @param Node $message
     * @param int $lineno
     * @param string|null $tag
     */
    public function __construct($code, Node $message, $lineno = 0, $tag = null)
    {
        parent::__construct(['message' => $message], ['code' => $code], $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Compiler $compiler A Twig Compiler instance
     * @return void
     * @throws LogicException
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('throw new \Lev\Common\Twig\Exception\TwigException(')
            ->subcompile($this->getNode('message'))
            ->write(', ')
            ->write($this->getAttribute('code') ?: 500)
            ->write(");\n");
    }
}
