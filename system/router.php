<?php

/**
 * @package    Lev\Grav\Core
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

if (PHP_SAPI !== 'cli-server') {
    die('This script cannot be run from browser. Run it from a CLI.');
}

$_SERVER['PHP_CLI_ROUTER'] = true;

$root = $_SERVER['DOCUMENT_ROOT'];
$path = $_SERVER['SCRIPT_NAME'];
if ($path !== '/index.php' && is_file($root . $path)) {
    if (!(
        // Block all direct access to files and folders beginning with a dot
        strpos($path, '/.') !== false
        // Block all direct access for these folders
        || preg_match('`^/(\.git|cache|bin|logs|backup|webserver-configs|tests)/`ui', $path)
        // Block access to specific file types for these system folders
        || preg_match('`^/(system|vendor)/(.*)\.(txt|xml|md|html|json|yaml|yml|php|pl|py|cgi|twig|sh|bat)$`ui', $path)
        // Block access to specific file types for these user folders
        || preg_match('`^/(user)/(.*)\.(txt|md|json|yaml|yml|php|pl|py|cgi|twig|sh|bat)$`ui', $path)
        // Block all direct access to .md files
        || preg_match('`\.md$`ui', $path)
        // Block access to specific files in the root folder
        || preg_match('`^/(LICENSE\.txt|composer\.lock|composer\.json|\.htaccess)$`ui', $path)
    )) {
        return false;
    }
}

$lev_index = 'index.php';

/* Check the LEV_BASEDIR environment variable and use if set */

$lev_basedir = getenv('LEV_BASEDIR') ?: '';
if ($lev_basedir) {
    $lev_index = ltrim($lev_basedir, '/') . DIRECTORY_SEPARATOR . $lev_index;
    $lev_basedir = DIRECTORY_SEPARATOR . trim($lev_basedir, DIRECTORY_SEPARATOR);
    define('LEV_SITE_DIR', str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . $lev_basedir);
}

$_SERVER = array_merge($_SERVER, $_ENV);
$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . $lev_basedir .DIRECTORY_SEPARATOR . 'index.php';
$_SERVER['SCRIPT_NAME'] = $lev_basedir . DIRECTORY_SEPARATOR . 'index.php';
$_SERVER['PHP_SELF'] = $lev_basedir . DIRECTORY_SEPARATOR . 'index.php';

error_log(sprintf('%s:%d [%d]: %s', $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], http_response_code(), $_SERVER['REQUEST_URI']), 4);

require $lev_index;
