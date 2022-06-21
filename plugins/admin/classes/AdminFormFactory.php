<?php

declare(strict_types=1);

namespace Lev\Plugin\Admin;

use Lev\Common\Lev;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;
use Lev\Framework\Form\Interfaces\FormFactoryInterface;
use Lev\Framework\Form\Interfaces\FormInterface;

/**
 * Class FlexFormFactory
 * @package Lev\Grav\Plugin\FlexObjects
 */
class AdminFormFactory implements FormFactoryInterface
{
    /**
     * @param Page $page
     * @param string $name
     * @param array $form
     * @return FormInterface|null
     */
    public function createPageForm(Page $page, string $name, array $form): ?FormInterface
    {
        return $this->createFormForPage($page, $name, $form);
    }

    /**
     * @param PageInterface $page
     * @param string $name
     * @param array $form
     * @return FormInterface|null
     */
    public function createFormForPage(PageInterface $page, string $name, array $form): ?FormInterface
    {
        /** @var Admin|null $admin */
        $admin = Lev::instance()['admin'] ?? null;
        $object = $admin->form ?? null;

        return $object && $object->getName() === $name ? $object : null;
    }
}
