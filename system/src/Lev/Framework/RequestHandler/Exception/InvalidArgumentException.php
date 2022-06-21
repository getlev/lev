<?php

/**
 * @package    Lev\Grav\Framework\RequestHandler
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

declare(strict_types=1);

namespace Lev\Framework\RequestHandler\Exception;

use Throwable;

/**
 * Class InvalidArgumentException
 * @package Lev\Framework\RequestHandler\Exception
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /** @var mixed|null */
    private $invalidMiddleware;

    /**
     * InvalidArgumentException constructor.
     *
     * @param string $message
     * @param mixed|null $invalidMiddleware
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $invalidMiddleware = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->invalidMiddleware = $invalidMiddleware;
    }

    /**
     * Return the invalid middleware
     *
     * @return mixed|null
     */
    public function getInvalidMiddleware()
    {
        return $this->invalidMiddleware;
    }
}
