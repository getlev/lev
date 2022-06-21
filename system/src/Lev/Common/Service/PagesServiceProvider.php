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
use Lev\Common\Language\Language;
use Lev\Common\Page\Page;
use Lev\Common\Page\Pages;
use Lev\Common\Uri;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SplFileInfo;
use function defined;

/**
 * Class PagesServiceProvider
 * @package Lev\Common\Service
 */
class PagesServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container['pages'] = function (Lev $lev) {
            return new Pages($lev);
        };

        if (defined('LEV_CLI')) {
            $container['page'] = static function (Lev $lev) {
                $path = $lev['locator']->findResource('system://pages/notfound.md');
                $page = new Page();
                $page->init(new SplFileInfo($path));
                $page->routable(false);

                return $page;
            };

            return;
        }

        $container['page'] = static function (Lev $lev) {
            /** @var Pages $pages */
            $pages = $lev['pages'];

            /** @var Config $config */
            $config = $lev['config'];

            /** @var Uri $uri */
            $uri = $lev['uri'];

            $path = $uri->path() ?: '/'; // Don't trim to support trailing slash default routes
            $page = $pages->dispatch($path);

            // Redirection tests
            if ($page) {
                // some debugger override logic
                if ($page->debugger() === false) {
                    $lev['debugger']->enabled(false);
                }

                if ($config->get('system.force_ssl')) {
                    $scheme = $uri->scheme(true);
                    if ($scheme !== 'https') {
                        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $lev->redirect($url);
                    }
                }

                $route = $page->route();
                if ($route && \in_array($uri->method(), ['GET', 'HEAD'], true)) {
                    $pageExtension = $page->urlExtension();
                    $url = $pages->route($route) . $pageExtension;

                    if ($uri->params()) {
                        if ($url === '/') { //Avoid double slash
                            $url = $uri->params();
                        } else {
                            $url .= $uri->params();
                        }
                    }
                    if ($uri->query()) {
                        $url .= '?' . $uri->query();
                    }
                    if ($uri->fragment()) {
                        $url .= '#' . $uri->fragment();
                    }

                    /** @var Language $language */
                    $language = $lev['language'];

                    $redirect_default_route = $page->header()->redirect_default_route ?? $config->get('system.pages.redirect_default_route', 0);
                    $redirectCode = (int) $redirect_default_route;

                    // Language-specific redirection scenarios
                    if ($language->enabled() && ($language->isLanguageInUrl() xor $language->isIncludeDefaultLanguage())) {
                        $lev->redirect($url, $redirectCode);
                    }

                    // Default route test and redirect
                    if ($redirectCode) {
                        $uriExtension = $uri->extension();
                        $uriExtension = null !== $uriExtension ? '.' . $uriExtension : '';

                        if ($route !== $path || ($pageExtension !== $uriExtension
                                && \in_array($pageExtension, ['', '.htm', '.html'], true)
                                && \in_array($uriExtension, ['', '.htm', '.html'], true))) {
                            $lev->redirect($url, $redirectCode);
                        }
                    }
                }
            }

            // if page is not found, try some fallback stuff
            if (!$page || !$page->routable()) {
                // Try fallback URL stuff...
                $page = $lev->fallbackUrl($path);

                if (!$page) {
                    $path = $lev['locator']->findResource('system://pages/notfound.md');
                    $page = new Page();
                    $page->init(new SplFileInfo($path));
                    $page->routable(false);
                }
            }

            return $page;
        };
    }
}
