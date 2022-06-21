<?php

declare(strict_types=1);

/**
 * @package    Lev\Grav\Framework\Form
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Framework\Form\Interfaces;

use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;

/**
 * Interface FormFactoryInterface
 * @package Lev\Framework\Form\Interfaces
 */
interface FormFactoryInterface
{
    /**
     * @param Page $page
     * @param string $name
     * @param array $form
     * @return FormInterface|null
     * @deprecated 1.6 Use FormFactory::createFormByPage() instead.
     */
    public function createPageForm(Page $page, string $name, array $form): ?FormInterface;

    /**
     * Create form using the header of the page.
     *
     * @param PageInterface $page
     * @param string $name
     * @param array $form
     * @return FormInterface|null
     *
    public function createFormForPage(PageInterface $page, string $name, array $form): ?FormInterface;
    */
}
