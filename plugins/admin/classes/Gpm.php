<?php

namespace Lev\Plugin\Admin;

use Lev\Common\Cache;
use Lev\Common\Lev;
use Lev\Common\GPM\GPM as LevGPM;
use Lev\Common\GPM\Licenses;
use Lev\Common\GPM\Installer;
use Lev\Common\GPM\Upgrader;
use Lev\Common\HTTP\Response;
use Lev\Common\Filesystem\Folder;
use Lev\Common\GPM\Common\Package;

/**
 * Class Gpm
 *
 * @package Lev\Grav\Plugin\Admin
 */
class Gpm
{
    // Probably should move this to Lev DI container?
    /** @var LevGPM */
    protected static $GPM;

    public static function GPM()
    {
        if (!static::$GPM) {
            static::$GPM = new LevGPM();
        }

        return static::$GPM;
    }

    /**
     * Default options for the install
     *
     * @var array
     */
    protected static $options = [
        'destination'     => LEV_SITE_DIR,
        'overwrite'       => true,
        'ignore_symlinks' => true,
        'skip_invalid'    => true,
        'install_deps'    => true,
        'theme'           => false
    ];

    /**
     * @param Package[]|string[]|string $packages
     * @param array                     $options
     *
     * @return string|bool
     */
    public static function install($packages, array $options)
    {
        $options = array_merge(self::$options, $options);

        if (!Installer::isLevInstance($options['destination']) || !Installer::isValidDestination($options['destination'],
                [Installer::EXISTS, Installer::IS_LINK])
        ) {
            return false;
        }

        $packages = is_array($packages) ? $packages : [$packages];
        $count    = count($packages);

        $packages = array_filter(array_map(function ($p) {
            return !is_string($p) ? $p instanceof Package ? $p : false : self::GPM()->findPackage($p);
        }, $packages));

        if (!$options['skip_invalid'] && $count !== count($packages)) {
            return false;
        }

        $messages = '';

        foreach ($packages as $package) {
            if (isset($package->dependencies) && $options['install_deps']) {
                $result = static::install($package->dependencies, $options);

                if (!$result) {
                    return false;
                }
            }

            // Check destination
            Installer::isValidDestination($options['destination'] . '/' . $package->install_path);

            if (!$options['overwrite'] && Installer::lastErrorCode() === Installer::EXISTS) {
                return false;
            }

            if (!$options['ignore_symlinks'] && Installer::lastErrorCode() === Installer::IS_LINK) {
                return false;
            }

            $license = Licenses::get($package->slug);
            $local   = static::download($package, $license);

            Installer::install($local, $options['destination'],
                ['install_path' => $package->install_path, 'theme' => $options['theme']]);
            Folder::delete(dirname($local));

            $errorCode = Installer::lastErrorCode();
            if ($errorCode) {
                $msg = Installer::lastErrorMsg();
                throw new \RuntimeException($msg);
            }

            if (count($packages) === 1) {
                $message = Installer::getMessage();
                if ($message) {
                    return $message;
                }

                $messages .= $message;
            }
        }

        Cache::clearCache();

        return $messages ?: true;
    }

    /**
     * @param Package[]|string[]|string $packages
     * @param array                     $options
     *
     * @return string|bool
     */
    public static function update($packages, array $options)
    {
        $options['overwrite'] = true;

        return static::install($packages, $options);
    }

