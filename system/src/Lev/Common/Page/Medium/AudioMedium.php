<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

use Lev\Common\Media\Interfaces\AudioMediaInterface;
use Lev\Common\Media\Traits\AudioMediaTrait;

/**
 * Class AudioMedium
 * @package Lev\Common\Page\Medium
 */
class AudioMedium extends Medium implements AudioMediaInterface
{
    use AudioMediaTrait;

    /**
     * Reset medium.
     *
     * @return $this
     */
    public function reset()
    {
        parent::reset();

        $this->resetPlayer();

        return $this;
    }
}
