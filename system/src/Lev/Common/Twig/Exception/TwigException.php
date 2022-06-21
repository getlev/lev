<?php

/**
 * @package    Lev\Grav\Common\Twig\Exception
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Twig\Exception;

use RuntimeException;

/**
 * TwigException gets thrown when you use {% throw code message %} in twig.
 *
 * This allows Lev to catch 401, 403 and 404 exceptions and display proper error page.
 */
class TwigException extends RuntimeException
{
}