    /**
     * @param Package[]|string[]|string $packages
     * @param array                     $options
     *
     * @return string|bool
     */
    public static function uninstall($packages, array $options)
    {
        $options = array_merge(self::$options, $options);

        $packages = (array)$packages;
        $count    = count($packages);

        $packages = array_filter(array_map(function ($p) {

            if (is_string($p)) {
                $p      = strtolower($p);
                $plugin = static::GPM()->getInstalledPlugin($p);
                $p      = $plugin ?: static::GPM()->getInstalledTheme($p);
            }

            return $p instanceof Package ? $p : false;

        }, $packages));

        if (!$options['skip_invalid'] && $count !== count($packages)) {
            return false;
        }

        foreach ($packages as $package) {

            $location = Lev::instance()['locator']->findResource($package->package_type . '://' . $package->slug);

            // Check destination
            Installer::isValidDestination($location);

            if (!$options['ignore_symlinks'] && Installer::lastErrorCode() === Installer::IS_LINK) {
                return false;
            }

            Installer::uninstall($location);

            $errorCode = Installer::lastErrorCode();
            if ($errorCode && $errorCode !== Installer::IS_LINK && $errorCode !== Installer::EXISTS) {
                $msg = Installer::lastErrorMsg();
                throw new \RuntimeException($msg);
            }

            if (count($packages) === 1) {
                $message = Installer::getMessage();
                if ($message) {
                    return $message;
                }
            }
        }

        Cache::clearCache();

        return true;
    }

    /**
     * Direct install a file
     *
     * @param string $package_file
     *
     * @return string|bool
     */
    public static function directInstall($package_file)
    {
        if (!$package_file) {
            return Admin::translate('PLUGIN_ADMIN.NO_PACKAGE_NAME');
        }

        $tmp_dir = Lev::instance()['locator']->findResource('tmp://', true, true);
        $tmp_zip = $tmp_dir . '/Lev-' . uniqid('', false);

        if (Response::isRemote($package_file)) {
            $zip = LevGPM::downloadPackage($package_file, $tmp_zip);
        } else {
            $zip = LevGPM::copyPackage($package_file, $tmp_zip);
        }

        if (file_exists($zip)) {
            $tmp_source = $tmp_dir . '/Lev-' . uniqid('', false);
            $extracted  = Installer::unZip($zip, $tmp_source);

            if (!$extracted) {
                Folder::delete($tmp_source);
                Folder::delete($tmp_zip);
                return Admin::translate('PLUGIN_ADMIN.PACKAGE_EXTRACTION_FAILED');
            }

            $type = LevGPM::getPackageType($extracted);

            if (!$type) {
                Folder::delete($tmp_source);
                Folder::delete($tmp_zip);
                return Admin::translate('PLUGIN_ADMIN.NOT_VALID_LEV_PACKAGE');
            }

            if ($type === 'lev') {
                Installer::isValidDestination(LEV_SITE_DIR . '/system');
                if (Installer::IS_LINK === Installer::lastErrorCode()) {
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);
                    return Admin::translate('PLUGIN_ADMIN.CANNOT_OVERWRITE_SYMLINKS');
                }

                static::upgradeLev($zip, $extracted);
            } else {
                $name = LevGPM::getPackageName($extracted);

                if (!$name) {
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);
                    return Admin::translate('PLUGIN_ADMIN.NAME_COULD_NOT_BE_DETERMINED');
                }

                $install_path = LevGPM::getInstallPath($type, $name);
                $is_update    = file_exists($install_path);

                Installer::isValidDestination(LEV_SITE_DIR . '/' . $install_path);
                if (Installer::lastErrorCode() === Installer::IS_LINK) {
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);
                    return Admin::translate('PLUGIN_ADMIN.CANNOT_OVERWRITE_SYMLINKS');
                }

