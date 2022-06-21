<?php

/**
 * @package    Lev\Grav\Framework\Pagination
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Pagination\Interfaces;

use Countable;
use Lev\Framework\Pagination\PaginationPage;
use IteratorAggregate;

/**
 * Interface PaginationInterface
 * @package Lev\Framework\Pagination\Interfaces
 * @extends IteratorAggregate<int,PaginationPage>
 */
interface PaginationInterface extends Countable, IteratorAggregate
{
    /**
     * @return int
     */
    public function getTotalPages(): int;

    /**
     * @return int
     */
    public function getPageNumber(): int;

    /**
     * @param int $count
     * @return int|null
     */
    public function getPrevNumber(int $count = 1): ?int;

    /**
     * @param int $count
     * @return int|null
     */
    public function getNextNumber(int $count = 1): ?int;

    /**
     * @return int
     */
    public function getStart(): int;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param int $page
     * @param string|null $label
     * @return PaginationPage|null
     */
    public function getPage(int $page, string $label = null): ?PaginationPage;

    /**
     * @param string|null $label
     * @param int $count
     * @return PaginationPage|null
     */
    public function getFirstPage(string $label = null, int $count = 0): ?PaginationPage;

    /**
     * @param string|null $label
     * @param int $count
     * @return PaginationPage|null
     */
    public function getPrevPage(string $label = null, int $count = 1): ?PaginationPage;

    /**
     * @param string|null $label
     * @param int $count
     * @return PaginationPage|null
     */
    public function getNextPage(string $label = null, int $count = 1): ?PaginationPage;

    /**
     * @param string|null $label
     * @param int $count
     * @return PaginationPage|null
     */
    public function getLastPage(string $label = null, int $count = 0): ?PaginationPage;
}
