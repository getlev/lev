<?php

/**
 * @package    Lev\Grav\Common\Errors
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Errors;

use Whoops\Handler\Handler;

/**
 * Class BareHandler
 * @package Lev\Common\Errors
 */
class BareHandler extends Handler
{
    /**
     * @return int
     */
    public function handle()
    {
        $inspector = $this->getInspector();
        $code = $inspector->getException()->getCode();
        if (($code >= 400) && ($code < 600)) {
            $this->getRun()->sendHttpCode($code);
        }

        return Handler::QUIT;
    }
}
