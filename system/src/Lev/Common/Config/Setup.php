<?php

/**
 * @package    Lev\Grav\Common\Config
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Config;

use BadMethodCallException;
use Lev\Common\File\CompiledYamlFile;
use Lev\Common\Data\Data;
use Lev\Common\Utils;
use InvalidArgumentException;
use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RuntimeException;
use function defined;
use function is_array;

/**
 * Class Setup
 * @package Lev\Common\Config
 */
class Setup extends Data
{
    /**
     * @var array Environment aliases normalized to lower case.
     */
    public static $environments = [
        '' => 'unknown',
        '127.0.0.1' => 'localhost',
        '::1' => 'localhost'
    ];

    /**
     * @var string|null Current environment normalized to lower case.
     */
    public static $environment;

    /** @var string */
    public static $securityFile = 'config://security.yaml';

    /** @var array */
    protected $streams = [
        'site' => [
            'paths' => '',  // Set in constructor
        ],
        'host' => [
            'paths' => '',  // Set in constructor - !!! Relative-only path from site to host !!!
        ],

        // Host streams
        'system' => [
            'paths' => ['' => 'host://' . LEV_SYSTEM_PATH],
        ],
        'plugins' => [
            'paths' => ['site://plugins', 'host://plugins'],
        ],
        'themes' => [
            'paths' => ['site://themes', 'host://themes'],
        ],

        // Site streams
        'app' => [
            'paths' => ['' => 'site://' . LEV_APP_PATH],
            'force' => true,
        ],
        'environment' => [
            // If not defined, environment will be set up in the constructor.
        ],
        'cache' => [
            'prefixes' => ['' => LEV_CACHE_PATH, 'images' => 'images'],
            'type' => 'Stream',
            'force' => true,
        ],
        'log' => [
            'prefixes' => ['' => LEV_LOG_PATH],
            'type' => 'Stream',
            'force' => true,
        ],
        'tmp' => [
            'prefixes' => ['' => LEV_TMP_PATH],
            'type' => 'Stream',
            'force' => true,
        ],
        'backup' => [
            'prefixes' => ['' => LEV_BACKUP_PATH],
            'type' => 'Stream',
            'force' => true,
        ],
        'asset' => [
            'prefixes' => ['' => 'assets'],
            'type' => 'Stream',
        ],
        'blueprints' => [
            'prefixes' => ['' => ['environment://blueprints', 'app://blueprints', 'system://blueprints']],
        ],
        'config' => [
            'prefixes' => ['' => ['environment://config', 'app://config', 'system://config']],
        ],
        'languages' => [
            'prefixes' => ['environment://languages', 'app://languages', 'system://languages'],
        ],
        'image' => [
            'prefixes' => ['' => ['app://images', 'system://images']],
            'type' => 'Stream',
        ],
        'page' => [
            'prefixes' => ['' => 'app://pages'],
        ],
        'user-data' => [
            'prefixes' => ['' => 'app://data'],
            'type' => 'Stream',
            'force' => true,
        ],
        'account' => [
            'prefixes' => ['' => 'app://accounts'],
        ],
    ];

