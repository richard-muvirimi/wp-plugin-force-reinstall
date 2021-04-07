<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tyganeutronics.com
 * @since      1.0.0
 *
 * @package    Force_Reinstall
 * @subpackage Force_Reinstall/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Force_Reinstall
 * @subpackage Force_Reinstall/admin
 * @author     Richard Muvirimi <tygalive@gmail.com>
 */
class Force_Reinstall_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * @return void
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function enqueue_styles()
	{
		//wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/force-reinstall-admin.css', array(), $this->version, 'all');
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
				wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/force-reinstall-themes.js', array('jquery'), $this->version, false);
				wp_localize_script($this->plugin_name, "force_reinstall", array(
					"button" => $this->getThemeActionButton()
				));
				break;
		}
	}

	/**
	 * Get theme action button
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function getThemeActionButton()
	{

		$arguments = array(
			"action" => $this->plugin_name,
			$this->plugin_name . "-nonce" => wp_create_nonce($this->plugin_name),
			$this->plugin_name => "" //the rest of the url will be appended by js
		);
		$target =  admin_url(get_current_screen()->base . ".php") . "?" . http_build_query($arguments);

		return	sprintf(
			'<a class="button ' . $this->plugin_name . '" href="%s">%s</a>',
			esc_attr($target),
			__('Force Reinstall', $this->plugin_name)
		);
	}

	/**
	 * Add plugins page actions
	 * 
	 * @since 1.0.0
	 * @param array $actions
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @return array
	 */
	function plugin_action_links($actions, $plugin_file, $plugin_data)
	{

		if ((isset($plugin_data["url"]) && strlen($plugin_data["url"]) != 0) &&  (isset($plugin_data["package"]) && strlen($plugin_data["package"]) != 0)) {

			//target same page with plugin name to keep arguments
			$arguments = array_merge(
				array(
					"action" => $this->plugin_name,
					$this->plugin_name => $plugin_file,
					$this->plugin_name . "-nonce" => wp_create_nonce($this->plugin_name)
				),
				filter_input_array(INPUT_GET) ?: array()
			);

			$target = admin_url(get_current_screen()->base . ".php") . "?" . http_build_query($arguments);

			$link =  sprintf(
				'<a href="%s">%s</a>',
				esc_attr($target),
				__('Force Reinstall', $this->plugin_name)
			);

			array_push($actions, $link);
		}

		return $actions;
	}

	/**
	 * Force an update on requested item
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	function force_update()
	{

		$target =	filter_input(INPUT_GET, $this->plugin_name);

		if ($target && wp_verify_nonce(filter_input(INPUT_GET, $this->plugin_name . "-nonce"), $this->plugin_name)) {

			$this->_force_update($target);

			$this->request_rating();

			//remove our args and redirect
			wp_redirect(remove_query_arg(array("action", $this->plugin_name . "-nonce", $this->plugin_name), $_SERVER['REQUEST_URI']));
			exit;
		}
	}

	/**
	 * Force an update on requested item
	 *
	 * @since 1.0.0
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

			//modify version to force an update
			if (is_array($item)) {
				$item["new_version"] = $this->subtractVersion($item["new_version"]);
			} else {
				$item->{"new_version"} = $this->subtractVersion($item->{"new_version"});
			}

			//add to updates
			$updates->response[$target] = $item;

			//stall update checking
			$updates->last_checked = time();

			//save
			set_site_transient('update_' . get_current_screen()->base, $updates);
		}
	}

	/**
	 * Add Plugins bulk acton
	 *
	 * @since 1.0.0
	 * @param array $actions
	 * @return array
	 */
	public function add_plugins_bulk_action($actions)
	{
		$actions[$this->plugin_name] = __('Force Reinstall', $this->plugin_name);
		return $actions;
	}

	/**
	 * Force update on plugins
	 *
	 * @since 1.0.0
	 * @param string $redirect
	 * @param string $action
	 * @param array $plugins
	 * @return string
	 */
	public function plugins_bulk_update($redirect, $action, $plugins)
	{

		if ($action == $this->plugin_name) {

			foreach ($plugins as $plugin) {
				$this->_force_update($plugin);
			}
		}

		$this->request_rating();

		return admin_url("update-core.php");
	}

	/**
	 * Get a lower version number than provided
	 *
	 * @since 1.0.0
	 * @param string $version
	 * @return void
	 */
	private function subtractVersion($version)
	{

		/* 		$_version = "1";
		while (version_compare($_version, $version, ">=")) {
			$_version = "0." . $_version;
		}
		return $_version; */

		$versions = explode(".", $version);
		$length = count($versions);

		if ($length == 1) {
			//version is int
			$_version = "1";
			while (version_compare($_version, $versions[0], ">=")) {
				$_versions = "0." . $_version;
			}
			$versions = explode(".", $_versions);
		} else if ($length != 0 && intval($versions[$length - 1]) == 0) {
			//last element is zero and cannot subtract zero

			//1.0.0
			//1.0
			//1
			while (count($versions) != 0 && intval($versions[count($versions) - 1]) == 0) {
				array_pop($versions);
			}

			//now try working on it
			$versions = explode(".", $this->subtractVersion(implode(".", $versions)));
		} else {
			$versions[$length - 1]--;
		}

		return implode(".", $versions ?: $this->subtractVersion("1"));
	}

	/**
	 * Show rating request
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function request_rating()
	{

		/**
		 * When shown, will persist for a minute then wait quarter year
		 */
		if (boolval(get_transient($this->plugin_name . "-rate-persist")) === true || boolval(get_transient($this->plugin_name . "-rate")) === false) {
			include plugin_dir_path(__FILE__) . "/partials/force-reinstall-admin-rating.php";

			if (boolval(get_transient($this->plugin_name . "-rate-persist")) === false) {
				//remind again quarter year
				set_transient($this->plugin_name . "-rate", true, defined("MONTH_IN_SECONDS") ? MONTH_IN_SECONDS * 3 : YEAR_IN_SECONDS / 4);

				set_transient($this->plugin_name . "-rate-persist", true, MINUTE_IN_SECONDS * 1.5);
			}
		}
	}
}