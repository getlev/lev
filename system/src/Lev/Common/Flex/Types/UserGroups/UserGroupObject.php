<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex\Types\UserGroups;

use Lev\Common\Flex\FlexObject;
use Lev\Common\User\Access;
use Lev\Common\User\Interfaces\UserGroupInterface;
use function is_bool;

/**
 * Flex User Group
 *
 * @package Lev\Common\User
 *
 * @property string $groupname
 * @property Access $access
 */
class UserGroupObject extends FlexObject implements UserGroupInterface
{
    /** @var Access */
    protected $_access;
    /** @var array|null */
    protected $access;

    /**
     * @return array
     */
    public static function getCachedMethods(): array
    {
        return [
            'authorize' => false,
        ] + parent::getCachedMethods();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getProperty('readableName');
    }

    /**
     * Checks user authorization to the action.
     *
     * @param  string $action
     * @param  string|null $scope
     * @return bool|null
     */
    public function authorize(string $action, string $scope = null): ?bool
    {
        if ($scope === 'test') {
            $scope = null;
        } elseif (!$this->getProperty('enabled', true)) {
            return null;
        }

        $access = $this->getAccess();

        $authorized = $access->authorize($action, $scope);
        if (is_bool($authorized)) {
            return $authorized;
        }

        return $access->authorize('admin.super') ? true : null;
    }

    /**
     * @return Access
     */
    protected function getAccess(): Access
    {
        if (null === $this->_access) {
            $this->getProperty('access');
        }

        return $this->_access;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function offsetLoad_access($value): array
    {
        if (!$value instanceof Access) {
            $value = new Access($value);
        }

        $this->_access = $value;

        return $value->jsonSerialize();
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function offsetPrepare_access($value): array
    {
        return $this->offsetLoad_access($value);
    }

    /**
     * @param array|null $value
     * @return array|null
     */
    protected function offsetSerialize_access(?array $value): ?array
    {
        return $value;
    }
}
