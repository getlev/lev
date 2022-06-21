<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\File
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\File;

use Lev\Framework\File\Formatter\IniFormatter;

/**
 * Class IniFile
 * @package RocketTheme\Toolbox\File
 */
class IniFile extends DataFile
{
    /**
     * File constructor.
     * @param string $filepath
     * @param IniFormatter $formatter
     */
    public function __construct($filepath, IniFormatter $formatter)
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
