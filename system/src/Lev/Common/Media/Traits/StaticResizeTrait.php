<?php

/**
 * @package    Lev\Grav\Common\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Media\Traits;

/**
 * Trait StaticResizeTrait
 * @package Lev\Common\Media\Traits
 */
trait StaticResizeTrait
{
    /**
     * Resize media by setting attributes
     *
     * @param  int|null $width
     * @param  int|null $height
     * @return $this
     */
    public function resize($width = null, $height = null)
    {
        if ($width) {
            $this->styleAttributes['width'] = $width . 'px';
        } else {
            unset($this->styleAttributes['width']);
        }
        if ($height) {
            $this->styleAttributes['height'] = $height . 'px';
        } else {
            unset($this->styleAttributes['height']);
        }

        return $this;
    }
}
