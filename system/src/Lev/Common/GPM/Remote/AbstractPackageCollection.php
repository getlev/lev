<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Remote;

use Lev\Common\Lev;
use Lev\Common\HTTP\Response;
use Lev\Common\GPM\Common\AbstractPackageCollection as BaseCollection;
use \Doctrine\Common\Cache\FilesystemCache;
use RuntimeException;

/**
 * Class AbstractPackageCollection
 * @package Lev\Common\GPM\Remote
 */
class AbstractPackageCollection extends BaseCollection
{
    /** @var string The cached data previously fetched */
    protected $raw;
    /** @var string */
    protected $repository;
    /** @var FilesystemCache */
    protected $cache;

    /** @var int The lifetime to store the entry in seconds */
    private $lifetime = 86400;

    /**
     * AbstractPackageCollection constructor.
     *
     * @param string|null $repository
     * @param bool $refresh
     * @param callable|null $callback
     */
    public function __construct($repository = null, $refresh = false, $callback = null)
    {
        parent::__construct();
        if ($repository === null) {
            throw new RuntimeException('A repository is required to indicate the origin of the remote collection');
        }

        $channel = Lev::instance()['config']->get('system.gpm.releases', 'stable');
        $cache_dir = Lev::instance()['locator']->findResource('cache://gpm', true, true);
        $this->cache = new FilesystemCache($cache_dir);

        $this->repository = $repository . '?v=' . LEV_GVERSION . '&' . $channel . '=1';
        $this->raw        = $this->cache->fetch(md5($this->repository));

        $this->fetch($refresh, $callback);
        foreach (json_decode($this->raw, true) as $slug => $data) {
            // Temporarily fix for using multi-sites
            if (isset($data['install_path'])) {
                $path = preg_replace('~^user/~i', 'app://', $data['install_path']);
                $data['install_path'] = Lev::instance()['locator']->findResource($path, false, true);
            }
            $this->items[$slug] = new Package($data, $this->type);
        }
    }

    /**
     * @param bool $refresh
     * @param callable|null $callback
     * @return string
     */
    public function fetch($refresh = false, $callback = null)
    {
        if (!$this->raw || $refresh) {
            $response  = Response::get($this->repository, [], $callback);
            $this->raw = $response;
            $this->cache->save(md5($this->repository), $this->raw, $this->lifetime);
        }

        return $this->raw;
    }
}
