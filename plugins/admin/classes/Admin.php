<?php

namespace Lev\Plugin\Admin;

use DateTime;
use Lev\Common\Data;
use Lev\Common\Data\Data as LevData;
use Lev\Common\Debugger;
use Lev\Common\File\CompiledYamlFile;
use Lev\Common\Flex\Types\Users\UserObject;
use Lev\Common\GPM\GPM;
use Lev\Common\GPM\Licenses;
use Lev\Common\Lev;
use Lev\Common\Helpers\YamlLinter;
use Lev\Common\HTTP\Response;
use Lev\Common\Language\Language;
use Lev\Common\Language\LanguageCodes;
use Lev\Common\Page\Collection;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Page\Page;
use Lev\Common\Page\Pages;
use Lev\Common\Plugins;
use Lev\Common\Security;
use Lev\Common\Session;
use Lev\Common\Themes;
use Lev\Common\Uri;
use Lev\Common\User\Interfaces\UserCollectionInterface;
use Lev\Common\User\Interfaces\UserInterface;
use Lev\Common\Utils;
use Lev\Framework\Acl\Action;
use Lev\Framework\Acl\Permissions;
use Lev\Framework\Collection\ArrayCollection;
use Lev\Framework\Flex\Flex;
use Lev\Framework\Flex\Interfaces\FlexInterface;
use Lev\Framework\Flex\Interfaces\FlexObjectInterface;
use Lev\Framework\Route\Route;
use Lev\Framework\Route\RouteFactory;
use Lev\Plugin\AdminPlugin;
use Lev\Plugin\Login\Login;
use Lev\Plugin\Login\TwoFactorAuth\TwoFactorAuth;
use JsonException;
use PicoFeed\Parser\MalformedXmlException;
use Psr\Http\Message\ServerRequestInterface;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\File\File;
use RocketTheme\Toolbox\File\JsonFile;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceIterator;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\Session\Message;
use Lev\Common\Yaml;
use Composer\Semver\Semver;
use PicoFeed\Reader\Reader;

define('LOGIN_REDIRECT_COOKIE', 'lev-login-redirect');

/**
 * Class Admin
 * @package Lev\Grav\Plugin\Admin
 */
class Admin
{
    /** @var int */
    public const DEBUG = 1;
    /** @var int */
    public const MEDIA_PAGINATION_INTERVAL = 20;
    /** @var string */
    public const TMP_COOKIE_NAME = 'tmp-admin-message';

    /** @var Lev */
    public $lev;
    /** @var ServerRequestInterface|null */
    public $request;
    /** @var AdminForm */
    public $form;
    /** @var string */
    public $base;
    /** @var string */
    public $location;
    /** @var string */
    public $route;
    /** @var UserInterface */
    public $user;
    /** @var array */
    public $forgot;
    /** @var string */
    public $task;
    /** @var array */
    public $json_response;
    /** @var Collection */
    public $collection;
    /** @var bool */
    public $multilang;
    /** @var string */
    public $language;
    /** @var array */
    public $languages_enabled = [];
    /** @var Uri $uri */
    protected $uri;
    /** @var array */
    protected $pages = [];
    /** @var Session */
    protected $session;
    /** @var Data\Blueprints */
    protected $blueprints;
    /** @var GPM */
    protected $gpm;
    /** @var int */
    protected $pages_count;
    /** @var bool */
    protected $load_additional_files_in_background = false;
    /** @var bool */
    protected $loading_additional_files_in_background = false;
    /** @var array */
    protected $temp_messages = [];

    /**
     * Constructor.
     *
     * @param Lev   $lev
     * @param string $base
     * @param string $location
     * @param string|null $route
     */
    public function __construct(Lev $lev, $base, $location, $route)
    {
        // Register admin to lev because of calling $lev['user'] requires it.
        $lev['admin']     = $this;

        $this->lev        = $lev;
        $this->base        = $base;
        $this->location    = $location;
        $this->route       = $route ?? '';
        $this->uri         = $lev['uri'];
        $this->session     = $lev['session'];

        /** @var FlexInterface|null $flex */
        $flex = $lev['flex_objects'] ?? null;

        /** @var UserInterface $user */
        $user = $lev['user'];

        // Convert old user to Flex User if Flex Objects plugin has been enabled.
        if ($flex && !$user instanceof FlexObjectInterface) {
            $managed = !method_exists($flex, 'isManaged') || $flex->isManaged('user-accounts');
            $directory = $managed ? $flex->getDirectory('user-accounts') : null;

            /** @var UserObject|null $test */
            $test = $directory ? $directory->getObject(mb_strtolower($user->username)) : null;
            if ($test) {
                $test = clone $test;
                $test->access = $user->access;
                $test->groups = $user->groups;
                $test->authenticated = $user->authenticated;
                $test->authorized = $user->authorized;
                $user = $test;
            }
        }
        $this->user = $user;

        /** @var Language $language */
        $language = $lev['language'];

        $this->multilang = $language->enabled();

        // Load utility class
        if ($this->multilang) {
            $this->language = $language->getActive() ?? '';
            $this->languages_enabled = (array)$this->lev['config']->get('system.languages.supported', []);

            //Set the currently active language for the admin
            $languageCode = $this->uri->param('lang');
            if (null === $languageCode && !$this->session->admin_lang) {
                $this->session->admin_lang = $language->getActive() ?? '';
            }
        } else {
            $this->language = '';
        }

        // Set admin route language.
        RouteFactory::setLanguage($this->language);
    }

    /**
     * @param string $message
     * @param array|object $data
     * @return void
     */
    public static function addDebugMessage(string $message, $data = [])
    {
        /** @var Debugger $debugger */
        $debugger = Lev::instance()['debugger'];
        $debugger->addMessage($message, 'debug', $data);
    }

    /**
     * @return string[]
     */
    public static function contentEditor()
    {
        $options = [
            'default' => 'Default',
            'codemirror' => 'CodeMirror'
        ];
        $event = new Event(['options' => &$options]);
        Lev::instance()->fireEvent('onAdminListContentEditors', $event);
        return $options;
    }

    /**
     * Return the languages available in the admin
     *
     * @return array
     */
    public static function adminLanguages()
    {
        $languages = [];

        $path = Lev::instance()['locator']->findResource('plugins://admin/languages');

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDir() || $file->isDot() || Utils::startsWith($file->getFilename(), '.')) {
                continue;
            }

            $lang = $file->getBasename('.yaml');

