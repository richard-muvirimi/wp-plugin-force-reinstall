<?php
/**
 * File for the plugin Specific func]\tions
 *
 * All plugin specific functions are handled in one place
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Controller;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;
use Rich4rdMuvirimi\ForceReinstall\Helpers\Logger;

/**
 * Plugin controller
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Plugin extends BaseController
{


    /**
     * On plugin activation
     *
     * @return void
     * @version 1.1.3
     * @since 1.0.0
     */
    public static function on_activation(): void
    {

        if (boolval(get_transient(Functions::get_plugin_slug('-rate'))) === false) {
            set_transient(Functions::get_plugin_slug('-rate'), true, YEAR_IN_SECONDS / 4);
        }

        Logger::logEvent("activate_plugin");

    }

    /**
     * On plugin deactivation
     *
     * @return void
     * @version 1.1.3
     * @since 1.0.0
     */
    public static function on_deactivation(): void
    {
        Logger::logEvent("deactivate_plugin");
    }

    /**
     * On plugin uninstall
     *
     * @return void
     * @version 1.1.3
     * @since 1.0.0
     */
    public static function on_uninstall(): void
    {
        Logger::logEvent("uninstall_plugin");
    }
}
