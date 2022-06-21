<?php

/**
 * @package    Lev\Grav\Framework\Collection
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Collection;

use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * Collection Interface.
 *
 * @package Lev\Framework\Collection
 * @template TKey of array-key
 * @template T
 * @extends Collection<TKey,T>
 */
interface CollectionInterface extends Collection, JsonSerializable
{
    /**
     * Reverse the order of the items.
     *
     * @return CollectionInterface
     * @phpstan-return static<TKey,T>
     */
    public function reverse();

    /**
     * Shuffle items.
     *
     * @return CollectionInterface
     * @phpstan-return static<TKey,T>
     */
    public function shuffle();

    /**
     * Split collection into chunks.
     *
     * @param int $size     Size of each chunk.
     * @return array
     * @phpstan-return array<array<TKey,T>>
     */
    public function chunk($size);

    /**
     * Select items from collection.
     *
     * Collection is returned in the order of $keys given to the function.
     *
     * @param array<int|string> $keys
     * @return CollectionInterface
     * @phpstan-return static<TKey,T>
     */
    public function select(array $keys);

    /**
     * Un-select items from collection.
     *
     * @param array<int|string> $keys
     * @return CollectionInterface
     * @phpstan-return static<TKey,T>
     */
    public function unselect(array $keys);
}
