<?php

/**
 * @package    Lev\Grav\Common\Service
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Lev\Common\Service;

use Lev\Common\Config\Config;
use Lev\Common\Lev;
use Lev\Common\Page\Header;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\User\DataUser;
use Lev\Common\User\User;
use Lev\Events\PermissionsRegisterEvent;
use Lev\Framework\Acl\Permissions;
use Lev\Framework\Acl\PermissionsReader;
use Lev\Framework\Flex\Flex;
use Lev\Framework\Flex\Interfaces\FlexIndexInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RocketTheme\Toolbox\Event\Event;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcher;
use function define;
use function defined;
use function is_array;

/**
 * Class AccountsServiceProvider
 * @package Lev\Common\Service
 */
class AccountsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['permissions'] = static function (Lev $container) {
            /** @var Config $config */
            $config = $container['config'];

            $permissions = new Permissions();
            $permissions->addTypes($config->get('permissions.types', []));

            $array = $config->get('permissions.actions');
            if (is_array($array)) {
                $actions = PermissionsReader::fromArray($array, $permissions->getTypes());
                $permissions->addActions($actions);
            }

            $event = new PermissionsRegisterEvent($permissions);
            $container->dispatchEvent($event);

            return $permissions;
        };

        $container['accounts'] = function (Container $container) {
            $type = $this->initialize($container);

            return $type === 'flex' ? $this->flexAccounts($container) : $this->regularAccounts($container);
        };

        $container['user_groups'] = static function (Container $container) {
            /** @var Flex $flex */
            $flex = $container['flex'];
            $directory = $flex->getDirectory('user-groups');

            return $directory ? $directory->getIndex() : null;
        };

        $container['users'] = $container->factory(static function (Container $container) {
            user_error('Lev::instance()[\'users\'] is deprecated since Lev 1.6, use Lev::instance()[\'accounts\'] instead', E_USER_DEPRECATED);

            return $container['accounts'];
        });
    }

    /**
     * @param Container $container
     * @return string
     */
    protected function initialize(Container $container): string
    {
        $isDefined = defined('LEV_USER_INSTANCE');
        $type = strtolower($isDefined ? LEV_USER_INSTANCE : $container['config']->get('system.accounts.type', 'regular'));

        if ($type === 'flex') {
            if (!$isDefined) {
                define('LEV_USER_INSTANCE', 'FLEX');
            }

            /** @var EventDispatcher $dispatcher */
            $dispatcher = $container['events'];

            // Stop /admin/user from working, display error instead.
            $dispatcher->addListener(
                'onAdminPage',
                static function (Event $event) {
                    $lev = Lev::instance();
                    $admin = $lev['admin'];
                    [$base,$location,] = $admin->getRouteDetails();
                    if ($location !== 'user' || isset($lev['flex_objects'])) {
                        return;
                    }

                    /** @var PageInterface $page */
                    $page = $event['page'];
                    $page->init(new SplFileInfo('plugins://admin/pages/admin/error.md'));
                    $page->routable(true);
                    $header = $page->header();
                    $header->title = 'Please install missing plugin';
                    $page->content("## Please install and enable **[Flex Objects]({$base}/plugins/flex-objects)** plugin. It is required to edit **Flex User Accounts**.");

                    /** @var Header $header */
                    $header = $page->header();
                    $directory = $lev['accounts']->getFlexDirectory();
                    $menu = $directory->getConfig('admin.menu.list');
                    $header->access = $menu['authorize'] ?? ['admin.super'];
                },
                100000
            );
        } elseif (!$isDefined) {
            define('LEV_USER_INSTANCE', 'REGULAR');
        }

        return $type;
    }

    /**
     * @param Container $container
     * @return DataUser\UserCollection
     */
    protected function regularAccounts(Container $container)
    {
        // Use User class for backwards compatibility.
        return new DataUser\UserCollection(User::class);
    }

    /**
     * @param Container $container
     * @return FlexIndexInterface|null
     */
    protected function flexAccounts(Container $container)
    {
        /** @var Flex $flex */
        $flex = $container['flex'];
        $directory = $flex->getDirectory('user-accounts');

        return $directory ? $directory->getIndex() : null;
    }
}
