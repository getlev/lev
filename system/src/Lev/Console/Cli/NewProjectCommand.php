<?php

/**
 * @package    Lev\Grav\Console\Cli
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Cli;

use Lev\Console\LevCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class NewProjectCommand
 * @package Lev\Console\Cli
 */
class NewProjectCommand extends LevCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new-project')
            ->setAliases(['newproject'])
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
                'The destination directory of your new Lev project'
            )
            ->addOption(
                'symlink',
                's',
                InputOption::VALUE_NONE,
                'Symlink the required bits'
            )
            ->setDescription('Creates a new Lev project with all the dependencies installed')
            ->setHelp("The <info>new-project</info> command is a combination of the `setup` and `install` commands.\nCreates a new Lev instance and performs the installation of all the required dependencies.");
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $io = $this->getIO();

        $sandboxCommand = $this->getApplication()->find('sandbox');
        $installCommand = $this->getApplication()->find('install');

        $sandboxArguments = new ArrayInput([
            'command'     => 'sandbox',
            'destination' => $this->input->getArgument('destination'),
            '-s'          => $this->input->getOption('symlink')
        ]);

        $installArguments = new ArrayInput([
            'command'     => 'install',
            'destination' => $this->input->getArgument('destination'),
            '-s'          => $this->input->getOption('symlink')
        ]);

        $error = $sandboxCommand->run($sandboxArguments, $io);
        if ($error === 0) {
            $error = $installCommand->run($installArguments, $io);
        }

        return $error;
    }
}
