<?php

/**
 * @package    Lev\Grav\Console
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Application;

use Lev\Console\Cli\BackupCommand;
use Lev\Console\Cli\CleanCommand;
use Lev\Console\Cli\ClearCacheCommand;
use Lev\Console\Cli\ComposerCommand;
use Lev\Console\Cli\InstallCommand;
use Lev\Console\Cli\LogViewerCommand;
use Lev\Console\Cli\NewProjectCommand;
use Lev\Console\Cli\PageSystemValidatorCommand;
use Lev\Console\Cli\SandboxCommand;
use Lev\Console\Cli\SchedulerCommand;
use Lev\Console\Cli\SecurityCommand;
use Lev\Console\Cli\ServerCommand;
use Lev\Console\Cli\YamlLinterCommand;

/**
 * Class LevApplication
 * @package Lev\Console\Application
 */
class LevApplication extends Application
{
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->addCommands([
            new InstallCommand(),
            new ComposerCommand(),
            new SandboxCommand(),
            new CleanCommand(),
            new ClearCacheCommand(),
            new BackupCommand(),
            new NewProjectCommand(),
            new SchedulerCommand(),
            new SecurityCommand(),
            new LogViewerCommand(),
            new YamlLinterCommand(),
            new ServerCommand(),
            new PageSystemValidatorCommand(),
        ]);
    }
}
