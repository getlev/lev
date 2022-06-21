<?php

/**
 * @package    Lev\Grav\Console\Cli
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Cli;

use Lev\Console\LevCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ComposerCommand
 * @package Lev\Console\Cli
 */
class ComposerCommand extends LevCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('composer')
            ->addOption(
                'install',
                'i',
                InputOption::VALUE_NONE,
                'install the dependencies'
            )
            ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'update the dependencies'
            )
            ->setDescription('Updates the composer vendor dependencies needed by Lev.')
            ->setHelp('The <info>composer</info> command updates the composer vendor dependencies needed by Lev');
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $input = $this->getInput();
        $io = $this->getIO();

        $action = $input->getOption('install') ? 'install' : ($input->getOption('update') ? 'update' : 'install');

        if ($input->getOption('install')) {
            $action = 'install';
        }

        // Updates composer first
        $io->writeln("\nInstalling vendor dependencies");
        $io->writeln($this->composerUpdate(LEV_SITE_DIR, $action));

        return 0;
    }
}
