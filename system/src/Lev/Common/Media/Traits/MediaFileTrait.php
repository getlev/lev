<?php

/**
 * @package    Lev\Grav\Common\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Media\Traits;

use Lev\Common\Lev;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

/**
 * Trait MediaFileTrait
 * @package Lev\Common\Media\Traits
 */
trait MediaFileTrait
{
    /**
     * Check if this medium exists or not
     *
     * @return bool
     */
    public function exists()
    {
        $path = $this->path(false);

        return file_exists($path);
    }

    /**
     * Get file modification time for the medium.
     *
     * @return int|null
     */
    public function modified()
    {
        $path = $this->path(false);
        if (!file_exists($path)) {
            return null;
        }

        return filemtime($path) ?: null;
    }

    /**
     * Get size of the medium.
     *
     * @return int
     */
    public function size()
    {
        $path = $this->path(false);
        if (!file_exists($path)) {
            return 0;
        }

        return filesize($path) ?: 0;
    }

    /**
     * Return PATH to file.
     *
     * @param bool $reset
     * @return string path to file
     */
    public function path($reset = true)
    {
        if ($reset) {
            $this->reset();
        }

        return $this->get('url') ?? $this->get('filepath');
    }

    /**
     * Return the relative path to file
     *
     * @param bool $reset
     * @return string
     */
    public function relativePath($reset = true)
    {
        if ($reset) {
            $this->reset();
        }

        $path = $this->path(false);
        $output = preg_replace('|^' . preg_quote(LEV_SITE_DIR, '|') . '|', '', $path) ?: $path;

        /** @var UniformResourceLocator $locator */
        $locator = $this->getLev()['locator'];
        if ($locator->isStream($output)) {
            $output = (string)($locator->findResource($output, false) ?: $locator->findResource($output, false, true));
        }

        return $output;
    }

    /**
     * Return URL to file.
     *
     * @param bool $reset
     * @return string
     */
    public function url($reset = true)
    {
        $url = $this->get('url');
        if ($url) {
            return $url;
        }

        $path = $this->relativePath($reset);

        return trim($this->getLev()['base_url'] . '/' . $this->urlQuerystring($path), '\\');
    }

    /**
     * Get the URL with full querystring
     *
     * @param string $url
     * @return string
     */
    abstract public function urlQuerystring($url);

    /**
     * Reset medium.
     *
     * @return $this
     */
    abstract public function reset();

    /**
     * @return Lev
     */
    abstract protected function getLev(): Lev;
}
