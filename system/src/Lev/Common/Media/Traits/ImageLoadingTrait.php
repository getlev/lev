<?php

/**
 * @package    Lev\Grav\Common\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Media\Traits;

use Lev\Common\Lev;

/**
 * Trait ImageLoadingTrait
 * @package Lev\Common\Media\Traits
 */
trait ImageLoadingTrait
{
    /**
     * Allows to set the loading attribute from Markdown or Twig
     *
     * @param string|null $value
     * @return $this
     */
    public function loading($value = null)
    {
        if (null === $value) {
            $value = Lev::instance()['config']->get('system.images.defaults.loading', 'auto');
        }
        if ($value && $value !== 'auto') {
            $this->attributes['loading'] = $value;
        }

        return $this;
    }
}
