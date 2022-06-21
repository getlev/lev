<?php

/**
 * @package    Lev\Grav\Framework\Object
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Object;

use ArrayAccess;
use Lev\Framework\Object\Access\NestedArrayAccessTrait;
use Lev\Framework\Object\Access\NestedPropertyTrait;
use Lev\Framework\Object\Access\OverloadedPropertyTrait;
use Lev\Framework\Object\Base\ObjectTrait;
use Lev\Framework\Object\Interfaces\NestedObjectInterface;
use Lev\Framework\Object\Property\LazyPropertyTrait;

/**
 * Lazy Objects keep their data in both protected object properties and falls back to a stored array if property does
 * not exist or is not initialized.
 *
 * @package Lev\Framework\Object
 * @implements ArrayAccess<string,mixed>
 */
class LazyObject implements NestedObjectInterface, ArrayAccess
{
    use ObjectTrait;
    use LazyPropertyTrait;
    use NestedPropertyTrait;
    use OverloadedPropertyTrait;
    use NestedArrayAccessTrait;
}
