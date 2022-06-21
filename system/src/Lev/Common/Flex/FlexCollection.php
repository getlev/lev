<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Flex;

use Lev\Common\Flex\Traits\FlexCollectionTrait;
use Lev\Common\Flex\Traits\FlexLevTrait;

/**
 * Class FlexCollection
 *
 * @package Lev\Common\Flex
 * @template T of \Lev\Framework\Flex\Interfaces\FlexObjectInterface
 * @extends \Lev\Framework\Flex\FlexCollection<T>
 */
abstract class FlexCollection extends \Lev\Framework\Flex\FlexCollection
{
    use FlexLevTrait;
    use FlexCollectionTrait;
}
