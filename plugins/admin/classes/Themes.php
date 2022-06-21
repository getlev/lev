<?php

namespace Lev\Plugin\Admin;

/**
 * Admin theme object
 *
 * @author  RocketTheme
 * @license MIT
 */
class Themes extends \Lev\Common\Themes
{
    public function init()
    {
        /** @var Themes $themes */
        $themes = $this->lev['themes'];
        $themes->configure();
        $themes->initTheme();

        $this->lev->fireEvent('onAdminThemeInitialized');
    }
}
