<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

use Lev\Common\Media\Interfaces\ImageMediaInterface;
use Lev\Common\Media\Traits\ImageLoadingTrait;
use Lev\Common\Media\Traits\StaticResizeTrait;

/**
 * Class StaticImageMedium
 * @package Lev\Common\Page\Medium
 */
class StaticImageMedium extends Medium implements ImageMediaInterface
{
    use StaticResizeTrait;
    use ImageLoadingTrait;

    /**
     * Parsedown element for source display mode
     *
     * @param  array $attributes
     * @param  bool $reset
     * @return array
     */
    protected function sourceParsedownElement(array $attributes, $reset = true)
    {
        if (empty($attributes['src'])) {
            $attributes['src'] = $this->url($reset);
        }

        return ['name' => 'img', 'attributes' => $attributes];
    }

    /**
     * @return $this
     */
    public function higherQualityAlternative()
    {
        return $this;
    }
}