            $languages[$lang] = LanguageCodes::getNativeName($lang);

        }

        // sort languages
        asort($languages);

        return $languages;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language ?: $this->lev['language']->getLanguage() ?: 'en';
    }

    /**
     * Return the found configuration blueprints
     *
     * @param bool $checkAccess
     * @return array
     */
    public static function configurations(bool $checkAccess = false): array
    {
        $lev = Lev::instance();

        /** @var Admin $admin */
        $admin = $lev['admin'];

        /** @var UniformResourceIterator $iterator */
        $iterator = $lev['locator']->getIterator('blueprints://config');

        // Find all main level configuration files.
        $configurations = [];
        foreach ($iterator as $file) {
            if ($file->isDir() || !preg_match('/^[^.].*.yaml$/', $file->getFilename())) {
                continue;
            }

            $name = $file->getBasename('.yaml');

            // Check that blueprint exists and is not hidden.
            $data = $admin->getConfigurationData('config/'. $name);
            if (!is_callable([$data, 'blueprints'])) {
                continue;
            }

            $blueprint = $data->blueprints();
            if (!$blueprint) {
                continue;
            }

            $test = $blueprint->toArray();
            if (empty($test['form']['hidden']) && (!empty($test['form']['field']) || !empty($test['form']['fields']))) {
                $configurations[$name] = true;
            }
        }

        // Remove scheduler and backups configs (they belong to the tools).
        unset($configurations['scheduler'], $configurations['backups']);

        // Sort configurations.
        ksort($configurations);
        $configurations = ['system' => true, 'site' => true] + $configurations + ['info' => true];

        if ($checkAccess) {
            // ACL checks.
            foreach ($configurations as $name => $value) {
                if (!$admin->authorize(['admin.configuration.' . $name, 'admin.super'])) {
                    unset($configurations[$name]);
                }
            }
        }

        return array_keys($configurations);
    }

    /**
     * Return the tools found
     *
     * @return array
     */
    public static function tools()
    {
        $tools = [];
        Lev::instance()->fireEvent('onAdminTools', new Event(['tools' => &$tools]));

        return $tools;
    }

    /**
     * @return array
     */
    public static function toolsPermissions()
    {
        $tools = static::tools();
        $perms = [];

        foreach ($tools as $tool) {
            $perms = array_merge($perms, $tool[0]);
        }

        return array_unique($perms);
    }

    /**
     * Return the languages available in the site
     *
     * @return array
     */
    public static function siteLanguages()
    {
        $languages = [];
        $lang_data = (array) Lev::instance()['config']->get('system.languages.supported', []);

        foreach ($lang_data as $index => $lang) {
            $languages[$lang] = LanguageCodes::getNativeName($lang);
        }

        return $languages;
    }

    /**
     * Static helper method to return the admin form nonce
     *
     * @param string $action
     * @return string
     */
    public static function getNonce(string $action = 'admin-form')
    {
        return Utils::getNonce($action);
    }

    /**
     * Static helper method to return the last used page name
     *
     * @return string
     */
    public static function getLastPageName()
    {
        return Lev::instance()['session']->lastPageName ?: 'default';
    }

    /**
     * Static helper method to return the last used page route
     *
     * @return string
     */
    public static function getLastPageRoute()
    {
        /** @var Session $session */
        $session = Lev::instance()['session'];
        $route = $session->lastPageRoute;
        if ($route) {
            return $route;
        }

        /** @var Admin $admin */
        $admin = Lev::instance()['admin'];

        return $admin->getCurrentRoute();
    }

    /**
     * @param string $path
     * @param string|null $languageCode
     * @return Route
     */
    public function getAdminRoute(string $path = '', $languageCode = null): Route
    {
        /** @var Language $language */
        $language = $this->lev['language'];
        $languageCode = $languageCode ?? ($language->getActive() ?: null);
        $languagePrefix = $languageCode ? '/' . $languageCode : '';

        $root = $this->lev['uri']->rootUrl();
        $subRoute = rtrim($this->lev['pages']->base(), '/');
        $adminRoute = rtrim($this->lev['config']->get('plugins.admin.route'), '/');

        $parts = [
            'path' => $path,
            'query' => '',
            'query_params' => [],
            'lev' => [
                // TODO: Make URL to be /admin/en, not /en/admin.
                'root' => preg_replace('`//+`', '/', $root . $subRoute . $languagePrefix . $adminRoute),
                'language' => '', //$languageCode,
                'route' => ltrim($path, '/'),
                'params' => ''
            ],
        ];

        return RouteFactory::createFromParts($parts);
    }

    /**
     * @param string $route
     * @param string|null $languageCode
     * @return string
     */
    public function adminUrl(string $route = '', $languageCode = null)
    {
        return $this->getAdminRoute($route, $languageCode)->toString(true);
    }

    /**
     * Static helper method to return current route.
     *
     * @return string
     * @deprecated 1.10 Use $admin->getCurrentRoute() instead
     */
    public static function route()
    {
        user_error(__CLASS__ . '::' . __FUNCTION__ . '() is deprecated since Admin 1.9.7, use $admin->getCurrentRoute() instead', E_USER_DEPRECATED);

        $admin = Lev::instance()['admin'];

        return $admin->getCurrentRoute();
    }

    /**
     * @return string|null
     */
    public function getCurrentRoute()
    {
        $pages = static::enablePages();

        $route = '/' . ltrim($this->route, '/');

        /** @var PageInterface $page */
        $page         = $pages->find($route);
        $parent_route = null;
        if ($page) {
            /** @var PageInterface $parent */
            $parent       = $page->parent();
            $parent_route = $parent->rawRoute();
        }

        return $parent_route;
    }

    /**
     * Redirect to the route stored in $this->redirect
     *
     * Route may or may not be prefixed by /en or /admin or /en/admin.
     *
     * @param string $redirect
     * @param int $redirectCode
     * @return void
     */
    public function redirect($redirect, $redirectCode = 303)
    {
        // No redirect, do nothing.
        if (!$redirect) {
            return;
        }

        Admin::DEBUG && Admin::addDebugMessage("Admin redirect: {$redirectCode} {$redirect}");

        $redirect = '/' . ltrim(preg_replace('`//+`', '/', $redirect), '/');
        $base = $this->base;
        $root = Lev::instance()['uri']->rootUrl();
        if ($root === '/') {
            $root = '';
        }

        $pattern = '`^((' . preg_quote($root, '`') . ')?(/[^/]+)?)' . preg_quote($base, '`') . '`ui';
        // Check if we already have an admin path: /admin, /en/admin, /root/admin or /root/en/admin.
        if (preg_match($pattern, $redirect)) {
            $redirect = preg_replace('|^' . preg_quote($root, '|') . '|', '', $redirect);

            $this->lev->redirect($redirect, $redirectCode);
        }

        if ($this->isMultilang()) {
            // Check if URL does not have language prefix.
            if (!Utils::pathPrefixedByLangCode($redirect)) {
                /** @var Language $language */
                $language = $this->lev['language'];

                // Prefix path with language prefix: /en
                // TODO: Use /admin/en instead of /en/admin in the future.
                $redirect = $language->getLanguageURLPrefix($this->lev['session']->admin_lang) . $base . $redirect;
            } else {
                // TODO: Use /admin/en instead of /en/admin in the future.
                //$redirect = preg_replace('`^(/[^/]+)/admin`', '\\1', $redirect);

                // Check if we already have language prefixed admin path: /en/admin
                $this->lev->redirect($redirect, $redirectCode);
            }
        } else {
            // TODO: Use /admin/en instead of /en/admin in the future.
            // Prefix path with /admin
            $redirect = $base . $redirect;
        }

        $this->lev->redirect($redirect, $redirectCode);
    }

    /**
     * Return true if multilang is active
     *
     * @return bool True if multilang is active
     */
    protected function isMultilang()
    {
        return count($this->lev['config']->get('system.languages.supported', [])) > 1;
    }

    /**
     * @return string
     */
    public static function getTempDir()
    {
        try {
            $tmp_dir = Lev::instance()['locator']->findResource('tmp://', true, true);
        } catch (\Exception $e) {
            $tmp_dir = Lev::instance()['locator']->findResource('cache://', true, true) . '/tmp';
        }

        return $tmp_dir;
    }

    /**
     * @return array
     */
    public static function getPageMedia()
    {
        $files = [];
        $lev = Lev::instance();

        $pages = static::enablePages();

        $route = '/' . ltrim($lev['admin']->route, '/');

        /** @var PageInterface $page */
        $page = $pages->find($route);
        $parent_route = null;
        if ($page) {
            $media = $page->media()->all();
            $files = array_keys($media);
        }
        return $files;

    }

    /**
     * Get current session.
     *
     * @return Session
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Fetch and delete messages from the session queue.
     *
     * @param string|null $type
     * @return array
     */
    public function messages($type = null)
    {
        /** @var Message $messages */
        $messages = $this->lev['messages'];

        return $messages->fetch($type);
    }

    /**
     * Authenticate user.
     *
     * @param array $credentials User credentials.
     * @param array $post
     * @return never-return
     */
    public function authenticate($credentials, $post)
    {
        /** @var Login $login */
        $login = $this->lev['login'];

        // Remove login nonce from the form.
        $credentials = array_diff_key($credentials, ['admin-nonce' => true]);
        $twofa = $this->lev['config']->get('plugins.admin.twofa_enabled', false);

        $rateLimiter = $login->getRateLimiter('login_attempts');

        $userKey = (string)($credentials['username'] ?? '');
        $ipKey = Uri::ip();
        $redirect = $post['redirect'] ?? $this->base . $this->route;

        // Pseudonymization of the IP
        $ipKey = sha1($ipKey . $this->lev['config']->get('security.salt'));

        // Check if the current IP has been used in failed login attempts.
        $attempts = count($rateLimiter->getAttempts($ipKey, 'ip'));

        $rateLimiter->registerRateLimitedAction($ipKey, 'ip')->registerRateLimitedAction($userKey);

        // Check rate limit for both IP and user, but allow each IP a single try even if user is already rate limited.
        if ($rateLimiter->isRateLimited($ipKey, 'ip') || ($attempts && $rateLimiter->isRateLimited($userKey))) {
            Admin::DEBUG && Admin::addDebugMessage('Admin login: rate limit, redirecting', $credentials);

            $this->setMessage(static::translate(['PLUGIN_LOGIN.TOO_MANY_LOGIN_ATTEMPTS', $rateLimiter->getInterval()]), 'error');

            $this->lev->redirect('/');
        }

        Admin::DEBUG && Admin::addDebugMessage('Admin login', $credentials);

        // Fire Login process.
        $event = $login->login(
            $credentials,
            ['admin' => true, 'twofa' => $twofa],
            ['authorize' => 'admin.login', 'return_event' => true]
        );
        $user = $event->getUser();

        Admin::DEBUG && Admin::addDebugMessage('Admin login: user', $user);

        if ($user->authenticated) {
            $rateLimiter->resetRateLimit($ipKey, 'ip')->resetRateLimit($userKey);
            if ($user->authorized) {
                $event->defMessage('PLUGIN_ADMIN.LOGIN_LOGGED_IN', 'info');

                $event->defRedirect($post['redirect'] ?? $redirect);
            } else {
                $this->session->redirect = $redirect;
            }
        } else {
            if ($user->authorized) {
                $event->defMessage('PLUGIN_LOGIN.ACCESS_DENIED', 'error');
            } else {
                $event->defMessage('PLUGIN_LOGIN.LOGIN_FAILED', 'error');
            }
        }

        $event->defRedirect($redirect);

        $message = $event->getMessage();
        if ($message) {
            $this->setMessage(static::translate($message), $event->getMessageType());
        }

        /** @var Pages $pages */
        $pages = $this->lev['pages'];
        $redirect = $pages->baseRoute() . $event->getRedirect();

        $this->lev->redirect($redirect, $event->getRedirectCode());
    }

    /**
     * Check Two-Factor Authentication.
     *
     * @param array $data
     * @param array $post
     * @return never-return
     */
    public function twoFa($data, $post)
    {
        /** @var Pages $pages */
        $pages = $this->lev['pages'];
        $baseRoute = $pages->baseRoute();

        /** @var Login $login */
        $login = $this->lev['login'];

        /** @var TwoFactorAuth $twoFa */
        $twoFa = $login->twoFactorAuth();
        $user = $this->lev['user'];

        $code = $data['2fa_code'] ?? null;

        $secret = $user->twofa_secret ?? null;

        if (!$code || !$secret || !$twoFa->verifyCode($secret, $code)) {
            $login->logout(['admin' => true]);

            $this->lev['session']->setFlashCookieObject(Admin::TMP_COOKIE_NAME, ['message' => $this->translate('PLUGIN_ADMIN.2FA_FAILED'), 'status' => 'error']);

            $this->lev->redirect($baseRoute . $this->uri->route(), 303);
        }

        $this->setMessage($this->translate('PLUGIN_ADMIN.LOGIN_LOGGED_IN'), 'info');

        $user->authorized = true;

        $redirect = $baseRoute . $post['redirect'];

        $this->lev->redirect($redirect);
    }

    /**
     * Logout from admin.
     *
     * @param array $data
     * @param array $post
     * @return never-return
     */
    public function logout($data, $post)
    {
        /** @var Login $login */
        $login = $this->lev['login'];

        $event = $login->logout(['admin' => true], ['return_event' => true]);

        $event->defMessage('PLUGIN_ADMIN.LOGGED_OUT', 'info');
        $message = $event->getMessage();
        if ($message) {
            $this->lev['session']->setFlashCookieObject(Admin::TMP_COOKIE_NAME, ['message' => $this->translate($message), 'status' => $event->getMessageType()]);
        }

        $this->lev->redirect($this->base);
    }

    /**
     * @return bool
     */
    public static function doAnyUsersExist()
    {
        $accounts = Lev::instance()['accounts'] ?? null;

        return $accounts && $accounts->count() > 0;
    }

    /**
     * Add message into the session queue.
     *
     * @param string $msg
     * @param string $type
     * @return void
     */
    public function setMessage($msg, $type = 'info')
    {
        /** @var Message $messages */
        $messages = $this->lev['messages'];
        $messages->add($msg, $type);
    }

    /**
     * @param string $msg
     * @param string $type
     * @return void
     */
    public function addTempMessage($msg, $type)
    {
        $this->temp_messages[] = ['message' => $msg, 'scope' => $type];
    }

    /**
     * @return array
     */
    public function getTempMessages()
    {
        return $this->temp_messages;
    }

    /**
     * Translate a string to the user-defined language
     *
     * @param array|string $args
     * @param array|null $languages
     * @return string|string[]|null
     */
    public static function translate($args, $languages = null)
    {
        $lev = Lev::instance();

        if (is_array($args)) {
            $lookup = array_shift($args);
        } else {
            $lookup = $args;
            $args   = [];
        }

        if (!$languages) {
            if ($lev['config']->get('system.languages.translations_fallback', true)) {
                $languages = $lev['language']->getFallbackLanguages();
            } else {
                $languages = (array)$lev['language']->getDefault();
            }
            $languages = $lev['user']->authenticated ? [$lev['user']->language] : $languages;
        } else {
            $languages = (array)$languages;
        }

        foreach ((array)$languages as $lang) {
            $translation = $lev['language']->getTranslation($lang, $lookup, true);

            if (!$translation) {
                $language    = $lev['language']->getDefault() ?: 'en';
                $translation = $lev['language']->getTranslation($language, $lookup, true);
            }

            if (!$translation) {
                $language    = 'en';
                $translation = $lev['language']->getTranslation($language, $lookup, true);
            }

            if ($translation) {
                if (count($args) >= 1) {
                    return vsprintf($translation, $args);
                }

                return $translation;
            }
        }

        return $lookup;
    }

    /**
     * Checks user authorisation to the action.
     *
     * @param  string|string[] $action
     * @return bool
     */
    public function authorize($action = 'admin.login')
    {
        $action = (array)$action;

        $user = $this->user;

        foreach ($action as $a) {
            // Ignore 'admin.super' if it's not the only value to be checked.
            if ($a === 'admin.super' && count($action) > 1 && $user instanceof FlexObjectInterface) {
                continue;
            }
            if ($user->authorize($a)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets configuration data.
     *
     * @param string $type
     * @param array  $post
     * @return object
     * @throws \RuntimeException
     */
    public function data($type, array $post = [])
    {
        if (!$post) {
            $post = $this->preparePost($this->lev['uri']->post()['data'] ?? []);
        }

        try {
            return $this->getConfigurationData($type, $post);
        } catch (\RuntimeException $e) {
            return new Data\Data();
        }
    }

    /**
     * Get configuration data.
     *
     * Note: If you pass $post, make sure you pass all the fields in the blueprint or data gets lost!
     *
     * @param string $type
     * @param array|null  $post
     * @return object
     * @throws \RuntimeException
     */
    public function getConfigurationData($type, array $post = null)
    {
        static $data = [];

        if (isset($data[$type])) {
            $obj = $data[$type];
            if ($post) {
                if ($obj instanceof Data\Data) {
                    $obj = $this->mergePost($obj, $post);
                } elseif ($obj instanceof UserInterface) {
                    $obj->update($this->cleanUserPost($post));
                }
            }

            return $obj;
        }

        // Check to see if a data type is plugin-provided, before looking into core ones
        $event = $this->lev->fireEvent('onAdminData', new Event(['type' => &$type]));
        if ($event) {
            if (isset($event['data_type'])) {
                return $event['data_type'];
            }

            if (is_string($event['type'])) {
                $type = $event['type'];
            }
        }

        /** @var UniformResourceLocator $locator */
        $locator  = $this->lev['locator'];

        // Configuration file will be saved to the existing config stream.
        $filename = $locator->findResource('config://') . "/{$type}.yaml";
        $file     = CompiledYamlFile::instance($filename);

        if (preg_match('|plugins/|', $type)) {
            $obj = Plugins::get(preg_replace('|plugins/|', '', $type));
            if (null === $obj) {
                throw new \RuntimeException("Plugin '{$type}' doesn't exist!");
            }
            $obj->file($file);

        } elseif (preg_match('|themes/|', $type)) {
            /** @var Themes $themes */
            $themes = $this->lev['themes'];
            $obj = $themes->get(preg_replace('|themes/|', '', $type));
            if (null === $obj) {
                throw new \RuntimeException("Theme '{$type}' doesn't exist!");
            }
            $obj->file($file);

        } elseif (preg_match('|users?/|', $type)) {
            /** @var UserCollectionInterface $users */
            $users = $this->lev['accounts'];

            $obj = $users->load(preg_replace('|users?/|', '', $type));

        } elseif (preg_match('|config/|', $type)) {
            $type       = preg_replace('|config/|', '', $type);
            $blueprints = $this->blueprints("config/{$type}");
            if (!$blueprints->form()) {
                throw new \RuntimeException("Configuration type '{$type}' doesn't exist!");
            }

            // Configuration file will be saved to the existing config stream.
            $filename = $locator->findResource('config://') . "/{$type}.yaml";
            $file     = CompiledYamlFile::instance($filename);

            $config = $this->lev['config'];
            $obj = new Data\Data($config->get($type, []), $blueprints);
            $obj->file($file);

        } elseif (preg_match('|media-manager/|', $type)) {
            $filename = base64_decode(preg_replace('|media-manager/|', '', $type));

            $file = File::instance($filename);

            $pages = static::enablePages();

            $obj = new \stdClass();
            $obj->title = $file->basename();
            $obj->path = $file->filename();
            $obj->file = $file;
            $obj->page = $pages->get(dirname($obj->path));

            $fileInfo = Utils::pathinfo($obj->title);
            $filename = str_replace(['@3x', '@2x'], '', $fileInfo['filename']);
            if (isset($fileInfo['extension'])) {
                $filename .= '.' . $fileInfo['extension'];
            }

            if ($obj->page && isset($obj->page->media()[$filename])) {
                $obj->metadata = new Data\Data($obj->page->media()[$filename]->metadata());
            }

        } else {
            throw new \RuntimeException("Data type '{$type}' doesn't exist!");
        }

        $data[$type] = $obj;
        if ($post) {
            if ($obj instanceof Data\Data) {
                $obj = $this->mergePost($obj, $post);
            } elseif ($obj instanceof UserInterface) {
                $obj->update($this->cleanUserPost($post));
            }
        }

        return $obj;
    }

    /**
     * @param Data\Data $object
     * @param array $post
     * @return Data\Data
     */
    protected function mergePost(Data\Data $object, array $post)
    {
        $object->merge($post);

        $blueprint = $object->blueprints();
        $data = $blueprint->flattenData($post, true);

        foreach ($data as $key => $val) {
            if ($val === null) {
                $object->set($key, $val);
            }
        }

        return $object;
    }

    /**
     * Clean user form post and remove extra stuff that may be passed along
     *
     * @param array $post
     * @return array
     */
    public function cleanUserPost($post)
    {
        // Clean fields for all users
        unset($post['hashed_password']);

        // Clean field for users who shouldn't be able to modify these fields
        if (!$this->authorize(['admin.user', 'admin.super'])) {
            unset($post['access'], $post['state']);
        }

        return $post;
    }

    /**
     * @return bool
     */
    protected function hasErrorMessage()
    {
        $msgs = $this->lev['messages']->all();
        foreach ($msgs as $msg) {
            if (isset($msg['scope']) && $msg['scope'] === 'error') {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns blueprints for the given type.
     *
     * @param string $type
     * @return Data\Blueprint
     */
    public function blueprints($type)
    {
        if ($this->blueprints === null) {
            $this->blueprints = new Data\Blueprints('blueprints://');
        }

        return $this->blueprints->get($type);
    }

    /**
     * Converts dot notation to array notation.
     *
     * @param  string $name
     * @return string
     */
    public function field($name)
    {
        $path = explode('.', $name);

        return array_shift($path) . ($path ? '[' . implode('][', $path) . ']' : '');
    }

    /**
     * Get all routes.
     *
     * @param bool $unique
     * @return array
     */
    public function routes($unique = false)
    {
        $pages = static::enablePages();

        if ($unique) {
            $routes = array_unique($pages->routes());
        } else {
            $routes = $pages->routes();
        }

        return $routes;
    }

    /**
     * Count the pages
     *
     * @return int
     */
    public function pagesCount()
    {
        if (!$this->pages_count) {
            $pages = static::enablePages();
            $this->pages_count = count($pages->all());
        }

        return $this->pages_count;
    }

    /**
     * Get all template types
     *
     * @param array|null $ignore
     * @return array
     */
    public function types(?array $ignore = [])
    {
        if (null === $ignore) {
            return AdminPlugin::pagesTypes();
        }

        $types = Pages::types();

        return $ignore ? array_diff_key($types, array_flip($ignore)) : $types;
    }

    /**
     * Get all modular template types
     *
     * @param array|null $ignore
     * @return array
     */
    public function modularTypes(?array $ignore = [])
    {
        if (null === $ignore) {
            return AdminPlugin::pagesModularTypes();
        }

        $types = Pages::modularTypes();

        return $ignore ? array_diff_key($types, array_flip($ignore)) : $types;
    }

    /**
     * Get all access levels
     *
     * @return array
     */
    public function accessLevels()
    {
        $pages = static::enablePages();

        if (method_exists($pages, 'accessLevels')) {
            return $pages->accessLevels();
        }

        return [];
    }

    /**
     * @param string|null $package_slug
     * @return string[]|string
     */
    public function license($package_slug)
    {
        return Licenses::get($package_slug);
    }

    /**
     * Generate an array of dependencies for a package, used to generate a list of
     * packages that can be removed when removing a package.
     *
     * @param string $slug The package slug
     * @return array|bool
     */
    public function dependenciesThatCanBeRemovedWhenRemoving($slug)
    {
        $gpm = $this->gpm();
        if (!$gpm) {
            return false;
        }

        $dependencies = [];

        $package = $this->getPackageFromGPM($slug);

        if ($package && $package->dependencies) {
            foreach ($package->dependencies as $dependency) {
//                if (count($gpm->getPackagesThatDependOnPackage($dependency)) > 1) {
//                    continue;
//                }
                if (isset($dependency['name'])) {
                    $dependency = $dependency['name'];
                }

                if (!in_array($dependency, $dependencies, true) && !in_array($dependency, ['admin', 'form', 'login', 'email', 'php'])) {
                    $dependencies[] = $dependency;
                }
            }
        }

        return $dependencies;
    }

    /**
     * Get the GPM instance
     *
     * @return GPM The GPM instance
     */
    public function gpm()
    {
        if (!$this->gpm) {
            try {
                $this->gpm = new GPM();
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        return $this->gpm;
    }

    /**
     * @param string $package_slug
     * @return mixed
     */
    public function getPackageFromGPM($package_slug)
    {
        $package = $this->plugins(true)[$package_slug];
        if (!$package) {
            $package = $this->themes(true)[$package_slug];
        }

        return $package;
    }

    /**
     * Get all plugins.
     *
     * @param bool $local
     * @return mixed
     */
    public function plugins($local = true)
    {
        $gpm = $this->gpm();

        if (!$gpm) {
            return false;
        }

        if ($local) {
            return $gpm->getInstalledPlugins();
        }

        $plugins = $gpm->getRepositoryPlugins();
        if ($plugins) {
            return $plugins->filter(function ($package, $slug) use ($gpm) {
                return !$gpm->isPluginInstalled($slug);
            });
        }

        return [];
    }

    /**
     * Get all themes.
     *
     * @param bool $local
     * @return mixed
     */
    public function themes($local = true)
    {
        $gpm = $this->gpm();

        if (!$gpm) {
            return false;
        }

        if ($local) {
            return $gpm->getInstalledThemes();
        }

        $themes = $gpm->getRepositoryThemes();
        if ($themes) {
            return $themes->filter(function ($package, $slug) use ($gpm) {
                return !$gpm->isThemeInstalled($slug);
            });
        }

        return [];
    }

    /**
     * Get list of packages that depend on the passed package slug
     *
     * @param string $slug The package slug
     *
     * @return array|bool
     */
    public function getPackagesThatDependOnPackage($slug)
    {
        $gpm = $this->gpm();
        if (!$gpm) {
            return false;
        }

        return $gpm->getPackagesThatDependOnPackage($slug);
    }

    /**
     * Check the passed packages list can be updated
     *
     * @param array $packages
     * @return bool
     * @throws \Exception
     */
    public function checkPackagesCanBeInstalled($packages)
    {
        $gpm = $this->gpm();
        if (!$gpm) {
            return false;
        }

        $this->gpm->checkPackagesCanBeInstalled($packages);

        return true;
    }

    /**
     * Get an array of dependencies needed to be installed or updated for a list of packages
     * to be installed.
     *
     * @param array $packages The packages slugs
     * @return array|bool
     */
    public function getDependenciesNeededToInstall($packages)
    {
        $gpm = $this->gpm();
        if (!$gpm) {
            return false;
        }

        return $this->gpm->getDependencies($packages);
    }

    /**
     * Used by the Dashboard in the admin to display the X latest pages
     * that have been modified
     *
     * @param  int $count number of pages to pull back
     * @return array|null
     */
    public function latestPages($count = 10)
    {
        /** @var Flex $flex */
        $flex = $this->lev['flex_objects'] ?? null;
        $directory = $flex ? $flex->getDirectory('pages') : null;
        if ($directory) {
            return $directory->getIndex()->sort(['timestamp' => 'DESC'])->slice(0, $count);
        }

        $pages = static::enablePages();

        $latest = [];

        if (null === $pages->routes()) {
            return null;
        }

        foreach ($pages->routes() as $url => $path) {
            $page = $pages->find($url, true);
            if ($page && $page->routable()) {
                $latest[$page->route()] = ['modified' => $page->modified(), 'page' => $page];
            }
        }

        // sort based on modified
        uasort($latest, function ($a, $b) {
            if ($a['modified'] == $b['modified']) {
                return 0;
            }

            return ($a['modified'] > $b['modified']) ? -1 : 1;
        });

        // build new array with just pages in it
        $list = [];
        foreach ($latest as $item) {
            $list[] = $item['page'];
        }

        return array_slice($list, 0, $count);
    }

    /**
     * Get log file for fatal errors.
     *
     * @return string
     */
    public function logEntry()
    {
        $file    = File::instance($this->lev['locator']->findResource("log://{$this->route}.html"));
        $content = $file->content();
        $file->free();

        return $content;
    }

    /**
     * Search in the logs when was the latest backup made
     *
     * @return array Array containing the latest backup information
     */
    public function lastBackup()
    {
        $file    = JsonFile::instance($this->lev['locator']->findResource('log://backup.log'));
        $content = $file->content();
        if (empty($content)) {
            return [
                'days'        => '&infin;',
                'chart_fill'  => 100,
                'chart_empty' => 0
            ];
        }

        $backup = new \DateTime();
        $backup->setTimestamp($content['time']);
        $diff = $backup->diff(new \DateTime());

        $days       = $diff->days;
        $chart_fill = $days > 30 ? 100 : round($days / 30 * 100);

        return [
            'days'        => $days,
            'chart_fill'  => $chart_fill,
            'chart_empty' => 100 - $chart_fill
        ];
    }

    /**
     * Determine if the plugin or theme info passed is from Team Lev
     *
     * @param object $info Plugin or Theme info object
     * @return bool
     */
    public function isTeamLev($info)
    {
        return isset($info['author']['name']) && ($info['author']['name'] === 'Team Lev' || Utils::contains($info['author']['name'], 'Levitation'));
    }

    /**
     * Determine if the plugin or theme info passed is premium
     *
     * @param object $info Plugin or Theme info object
     * @return bool
     */
    public function isPremiumProduct($info)
    {
        return isset($info['premium']);
    }

    /**
     * Renders phpinfo
     *
     * @return string The phpinfo() output
     */
    public function phpinfo()
    {
        if (function_exists('phpinfo')) {
            ob_start();
            phpinfo();
            $pinfo = ob_get_clean();
            $pinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);

            return $pinfo;
        }

        return 'phpinfo() method is not available on this server.';
    }

    /**
     * Guest date format based on euro/US
     *
     * @param string|null $date
     * @return string
     */
    public function guessDateFormat($date)
    {
        static $guess;

        $date_formats = [
            'm/d/y',
            'm/d/Y',
            'n/d/y',
            'n/d/Y',
            'd-m-Y',
            'd-m-y',
        ];

        $time_formats = [
            'H:i',
            'G:i',
            'h:ia',
            'g:ia'
        ];

        $date = (string)$date;
        if (!isset($guess[$date])) {
            $guess[$date] = 'd-m-Y H:i';
            foreach ($date_formats as $date_format) {
                foreach ($time_formats as $time_format) {
                    $full_format = "{$date_format} {$time_format}";
                    if ($this->validateDate($date, $full_format)) {
                        $guess[$date] = $full_format;
                        break 2;
                    }
                    $full_format = "{$time_format} {$date_format}";
                    if ($this->validateDate($date, $full_format)) {
                        $guess[$date] = $full_format;
                        break 2;
                    }
                }
            }
        }

        return $guess[$date];
    }

    /**
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function validateDate($date, $format)
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    /**
     * @param string $php_format
     * @return string
     */
    public function dateformatToMomentJS($php_format)
    {
        $SYMBOLS_MATCHING = [
            // Day
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'Do',
            'w' => 'd',
            'z' => 'DDD',
            // Week
            'W' => 'W',
            // Month
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '',
            // Year
            'L' => '',
            'o' => 'GGGG',
            'Y' => 'YYYY',
            'y' => 'yy',
            // Time
            'a' => 'a',
            'A' => 'A',
            'B' => 'SSS',
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => '',
            // Timezone
            'e' => '',
            'I' => '',
            'O' => 'ZZ',
            'P' => 'Z',
            'T' => 'z',
            'Z' => '',
            // Full Date/Time
            'c' => '',
            'r' => 'llll ZZ',
            'U' => 'X'
        ];
        $js_format        = '';
        $escaping         = false;
        $len = strlen($php_format);
        for ($i = 0; $i < $len; $i++) {
            $char = $php_format[$i];
            if ($char === '\\') // PHP date format escaping character
            {
                $i++;
                if ($escaping) {
                    $js_format .= $php_format[$i];
                } else {
                    $js_format .= '\'' . $php_format[$i];
                }
                $escaping = true;
            } else {
                if ($escaping) {
                    $js_format .= "'";
                    $escaping = false;
                }
                if (isset($SYMBOLS_MATCHING[$char])) {
                    $js_format .= $SYMBOLS_MATCHING[$char];
                } else {
                    $js_format .= $char;
                }
            }
        }

        return $js_format;
    }

    /**
     * Gets the entire permissions array
     *
     * @return array
     * @deprecated 1.10 Use $lev['permissions']->getInstances() instead.
     */
    public function getPermissions()
    {
        user_error(__METHOD__ . '() is deprecated since Admin 1.10, use $lev[\'permissions\']->getInstances() instead', E_USER_DEPRECATED);

        $lev = $this->lev;
        /** @var Permissions $permissions */
        $permissions = $lev['permissions'];

        return array_fill_keys(array_keys($permissions->getInstances()), 'boolean');
    }

    /**
     * Sets the entire permissions array
     *
     * @param array $permissions
     * @deprecated 1.10 Use PermissionsRegisterEvent::class event instead.
     */
    public function setPermissions($permissions)
    {
        user_error(__METHOD__ . '() is deprecated since Admin 1.10, use PermissionsRegisterEvent::class event instead', E_USER_DEPRECATED);

        $this->addPermissions($permissions);
    }

    /**
     * Adds a permission to the permissions array
     *
     * @param array $permissions
     * @deprecated 1.10 Use RegisterPermissionsEvent::class event instead.
     */
    public function addPermissions($permissions)
    {
        user_error(__METHOD__ . '() is deprecated since Admin 1.10, use RegisterPermissionsEvent::class event instead', E_USER_DEPRECATED);

        $lev = $this->lev;
        /** @var Permissions $object */
        $object = $lev['permissions'];
        foreach ($permissions as $name => $type) {
            if (!$object->hasAction($name)) {
                $action = new Action($name);
                $object->addAction($action);
            }
        }
    }

    public function getNotifications($force = false)
    {
        $last_checked = null;
        $filename = $this->lev['locator']->findResource('app://data/notifications/' . md5($this->lev['user']->username) . YAML_EXT, true, true);
        $userStatus = $this->lev['locator']->findResource('app://data/notifications/' . $this->lev['user']->username . YAML_EXT, true, true);

        $notifications_file = CompiledYamlFile::instance($filename);
        $notifications_content = (array)$notifications_file->content();

        $userStatus_file = CompiledYamlFile::instance($userStatus);
        $userStatus_content = (array)$userStatus_file->content();

        $last_checked = $notifications_content['last_checked'] ?? null;
        $notifications = $notifications_content['data'] ?? array();
        $timeout = $this->lev['config']->get('system.session.timeout', 1800);

        if ($force || !$last_checked || empty($notifications) || (time() - $last_checked > $timeout)) {
            $body = Response::get('https://getgrav.org/notifications.json?' . time());
//            $body = Response::get('http://localhost/notifications.json?' . time());
            $notifications = json_decode($body, true);

            // Sort by date
            usort($notifications, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            // Reverse order and create a new array
            $notifications = array_reverse($notifications);
            $cleaned_notifications = [];

            foreach ($notifications as $key => $notification) {

                if (isset($notification['permissions']) && !$this->authorize($notification['permissions'])) {
                    continue;
                }

                if (isset($notification['dependencies'])) {
                    foreach ($notification['dependencies'] as $dependency => $constraints) {
                        if ($dependency === 'lev') {
                            if (!Semver::satisfies(LEV_GVERSION, $constraints)) {
                                continue 2;
                            }
                        } else {
                            $packages = array_merge($this->plugins()->toArray(), $this->themes()->toArray());
                            if (!isset($packages[$dependency])) {
                                continue 2;
                            } else {
                                $version = $packages[$dependency]['version'];
                                if (!Semver::satisfies($version, $constraints)) {
                                    continue 2;
                                }
                            }
                        }
                    }
                }

                $cleaned_notifications[] = $notification;

            }

            // reset notifications
            $notifications = [];

            foreach($cleaned_notifications as $notification) {
                foreach ($notification['location'] as $location) {
                    $notifications = array_merge_recursive($notifications, [$location => [$notification]]);
                }
            }


            $notifications_file->content(['last_checked' => time(), 'data' => $notifications]);
            $notifications_file->save();
        }

        foreach ($notifications as $location => $list) {
            $notifications[$location] = array_filter($list, function ($notification) use ($userStatus_content) {
                $element      = $userStatus_content[$notification['id']] ?? null;
                if (isset($element)) {
                    if (isset($notification['reappear_after'])) {
                        $now = new \DateTime();
                        $hidden_on = new \DateTime($element);
                        $hidden_on->modify($notification['reappear_after']);

                        if ($now >= $hidden_on) {
                            return true;
                        }
                    }

                    return false;
                }

                return true;
            });
        }


        return $notifications;
    }

    /**
     * Get https://getgrav.org news feed
     *
     * @return mixed
     * @throws MalformedXmlException
     */
    public function getFeed($force = false)
    {
        $last_checked = null;
        $filename = $this->lev['locator']->findResource('app://data/feed/' . md5($this->lev['user']->username) . YAML_EXT, true, true);

        $feed_file = CompiledYamlFile::instance($filename);
        $feed_content = (array)$feed_file->content();

        $last_checked = $feed_content['last_checked'] ?? null;
        $feed = $feed_content['data'] ?? array();
        $timeout = $this->lev['config']->get('system.session.timeout', 1800);

        if ($force || !$last_checked || empty($feed) || ($last_checked && (time() - $last_checked > $timeout))) {
            $feed_url = 'https://getgrav.org/blog.atom';
            $body = Response::get($feed_url);

            $reader = new Reader();
            $parser = $reader->getParser($feed_url, $body, 'utf-8');
            $data = $parser->execute()->getItems();

            // Get top 10
            $data = array_slice($data, 0, 10);

            $feed = array_map(function ($entry) {
                $simple_entry['title'] = $entry->getTitle();
                $simple_entry['url'] = $entry->getUrl();
                $simple_entry['date'] = $entry->getDate()->getTimestamp();
                $simple_entry['nicetime'] = $this->adminNiceTime($simple_entry['date']);
                return $simple_entry;
            }, $data);

            $feed_file->content(['last_checked' => time(), 'data' => $feed]);
            $feed_file->save();
        }

        return $feed;

    }

    public function adminNiceTime($date, $long_strings = true)
    {
        if (empty($date)) {
            return $this->translate('LEV.NICETIME.NO_DATE_PROVIDED', null);
        }

        if ($long_strings) {
            $periods = [
                'NICETIME.SECOND',
                'NICETIME.MINUTE',
                'NICETIME.HOUR',
                'NICETIME.DAY',
                'NICETIME.WEEK',
                'NICETIME.MONTH',
                'NICETIME.YEAR',
                'NICETIME.DECADE'
            ];
        } else {
            $periods = [
                'NICETIME.SEC',
                'NICETIME.MIN',
                'NICETIME.HR',
                'NICETIME.DAY',
                'NICETIME.WK',
                'NICETIME.MO',
                'NICETIME.YR',
                'NICETIME.DEC'
            ];
        }

        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];

        $now = time();

        // check if unix timestamp
        if ((string)(int)$date === (string)$date) {
            $unix_date = $date;
        } else {
            $unix_date = strtotime($date);
        }

        // check validity of date
        if (empty($unix_date)) {
            return $this->translate('LEV.NICETIME.BAD_DATE', null);
        }

        // is it future date or past date
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense      = $this->translate('LEV.NICETIME.AGO', null);

        } else {
            $difference = $unix_date - $now;
            $tense      = $this->translate('LEV.NICETIME.FROM_NOW', null);
        }

        $len = count($lengths) - 1;
        for ($j = 0; $difference >= $lengths[$j] && $j < $len; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference !== 1) {
            $periods[$j] .= '_PLURAL';
        }

        if ($this->lev['language']->getTranslation($this->lev['user']->language,
            $periods[$j] . '_MORE_THAN_TWO')
        ) {
            if ($difference > 2) {
                $periods[$j] .= '_MORE_THAN_TWO';
            }
        }

        $periods[$j] = $this->translate('LEV.'.$periods[$j], null);

        return "{$difference} {$periods[$j]} {$tense}";
    }

    public function findFormFields($type, $fields, $found_fields = [])
    {
        foreach ($fields as $key => $field) {

            if (isset($field['type']) && $field['type'] == $type) {
                $found_fields[$key] = $field;
            } elseif (isset($field['fields'])) {
                $result = $this->findFormFields($type, $field['fields'], $found_fields);
                if (!empty($result)) {
                    $found_fields = array_merge($found_fields, $result);
                }
            }
        }

        return $found_fields;
    }

    public function getPagePathFromToken($path, $page = null)
    {
        return Utils::getPagePathFromToken($path, $page ?: $this->page(true));
    }

    /**
     * Returns edited page.
     *
     * @param bool $route
     *
     * @param null $path
     *
     * @return PageInterface
     */
    public function page($route = false, $path = null)
    {
        if (!$path) {
            $path = $this->route;
        }

        if ($route && !$path) {
            $path = '/';
        }

        if (!isset($this->pages[$path])) {
            $this->pages[$path] = $this->getPage($path);
        }

        return $this->pages[$path];
    }

    /**
     * Returns the page creating it if it does not exist.
     *
     * @param string $path
     *
     * @return PageInterface|null
     */
    public function getPage($path)
    {
        $pages = static::enablePages();

        if ($path && $path[0] !== '/') {
            $path = "/{$path}";
        }

        // Fix for entities in path causing looping...
        $path = urldecode($path);

        $page = $path ? $pages->find($path, true) : $pages->root();

        if (!$page) {
            $slug = Utils::basename($path);

            if ($slug === '') {
                return null;
            }

            $ppath = str_replace('\\', '/', dirname($path));

            // Find or create parent(s).
            $parent = $this->getPage($ppath !== '/' ? $ppath : '');

            // Create page.
            $page = new Page();
            $page->parent($parent);
            $page->filePath($parent->path() . '/' . $slug . '/' . $page->name());

            // Add routing information.
            $pages->addPage($page, $path);

            // Set if Modular
            $page->modularTwig($slug[0] === '_');

            // Determine page type.
            if (isset($this->session->{$page->route()})) {
                // Found the type and header from the session.
                $data = $this->session->{$page->route()};

                // Set the key header value
                $header = ['title' => $data['title']];

                if (isset($data['visible'])) {
                    if ($data['visible'] === '' || $data['visible']) {
                        // if auto (ie '')
                        $pageParent = $page->parent();
                        $children = $pageParent ? $pageParent->children() : [];
                        foreach ($children as $child) {
                            if ($child->order()) {
                                // set page order
                                $page->order(AdminController::getNextOrderInFolder($pageParent->path()));
                                break;
                            }
                        }
                    }
                    if ((int)$data['visible'] === 1 && !$page->order()) {
                        $header['visible'] = $data['visible'];
                    }

                }

                if ($data['name'] === 'modular') {
                    $header['body_classes'] = 'modular';
                }

                $name = $page->isModule() ? str_replace('modular/', '', $data['name']) : $data['name'];
                $page->name($name . '.md');

                // Fire new event to allow plugins to manipulate page frontmatter
                $this->lev->fireEvent('onAdminCreatePageFrontmatter', new Event(['header' => &$header,
                        'data' => $data]));

                $page->header($header);
                $page->frontmatter(Yaml::dump((array)$page->header(), 20));
            } else {
                // Find out the type by looking at the parent.
                $type = $parent->childType() ?: $parent->blueprints()->get('child_type', 'default');
                $page->name($type . CONTENT_EXT);
                $page->header();
            }
        }

        return $page;
    }

    public function generateReports()
    {
        $reports = new ArrayCollection();

        $pages = static::enablePages();

        // Default to XSS Security Report
        $result = Security::detectXssFromPages($pages, true);

        $reports['Lev Security Check'] = $this->lev['twig']->processTemplate('reports/security.html.twig', [
            'result' => $result,
        ]);

        // Linting Issues

        $result = YamlLinter::lint();

        $reports['Lev Yaml Linter'] = $this->lev['twig']->processTemplate('reports/yamllinter.html.twig', [
           'result' => $result,
        ]);

        // Fire new event to allow plugins to manipulate page frontmatter
        $this->lev->fireEvent('onAdminGenerateReports', new Event(['reports' => $reports]));

        return $reports;
    }

    public function getRouteDetails()
    {
        return [$this->base, $this->location, $this->route];
    }

    /**
     * Get the files list
     *
     * @param bool $filtered
     * @param int $page_index
     * @return array|null
     * @todo allow pagination
     */
    public function files($filtered = true, $page_index = 0)
    {
        $param_type = $this->lev['uri']->param('type');
        $param_date = $this->lev['uri']->param('date');
        $param_page = $this->lev['uri']->param('page');
        $param_page = str_replace('\\', '/', $param_page);

        $files_cache_key = 'media-manager-files';

        if ($param_type) {
            $files_cache_key .= "-{$param_type}";
        }
        if ($param_date) {
            $files_cache_key .= "-{$param_date}";
        }
        if ($param_page) {
            $files_cache_key .= "-{$param_page}";
        }

        $page_files = null;

        $cache_enabled = $this->lev['config']->get('plugins.admin.cache_enabled');
        if (!$cache_enabled) {
            $this->lev['cache']->setEnabled(true);
        }

        $page_files = $this->lev['cache']->fetch(md5($files_cache_key));

        if (!$cache_enabled) {
            $this->lev['cache']->setEnabled(false);
        }

        if (!$page_files) {
            $page_files = [];
            $pages = static::enablePages();

            if ($param_page) {
                $page = $pages->find($param_page);

                $page_files = $this->getFiles('images', $page, $page_files, $filtered);
                $page_files = $this->getFiles('videos', $page, $page_files, $filtered);
                $page_files = $this->getFiles('audios', $page, $page_files, $filtered);
                $page_files = $this->getFiles('files', $page, $page_files, $filtered);
            } else {
                $allPages = $pages->all();

                if ($allPages) foreach ($allPages as $page) {
                    $page_files = $this->getFiles('images', $page, $page_files, $filtered);
                    $page_files = $this->getFiles('videos', $page, $page_files, $filtered);
                    $page_files = $this->getFiles('audios', $page, $page_files, $filtered);
                    $page_files = $this->getFiles('files', $page, $page_files, $filtered);
                }
            }

            if (count($page_files) >= self::MEDIA_PAGINATION_INTERVAL) {
                $this->shouldLoadAdditionalFilesInBackground(true);
            }

            if (!$cache_enabled) {
                $this->lev['cache']->setEnabled(true);
            }
            $this->lev['cache']->save(md5($files_cache_key), $page_files, 600); //cache for 10 minutes
            if (!$cache_enabled) {
                $this->lev['cache']->setEnabled(false);
            }

        }

        if (count($page_files) >= self::MEDIA_PAGINATION_INTERVAL) {
            $page_files = array_slice($page_files, $page_index * self::MEDIA_PAGINATION_INTERVAL, self::MEDIA_PAGINATION_INTERVAL);
        }

        return $page_files;
    }

    public function shouldLoadAdditionalFilesInBackground($status = null)
    {
        if ($status) {
            $this->load_additional_files_in_background = true;
        }

        return $this->load_additional_files_in_background;
    }

    public function loadAdditionalFilesInBackground($status = null)
    {
        if (!$this->loading_additional_files_in_background) {
            $this->loading_additional_files_in_background = true;
            $this->files(false, false);
            $this->shouldLoadAdditionalFilesInBackground(false);
            $this->loading_additional_files_in_background = false;
        }
    }

    private function getFiles($type, $page, $page_files, $filtered)
    {
        $page_files = $this->getMediaOfType($type, $page, $page_files);

        if ($filtered) {
            $page_files = $this->filterByType($page_files);
            $page_files = $this->filterByDate($page_files);
        }

        return $page_files;
    }

    /**
     * Get all the media of a type ('images' | 'audios' | 'videos' | 'files')
     *
     * @param string $type
     * @param PageInterface|null $page
     * @param array $files
     *
     * @return array
     */
    private function getMediaOfType($type, ?PageInterface $page, array $files)
    {
        if ($page) {
            $media = $page->media();
            $mediaOfType = $media->$type();

            foreach($mediaOfType as $title => $file) {
                $files[] = [
                    'title' => $title,
                    'type' => $type,
                    'page_route' => $page->route(),
                    'file' => $file->higherQualityAlternative()
                ];
            }

            return $files;
        }

        return [];
    }

    /**
     * Filter media by type
     *
     * @param array $filesFiltered
     *
     * @return array
     */
    private function filterByType($filesFiltered)
    {
        $filter_type = $this->lev['uri']->param('type');
        if (!$filter_type) {
            return $filesFiltered;
        }

        $filesFiltered = array_filter($filesFiltered, function ($file) use ($filter_type) {
            return $file['type'] == $filter_type;
        });

        return $filesFiltered;
    }

    /**
     * Filter media by date
     *
     * @param array $filesFiltered
     *
     * @return array
     */
    private function filterByDate($filesFiltered)
    {
        $filter_date = $this->lev['uri']->param('date');
        if (!$filter_date) {
            return $filesFiltered;
        }

        $year = substr($filter_date, 0, 4);
        $month = substr($filter_date, 5, 2);

        $filesFilteredByDate = [];

        foreach($filesFiltered as $file) {
            $filedate = $this->fileDate($file['file']);
            $fileYear = $filedate->format('Y');
            $fileMonth = $filedate->format('m');

            if ($fileYear == $year && $fileMonth == $month) {
                $filesFilteredByDate[] = $file;
            }
        }

        return $filesFilteredByDate;
    }

    /**
     * Return the DateTime object representation of a file modified date
     *
     * @param File $file
     *
     * @return DateTime
     */
    private function fileDate($file) {
        $datetime = new \DateTime();
        $datetime->setTimestamp($file->toArray()['modified']);
        return $datetime;
    }

    /**
     * Get the files dates list to be used in the Media Files filter
     *
     * @return array
     */
    public function filesDates()
    {
        $files = $this->files(false);
        $dates = [];

        foreach ($files as $file) {
            $datetime = $this->fileDate($file['file']);
            $year = $datetime->format('Y');
            $month = $datetime->format('m');

            if (!isset($dates[$year])) {
                $dates[$year] = [];
            }

            if (!isset($dates[$year][$month])) {
                $dates[$year][$month] = 1;
            } else {
                $dates[$year][$month]++;
            }
        }

        return $dates;
    }

    /**
     * Get the pages list to be used in the Media Files filter
     *
     * @return array
     */
    public function pages()
    {
        $pages = static::enablePages();

        $collection = $pages->all();

        $pagesWithFiles = [];
        foreach ($collection as $page) {
            if (count($page->media()->all())) {
                $pagesWithFiles[] = $page;
            }
        }

        return $pagesWithFiles;
    }

    /**
     * @return Pages
     */
    public static function enablePages()
    {
        static $pages;

        if ($pages) {
            return $pages;
        }

        $lev = Lev::instance();
        $admin = $lev['admin'];

        /** @var Pages $pages */
        $pages = Lev::instance()['pages'];
        $pages->enablePages();

        // If page is null, the default page does not exist, and we cannot route to it
        $page = $pages->find('/', true);
        if ($page) {
            // Set original route for the home page.
            $home = '/' . trim($lev['config']->get('system.home.alias'), '/');

            $page->route($home);
        }

        $admin->routes = $pages->routes();

        // Remove default route from routes.
        if (isset($admin->routes['/'])) {
            unset($admin->routes['/']);
        }

        return $pages;
    }

    /**
     * Return HTTP_REFERRER if set
     *
     * @return null
     */
    public function getReferrer()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }


    /**
     * Get Lev system log files
     *
     * @return array
     */
    public function getLogFiles()
    {
        $logs = new LevData(['lev.log' => 'Lev System Log', 'email.log' => 'Email Log']);
        Lev::instance()->fireEvent('onAdminLogFiles', new Event(['logs' => &$logs]));
        return $logs->toArray();
    }

    /**
     * Get changelog for a given GPM package based on slug
     *
     * @param string|null $slug
     * @return array
     */
    public function getChangelog($slug = null)
    {
        $gpm = $this->gpm();
        $changelog = [];

        if (!empty($slug)) {
            $package = $gpm->findPackage($slug);
        } else {
            $package = $gpm->lev;
        }


        if ($package) {
            $changelog = $package->getChangelog();
        }

        return $changelog;
    }

    /**
     * Prepare and return POST data.
     *
     * @param array $post
     * @return array
     */
    public function preparePost($post): array
    {
        if (!is_array($post)) {
            return [];
        }

        unset($post['task']);

        // Decode JSON encoded fields and merge them to data.
        if (isset($post['_json'])) {
            $post = array_replace_recursive($post, $this->jsonDecode($post['_json']));
            unset($post['_json']);
        }

        return $this->cleanDataKeys($post);
    }

    /**
     * Recursively JSON decode data.
     *
     * @param array $data
     * @return array
     * @throws JsonException
     */
    private function jsonDecode(array $data): array
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->jsonDecode($value);
            } else {
                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
        }

        return $data;
    }

    /**
     * @param array $source
     * @return array
     */
    private function cleanDataKeys(array $source): array
    {
        $out = [];
        foreach ($source as $key => $value) {
            $key = str_replace(['%5B', '%5D'], ['[', ']'], $key);
            if (is_array($value)) {
                $out[$key] = $this->cleanDataKeys($value);
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }
}
