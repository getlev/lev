<?php

/**
 * @package    Lev\Grav\Common\User
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\User\Interfaces;

/**
 * Interface AuthorizeInterface
 * @package Lev\Common\User\Interfaces
 */
interface AuthorizeInterface
{
    /**
     * Checks user authorization to the action.
     *
     * @param  string $action
     * @param  string|null $scope
     * @return bool|null
     */
    public function authorize(string $action, string $scope = null): ?bool;
}
