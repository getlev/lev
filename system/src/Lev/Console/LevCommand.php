<?php

/**
 * @package    Lev\Grav\Console
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleCommand
 * @package Lev\Console
 */
class LevCommand extends Command
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

        // Old versions of Lev called this command after lev upgrade.
        // We need make this command to work with older ConsoleTrait:
        if (method_exists($this, 'initializeLev')) {
            $this->initializeLev();
        }

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
}
