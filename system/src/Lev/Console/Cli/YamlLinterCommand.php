<?php

/**
 * @package    Lev\Grav\Console\Cli
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Cli;

use Lev\Common\Helpers\YamlLinter;
use Lev\Console\LevCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class YamlLinterCommand
 * @package Lev\Console\Cli
 */
class YamlLinterCommand extends LevCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('yamllinter')
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Go through the whole Lev installation'
            )
            ->addOption(
                'folder',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Go through specific folder'
            )
            ->setDescription('Checks various files for YAML errors')
            ->setHelp('Checks various files for YAML errors');
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $input = $this->getInput();
        $io = $this->getIO();

        $io->title('Yaml Linter');

        $error = 0;
        if ($input->getOption('all')) {
            $io->section('All');
            $errors = YamlLinter::lint('');

            if (empty($errors)) {
                $io->success('No YAML Linting issues found');
            } else {
                $error = 1;
                $this->displayErrors($errors, $io);
            }
        } elseif ($folder = $input->getOption('folder')) {
            $io->section($folder);
            $errors = YamlLinter::lint($folder);

            if (empty($errors)) {
                $io->success('No YAML Linting issues found');
            } else {
                $error = 1;
                $this->displayErrors($errors, $io);
            }
        } else {
            $io->section('User Configuration');
            $errors = YamlLinter::lintConfig();

            if (empty($errors)) {
                $io->success('No YAML Linting issues with configuration');
            } else {
                $error = 1;
                $this->displayErrors($errors, $io);
            }

            $io->section('Pages Frontmatter');
            $errors = YamlLinter::lintPages();

            if (empty($errors)) {
                $io->success('No YAML Linting issues with pages');
            } else {
                $error = 1;
                $this->displayErrors($errors, $io);
            }

            $io->section('Page Blueprints');
            $errors = YamlLinter::lintBlueprints();

            if (empty($errors)) {
                $io->success('No YAML Linting issues with blueprints');
            } else {
                $error = 1;
                $this->displayErrors($errors, $io);
            }
        }

        return $error;
    }

    /**
     * @param array $errors
     * @param SymfonyStyle $io
     * @return void
     */
    protected function displayErrors(array $errors, SymfonyStyle $io): void
    {
        $io->error('YAML Linting issues found...');
        foreach ($errors as $path => $error) {
            $io->writeln("<yellow>{$path}</yellow> - {$error}");
        }
    }
}
