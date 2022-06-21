<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex\Traits;

use Lev\Common\Lev;
use Lev\Common\User\Interfaces\UserInterface;
use Lev\Framework\Flex\Flex;

/**
 * Implements Lev specific logic
 */
trait FlexLevTrait
{
    /**
     * @return Lev
     */
    protected function getContainer(): Lev
    {
        return Lev::instance();
    }

    /**
     * @return Flex
     */
    protected function getFlexContainer(): Flex
    {
        $container = $this->getContainer();

        /** @var Flex $flex */
        $flex = $container['flex'];

        return $flex;
    }

    /**
     * @return UserInterface|null
     */
    protected function getActiveUser(): ?UserInterface
    {
        $container = $this->getContainer();

        /** @var UserInterface|null $user */
        $user = $container['user'] ?? null;

        return $user;
    }

    /**
     * @return bool
     */
    protected function isAdminSite(): bool
    {
        $container = $this->getContainer();

        return isset($container['admin']);
    }

    /**
     * @return string
     */
    protected function getAuthorizeScope(): string
    {
        return $this->isAdminSite() ? 'admin' : 'site';
    }
}
