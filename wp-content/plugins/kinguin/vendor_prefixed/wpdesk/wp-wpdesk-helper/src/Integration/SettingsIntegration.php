<?php

namespace ILKinguinVendor\WPDesk\Helper\Integration;

use ILKinguinVendor\WPDesk\Helper\Page\SettingsPage;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \ILKinguinVendor\WPDesk\PluginBuilder\Plugin\Hookable, \ILKinguinVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\ILKinguinVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
    {
        $this->add_hookable($settingsPage);
    }
    /**
     * @return void
     */
    public function hooks()
    {
        $this->hooks_on_hookable_objects();
    }
}
