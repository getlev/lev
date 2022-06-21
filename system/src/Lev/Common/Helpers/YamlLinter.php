<?php

/**
 * @package    Lev\Grav\Common\Helpers
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Helpers;

use Exception;
use Lev\Common\Lev;
use Lev\Common\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RocketTheme\Toolbox\File\MarkdownFile;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlLinter
 * @package Lev\Common\Helpers
 */
class YamlLinter
{
    /**
     * @param string|null $folder
     * @return array
     */
    public static function lint(string $folder = null)
    {
        if (null !== $folder) {
            $folder = $folder ?: LEV_SITE_DIR;

            return static::recurseFolder($folder);
        }

        return array_merge(static::lintConfig(), static::lintPages(), static::lintBlueprints());
    }

    /**
     * @return array
     */
    public static function lintPages()
    {
        return static::recurseFolder('page://');
    }

    /**
     * @return array
     */
    public static function lintConfig()
    {
        return static::recurseFolder('config://');
    }

    /**
     * @return array
     */
    public static function lintBlueprints()
    {
        /** @var UniformResourceLocator $locator */
        $locator = Lev::instance()['locator'];

        $current_theme = Lev::instance()['config']->get('system.pages.theme');
        $theme_path = 'themes://' . $current_theme . '/blueprints';

        $locator->addPath('blueprints', '', [$theme_path]);
        return static::recurseFolder('blueprints://');
    }

    /**
     * @param string $path
     * @param string $extensions
     * @return array
     */
    public static function recurseFolder($path, $extensions = '(md|yaml)')
    {
        $lint_errors = [];

        /** @var UniformResourceLocator $locator */
        $locator = Lev::instance()['locator'];
        $flags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        if ($locator->isStream($path)) {
            $directory = $locator->getRecursiveIterator($path, $flags);
        } else {
            $directory = new RecursiveDirectoryIterator($path, $flags);
        }
        $recursive = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        $iterator = new RegexIterator($recursive, '/^.+\.'.$extensions.'$/ui');

        /** @var RecursiveDirectoryIterator $file */
        foreach ($iterator as $filepath => $file) {
            try {
                Yaml::parse(static::extractYaml($filepath));
            } catch (Exception $e) {
                $lint_errors[str_replace(LEV_SITE_DIR, '', $filepath)] = $e->getMessage();
            }
        }

        return $lint_errors;
    }

    /**
     * @param string $path
     * @return string
     */
    protected static function extractYaml($path)
    {
        $extension = Utils::pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'md') {
            $file = MarkdownFile::instance($path);
            $contents = $file->frontmatter();
            $file->free();
        } else {
            $contents = file_get_contents($path);
        }
        return $contents;
    }
}
