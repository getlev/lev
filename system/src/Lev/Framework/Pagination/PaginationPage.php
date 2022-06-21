<?php

/**
 * @package    Lev\Grav\Framework\Pagination
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Pagination;

/**
 * Class PaginationPage
 * @package Lev\Framework\Pagination
 */
class PaginationPage extends AbstractPaginationPage
{
    /**
     * PaginationPage constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }
}