    /**
     * @param Container|array $container
     */
    public function __construct($container)
    {
        // Set host path as rel path from site to host - Grav's `Locator` does not support abs paths...
        $this->streams['host']['paths'] = $this->getRelPath(LEV_SITE_DIR, LEV_HOST_DIR);

        // If environment is not set, look for the environment variable and then the constant.
        $environment = static::$environment ??
            (defined('LEV_ENVIRONMENT') ? LEV_ENVIRONMENT : (getenv('LEV_ENVIRONMENT') ?: null));

        // If no environment is set, make sure we get one (CLI or hostname).
        if (null === $environment) {
            if (defined('LEV_CLI')) {
                $environment = 'cli';
            } else {
                /** @var ServerRequestInterface $request */
                $request = $container['request'];
                $host = $request->getUri()->getHost();

                $environment = Utils::substrToString($host, ':');
            }
        }

        // Resolve server aliases to the proper environment.
        static::$environment = static::$environments[$environment] ?? $environment;

        // Pre-load setup.php which contains our initial configuration.
        // Configuration may contain dynamic parts, which is why we need to always load it.
        // If LEV_SETUP_PATH has been defined, use it, otherwise use defaults.
        $setupFile = defined('LEV_SETUP_PATH') ? LEV_SETUP_PATH : (getenv('LEV_SETUP_PATH') ?: null);
        if (null !== $setupFile) {
            // Make sure that the custom setup file exists. Terminates the script if not.
            if (!str_starts_with($setupFile, '/')) {
                $setupFile = LEV_SITE_DIR . '/' . $setupFile;
            }
            if (!is_file($setupFile)) {
                echo 'LEV_SETUP_PATH is defined but does not point to existing setup file.';
                exit(1);
            }
        } else {
            $setupFile = LEV_SITE_DIR . '/setup.php';
            if (!is_file($setupFile)) {
                $setupFile = LEV_SITE_DIR . '/' . LEV_APP_PATH . '/setup.php';
            }
            if (!is_file($setupFile)) {
                $setupFile = null;
            }
        }
        $setup = $setupFile ? (array) include $setupFile : [];

        // Add default streams defined in beginning of the class.
        if (!isset($setup['streams']['schemes'])) {
            $setup['streams']['schemes'] = [];
        }
        $setup['streams']['schemes'] += $this->streams;

        // Initialize class.
        parent::__construct($setup);

        $this->def('environment', static::$environment);

        // Figure out path for the current environment.
        $envPath = defined('LEV_ENVIRONMENT_PATH') ? LEV_ENVIRONMENT_PATH : (getenv('LEV_ENVIRONMENT_PATH') ?: null);
        if (null === $envPath) {
            // Find common path for all environments and append current environment into it.
            $envPath = defined('LEV_ENVIRONMENTS_PATH') ? LEV_ENVIRONMENTS_PATH : (getenv('LEV_ENVIRONMENTS_PATH') ?: null);
            if (null !== $envPath) {
                $envPath .= '/';
            } else {
                // Use default location. Start with Lev 1.7 default.
                $envPath = LEV_SITE_DIR. '/' . LEV_APP_PATH . '/env';
                if (is_dir($envPath)) {
                    $envPath = 'app://env/';
                } else {
                    // Fallback to Lev 1.6 default.
                    $envPath = 'app://';
                }
            }
            $envPath .= $this->get('environment');
        }

        // Set up environment.
        $this->def('environment', static::$environment);
        $this->def('streams.schemes.environment.prefixes', ['' => [$envPath]]);
    }

	/**
	 * Make relative path from given first path to second
	 * Both paths should exist - they will be canonicalized by realpath().
     *
     * Grav has similar func Grav\Common\Filesystem\Folder::getRelativePathDotDot()
     * But it don't used at all and seemed to be incorrect...
	 *
	 * @param  string $path1 Path from
	 * @param  string $path2 Path to
	 * @return string Relative path from path1 to path2
	 */
	public function getRelPath(string $path1, string $path2): string
	{
		if (!($real1 = realpath($path1))) {
			throw new RuntimeException(__METHOD__ . "(): First path `$path1` does not exist");
		}
        $real1 = str_replace(DIRECTORY_SEPARATOR, '/', $real1);

        if (!($real2 = realpath($path2))) {
			throw new RuntimeException(__METHOD__ . "(): Second path `$path2` does not exist");
		}
		$real2 = str_replace(DIRECTORY_SEPARATOR, '/', $real2);

        if ($real1 == $real2) {
			return '';
		}

		$segments1 = $real1 ? explode('/', $real1) : [];
		$segments2 = $real2 ? explode('/', $real2) : [];

		// Detect common starting segments count
		$i = 0;
		foreach ($segments1 as $i => $path) {
			if (!isset($segments2[$i]) or $path != $segments2[$i]) {
				break;
			}
		}

		$up = str_repeat('../', count($segments1) - $i);
		$dn = implode('/', array_slice($segments2, $i));

		return  empty($dn) ? rtrim($up, '/') : $up . $dn;
	}

