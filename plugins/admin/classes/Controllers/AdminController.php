<?php

/**
 * @package    Lev\Grav\Plugin\Admin
 *
 * @copyright  Copyright (c) 2015 - 2022 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

declare(strict_types=1);

namespace Lev\Plugin\Admin\Controllers;

use Lev\Common\Config\Config;
use Lev\Common\Data\Blueprint;
use Lev\Common\Lev;
use Lev\Common\Language\Language;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;
use Lev\Common\Page\Pages;
use Lev\Common\Uri;
use Lev\Common\User\Interfaces\UserInterface;
use Lev\Common\Utils;
use Lev\Framework\Controller\Traits\ControllerResponseTrait;
use Lev\Framework\RequestHandler\Exception\PageExpiredException;
use Lev\Framework\Session\SessionInterface;
use Lev\Plugin\Admin\Admin;
use Lev\Plugin\Admin\AdminForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RocketTheme\Toolbox\Session\Message;

abstract class AdminController
{
    use ControllerResponseTrait {
        createRedirectResponse as traitCreateRedirectResponse;
        getErrorJson as traitGetErrorJson;
    }

    /** @var string */
    protected $nonce_action = 'admin-form';
    /** @var string */
    protected $nonce_name = 'admin-nonce';
    /** @var Lev */
    protected $lev;
    /** @var PageInterface */
    protected $page;
    /** @var AdminForm|null */
    protected $form;

    public function __construct(Lev $lev)
    {
        $this->lev = $lev;
    }

    /**
     * @return PageInterface|null
     */
    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    /**
     * Get currently active form.
     *
     * @return AdminForm|null
     */
    public function getActiveForm(): ?AdminForm
    {
        if (null === $this->form) {
            $post = $this->getPost();

            $active = $post['__form-name__'] ?? null;

            $this->form = $active ? $this->getForm($active) : null;
        }

        return $this->form;
    }

    /**
     * Get a form.
     *
     * @param string $name
     * @param array $options
     * @return AdminForm|null
     */
    public function getForm(string $name, array $options = []): ?AdminForm
    {
        $post = $this->getPost();
        $page = $this->getPage();
        $forms = $page ? $page->forms() : [];
        $blueprint = $forms[$name] ?? null;
        if (null === $blueprint) {
            return null;
        }

        $active = $post['__form-name__'] ?? null;
        $unique_id = $active && $active === $name ? ($post['__unique_form_id__'] ?? null) : null;

        $options += [
            'unique_id' => $unique_id,
            'blueprint' => new Blueprint(null, ['form' => $blueprint]),
            'submit_method' => $this->getFormSubmitMethod($name),
            'nonce_name' => $this->nonce_name,
            'nonce_action' => $this->nonce_action,
        ];

        return new AdminForm($name, $options);
    }

    abstract protected function getFormSubmitMethod(string $name): callable;

    /**
     * @param string $route
     * @param string|null $lang
     * @return string
     */
    public function getAdminUrl(string $route, string $lang = null): string
    {
        /** @var Pages $pages */
        $pages = $this->lev['pages'];
        $admin = $this->getAdmin();

        return $pages->baseUrl($lang) . $admin->base . $route;
    }

    /**
     * @param string $route
     * @param string|null $lang
     * @return string
     */
    public function getAbsoluteAdminUrl(string $route, string $lang = null): string
    {
        /** @var Pages $pages */
        $pages = $this->lev['pages'];
        $admin = $this->getAdmin();

        return $pages->baseUrl($lang, true) . $admin->base . $route;
    }

    /**
     * Get session.
     *
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->lev['session'];
    }

    /**
     * @return Admin
     */
    protected function getAdmin(): Admin
    {
        return $this->lev['admin'];
    }

    /**
     * @return UserInterface
     */
    protected function getUser(): UserInterface
    {
        return $this->getAdmin()->user;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->getAdmin()->request;
    }

    /**
     * @return array
     */
    public function getPost(): array
    {
        return (array)($this->getRequest()->getParsedBody() ?? []);
    }

    /**
     * Translate a string.
     *
     * @param string $string
     * @param mixed ...$args
     * @return string
     */
    public function translate(string $string, ...$args): string
    {
        /** @var Language $language */
        $language = $this->lev['language'];

        array_unshift($args, $string);

        return $language->translate($args);
    }

    /**
     * Set message to be shown in the admin.
     *
     * @param string $message
     * @param string $type
     * @return $this
     */
    public function setMessage(string $message, string $type = 'info'): AdminController
    {
        /** @var Message $messages */
        $messages = $this->lev['messages'];
        $messages->add($message, $type);

        return $this;
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        return $this->lev['config'];
    }

    /**
     * Check if request nonce is valid.
     *
     * @return void
     * @throws PageExpiredException  If nonce is not valid.
     */
    protected function checkNonce(): void
    {
        $nonce = null;

        $nonce_name = $this->form ? $this->form->getNonceName() : $this->nonce_name;
        $nonce_action = $this->form ? $this->form->getNonceAction() : $this->nonce_action;

        if (\in_array(strtoupper($this->getRequest()->getMethod()), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $post = $this->getPost();
            $nonce = $post[$nonce_name] ?? null;
        }

        /** @var Uri $uri */
        $uri = $this->lev['uri'];
        if (!$nonce) {
            $nonce = $uri->param($nonce_name);
        }

        if (!$nonce) {
            $nonce = $uri->query($nonce_name);
        }

        if (!$nonce || !Utils::verifyNonce($nonce, $nonce_action)) {
            throw new PageExpiredException($this->getRequest());
        }
    }

    /**
     * Return the best matching mime type for the request.
     *
     * @param  string[] $compare
     * @return string|null
     */
    protected function getAccept(array $compare): ?string
    {
        $accepted = [];
        foreach ($this->getRequest()->getHeader('Accept') as $accept) {
            foreach (explode(',', $accept) as $item) {
                if (!$item) {
                    continue;
                }

                $split = explode(';q=', $item);
                $mime = array_shift($split);
                $priority = array_shift($split) ?? 1.0;

                $accepted[$mime] = $priority;
            }
        }

        arsort($accepted);

        // TODO: add support for image/* etc
        $list = array_intersect($compare, array_keys($accepted));
        if (!$list && (isset($accepted['*/*']) || isset($accepted['*']))) {
            return reset($compare) ?: null;
        }

        return reset($list) ?: null;
    }

    /**
     * @param string $template
     * @return PageInterface
     */
    protected function createPage(string $template): PageInterface
    {
        $page = new Page();

        // Plugins may not have the correct Cache-Control header set, force no-store for the proxies.
        $page->expires(0);

        $filename = "plugins://admin/pages/admin/{$template}.md";
        if (!file_exists($filename)) {
            throw new \RuntimeException(sprintf('Creating admin page %s failed: not found', $template));
        }

        Admin::DEBUG && Admin::addDebugMessage("Admin page: {$template}");

        $page->init(new \SplFileInfo($filename));
        $page->slug($template);

        return $page;
    }

    /**
     * @param string|null $url
     * @param int|null $code
     * @return ResponseInterface
     */
    protected function createRedirectResponse(string $url = null, int $code = null): ResponseInterface
    {
        $request = $this->getRequest();

        if (null === $url || '' === $url) {
            $url = (string)$request->getUri();
        } elseif (mb_strpos($url, '/') === 0) {
            $url = $this->getAbsoluteAdminUrl($url);
        }

        if (null === $code) {
            if (in_array($request->getMethod(), ['GET', 'HEAD'])) {
                $code = 302;
            } else {
                $code = 303;
            }
        }

        return $this->traitCreateRedirectResponse($url, $code);
    }

    /**
     * @param \Throwable $e
     * @return array
     */
    protected function getErrorJson(\Throwable $e): array
    {
        $json = $this->traitGetErrorJson($e);
        $code = $e->getCode();
        if ($code === 401) {
            $json['redirect'] = $this->getAbsoluteAdminUrl('/');
        }

        return $json;
    }


}
