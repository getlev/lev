<?php

/**
 * @package    Lev\Grav\Common\Assets
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Assets;

use Lev\Common\Utils;

/**
 * Class InlineCss
 * @package Lev\Common\Assets
 */
class InlineCss extends BaseAsset
{
    /**
     * InlineCss constructor.
     * @param array $elements
     * @param string|null $key
     */
    public function __construct(array $elements = [], ?string $key = null)
    {
        $base_options = [
            'asset_type' => 'css',
            'position' => 'after'
        ];

        $merged_attributes = Utils::arrayMergeRecursiveUnique($base_options, $elements);

        parent::__construct($merged_attributes, $key);
    }

    /**
     * @return string
     */
    public function render()
    {
        return '<style' . $this->renderAttributes(). ">\n" . trim($this->asset) . "\n</style>\n";
    }
}
