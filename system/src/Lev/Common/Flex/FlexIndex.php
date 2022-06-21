<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex;

use Lev\Common\Flex\Traits\FlexLevTrait;
use Lev\Common\Flex\Traits\FlexIndexTrait;

/**
 * Class FlexIndex
 *
 * @package Lev\Common\Flex
 * @template T of \Lev\Framework\Flex\Interfaces\FlexObjectInterface
 * @template C of \Lev\Framework\Flex\Interfaces\FlexCollectionInterface
 * @extends \Lev\Framework\Flex\FlexIndex<T,C>
 */
abstract class FlexIndex extends \Lev\Framework\Flex\FlexIndex
{
    use FlexLevTrait;
    use FlexIndexTrait;
}
