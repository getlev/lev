<?php

/**
 * @package    Lev\Grav\Console\Gpm
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Console\Gpm;

use Exception;
use Lev\Common\Lev;
use Lev\Common\Filesystem\Folder;
use Lev\Common\HTTP\Response;
use Lev\Common\GPM\GPM;
use Lev\Common\GPM\Installer;
use Lev\Console\GpmCommand;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use ZipArchive;
use function is_array;
use function is_callable;

/**
 * Class DirectInstallCommand
 * @package Lev\Console\Gpm
 */
class DirectInstallCommand extends GpmCommand
{
    /** @var string */
    protected $all_yes;
    /** @var string */
    protected $destination;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('direct-install')
            ->setAliases(['directinstall'])
            ->addArgument(
                'package-file',
                InputArgument::REQUIRED,
                'Installable package local <path> or remote <URL>. Can install specific version'
            )
            ->addOption(
                'all-yes',
                'y',
                InputOption::VALUE_NONE,
                'Assumes yes (or best approach) instead of prompting'
            )
            ->addOption(
                'destination',
                'd',
                InputOption::VALUE_OPTIONAL,
                'The destination where the package should be installed at. By default this would be where the lev instance has been launched from',
                LEV_SITE_DIR
            )
            ->setDescription('Installs Lev, plugin, or theme directly from a file or a URL')
            ->setHelp('The <info>direct-install</info> command installs Lev, plugin, or theme directly from a file or a URL');
    }

    /**
     * @return int
     */
    protected function serve(): int
    {
        $input = $this->getInput();
        $io = $this->getIO();

        if (!class_exists(ZipArchive::class)) {
            $io->title('Direct Install');
            $io->error('php-zip extension needs to be enabled!');

            return 1;
        }

        // Making sure the destination is usable
        $this->destination = realpath($input->getOption('destination'));

        if (!Installer::isLevInstance($this->destination) ||
            !Installer::isValidDestination($this->destination, [Installer::EXISTS, Installer::IS_LINK])
        ) {
            $io->writeln('<red>ERROR</red>: ' . Installer::lastErrorMsg());

            return 1;
        }

        $this->all_yes = $input->getOption('all-yes');

        $package_file = $input->getArgument('package-file');

        $question = new ConfirmationQuestion("Are you sure you want to direct-install <cyan>{$package_file}</cyan> [y|N] ", false);

        $answer = $this->all_yes ? true : $io->askQuestion($question);

        if (!$answer) {
            $io->writeln('exiting...');
            $io->newLine();

            return 1;
        }

        $tmp_dir = Lev::instance()['locator']->findResource('tmp://', true, true);
        $tmp_zip = $tmp_dir . uniqid('/Lev-', false);

        $io->newLine();
        $io->writeln("Preparing to install <cyan>{$package_file}</cyan>");

        $zip = null;
        if (Response::isRemote($package_file)) {
            $io->write('  |- Downloading package...     0%');
            try {
                $zip = GPM::downloadPackage($package_file, $tmp_zip);
            } catch (RuntimeException $e) {
                $io->newLine();
                $io->writeln("  `- <red>ERROR: {$e->getMessage()}</red>");
                $io->newLine();

                return 1;
            }

            if ($zip) {
                $io->write("\x0D");
                $io->write('  |- Downloading package...   100%');
                $io->newLine();
            }
        } elseif (is_file($package_file)) {
            $io->write('  |- Copying package...         0%');
            $zip = GPM::copyPackage($package_file, $tmp_zip);
            if ($zip) {
                $io->write("\x0D");
                $io->write('  |- Copying package...       100%');
                $io->newLine();
            }
        }

        if ($zip && file_exists($zip)) {
            $tmp_source = $tmp_dir . uniqid('/Lev-', false);

            $io->write('  |- Extracting package...    ');
            $extracted = Installer::unZip($zip, $tmp_source);

            if (!$extracted) {
                $io->write("\x0D");
                $io->writeln('  |- Extracting package...    <red>failed</red>');
                Folder::delete($tmp_source);
                Folder::delete($tmp_zip);

                return 1;
            }

            $io->write("\x0D");
            $io->writeln('  |- Extracting package...    <green>ok</green>');


            $type = GPM::getPackageType($extracted);

            if (!$type) {
                $io->writeln("  '- <red>ERROR: Not a valid Lev package</red>");
                $io->newLine();
                Folder::delete($tmp_source);
                Folder::delete($tmp_zip);

                return 1;
            }

            $blueprint = GPM::getBlueprints($extracted);
            if ($blueprint) {
                if (isset($blueprint['dependencies'])) {
                    $dependencies = [];
                    foreach ($blueprint['dependencies'] as $dependency) {
                        if (is_array($dependency)) {
                            if (isset($dependency['name'])) {
                                $dependencies[] = $dependency['name'];
                            }
                            if (isset($dependency['github'])) {
                                $dependencies[] = $dependency['github'];
                            }
                        } else {
                            $dependencies[] = $dependency;
                        }
                    }
                    $io->writeln('  |- Dependencies found...    <cyan>[' . implode(',', $dependencies) . ']</cyan>');

                    $question = new ConfirmationQuestion("  |  '- Dependencies will not be satisfied. Continue ? [y|N] ", false);
                    $answer = $this->all_yes ? true : $io->askQuestion($question);

                    if (!$answer) {
                        $io->writeln('exiting...');
                        $io->newLine();
                        Folder::delete($tmp_source);
                        Folder::delete($tmp_zip);

                        return 1;
                    }
                }
            }

            if ($type === 'lev') {
                $io->write('  |- Checking destination...  ');
                Installer::isValidDestination(LEV_SITE_DIR . '/system');
                if (Installer::IS_LINK === Installer::lastErrorCode()) {
                    $io->write("\x0D");
                    $io->writeln('  |- Checking destination...  <yellow>symbolic link</yellow>');
                    $io->writeln("  '- <red>ERROR: symlinks found...</red> <yellow>" . LEV_SITE_DIR . '</yellow>');
                    $io->newLine();
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);

                    return 1;
                }

                $io->write("\x0D");
                $io->writeln('  |- Checking destination...  <green>ok</green>');

                $io->write('  |- Installing package...  ');

                $this->upgradeLev($zip, $extracted);
            } else {
                $name = GPM::getPackageName($extracted);

                if (!$name) {
                    $io->writeln('<red>ERROR: Name could not be determined.</red> Please specify with --name|-n');
                    $io->newLine();
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);

                    return 1;
                }

                $install_path = GPM::getInstallPath($type, $name);
                $is_update = file_exists($install_path);

                $io->write('  |- Checking destination...  ');

                Installer::isValidDestination(LEV_SITE_DIR . '/' . $install_path);
                if (Installer::lastErrorCode() === Installer::IS_LINK) {
                    $io->write("\x0D");
                    $io->writeln('  |- Checking destination...  <yellow>symbolic link</yellow>');
                    $io->writeln("  '- <red>ERROR: symlink found...</red>  <yellow>" . LEV_SITE_DIR . '/' . $install_path . '</yellow>');
                    $io->newLine();
                    Folder::delete($tmp_source);
                    Folder::delete($tmp_zip);

                    return 1;
                }

                $io->write("\x0D");
                $io->writeln('  |- Checking destination...  <green>ok</green>');

                $io->write('  |- Installing package...  ');

                Installer::install(
                    $zip,
                    $this->destination,
                    $options = [
                        'install_path' => $install_path,
                        'theme' => (($type === 'theme')),
                        'is_update' => $is_update
                    ],
                    $extracted
                );

                // clear cache after successful upgrade
                $this->clearCache();
            }

            Folder::delete($tmp_source);

            $io->write("\x0D");

            if (Installer::lastErrorCode()) {
                $io->writeln("  '- <red>" . Installer::lastErrorMsg() . '</red>');
                $io->newLine();
            } else {
                $io->writeln('  |- Installing package...    <green>ok</green>');
                $io->writeln("  '- <green>Success!</green>  ");
                $io->newLine();
            }
        } else {
            $io->writeln("  '- <red>ERROR: ZIP package could not be found</red>");
            Folder::delete($tmp_zip);

            return 1;
        }

        Folder::delete($tmp_zip);

        return 0;
    }

    /**
     * @param string $zip
     * @param string $folder
     * @return void
     */
    private function upgradeLev(string $zip, string $folder): void
    {
        if (!is_dir($folder)) {
            Installer::setError('Invalid source folder');
        }

        try {
            $script = $folder . '/system/install.php';
            /** Install $installer */
            if ((file_exists($script) && $install = include $script) && is_callable($install)) {
                $install($zip);
            } else {
                throw new RuntimeException('Uploaded archive file is not a valid Lev update package');
            }
        } catch (Exception $e) {
            Installer::setError($e->getMessage());
        }
    }
}
