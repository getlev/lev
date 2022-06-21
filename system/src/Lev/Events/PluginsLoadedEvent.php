<?php

/**
 * @package    Lev\Grav\Events
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Events;

use Lev\Common\Lev;
use Lev\Common\Plugins;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Plugins Loaded Event
 *
 * This event is called from InitializeProcessor.
 *
 * This is the first event plugin can see. Please avoid using this event if possible.
 *
 * @property Lev $lev Lev container.
 * @property Plugins $plugins Plugins instance.
 */
class PluginsLoadedEvent extends Event
{
    /** @var Lev */
    public $lev;
    /** @var Plugins */
    public $plugins;

    /**
     * PluginsLoadedEvent constructor.
     * @param Lev $lev
     * @param Plugins $plugins
     */
    public function __construct(Lev $lev, Plugins $plugins)
    {
        $this->lev = $lev;
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'plugins' => $this->plugins
        ];
    }
}
