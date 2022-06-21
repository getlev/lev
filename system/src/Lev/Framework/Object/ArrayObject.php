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
use Lev\Framework\Object\Property\ArrayPropertyTrait;

/**
 * Array Objects keep the data in private array property.
 * @implements ArrayAccess<string,mixed>
 */
class ArrayObject implements NestedObjectInterface, ArrayAccess
{
    use ObjectTrait;
    use ArrayPropertyTrait;
    use NestedPropertyTrait;
    use OverloadedPropertyTrait;
    use NestedArrayAccessTrait;
}
