<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Flex\Pages;

use Lev\Common\Lev;
use Lev\Framework\Flex\FlexIndex;

/**
 * Class FlexPageObject
 * @package Lev\Plugin\FlexObjects\Types\FlexPages
 *
 * @method FlexPageIndex withRoutable(bool $bool = true)
 * @method FlexPageIndex withPublished(bool $bool = true)
 * @method FlexPageIndex withVisible(bool $bool = true)
 *
 * @template T of FlexPageObject
 * @template C of FlexPageCollection
 * @extends FlexIndex<T,C>
 */
class FlexPageIndex extends FlexIndex
{
    public const ORDER_PREFIX_REGEX = '/^\d+\./u';

    /**
     * @param string $route
     * @return string
     * @internal
     */
    public static function normalizeRoute(string $route)
    {
        static $case_insensitive;

        if (null === $case_insensitive) {
            $case_insensitive = Lev::instance()['config']->get('system.force_lowercase_urls', false);
        }

        return $case_insensitive ? mb_strtolower($route) : $route;
    }
}
