<?php
/**
 * File for the plugin Specific func]\tions
 *
 * All plugin specific functions are handled in one place
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Controller;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

/**
 * Plugin controller
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Plugin extends BaseController {


	/**
	 * On plugin activation
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public static function on_activation() {

		if ( boolval( get_transient( Functions::get_plugin_slug( '-rate' ) ) ) === false ) {
			set_transient( Functions::get_plugin_slug( '-rate' ), true, YEAR_IN_SECONDS / 4 );
		}

	}

	/**
	 * On plugin deactivation
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public static function on_deactivation() {

	}

	/**
	 * On plugin uninstall
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public static function on_uninstall() {

	}
}
