<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

use Lev\Common\Media\Interfaces\VideoMediaInterface;
use Lev\Common\Media\Traits\VideoMediaTrait;

/**
 * Class VideoMedium
 * @package Lev\Common\Page\Medium
 */
class VideoMedium extends Medium implements VideoMediaInterface
{
    use VideoMediaTrait;

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
