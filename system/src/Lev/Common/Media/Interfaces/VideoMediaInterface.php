<?php

/**
 * @package    Lev\Grav\Common\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Media\Interfaces;

/**
 * Class implements video media interface.
 */
interface VideoMediaInterface extends MediaObjectInterface, MediaPlayerInterface
{
    /**
     * Allows to set the video's poster image
     *
     * @param string $urlImage
     * @return $this
     */
    public function poster($urlImage);

    /**
     * Allows to set the playsinline attribute
     *
     * @param bool $status
     * @return $this
     */
    public function playsinline($status = false);
}
