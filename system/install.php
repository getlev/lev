<?php
/**
 * @package    Lev\Grav\Core
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

if (!defined('LEV_SITE_DIR')) {
    die();
}

require_once __DIR__ . '/src/Lev/Installer/Install.php';

return Lev\Installer\Install::instance();
