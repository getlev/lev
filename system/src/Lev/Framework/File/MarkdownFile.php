<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\File
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\File;

use Lev\Framework\File\Formatter\MarkdownFormatter;

/**
 * Class MarkdownFile
 * @package Lev\Framework\File
 */
class MarkdownFile extends DataFile
{
    /**
     * File constructor.
     * @param string $filepath
     * @param MarkdownFormatter $formatter
     */
    public function __construct($filepath, MarkdownFormatter $formatter)
    {
        parent::__construct($filepath, $formatter);
    }

    /**
     * @return array
     */
    public function load(): array
    {
        /** @var array */
        return parent::load();
    }
}
