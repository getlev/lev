<?php

/**
 * @package    Lev\Grav\Common\File
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\File;

use RocketTheme\Toolbox\File\MarkdownFile;

/**
 * Class CompiledMarkdownFile
 * @package Lev\Common\File
 */
class CompiledMarkdownFile extends MarkdownFile
{
    use CompiledFile;
}
