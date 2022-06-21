<?php

/**
 * @package    Lev\Core
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

// Some basic defines
define('LEV', true);
define('LEV_VERSION', '0.2.4');
define('LEV_TESTING', false);
define('LEV_GVERSION', '1.7.34');

// Some relative paths
foreach (['app', 'system', 'cache', 'logs', 'tmp', 'backup'] as $slug) {
    $def = 'LEV_' . strtoupper('logs' == $slug ? 'log' : $slug) . '_PATH';
    defined($def) || define($def, $slug);
}
unset($def, $slug);

// Some extensions
define('CONTENT_EXT', '.md');
define('TEMPLATE_EXT', '.html.twig');
define('TWIG_EXT', '.twig');
define('PLUGIN_EXT', '.php');
define('YAML_EXT', '.yaml');

// Content types
define('RAW_CONTENT', 1);
define('TWIG_CONTENT', 2);
define('TWIG_CONTENT_LIST', 3);
define('TWIG_TEMPLATES', 4);
