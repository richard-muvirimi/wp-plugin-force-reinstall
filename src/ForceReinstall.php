<?php
/**
 * Bootstrap the plugin
 *
 * This file is the entry point into the plugin, registering all functions
 *
 * @package    Force_Reinstall
 * @subpackage Force_Reinstall/includes
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.2
 */

namespace Rich4rdMuvirimi\ForceReinstall;

use BadMethodCallException;
use Rich4rdMuvirimi\ForceReinstall\Controller\Admin;
use Rich4rdMuvirimi\ForceReinstall\Controller\Ajax;
use Rich4rdMuvirimi\ForceReinstall\Controller\Plugin;
use Rich4rdMuvirimi\ForceReinstall\Controller\Site;
use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;
use Rich4rdMuvirimi\ForceReinstall\Locale\I18n;

/**
 * Class to bootstrap the plugin
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.2
 *
 * @method void register_deactivation_hook($file, $component, $method)
 *  {@see \register_deactivation_hook}
 * @method void register_uninstall_hook($file, $component, $method)
 *  {@see \register_uninstall_hook}
 * @method void register_activation_hook($file, $component, $method)
 *  {@see \register_activation_hook}
 * @method bool|true add_filter($hook_name, $component, $method, $priority = 10, $accepted_args = 1)
 *  {@see \add_filter}
 * @method bool|true add_action($hook_name, $component, $method, $priority = 10, $accepted_args = 1)
 *  {@see \add_action}
 */
class ForceReinstall
{

    /**
     * Hold reference to a single instance of this class
     *
     * @var self
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     * @since 1.0.0
     * @version 1.0.0
     */
    private static $instance;

    /**
     * Init plugin Loader
     *
     * @return void
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    protected function __construct()
    {
        $this->register_activation_hook(FORCE_REINSTALL_FILE, Plugin::class, 'on_activation');
        $this->register_deactivation_hook(FORCE_REINSTALL_FILE, Plugin::class, 'on_deactivation');
        $this->register_uninstall_hook(FORCE_REINSTALL_FILE, Plugin::class, 'on_uninstall');
    }

    /**
     * Bootstrap the plugin
     *
     * @return self
     * @version 1.0.0
     * @since 1.0.0
     */
    public static function instance(): ForceReinstall
    {

        if (!(self::$instance instanceof static)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Initialise the plugin
     *
     * @return void
     * @version 1.0.0
     * @since 1.0.0
     */
    public function run()
    {
        $this->define_locale();
        $this->define_admin_hooks();
        $this->define_site_hooks();
        $this->define_ajax_hooks();
    }

    /**
     * Set the plugin locale
     *
     * @return void
     * @version 1.0.0
     * @since 1.0.0
     */
    public function define_locale()
    {
        $locale = new I18n();

        $this->add_action('plugins_loaded', $locale, 'load_plugin_textdomain');
    }

    /**
     * Define hooks for the admin side of the plugin
     *
     * @return void
     * @version 1.0.1
     * @since 1.0.0
     */
    public function define_admin_hooks()
    {
        $controller = new Admin();

        $this->add_action('admin_menu', $controller, 'on_admin_menu');

        $this->add_action('admin_enqueue_scripts', $controller, 'enqueue_styles');
        $this->add_action('admin_enqueue_scripts', $controller, 'enqueue_scripts');

        $this->add_filter('plugin_action_links', $controller, 'plugin_action_links', 10, 3);

        $this->add_filter('admin_notices', $controller, 'showAdminNotices');

        $this->add_filter('admin_action_' . Functions::get_plugin_slug(), $controller, 'handle_action');

        $this->add_filter('bulk_actions-plugins', $controller, 'add_plugins_bulk_action');
        $this->add_filter('handle_bulk_actions-plugins', $controller, 'plugins_bulk_update', 10, 3);

        $this->add_filter('admin_init', $controller, 'registerOptions');
    }

    /**
     * Register hooks for the site side of the plugin
     *
     * @return void
     * @version 1.0.0
     * @since 1.0.0
     */
    public function define_site_hooks()
    {
        $controller = new Site();

    }

    /**
     * Register hooks for the ajax side of the plugin
     *
     * @return void
     * @version 1.0.0
     * @since 1.0.0
     */
    public function define_ajax_hooks()
    {
        $controller = new Ajax();

        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-rate-enable'), $controller, 'ajaxDoRate');
        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-rate-remind'), $controller, 'ajaxDoRemindRate');
        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-rate-cancel'), $controller, 'ajaxDoCancelRate');

        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-analytics-enable'), $controller, 'ajaxDoAnalytics');
        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-analytics-remind'), $controller, 'ajaxDoRemindAnalytics');
        $this->add_action('wp_ajax_' . Functions::get_plugin_slug('-analytics-cancel'), $controller, 'ajaxDoCancelAnalytics');

    }

    /**
     * Call the appropriate WordPress registration hooks
     *
     * Allows us to hook the functions to this class so that we have a unified api
     *
     * @param string $name Name of function to call.
     * @param array $arguments Arguments passed to function.
     * @return mixed #type intentionally left out.
     * @throws BadMethodCallException When called function does not exist or has missing arguments.
     * @since 1.0.0
     * @version 1.0.5
     */
    public function __call(string $name, array $arguments)
    {

        assert(count($arguments) >= 2, new BadMethodCallException('You need to provide at least two arguments for ' . $name));

        switch ($name) {
            case 'register_activation_hook':
            case 'register_deactivation_hook':
            case 'register_uninstall_hook':
                // Hook file.
                $file = array_shift($arguments);

                assert(file_exists($file), new BadMethodCallException('Please provide a valid file path for ' . $name));

                // Function to call.
                $component = array_shift($arguments);
                if (is_array($component) || (is_string($component) && is_callable($component))) {
                    $callable = $component;
                } else {
                    $callable = array($component, array_shift($arguments));
                }
                unset($component);

                assert(is_callable($callable, true), new BadMethodCallException('Please provide a callable function for ' . $name));

                // Register Hook.
                $name($file, $callable);
                break;
            case 'add_filter':
            case 'add_action':
                // The hook.
                $hook = array_shift($arguments);

                assert(is_string($hook), new BadMethodCallException('Please provide the name of the hook for ' . $name));

                // Function to call.
                $component = array_shift($arguments);
                if (is_array($component) || (is_string($component) && is_callable($component))) {
                    $callable = $component;
                } else {
                    $callable = array($component, array_shift($arguments));
                }
                unset($component);

                assert(is_callable($callable, true), new BadMethodCallException('Please provide a callable function for ' . $name));

                // Function Priority.
                $priority = array_shift($arguments);
                if (is_null($priority)) {
                    $priority = 10;
                }

                assert(is_numeric($priority), new BadMethodCallException('Priority should be numeric for ' . $name));

                // Arguments Count.
                $args = array_shift($arguments);
                if (is_null($args)) {
                    $args = 1;
                }

                assert(is_numeric($args), new BadMethodCallException('Number of arguments should be numeric for ' . $name));

                // Register hook.
                return $name($hook, $callable, $priority, $args);

            default:
                throw new BadMethodCallException('The method ' . $name . ' does not exist');
        }
    }
}
