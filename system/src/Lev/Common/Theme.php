<?php

/**
 * @package    Lev\Grav\Common
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common;

use Lev\Common\Config\Config;
use RocketTheme\Toolbox\File\YamlFile;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

/**
 * Class Theme
 * @package Lev\Common
 */
class Theme extends Plugin
{
    /**
     * Constructor.
     *
     * @param Lev   $lev
     * @param Config $config
     * @param string $name
     */
    public function __construct(Lev $lev, Config $config, $name)
    {
        parent::__construct($name, $lev, $config);
    }

    /**
     * Get configuration of the plugin.
     *
     * @return array
     */
    public function config()
    {
        return $this->config["themes.{$this->name}"] ?? [];
    }

    /**
     * Persists to disk the theme parameters currently stored in the Lev Config object
     *
     * @param string $name The name of the theme whose config it should store.
     * @return bool
     */
    public static function saveConfig($name)
    {
        if (!$name) {
            return false;
        }

        $lev = Lev::instance();

        /** @var UniformResourceLocator $locator */
        $locator = $lev['locator'];

        $filename = 'config://themes/' . $name . '.yaml';
        $file = YamlFile::instance((string)$locator->findResource($filename, true, true));
        $content = $lev['config']->get('themes.' . $name);
        $file->save($content);
        $file->free();
        unset($file);

        return true;
    }

    /**
     * Load blueprints.
     *
     * @return void
     */
    protected function loadBlueprint()
    {
        if (!$this->blueprint) {
            $lev = Lev::instance();
            /** @var Themes $themes */
            $themes = $lev['themes'];
            $data = $themes->get($this->name);
            \assert($data !== null);
            $this->blueprint = $data->blueprints();
        }
    }
}
