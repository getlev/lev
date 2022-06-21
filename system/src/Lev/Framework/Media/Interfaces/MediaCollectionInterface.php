<?php

/**
 * @package    Lev\Grav\Framework\Media
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Media\Interfaces;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Class implements media collection interface.
 * @extends ArrayAccess<string,MediaObjectInterface>
 * @extends Iterator<string,MediaObjectInterface>
 */
interface MediaCollectionInterface extends ArrayAccess, Countable, Iterator
{
}
