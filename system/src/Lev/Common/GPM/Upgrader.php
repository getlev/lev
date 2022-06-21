<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM;

use Lev\Common\GPM\Remote\LevCore;
use InvalidArgumentException;

/**
 * Class Upgrader
 *
 * @package Lev\Common\GPM
 */
class Upgrader
{
    /** @var LevCore Remote details about latest Lev version */
    private $remote;

    /** @var string|null */
    private $min_php;

    /**
     * Creates a new GPM instance with Local and Remote packages available
     *
     * @param boolean  $refresh  Applies to Remote Packages only and forces a refetch of data
     * @param callable|null $callback Either a function or callback in array notation
     * @throws InvalidArgumentException
     */
    public function __construct($refresh = false, $callback = null)
    {
        $this->remote = new Remote\LevCore($refresh, $callback);
    }

    /**
     * Returns the release date of the latest version of Lev
     *
     * @return string
     */
    public function getReleaseDate()
    {
        return $this->remote->getDate();
    }

    /**
     * Returns the version of the installed Lev
     *
     * @return string
     */
    public function getLocalVersion()
    {
        return LEV_GVERSION;
    }

    /**
     * Returns the version of the remotely available Lev
     *
     * @return string
     */
    public function getRemoteVersion()
    {
        return $this->remote->getVersion();
    }

    /**
     * Returns an array of assets available to download remotely
     *
     * @return array
     */
    public function getAssets()
    {
        return $this->remote->getAssets();
    }

    /**
     * Returns the changelog list for each version of Lev
     *
     * @param string|null $diff the version number to start the diff from
     * @return array return the changelog list for each version
     */
    public function getChangelog($diff = null)
    {
        return $this->remote->getChangelog($diff);
    }

    /**
     * Make sure this meets minimum PHP requirements
     *
     * @return bool
     */
    public function meetsRequirements()
    {
        if (version_compare(PHP_VERSION, $this->minPHPVersion(), '<')) {
            return false;
        }

        return true;
    }

    /**
     * Get minimum PHP version from remote
     *
     * @return string
     */
    public function minPHPVersion()
    {
        if (null === $this->min_php) {
            $this->min_php = $this->remote->getMinPHPVersion();
        }

        return $this->min_php;
    }

    /**
     * Checks if the currently installed Lev is upgradable to a newer version
     *
     * @return bool True if it's upgradable, False otherwise.
     */
    public function isUpgradable()
    {
        return version_compare($this->getLocalVersion(), $this->getRemoteVersion(), '<');
    }

    /**
     * Checks if Lev is currently symbolically linked
     *
     * @return bool True if Lev is symlinked, False otherwise.
     */
    public function isSymlink()
    {
        return $this->remote->isSymlink();
    }
}