    /**
     * @return $this
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function init()
    {
        $locator = new UniformResourceLocator(LEV_SITE_DIR);
        $files = [];

        $guard = 5;
        do {
            $check = $files;
            $this->initializeLocator($locator);
            $files = $locator->findResources('config://streams.yaml');

            if ($check === $files) {
                break;
            }

            // Update streams.
            foreach (array_reverse($files) as $path) {
                $file = CompiledYamlFile::instance($path);
                $content = (array)$file->content();
                if (!empty($content['schemes'])) {
                    $this->items['streams']['schemes'] = $content['schemes'] + $this->items['streams']['schemes'];
                }
            }
        } while (--$guard);

        if (!$guard) {
            throw new RuntimeException('Setup: Configuration reload loop detected!');
        }

        // Make sure we have valid setup.
        $this->check($locator);

        return $this;
    }

    /**
     * Initialize resource locator by using the configuration.
     *
     * @param UniformResourceLocator $locator
     * @return void
     * @throws BadMethodCallException
     */
    public function initializeLocator(UniformResourceLocator $locator)
    {
        $locator->reset();

        $schemes = (array) $this->get('streams.schemes', []);

        foreach ($schemes as $scheme => $config) {
            if (isset($config['paths'])) {
                $locator->addPath($scheme, '', $config['paths']);
            }

            $override = $config['override'] ?? false;
            $force = $config['force'] ?? false;

            if (isset($config['prefixes'])) {
                foreach ((array)$config['prefixes'] as $prefix => $paths) {
                    $locator->addPath($scheme, $prefix, $paths, $override, $force);
                }
            }
        }
    }

    /**
     * Get available streams and their types from the configuration.
     *
     * @return array
     */
    public function getStreams()
    {
        $schemes = [];
        foreach ((array) $this->get('streams.schemes') as $scheme => $config) {
            $type = $config['type'] ?? 'ReadOnlyStream';
            if ($type[0] !== '\\') {
                $type = '\\RocketTheme\\Toolbox\\StreamWrapper\\' . $type;
            }

            $schemes[$scheme] = $type;
        }

        return $schemes;
    }

    /**
     * @param UniformResourceLocator $locator
     * @return void
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    protected function check(UniformResourceLocator $locator)
    {
        $streams = $this->items['streams']['schemes'] ?? null;
        if (!is_array($streams)) {
            throw new InvalidArgumentException('Configuration is missing streams.schemes!');
        }
        $diff = array_keys(array_diff_key($this->streams, $streams));
        if ($diff) {
            throw new InvalidArgumentException(
                sprintf('Configuration is missing keys %s from streams.schemes!', implode(', ', $diff))
            );
        }

        try {
            // If environment is found, remove all missing override locations (B/C compatibility).
            if ($locator->findResource('environment://', true)) {
                $force = $this->get('streams.schemes.environment.force', false);
                if (!$force) {
                    $prefixes = $this->get('streams.schemes.environment.prefixes.');
                    $update = false;
                    foreach ($prefixes as $i => $prefix) {
                        if ($locator->isStream($prefix)) {
                            if ($locator->findResource($prefix, true)) {
                                break;
                            }
                        } elseif (file_exists($prefix)) {
                            break;
                        }

                        unset($prefixes[$i]);
                        $update = true;
                    }

                    if ($update) {
                        $this->set('streams.schemes.environment.prefixes', ['' => array_values($prefixes)]);
                        $this->initializeLocator($locator);
                    }
                }
            }

            if (!$locator->findResource('environment://config', true)) {
                // If environment does not have its own directory, remove it from the lookup.
                $prefixes = $this->get('streams.schemes.environment.prefixes');
                $prefixes['config'] = [];

                $this->set('streams.schemes.environment.prefixes', $prefixes);
                $this->initializeLocator($locator);
            }

            // Create security.yaml salt if it doesn't exist into existing configuration environment if possible.
            $securityFile = Utils::basename(static::$securityFile);
            $securityFolder = substr(static::$securityFile, 0, -\strlen($securityFile));
            $securityFolder = $locator->findResource($securityFolder, true) ?: $locator->findResource($securityFolder, true, true);
            $filename = "{$securityFolder}/{$securityFile}";

            $security_file = CompiledYamlFile::instance($filename);
            $security_content = (array)$security_file->content();

            if (!isset($security_content['salt'])) {
                $security_content = array_merge($security_content, ['salt' => Utils::generateRandomString(14)]);
                $security_file->content($security_content);
                $security_file->save();
                $security_file->free();
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf('Lev failed to initialize: %s', $e->getMessage()), 500, $e);
        }
    }
}
