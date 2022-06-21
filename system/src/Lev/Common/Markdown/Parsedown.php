<?php

/**
 * @package    Lev\Grav\Common\Markdown
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Markdown;

use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Markdown\Excerpts;

/**
 * Class Parsedown
 * @package Lev\Common\Markdown
 */
class Parsedown extends \Parsedown
{
    use ParsedownLevTrait;

    /**
     * Parsedown constructor.
     *
     * @param Excerpts|PageInterface|null $excerpts
     * @param array|null $defaults
     */
    public function __construct($excerpts = null, $defaults = null)
    {
        if (!$excerpts || $excerpts instanceof PageInterface || null !== $defaults) {
            // Deprecated in Lev 1.6.10
            if ($defaults) {
                $defaults = ['markdown' => $defaults];
            }
            $excerpts = new Excerpts($excerpts, $defaults);
            user_error(__CLASS__ . '::' . __FUNCTION__ . '($page, $defaults) is deprecated since Lev 1.6.10, use new ' . __CLASS__ . '(new ' . Excerpts::class . '($page, [\'markdown\' => $defaults])) instead.', E_USER_DEPRECATED);
        }

        $this->init($excerpts, $defaults);
    }
}
