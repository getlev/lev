<?php

/**
 * @package    Lev\Grav\Console\Plugin
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Plugin;

use Lev\Common\Filesystem\Folder;
use Lev\Common\Plugins;
use Lev\Console\ConsoleCommand;

/**
 * Class InfoCommand
 * @package Lev\Console\Gpm
 */
class PluginListCommand extends ConsoleCommand
{
    protected static $defaultName = 'plugins:list';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setHidden(true);
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $bin = $this->argv;
        $pattern = '([A-Z]\w+Command\.php)';

        $io = $this->getIO();
        $io->newLine();
        $io->writeln('<red>Usage:</red>');
        $io->writeln("  {$bin} [slug] [command] [arguments]");
        $io->newLine();
        $io->writeln('<red>Example:</red>');
        $io->writeln("  {$bin} error log -l 1 --trace");
        $io->newLine();
        $io->writeln('<red>Plugins with CLI available:</red>');

        $plugins = Plugins::all();
        $index = 0;
        foreach ($plugins as $name => $plugin) {
            if (!$plugin->enabled) {
                continue;
            }

            $list = Folder::all("plugins://{$name}", ['compare' => 'Pathname', 'pattern' => '/\/cli\/' . $pattern . '$/usm', 'levels' => 1]);
            if (!$list) {
                continue;
            }

            $index++;
            $num = str_pad((string)$index, 2, '0', STR_PAD_LEFT);
            $io->writeln('  ' . $num . '. <red>' . str_pad($name, 15) . "</red> <white>{$bin} {$name} list</white>");
        }

        return 0;
    }
}
