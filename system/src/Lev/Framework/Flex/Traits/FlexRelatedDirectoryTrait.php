<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Flex\Traits;

use Lev\Framework\Flex\FlexDirectory;
use Lev\Framework\Flex\Interfaces\FlexCollectionInterface;
use Lev\Framework\Flex\Interfaces\FlexObjectInterface;
use RuntimeException;
use function in_array;

/**
 * Trait LevTrait
 * @package Lev\Common\Flex\Traits
 */
trait FlexRelatedDirectoryTrait
{
    /**
     * @param string $type
     * @param string $property
     * @return FlexCollectionInterface<FlexObjectInterface>
     */
    protected function getCollectionByProperty($type, $property)
    {
        $directory = $this->getRelatedDirectory($type);
        $collection = $directory->getCollection();
        $list = $this->getNestedProperty($property) ?: [];

        /** @var FlexCollectionInterface<FlexObjectInterface> $collection */
        $collection = $collection->filter(static function ($object) use ($list) {
            return in_array($object->getKey(), $list, true);
        });

        return $collection;
    }

    /**
     * @param string $type
     * @return FlexDirectory
     * @throws RuntimeException
     */
    protected function getRelatedDirectory($type): FlexDirectory
    {
        $directory = $this->getFlexContainer()->getDirectory($type);
        if (!$directory) {
            throw new RuntimeException(ucfirst($type). ' directory does not exist!');
        }

        return $directory;
    }
}
