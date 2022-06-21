<?php

/**
 * @package    Lev\Grav\Framework\Flex
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Flex\Interfaces;

/**
 * Defines Forms for Flex Objects.
 *
 * @used-by \Lev\Framework\Flex\FlexForm
 * @since 1.7
 */
interface FlexDirectoryFormInterface extends FlexFormInterface
{
    /**
     * Get object associated to the form.
     *
     * @return FlexObjectInterface  Returns Flex Object associated to the form.
     * @api
     */
    public function getDirectory();
}
