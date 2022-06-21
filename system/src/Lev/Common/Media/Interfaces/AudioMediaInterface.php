<?php

/**
 * @package    Lev\Grav\Common\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Media\Interfaces;

/**
 * Class implements audio media interface.
 */
interface AudioMediaInterface extends MediaObjectInterface, MediaPlayerInterface
{
    /**
     * Allows to set the controlsList behaviour
     * Separate multiple values with a hyphen
     *
     * @param string $controlsList
     * @return $this
     */
    public function controlsList($controlsList);
}
