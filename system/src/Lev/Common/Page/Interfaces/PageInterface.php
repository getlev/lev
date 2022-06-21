<?php

/**
 * @package    Lev\Grav\Common\Page
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Page\Interfaces;

use Lev\Common\Media\Interfaces\MediaInterface;

/**
 * Class implements page interface.
 */
interface PageInterface extends
    PageContentInterface,
    PageFormInterface,
    PageRoutableInterface,
    PageTranslateInterface,
    MediaInterface,
    PageLegacyInterface
{
}
