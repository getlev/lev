<?php

/**
 * @package    Lev\Grav\Framework\DI
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

declare(strict_types=1);

namespace Lev\Framework\DI;

use Psr\Container\ContainerInterface;

class Container extends \Pimple\Container implements ContainerInterface
{
    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return $this->offsetExists($id);
    }
}
