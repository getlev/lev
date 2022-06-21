<?php
// Deprecated.

require_once __DIR__ . '/../classes/plugin/Twig/AdminTwigExtension.php';

user_error("require_once('user/plugins/admin/twig/AdminTwigExtension.php') is deprecated. All admin classes are using composer autoloader.", E_USER_DEPRECATED);
class_alias('Lev\Plugin\Admin\Twig\AdminTwigExtension', 'Lev\Plugin\Admin\AdminTwigExtension');
