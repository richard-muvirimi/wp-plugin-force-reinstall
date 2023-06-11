<?php
/**
 * Translations loader for Force Reinstall
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Locale
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Locale;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

/**
 * Class to handle plugin translations
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Locale
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class I18n
{


    /**
     * Load the plugin translation files
     *
     * @return void
     * @version 1.0.0
     * @since 1.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(Functions::get_plugin_slug(), false, plugin_dir_path(FORCE_REINSTALL_FILE) . 'languages');
    }
}
