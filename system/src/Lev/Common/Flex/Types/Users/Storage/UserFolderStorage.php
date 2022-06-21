<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex\Types\Users\Storage;

use Lev\Framework\Flex\Storage\FolderStorage;

/**
 * Class UserFolderStorage
 * @package Lev\Common\Flex\Types\Users\Storage
 */
class UserFolderStorage extends FolderStorage
{
    /**
     * Prepares the row for saving and returns the storage key for the record.
     *
     * @param array $row
     */
    protected function prepareRow(array &$row): void
    {
        parent::prepareRow($row);

        $access = $row['access'] ?? [];
        unset($row['access']);
        if ($access) {
            $row['access'] = $access;
        }
    }
}
