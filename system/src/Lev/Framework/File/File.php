<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\File
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\File;

use RuntimeException;
use function is_string;

/**
 * Class File
 * @package Lev\Framework\File
 */
class File extends AbstractFile
{
    /**
     * {@inheritdoc}
     * @see FileInterface::save()
     */
    public function save($data): void
    {
        if (!is_string($data)) {
            throw new RuntimeException('Cannot save data, string required');
        }

        parent::save($data);
    }
}
