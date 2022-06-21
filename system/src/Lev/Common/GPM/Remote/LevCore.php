<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Remote;

use Lev\Common\Lev;
use \Doctrine\Common\Cache\FilesystemCache;
use InvalidArgumentException;

/**
 * Class LevCore
 * @package Lev\Common\GPM\Remote
 */
class LevCore extends AbstractPackageCollection
{
    /** @var string */
    protected $repository = 'https://getgrav.org/downloads/grav.json';

    /** @var array */
    private $data;
    /** @var string */
    private $version;
    /** @var string */
    private $date;
    /** @var string|null */
    private $min_php;

    /**
     * @param bool $refresh
     * @param callable|null $callback
     * @throws InvalidArgumentException
     */
    public function __construct($refresh = false, $callback = null)
    {
        $channel = Lev::instance()['config']->get('system.gpm.releases', 'stable');
        $cache_dir   = Lev::instance()['locator']->findResource('cache://gpm', true, true);
        $this->cache = new FilesystemCache($cache_dir);
        $this->repository .= '?v=' . LEV_GVERSION . '&' . $channel . '=1';
        $this->raw = $this->cache->fetch(md5($this->repository));

        $this->fetch($refresh, $callback);

        $this->data    = json_decode($this->raw, true);
        $this->version = $this->data['version'] ?? '-';
        $this->date    = $this->data['date'] ?? '-';
        $this->min_php = $this->data['min_php'] ?? null;

        if (isset($this->data['assets'])) {
            foreach ((array)$this->data['assets'] as $slug => $data) {
                $this->items[$slug] = new Package($data);
            }
        }
    }

    /**
     * Returns the list of assets associated to the latest version of Lev
     *
     * @return array list of assets
     */
    public function getAssets()
    {
        return $this->data['assets'];
    }

    /**
     * Returns the changelog list for each version of Lev
     *
     * @param string|null $diff the version number to start the diff from
     * @return array changelog list for each version
     */
    public function getChangelog($diff = null)
    {
        if (!$diff) {
            return $this->data['changelog'];
        }

        $diffLog = [];
        foreach ((array)$this->data['changelog'] as $version => $changelog) {
            preg_match("/[\w\-\.]+/", $version, $cleanVersion);

            if (!$cleanVersion || version_compare($diff, $cleanVersion[0], '>=')) {
                continue;
            }

            $diffLog[$version] = $changelog;
        }

        return $diffLog;
    }

    /**
     * Return the release date of the latest Lev
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Determine if this version of Lev is eligible to be updated
     *
     * @return mixed
     */
    public function isUpdatable()
    {
        return version_compare(LEV_GVERSION, $this->getVersion(), '<');
    }

    /**
     * Returns the latest version of Lev available remotely
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the minimum PHP version
     *
     * @return string
     */
    public function getMinPHPVersion()
    {
        // If non min set, assume current PHP version
        if (null === $this->min_php) {
            $this->min_php = PHP_VERSION;
        }

        return $this->min_php;
    }

    /**
     * Is this installation symlinked?
     *
     * @return bool
     */
    public function isSymlink()
    {
        return is_link(LEV_SITE_DIR . '/' . 'index.php');
    }
}
