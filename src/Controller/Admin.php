<?php
/**
 * File for the Admin controller
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
 * Admin side controller
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.1
 */
class Admin extends BaseController {

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function enqueue_styles()
	{
		wp_register_style(Functions::get_plugin_slug( "-rate"),Functions::get_style_url('admin-rating.css'), array(), FORCE_REINSTALL_VERSION, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function enqueue_scripts()
	{

		switch (get_current_screen()->id) {
			case "themes":

				/**
				 * Would have loved a hook much like plugins but guess will have to add through javascript
				 * 
				 * tried "plugin_action_links" but could not be found on the front end
				 * But then again...
				 */
				wp_enqueue_script(FORCE_REINSTALL_SLUG, Functions::get_script_url('admin-reinstall-themes.js'), array('jquery'), FORCE_REINSTALL_VERSION, false);
				wp_localize_script(FORCE_REINSTALL_SLUG, "force_reinstall", array(
					"button" => $this->getThemeActionButton()
				));
				break;
		}

		wp_register_script(Functions::get_plugin_slug( "-rate"), Functions::get_script_url( 'admin-rating.js'), array('jquery'), FORCE_REINSTALL_VERSION, false);
		wp_localize_script(Functions::get_plugin_slug( "-rate"), "force_reinstall", array(
			"ajax_url" => admin_url('admin-ajax.php'),
			"name" => FORCE_REINSTALL_SLUG
		));
	}

	/**
	 * Get theme action button
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @return void
	 */
	private function getThemeActionButton()
	{

		$arguments = array(
			FORCE_REINSTALL_SLUG => "",
			Functions::get_plugin_slug( "-target") => "theme"
		);
		$target =  Functions::force_reinstall_target_self($arguments);

		return	sprintf(
			'<a class="button %s" href="%s">%s</a>',
			esc_attr(FORCE_REINSTALL_SLUG),
			esc_attr($target),
			__('Force Reinstall', FORCE_REINSTALL_SLUG)
		);
	}

	/**
	 * Add plugins page actions
	 * 
	 * @since 1.0.0
	 * @version 1.0.1
	 * @param array $actions
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @return array
	 */
	function plugin_action_links($actions, $plugin_file, $plugin_data)
	{

		if ((isset($plugin_data["url"]) && strlen($plugin_data["url"]) != 0) &&  (isset($plugin_data["package"]) && strlen($plugin_data["package"]) != 0)) {

			//target same page with plugin name to keep arguments
			$arguments = 				array(
				FORCE_REINSTALL_SLUG => $plugin_file,
				Functions::get_plugin_slug( "-target") => "plugin"
			);

			$target = Functions::force_reinstall_target_self($arguments);

			$link =  sprintf(
				'<a href="%s">%s</a>',
				esc_attr($target),
				__('Force Reinstall', FORCE_REINSTALL_SLUG)
			);

			array_push($actions, $link);
		}

		return $actions;
	}

	/**
	 * Handle requested action
	 * 
	 * @since 1.0.0
	 * @version 1.0.1
	 * @return void
	 */
	function handle_action()
	{

		if (wp_verify_nonce(filter_input(INPUT_GET, Functions::get_plugin_slug( "-nonce")), FORCE_REINSTALL_SLUG)) {

			switch (filter_input(INPUT_GET, Functions::get_plugin_slug( "-target"))) {
				case "plugin":
				case "theme":

					$target =	filter_input(INPUT_GET, FORCE_REINSTALL_SLUG);

					if ($target) {

						$this->_force_update($target);

						//remove our args and redirect
						wp_redirect(remove_query_arg(array("action", Functions::get_plugin_slug( "-nonce"), FORCE_REINSTALL_SLUG, Functions::get_plugin_slug( "-target")), $_SERVER['REQUEST_URI']));
						exit;
					}
					break;
				default:
			}
		}
	}

	/**
	 * Force an update on requested item
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @param string $target
	 * @return void
	 */
	private function _force_update($target)
	{
		/**
		 * Modify Site transient
		 */
		$updates =  get_site_transient('update_' . get_current_screen()->base);

		//check if we don't already need an update or can be...
		if (isset($updates->no_update[$target])) {

			$item = $updates->no_update[$target];

			//remove from no_updates
			unset($updates->no_update[$target]);

			//add to updates
			$updates->response[$target] = $item;

			//stall update checking
			$updates->last_checked = time();

			//save
			set_site_transient('update_' . get_current_screen()->base, $updates);
		}
	}

	/**
	 * Add Plugins bulk action
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @param array $actions
	 * @return array
	 */
	public function add_plugins_bulk_action($actions)
	{

		global $wp_version;

		//https://developer.wordpress.org/reference/hooks/handle_bulk_actions-screen/
		if (version_compare($wp_version, "4.7", ">=")) {
			$actions[FORCE_REINSTALL_SLUG] = __('Force Reinstall', FORCE_REINSTALL_SLUG);
		}
		return $actions;
	}

	/**
	 * Force update on plugins
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @param string $redirect
	 * @param string $action
	 * @param array $plugins
	 * @return string
	 */
	public function plugins_bulk_update($redirect, $action, $plugins)
	{

		if ($action == FORCE_REINSTALL_SLUG) {

			foreach ($plugins as $plugin) {
				$this->_force_update($plugin);
			}
		}

		return admin_url("update-core.php");
	}

	/**
	 * Show rating request
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @return void
	 */
	public function show_rating()
	{
		/**
		 * Request Rating
		 */
		if (boolval(get_transient(Functions::get_plugin_slug( "-rate"))) === false) {
			wp_enqueue_script(Functions::get_plugin_slug( "-rate"));
			wp_enqueue_style(Functions::get_plugin_slug( "-rate"));

			echo Functions::get_template( Functions::get_plugin_slug( '-admin-rating'), array(), 'admin-rating.php' );
		}
	}

}
