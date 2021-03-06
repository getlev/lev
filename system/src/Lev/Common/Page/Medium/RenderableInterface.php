<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

/**
 * Interface RenderableInterface
 * @package Lev\Common\Page\Medium
 */
interface RenderableInterface
{
    /**
     * Return HTML markup from the medium.
     *
     * @param string|null $title
     * @param string|null $alt
     * @param string|null $class
     * @param string|null $id
     * @param bool $reset
     * @return string
     */
    public function html($title = null, $alt = null, $class = null, $id = null, $reset = true);

    /**
     * Return Parsedown Element from the medium.
     *
     * @param string|null $title
     * @param string|null $alt
     * @param string|null $class
     * @param string|null $id
     * @param bool $reset
     * @return array
     */
    public function parsedownElement($title = null, $alt = null, $class = null, $id = null, $reset = true);
}
