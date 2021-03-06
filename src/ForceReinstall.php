<?php
/**
 * Bootstrap the plugin
 *
 * This file is the entry point into the plugin, registering all functions
 *
 * @package    Force_Reinstall
 * @subpackage Force_Reinstall/includes
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.2
 */

namespace Rich4rdMuvirimi\ForceReinstall;

use BadMethodCallException;
use Rich4rdMuvirimi\ForceReinstall\Controller\Admin;
use Rich4rdMuvirimi\ForceReinstall\Controller\Ajax;
use Rich4rdMuvirimi\ForceReinstall\Controller\Plugin;
use Rich4rdMuvirimi\ForceReinstall\Locale\I18n;
use Rich4rdMuvirimi\ForceReinstall\Controller\Site;
use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

/**
 * Class to bootstrap the plugin
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.2
 *
 * @method void register_deactivation_hook($file,$component, $method)
 *  {@see \register_deactivation_hook}
 * @method void register_uninstall_hook($file,$component, $method)
 *  {@see \register_uninstall_hook}
 * @method void register_activation_hook($file,$component, $method)
 *  {@see \register_activation_hook}
 * @method bool|true add_filter($hook_name, $component, $method, $priority = 10, $accepted_args = 1)
 *  {@see \add_filter}
 * @method bool|true add_action($hook_name, $component, $method, $priority = 10, $accepted_args = 1)
 *  {@see \add_action}
 */
class ForceReinstall {

	/**
	 * Hold reference to a single instance of this class
	 *
	 * @var self
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	private static $instance;

	/**
	 * Bootstrap the plugin
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return self
	 */
	public static function instance() {

		if ( ! ( self::$instance instanceof static ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Init plugin Loader
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->register_activation_hook( FORCE_REINSTALL_FILE, Plugin::class, 'on_activation' );
		$this->register_deactivation_hook( FORCE_REINSTALL_FILE, Plugin::class, 'on_deactivation' );
		$this->register_uninstall_hook( FORCE_REINSTALL_FILE, Plugin::class, 'on_uninstall' );
	}

	/**
	 * Define hooks for the admin side of the plugin
	 *
	 * @since 1.0.0
	 * @version 1.0.1
	 * @return void
	 */
	public function define_admin_hooks() {
		$controller = new Admin();

		$this->add_action('admin_enqueue_scripts', $controller, 'enqueue_styles');
		$this->add_action('admin_enqueue_scripts', $controller, 'enqueue_scripts');

		$this->add_filter('plugin_action_links', $controller, 'plugin_action_links', 10, 3);

		$this->add_filter('admin_notices', $controller, 'show_rating');

		$this->add_filter('admin_action_' . FORCE_REINSTALL_SLUG, $controller, 'handle_action');

		$this->add_filter('bulk_actions-plugins', $controller, 'add_plugins_bulk_action');
		$this->add_filter('handle_bulk_actions-plugins', $controller, 'plugins_bulk_update', 10, 3);
	}

	/**
	 * Register hooks for the site side of the plugin
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function define_site_hooks() {
		$controller = new Site();

	}

	/**
	 * Register hooks for the ajax side of the plugin
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function define_ajax_hooks() {
		$controller = new Ajax();

		$this->add_action( 'wp_ajax_' . Functions::get_plugin_slug( '-rate'), $controller, 'ajaxDoRate' );
		$this->add_action( 'wp_ajax_' . Functions::get_plugin_slug( '-remind'), $controller, 'ajaxDoRemind' );
		$this->add_action( 'wp_ajax_' . Functions::get_plugin_slug( '-cancel'), $controller, 'ajaxDoCancel' );

	}

	/**
	 * Set the plugin locale
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function define_locale() {
		$locale = new I18n();

		$this->add_action( 'plugins_loaded', $locale, 'load_plugin_textdomain' );
	}

	/**
	 * Initialise the plugin
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return void
	 */
	public function run() {
		$this->define_locale();
		$this->define_admin_hooks();
		$this->define_site_hooks();
		$this->define_ajax_hooks();
	}

	/**
	 * Call the appropriate WordPress registration hooks
	 *
	 * Allows us to hook the functions to this class so that we have a unified api
	 *
	 * @since 1.0.0
	 * @version 1.0.5
	 * @param string $name Name of function to call.
	 * @param array  $arguments Arguments passed to function.
	 * @return mixed #type intentionally left out.
	 * @throws BadMethodCallException When called function does not exist or has missing arguments.
	 */
	public function __call( $name, $arguments ) {

		assert( count( $arguments ) >= 2, new BadMethodCallException( 'You need to provide at least two arguments for ' . $name ) );

		switch ( $name ) {
			case 'register_activation_hook':
			case 'register_deactivation_hook':
			case 'register_uninstall_hook':
				// Hook file.
				$file = array_shift( $arguments );

				assert( file_exists( $file ), new BadMethodCallException( 'Please provide a valid file path for ' . $name ) );

				// Function to call.
				$component = array_shift( $arguments );
				if ( is_array( $component ) || ( is_string( $component ) && is_callable($component) )) {
					$callable = $component;
				} else {
					$callable = array( $component, array_shift( $arguments ) );
				}
				unset( $component );

				assert( is_callable( $callable, true ), new BadMethodCallException( 'Please provide a callable function for ' . $name ) );

				// Register Hook.
				$name( $file, $callable );
				break;
			case 'add_filter':
			case 'add_action':
				// The hook.
				$hook = array_shift( $arguments );

				assert( is_string( $hook ), new BadMethodCallException( 'Please provide the name of the hook for ' . $name ) );

				// Function to call.
				$component = array_shift( $arguments );
				if ( is_array( $component ) || ( is_string( $component ) && is_callable($component) )) {
					$callable = $component;
				} else {
					$callable = array( $component, array_shift( $arguments ) );
				}
				unset( $component );

				assert( is_callable( $callable, true ), new BadMethodCallException( 'Please provide a callable function for ' . $name ) );

				// Function Priority.
				$priority = array_shift( $arguments );
				if ( is_null( $priority ) ) {
					$priority = 10;
				}

				assert( is_numeric( $priority ), new BadMethodCallException( 'Priority should be numeric for ' . $name ) );

				// Arguments Count.
				$args = array_shift( $arguments );
				if ( is_null( $args ) ) {
					$args = 1;
				}

				assert( is_numeric( $args ), new BadMethodCallException( 'Number of arguments should be numeric for ' . $name ) );

				// Register hook.
				return $name( $hook, $callable, $priority, $args );

			default:
				throw new BadMethodCallException( 'The method ' . $name . ' does not exist' );
		}
	}
}
