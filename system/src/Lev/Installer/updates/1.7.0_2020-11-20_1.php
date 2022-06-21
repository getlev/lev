<?php

use Lev\Installer\InstallException;
use Lev\Installer\VersionUpdate;
use Lev\Installer\YamlUpdater;

return [
    'preflight' => null,
    'postflight' =>
        function () {
            /** @var VersionUpdate $this */
            try {
                // Keep old defaults for backwards compatibility.
                $yaml = YamlUpdater::instance(LEV_SITE_DIR . '/user/config/system.yaml');
                $yaml->define('twig.autoescape', false);
                $yaml->define('strict_mode.yaml_compat', true);
                $yaml->define('strict_mode.twig_compat', true);
                $yaml->define('strict_mode.blueprint_compat', true);
                $yaml->save();
            } catch (\Exception $e) {
                throw new InstallException('Could not update system configuration to maintain backwards compatibility', $e);
            }
        }
];
