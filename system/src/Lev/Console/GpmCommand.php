<?php

/**
 * @package    Lev\Grav\Console
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console;

use Lev\Common\Config\Config;
use Lev\Common\Lev;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleCommand
 * @package Lev\Console
 */
class GpmCommand extends Command
{
    use ConsoleTrait;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConsole($input, $output);

        $lev = Lev::instance();
        $lev['config']->init();
        $lev['uri']->init();
        // @phpstan-ignore-next-line
        $lev['accounts'];

        return $this->serve();
    }

    /**
     * Override with your implementation.
     *
     * @return int
     */
    protected function serve()
    {
        // Return error.
        return 1;
    }

    /**
     * @return void
     */
    protected function displayGPMRelease()
    {
        /** @var Config $config */
        $config = Lev::instance()['config'];

        $io = $this->getIO();
        $io->newLine();
        $io->writeln('GPM Releases Configuration: <yellow>' . ucfirst($config->get('system.gpm.releases')) . '</yellow>');
        $io->newLine();
    }
}
