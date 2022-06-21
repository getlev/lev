<?php

/**
 * @package    Lev\Grav\Console\Cli
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Cli;

use Lev\Common\Cache;
use Lev\Console\LevCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ClearCacheCommand
 * @package Lev\Console\Cli
 */
class ClearCacheCommand extends LevCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('cache')
            ->setAliases(['clearcache', 'cache-clear'])
            ->setDescription('Clears Lev cache')
            ->addOption('invalidate', null, InputOption::VALUE_NONE, 'Invalidate cache, but do not remove any files')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'If set purge old caches')
            ->addOption('all', null, InputOption::VALUE_NONE, 'If set will remove all including compiled, twig, doctrine caches')
            ->addOption('assets-only', null, InputOption::VALUE_NONE, 'If set will remove only assets/*')
            ->addOption('images-only', null, InputOption::VALUE_NONE, 'If set will remove only images/*')
            ->addOption('cache-only', null, InputOption::VALUE_NONE, 'If set will remove only cache/*')
            ->addOption('tmp-only', null, InputOption::VALUE_NONE, 'If set will remove only tmp/*')

            ->setHelp('The <info>cache</info> command allows you to interact with Lev cache');
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        // Old versions of Lev called this command after lev upgrade.
        // We need make this command to work with older LevCommand instance:
        if (!method_exists($this, 'initializePlugins')) {
            Cache::clearCache('all');

            return 0;
        }

        $this->initializePlugins();
        $this->cleanPaths();

        return 0;
    }

    /**
     * loops over the array of paths and deletes the files/folders
     *
     * @return void
     */
    private function cleanPaths(): void
    {
        $input = $this->getInput();
        $io = $this->getIO();

        $io->newLine();

        if ($input->getOption('purge')) {
            $io->writeln('<magenta>Purging old cache</magenta>');
            $io->newLine();

            $msg = Cache::purgeJob();
            $io->writeln($msg);
        } else {
            $io->writeln('<magenta>Clearing cache</magenta>');
            $io->newLine();

            if ($input->getOption('all')) {
                $remove = 'all';
            } elseif ($input->getOption('assets-only')) {
                $remove = 'assets-only';
            } elseif ($input->getOption('images-only')) {
                $remove = 'images-only';
            } elseif ($input->getOption('cache-only')) {
                $remove = 'cache-only';
            } elseif ($input->getOption('tmp-only')) {
                $remove = 'tmp-only';
            } elseif ($input->getOption('invalidate')) {
                $remove = 'invalidate';
            } else {
                $remove = 'standard';
            }

            foreach (Cache::clearCache($remove) as $result) {
                $io->writeln($result);
            }
        }
    }
}
