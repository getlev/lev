<?php

namespace Lev\Plugin\Admin\Twig;

use Lev\Common\Data\Data;
use Lev\Common\Lev;
use Lev\Common\Page\Interfaces\PageInterface;
use Lev\Common\Utils;
use Lev\Common\Yaml;
use Lev\Common\Language\Language;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Lev\Plugin\Admin\Admin;

class AdminTwigExtension extends AbstractExtension
{
    /** @var Lev */
    protected $lev;

    /** @var Language $lang */
    protected $lang;

    public function __construct()
    {
        $this->lev = Lev::instance();
        $this->lang = $this->lev['user']->language;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('tu', [$this, 'tuFilter']),
            new TwigFilter('toYaml', [$this, 'toYamlFilter']),
            new TwigFilter('fromYaml', [$this, 'fromYamlFilter']),
            new TwigFilter('adminNicetime', [$this, 'adminNicetimeFilter']),
            new TwigFilter('nested', [$this, 'nestedFilter']),
            new TwigFilter('flatten', [$this, 'flattenFilter']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_route', [$this, 'adminRouteFunc']),
            new TwigFunction('getPageUrl', [$this, 'getPageUrl']),
            new TwigFunction('clone', [$this, 'cloneFunc']),
            new TwigFunction('data', [$this, 'dataFunc']),
        ];
    }

    public function nestedFilter($current, $name)
    {
        $path = explode('.', trim($name, '.'));

        foreach ($path as $field) {
            if (is_object($current) && isset($current->{$field})) {
                $current = $current->{$field};
            } elseif (is_array($current) && isset($current[$field])) {
                $current = $current[$field];
            } else {
                return null;
            }
        }

        return $current;
    }

    public function flattenFilter($array)
    {
        return Utils::arrayFlattenDotNotation($array);
    }

    public function cloneFunc($obj)
    {
        return clone $obj;
    }

    public function adminRouteFunc(string $route = '', string $languageCode = null)
    {
        /** @var Admin $admin */
        $admin = Lev::instance()['admin'];

        return $admin->getAdminRoute($route, $languageCode)->toString(true);
    }

    public function getPageUrl(PageInterface $page)
    {
        /** @var Admin $admin */
        $admin = Lev::instance()['admin'];

        return $admin->getAdminRoute('/pages' . $page->rawRoute(), $page->language())->toString(true);
    }

    public static function tuFilter()
    {
        $args = func_get_args();
        $numargs = count($args);
        $lang = null;

        if (($numargs === 3 && is_array($args[1])) || ($numargs === 2 && !is_array($args[1]))) {
            $lang = array_pop($args);
        } elseif ($numargs === 2 && is_array($args[1])) {
            $subs = array_pop($args);
            $args = array_merge($args, $subs);
        }

        return Lev::instance()['admin']->translate($args, $lang);
    }

    public function toYamlFilter($value, $inline = null)
    {
        return Yaml::dump($value, $inline);

    }

    public function fromYamlFilter($value)
    {
        return Yaml::parse($value);
    }

    public function adminNicetimeFilter($date, $long_strings = true)
    {
        return Lev::instance()['admin']->adminNiceTime($date, $long_strings);
    }

    public function dataFunc(array $data, $blueprints = null)
    {
        return new Data($data, $blueprints);
    }
}
