<?php

/**
 * @package    Lev.Host
 *
 * @copyright  Copyright (c) 2021 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev;

define('LEV_REQUEST_TIME', microtime(true));
define('LEV_PHP_MIN', '7.3.6'); // Does not used, compatibility info only

// Abs unix path to current Lev host & site.
define('LEV_HOST_DIR', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__));
define('LEV_SITE_DIR', str_replace(DIRECTORY_SEPARATOR, '/', getcwd()));

if (PHP_SAPI === 'cli-server') {
    $symfony_server = stripos(getenv('_'), 'symfony') !== false || stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'symfony') !== false || stripos($_ENV['SERVER_SOFTWARE'] ?? '', 'symfony') !== false;

    if (!isset($_SERVER['PHP_CLI_ROUTER']) && !$symfony_server) {
        die("PHP webserver requires a router to run Lev, please use: <pre>php -S {$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']} system/router.php</pre>");
    }
}

// Set timezone to default, falls back to system if php.ini not set
date_default_timezone_set(@date_default_timezone_get());

// Set internal encoding.
if (!extension_loaded('mbstring')) {
    die("'mbstring' extension is not loaded.  This is required for Lev to run correctly");
}
@ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Ensure vendor libraries exist
$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    die('Please run: <i>bin/lev install</i>');
}

// Register the auto-loader.
$loader = require $autoload;

use Error;
use Exception;
use Lev\Common\Lev;
use RocketTheme\Toolbox\Event\Event;

// Get the Lev instance
$lev = Lev::instance(['loader' => $loader]);

// Process the page
try {
    $lev->process();
} catch (Error | Exception $e) {
    $lev->fireEvent('onFatalException', new Event(['exception' => $e]));
    throw $e;
}
