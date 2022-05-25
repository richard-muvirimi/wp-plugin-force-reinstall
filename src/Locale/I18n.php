<?php
/**
 * Translations loader for Force Reinstall
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Locale
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Locale;

/**
 * Class to handle plugin translations
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Locale
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class I18n {


	/**
	 * Load the plugin translation files
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( FORCE_REINSTALL_SLUG, false, plugin_dir_path( FORCE_REINSTALL_FILE ) . 'languages' );
	}
}
