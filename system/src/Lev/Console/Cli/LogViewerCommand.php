<?php

/**
 * @package    Lev\Grav\Console\Cli
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Cli;

use DateTime;
use Lev\Common\Lev;
use Lev\Common\Helpers\LogViewer;
use Lev\Console\LevCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class LogViewerCommand
 * @package Lev\Console\Cli
 */
class LogViewerCommand extends LevCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('logviewer')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'custom log file location (default = lev.log)'
            )
            ->addOption(
                'lines',
                'l',
                InputOption::VALUE_OPTIONAL,
                'number of lines (default = 10)'
            )
            ->setDescription('Display the last few entries of Lev log')
            ->setHelp('Display the last few entries of Lev log');
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $input = $this->getInput();
        $io = $this->getIO();

        $file = $input->getOption('file') ?? 'lev.log';
        $lines = $input->getOption('lines') ?? 20;
        $verbose = $input->getOption('verbose') ?? false;

        $io->title('Log Viewer');

        $io->writeln(sprintf('viewing last %s entries in <white>%s</white>', $lines, $file));
        $io->newLine();

        $viewer = new LogViewer();

        $lev = Lev::instance();

        $logfile = $lev['locator']->findResource('log://' . $file);
        if (!$logfile) {
            $io->error('cannot find the log file: logs/' . $file);

            return 1;
        }

        $rows = $viewer->objectTail($logfile, $lines, true);
        foreach ($rows as $log) {
            $date = $log['date'];
            $level_color = LogViewer::levelColor($log['level']);

            if ($date instanceof DateTime) {
                $output = "<yellow>{$log['date']->format('Y-m-d h:i:s')}</yellow> [<{$level_color}>{$log['level']}</{$level_color}>]";
                if ($log['trace'] && $verbose) {
                    $output .= " <white>{$log['message']}</white>\n";
                    foreach ((array) $log['trace'] as $index => $tracerow) {
                        $output .= "<white>{$index}</white>${tracerow}\n";
                    }
                } else {
                    $output .= " {$log['message']}";
                }
                $io->writeln($output);
            }
        }

        return 0;
    }
}
