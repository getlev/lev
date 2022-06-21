<?php

/**
 * @package    Lev\Grav\Framework\Pagination
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Pagination;

use Lev\Framework\Route\Route;

/**
 * Class Pagination
 * @package Lev\Framework\Pagination
 */
class Pagination extends AbstractPagination
{
    /**
     * Pagination constructor.
     * @param Route $route
     * @param int $total
     * @param int|null $pos
     * @param int|null $limit
     * @param array|null $options
     */
    public function __construct(Route $route, int $total, int $pos = null, int $limit = null, array $options = null)
    {
        $this->initialize($route, $total, $pos, $limit, $options);
    }
}
