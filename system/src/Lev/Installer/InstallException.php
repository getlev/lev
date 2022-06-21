<?php

/**
 * @package    Lev\Grav\Installer
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Installer;

use Throwable;

/**
 * Class InstallException
 * @package Lev\Installer
 */
class InstallException extends \RuntimeException
{
    /**
     * InstallException constructor.
     * @param string $message
     * @param Throwable $previous
     */
    public function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, $previous->getCode(), $previous);
    }
}
