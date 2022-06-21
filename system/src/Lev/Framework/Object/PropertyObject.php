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
use Lev\Framework\Object\Property\ObjectPropertyTrait;

/**
 * Property Objects keep their data in protected object properties.
 *
 * @implements ArrayAccess<string,mixed>
 */
class PropertyObject implements NestedObjectInterface, ArrayAccess
{
    use ObjectTrait;
    use ObjectPropertyTrait;
    use NestedPropertyTrait;
    use OverloadedPropertyTrait;
    use NestedArrayAccessTrait;
}
