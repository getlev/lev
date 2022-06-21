<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\File
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\File;

use Lev\Framework\File\Formatter\JsonFormatter;

/**
 * Class JsonFile
 * @package Lev\Framework\File
 */
class JsonFile extends DataFile
{
    /**
     * File constructor.
     * @param string $filepath
     * @param JsonFormatter $formatter
     */
    public function __construct($filepath, JsonFormatter $formatter)
    {
        parent::__construct($filepath, $formatter);
    }
}
