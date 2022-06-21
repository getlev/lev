<?php

/**
 * @package    Lev\Grav\Console
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Application;

use Lev\Console\Gpm\DirectInstallCommand;
use Lev\Console\Gpm\IndexCommand;
use Lev\Console\Gpm\InfoCommand;
use Lev\Console\Gpm\InstallCommand;
use Lev\Console\Gpm\SelfupgradeCommand;
use Lev\Console\Gpm\UninstallCommand;
use Lev\Console\Gpm\UpdateCommand;
use Lev\Console\Gpm\VersionCommand;

/**
 * Class GpmApplication
 * @package Lev\Console\Application
 */
class GpmApplication extends Application
{
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->addCommands([
            new IndexCommand(),
            new VersionCommand(),
            new InfoCommand(),
            new InstallCommand(),
            new UninstallCommand(),
            new UpdateCommand(),
            new SelfupgradeCommand(),
            new DirectInstallCommand(),
        ]);
    }
}
