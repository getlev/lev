<?php

declare(strict_types=1);

namespace Lev\Plugin\FlexObjects;

use Lev\Common\Lev;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;
use Lev\Framework\Form\Interfaces\FormFactoryInterface;
use Lev\Framework\Form\Interfaces\FormInterface;
use RocketTheme\Toolbox\Event\Event;
use function is_callable;
use function is_string;

/**
 * Class FlexFormFactory
 * @package Lev\Grav\Plugin\FlexObjects
 */
class FlexFormFactory implements FormFactoryInterface
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
        // Fire event
        $lev = Lev::instance();
        $lev->fireEvent('onBeforeFlexFormInitialize', new Event(['page' => $page, 'name' => $name, 'form' => &$form]));
        $page->addForms([$form], true);

        $formFlex = $form['flex'] ?? [];

        $type = $formFlex['type'] ?? null;
        $key = $formFlex['key'] ?? null;
        if (null !== $key && !is_string($key)) {
            $key = (string)$key;
        }
        $layout = $formFlex['layout'] ?? $name;

        /** @var Flex $flex */
        $flex = Lev::instance()['flex_objects'];
        if (is_string($type)) {
            $directory = $flex->getDirectory($type);
            if (!$directory) {
                return null;
            }

            $create = $form['actions']['create'] ?? true;
            $edit = $form['actions']['edit'] ?? true;

            $object = $edit && null !== $key ? $directory->getObject($key) : null;
            if ($object) {
                if (is_callable([$object, 'refresh'])) {
                    $object->refresh();
                }
            } elseif ($create) {
                $object = $directory->createObject([], $key ?? '');
            }
        } else {
            $object = $flex->getObject($key);
        }

        return $object ? $object->getForm($layout, ['form' => $form]) : null;
    }
}
