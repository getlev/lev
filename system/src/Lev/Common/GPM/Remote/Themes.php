<?php

/**
 * @package    Lev\Grav\Common\GPM
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\GPM\Remote;

/**
 * Class Themes
 * @package Lev\Common\GPM\Remote
 */
class Themes extends AbstractPackageCollection
{
    /** @var string */
    protected $type = 'themes';
    /** @var string */
    protected $repository = 'https://getgrav.org/downloads/themes.json';

    /**
     * Local Themes Constructor
     * @param bool $refresh
     * @param callable|null $callback Either a function or callback in array notation
     */
    public function __construct($refresh = false, $callback = null)
    {
        parent::__construct($this->repository, $refresh, $callback);
    }
}
