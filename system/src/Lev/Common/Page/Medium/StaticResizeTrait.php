<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Medium;

use Lev\Common\Media\Traits\StaticResizeTrait as NewResizeTrait;

user_error('Lev\Common\Page\Medium\StaticResizeTrait is deprecated since Lev 1.7, use Lev\Common\Media\Traits\StaticResizeTrait instead', E_USER_DEPRECATED);

/**
 * Trait StaticResizeTrait
 * @package Lev\Common\Page\Medium
 * @deprecated 1.7 Use `Lev\Common\Media\Traits\StaticResizeTrait` instead
 */
trait StaticResizeTrait
{
    use NewResizeTrait;
}
