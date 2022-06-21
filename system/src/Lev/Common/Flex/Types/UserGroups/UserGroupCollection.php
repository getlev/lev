<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex\Types\UserGroups;

use Lev\Common\Flex\FlexCollection;

/**
 * Class UserGroupCollection
 * @package Lev\Common\Flex\Types\UserGroups
 *
 * @extends FlexCollection<UserGroupObject>
 */
class UserGroupCollection extends FlexCollection
{
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
     * Checks user authorization to the action.
     *
     * @param  string $action
     * @param  string|null $scope
     * @return bool|null
     */
    public function authorize(string $action, string $scope = null): ?bool
    {
        $authorized = null;
        /** @var UserGroupObject $object */
        foreach ($this as $object) {
            $auth = $object->authorize($action, $scope);
            if ($auth === true) {
                $authorized = true;
            } elseif ($auth === false) {
                return false;
            }
        }

        return $authorized;
    }
}
