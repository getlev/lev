<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\Psr7
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Psr7;

use Lev\Framework\Psr7\Traits\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

/**
 * Class Stream
 * @package Lev\Framework\Psr7
 */
class Stream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * @param string|resource|StreamInterface $body
     * @return static
     */
    public static function create($body = '')
    {
        return new static($body);
    }

    /**
     * Stream constructor.
     *
     * @param string|resource|StreamInterface $body
     */
    public function __construct($body = '')
    {
        $this->stream = \Nyholm\Psr7\Stream::create($body);
    }
}
