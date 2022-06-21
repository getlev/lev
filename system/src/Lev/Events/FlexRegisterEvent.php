<?php

/**
 * @package    Lev\Grav\Events
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Events;

use Lev\Framework\Flex\Flex;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Flex Register Event
 *
 * This event is called the first time $lev['flex'] is being called.
 *
 * Use this event to register enabled Directories to Flex.
 *
 * @property Flex $flex Flex instance.
 */
class FlexRegisterEvent extends Event
{
    /** @var Flex */
    public $flex;

    /**
     * FlexRegisterEvent constructor.
     * @param Flex $flex
     */
    public function __construct(Flex $flex)
    {
        $this->flex = $flex;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return (array)$this;
    }
}
