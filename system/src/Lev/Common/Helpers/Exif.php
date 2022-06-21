<?php

/**
 * @package    Lev\Grav\Common\Helpers
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Helpers;

use Lev\Common\Lev;
use PHPExif\Reader\Reader;
use RuntimeException;
use function function_exists;

/**
 * Class Exif
 * @package Lev\Common\Helpers
 */
class Exif
{
    /** @var Reader */
    public $reader;

    /**
     * Exif constructor.
     * @throws RuntimeException
     */
    public function __construct()
    {
        if (Lev::instance()['config']->get('system.media.auto_metadata_exif')) {
            if (function_exists('exif_read_data') && class_exists(Reader::class)) {
                $this->reader = Reader::factory(Reader::TYPE_NATIVE);
            } else {
                throw new RuntimeException('Please enable the Exif extension for PHP or disable Exif support in Lev system configuration');
            }
        }
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }
}
