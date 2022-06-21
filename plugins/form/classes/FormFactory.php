<?php declare(strict_types=1);

namespace Lev\Plugin\Form;

use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;
use Lev\Framework\Form\Interfaces\FormFactoryInterface;
use Lev\Framework\Form\Interfaces\FormInterface;

class FormFactory implements FormFactoryInterface
{
    /**
     * Create form using the header of the page.
     *
     * @param Page $page
     * @param string $name
     * @param array $form
     * @return Form|null
     * @deprecated 1.6 Use FormFactory::createFormByPage() instead.
     */
    public function createPageForm(Page $page, string $name, array $form): ?FormInterface
    {
        return new Form($page, $name, $form);
    }

    /**
     * Create form using the header of the page.
     *
     * @param PageInterface $page
     * @param string $name
     * @param array $form
     * @return Form|null
     */
    public function createFormForPage(PageInterface $page, string $name, array $form): ?FormInterface
    {
        return new Form($page, $name, $form);
    }
}