                Installer::install($zip, LEV_SITE_DIR,
                    ['install_path' => $install_path, 'theme' => $type === 'theme', 'is_update' => $is_update],
                    $extracted);
            }

            Folder::delete($tmp_source);

            if (Installer::lastErrorCode()) {
                return Installer::lastErrorMsg();
            }

        } else {
            return Admin::translate('PLUGIN_ADMIN.ZIP_PACKAGE_NOT_FOUND');
        }

        Folder::delete($tmp_zip);
        Cache::clearCache();

        return true;
    }

    /**
     * @param Package $package
     *
     * @return string
     */
    private static function download(Package $package, $license = null)
    {
        $query = '';

        if ($package->premium) {
            $query = \json_encode(array_merge($package->premium, [
                'slug'        => $package->slug,
                'license_key' => $license,
                'sid' => md5(LEV_SITE_DIR)
            ]));

            $query = '?d=' . base64_encode($query);
        }

        try {
            $contents = Response::get($package->zipball_url . $query, []);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $tmp_dir = Admin::getTempDir() . '/Lev-' . uniqid('', false);
        Folder::mkdir($tmp_dir);

        $bad_chars = array_merge(array_map('chr', range(0, 31)), ['<', '>', ':', '"', '/', '\\', '|', '?', '*']);

        $filename = $package->slug . str_replace($bad_chars, '', \Lev\Common\Utils::basename($package->zipball_url));
        $filename = preg_replace('/[\\\\\/:"*?&<>|]+/m', '-', $filename);

        file_put_contents($tmp_dir . '/' . $filename . '.zip', $contents);

        return $tmp_dir . '/' . $filename . '.zip';
    }

    /**
     * @param array  $package
     * @param string $tmp
     *
     * @return string
     */
    private static function _downloadSelfupgrade(array $package, $tmp)
    {
        $output = Response::get($package['download'], []);
        Folder::mkdir($tmp);
        file_put_contents($tmp . '/' . $package['name'], $output);

        return $tmp . '/' . $package['name'];
    }

    /**
     * @return bool
     */
    public static function selfupgrade()
    {
        $upgrader = new Upgrader();

        if (!Installer::isLevInstance(LEV_SITE_DIR)) {
            return false;
        }

        if (is_link(LEV_SITE_DIR . '/' . 'index.php')) {
            Installer::setError(Installer::IS_LINK);

            return false;
        }

        if (method_exists($upgrader, 'meetsRequirements') &&
            method_exists($upgrader, 'minPHPVersion') &&
            !$upgrader->meetsRequirements()) {
            $error   = [];
            $error[] = '<p>Lev has increased the minimum PHP requirement.<br />';
            $error[] = 'You are currently running PHP <strong>' . phpversion() . '</strong>';
            $error[] = ', but PHP <strong>' . $upgrader->minPHPVersion() . '</strong> is required.</p>';
            $error[] = '<p><a href="https://getgrav.org/blog/changing-php-requirements-to-5.5" class="button button-small secondary">Additional information</a></p>';

            Installer::setError(implode("\n", $error));

            return false;
        }

        $update = $upgrader->getAssets()['lev-update'];
        $tmp    = Admin::getTempDir() . '/Lev-' . uniqid('', false);
        if ($tmp) {
            $file   = self::_downloadSelfupgrade($update, $tmp);
            $folder = Installer::unZip($file, $tmp . '/zip');
            $keepFolder = false;
        } else {
            // If you make $tmp empty, you can install your local copy of Lev (for testing purposes only).
            $file = 'lev.zip';
            $folder = '~/phpstorm/lev-clones/lev';
            //$folder = '/home/matias/phpstorm/rockettheme/lev-devtools/lev-clones/lev';
            $keepFolder = true;
        }

        static::upgradeLev($file, $folder, $keepFolder);

        $errorCode = Installer::lastErrorCode();

        if ($tmp) {
            Folder::delete($tmp);
        }

        return !(is_string($errorCode) || ($errorCode & (Installer::ZIP_OPEN_ERROR | Installer::ZIP_EXTRACT_ERROR)));
    }

    private static function upgradeLev($zip, $folder, $keepFolder = false)
    {
        static $ignores = [
            'backup',
            'cache',
            'images',
            'logs',
            'tmp',
            'user',
            '.htaccess',
            'robots.txt'
        ];

        if (!is_dir($folder)) {
            Installer::setError('Invalid source folder');
        }

        try {
            $script = $folder . '/system/install.php';
            /** Install $installer */
            if ((file_exists($script) && $install = include $script) && is_callable($install)) {
                $install($zip);
            } else {
                Installer::install(
                    $zip,
                    LEV_SITE_DIR,
                    ['sophisticated' => true, 'overwrite' => true, 'ignore_symlinks' => true, 'ignores' => $ignores],
                    $folder,
                    $keepFolder
                );

                Cache::clearCache();
            }
        } catch (\Exception $e) {
            Installer::setError($e->getMessage());
        }
    }
}
