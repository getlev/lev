<?php

/**
 * @package    Lev\Grav\Framework\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Media\Interfaces;

/**
 * Class implements media interface.
 */
interface MediaInterface
{
    /**
     * Gets the associated media collection.
     *
     * @return MediaCollectionInterface  Collection of associated media.
     */
    public function getMedia();

    /**
     * Get filesystem path to the associated media.
     *
     * @return string|null  Media path or null if the object doesn't have media folder.
     */
    public function getMediaFolder();

    /**
     * Get display order for the associated media.
     *
     * @return array Empty array means default ordering.
     */
    public function getMediaOrder();
}
