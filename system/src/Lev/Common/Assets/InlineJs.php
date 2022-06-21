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
 * Class InlineJs
 * @package Lev\Common\Assets
 */
class InlineJs extends BaseAsset
{
    /**
     * InlineJs constructor.
     * @param array $elements
     * @param string|null $key
     */
    public function __construct(array $elements = [], ?string $key = null)
    {
        $base_options = [
            'asset_type' => 'js',
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
        return '<script' . $this->renderAttributes(). ">\n" . trim($this->asset) . "\n</script>\n";
    }
}
