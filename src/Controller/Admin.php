<?php
/**
 * File for the Admin controller
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
use Rich4rdMuvirimi\ForceReinstall\Helpers\Template;

/**
 * Admin side controller
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.1
 */
class Admin extends BaseController
{

    /**
     * Register the stylesheets for the admin area.
     *
     * @return void
     * @since    1.0.0
     */
    public function enqueue_styles(): void
    {
        wp_register_style(Functions::get_plugin_slug("-rate"), Template::get_style_url('admin-rating.css'), array(), FORCE_REINSTALL_VERSION);

        wp_register_style(Functions::get_plugin_slug("-about"), Template::get_style_url('admin-about.css'), array(), FORCE_REINSTALL_VERSION);

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @return void
     * @since    1.0.0
     */
    public function enqueue_scripts(): void
    {

        switch (get_current_screen()->id) {
            case "themes":

                /**
                 * Would have loved a hook much like plugins but guess will have to add through javascript
                 *
                 * tried "plugin_action_links" but could not be found on the front end
                 * But then again...
                 */
                wp_enqueue_script(Functions::get_plugin_slug(), Template::get_script_url('admin-reinstall-themes.js'), array('jquery'), FORCE_REINSTALL_VERSION);
                wp_localize_script(Functions::get_plugin_slug(), "force_reinstall", array(
                    "button" => $this->getThemeActionButton()
                ));
                break;
        }

        wp_register_script(Functions::get_plugin_slug("-rate"), Template::get_script_url('admin-rating.js'), array('jquery'), FORCE_REINSTALL_VERSION);
        wp_localize_script(Functions::get_plugin_slug("-rate"), "force_reinstall", array(
            "ajax_url" => admin_url('admin-ajax.php'),
            "name" => Functions::get_plugin_slug()
        ));
    }

    /**
     * Get theme action button
     *
     * @return string
     * @version 1.0.1
     * @since 1.0.0
     */
    private function getThemeActionButton(): string
    {

        $arguments = array(
            Functions::get_plugin_slug() => "",
            Functions::get_plugin_slug("-target") => "theme"
        );
        $target = Functions::force_reinstall_target_self($arguments);

        return sprintf(
            '<a class="button %s" href="%s">%s</a>',
            esc_attr(Functions::get_plugin_slug()),
            esc_attr($target),
            __('Force Reinstall', Functions::get_plugin_slug())
        );
    }

    /**
     * Add plugins page actions
     *
     * @param array $actions
     * @param string $plugin_file
     * @param array|null $plugin_data
     * @return array
     * @since 1.0.0
     * @version 1.0.1
     */
    function plugin_action_links(array $actions, string $plugin_file, ?array $plugin_data): array
    {

        if ((isset($plugin_data["url"]) && strlen($plugin_data["url"]) != 0) && (isset($plugin_data["package"]) && strlen($plugin_data["package"]) != 0)) {

            //target same page with plugin name to keep arguments
            $arguments = array(
                Functions::get_plugin_slug() => $plugin_file,
                Functions::get_plugin_slug("-target") => "plugin"
            );

            $target = Functions::force_reinstall_target_self($arguments);

            $link = sprintf(
                '<a href="%s">%s</a>',
                esc_attr($target),
                __('Force Reinstall', Functions::get_plugin_slug())
            );

            $actions[] = $link;
        }

        return $actions;
    }

    /**
     * Handle requested action
     *
     * @return void
     * @version 1.0.1
     * @since 1.0.0
     */
    function handle_action(): void
    {

        if (wp_verify_nonce(filter_input(INPUT_GET, Functions::get_plugin_slug("-nonce")), Functions::get_plugin_slug())) {

            switch (filter_input(INPUT_GET, Functions::get_plugin_slug("-target"))) {
                case "plugin":
                case "theme":

                    $target = filter_input(INPUT_GET, Functions::get_plugin_slug());

                    if ($target) {

                        $this->_force_update($target);

                        //remove our args and redirect
                        wp_redirect(remove_query_arg(array("action", Functions::get_plugin_slug("-nonce"), Functions::get_plugin_slug(), Functions::get_plugin_slug("-target")), $_SERVER['REQUEST_URI']));
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
     * @param string $target
     * @return void
     * @since 1.0.0
     * @version 1.0.1
     */
    private function _force_update(string $target): void
    {

        $targetType = get_current_screen()->base;

        Logger::logEvent("force_reinstall_". $targetType);

        /**
         * Modify Site transient
         */
        $updates = get_site_transient('update_' . $targetType);

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
            set_site_transient('update_' . $targetType, $updates);
        }
    }

    /**
     * Add Plugins bulk action
     *
     * @param array $actions
     * @return array
     * @since 1.0.0
     * @version 1.0.1
     */
    public function add_plugins_bulk_action(array $actions): array
    {

        global $wp_version;

        //https://developer.wordpress.org/reference/hooks/handle_bulk_actions-screen/
        if (version_compare($wp_version, "4.7", ">=")) {
            $actions[Functions::get_plugin_slug()] = __('Force Reinstall', Functions::get_plugin_slug());
        }
        return $actions;
    }

    /**
     * Force update on plugins
     *
     * @param string $redirect
     * @param string $action
     * @param array $plugins
     * @return string
     * @since 1.0.0
     * @version 1.0.1
     */
    public function plugins_bulk_update(string $redirect, string $action, array $plugins): string
    {

        if ($action == Functions::get_plugin_slug()) {

            foreach ($plugins as $plugin) {
                $this->_force_update($plugin);
            }
        }

        return admin_url("update-core.php");
    }

    /**
     * Show rating request
     *
     * @return void
     * @version 1.0.1
     * @since 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public function showAdminNotices(): void
    {
        /**
         * Request Rating
         */
        if (boolval(get_transient(Functions::get_plugin_slug("-rate"))) === false) {
            wp_enqueue_script(Functions::get_plugin_slug("-rate"));
            wp_enqueue_style(Functions::get_plugin_slug("-rate"));

            echo Template::get_template(Functions::get_plugin_slug('-admin-notice-rating'), array(), 'admin-notice-rating.php');

            Logger::logEvent("request_plugin_rating");
        }

        if (get_option(Functions::get_plugin_slug("-analytics"), "off") !== "on" && boolval(get_transient(Functions::get_plugin_slug('-analytics'))) === false) {
            wp_enqueue_script(Functions::get_plugin_slug("-rate"));
            wp_enqueue_style(Functions::get_plugin_slug("-rate"));

            echo Template::get_template(Functions::get_plugin_slug('-admin-notice-analytics'), array(), 'admin-notice-analytics.php');

            Logger::logEvent("request_plugin_analytics");
        }
    }

    /**
     * Register plugin options
     *
     * @return void
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     * @since 1.1.0
     * @version 1.1.0
     */
    public function registerOptions(): void
    {

        register_setting(
            Functions::get_plugin_slug("-about"),
            Functions::get_plugin_slug("-analytics"),
            array("sanitize_callback" => "sanitize_text_field")
        );

        add_settings_section(
            Functions::get_plugin_slug("-settings"),
            __("Settings", Functions::get_plugin_slug()),
            array($this, "renderSectionHeader"),
            Functions::get_plugin_slug("-about")
        );

        add_settings_field(
            Functions::get_plugin_slug("-analytics"),
            __('Collect Anonymous Usage Data', Functions::get_plugin_slug()),
            array($this, 'renderInputField'),
            Functions::get_plugin_slug("-about"),
            Functions::get_plugin_slug("-settings"),
            array(
                'label_for' => Functions::get_plugin_slug("-analytics"),
                'class' => Functions::get_plugin_slug( '-row'),
                "value" => get_option(Functions::get_plugin_slug("-analytics"), "off"),
                'description' => Template::get_template(Functions::get_plugin_slug("-about-analytics-disclaimer"), [], "about-analytics-disclaimer.php"),
                "type" => "checkbox",
            )
        );
    }

    /**
     * Display the settings header
     *
     * @return void
     * @since 1.1.0
     * @version 1.1.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public function renderSectionHeader(): void
    {
        echo Template::get_template(Functions::get_plugin_slug("-about-section-header"), [], "about-section-header.php");
    }

    /**
     * Display input field
     *
     * @param array $args
     *
     * @return void
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public function renderInputField(array $args): void
    {
        echo Template::get_template(Functions::get_plugin_slug("-about-input-field"), $args, "about-input-field.php");
    }

    /**
     * On create the about menu
     *
     * @return void
     * @since 1.0.0
     * @version 1.1.8
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public function on_admin_menu(): void
    {
        add_menu_page(
            __('Force Reinstall', Functions::get_plugin_slug()),
            __('Force Reinstall', Functions::get_plugin_slug()),
            'manage_options',
            Functions::get_plugin_slug(),
            [$this, 'renderAboutPage'],
            Template::get_file_base64(Template::get_image_path('icon.svg'), "data:image/svg+xml;base64,"),
        );

    }

    /**
     * Render the about page
     *
     * @return void
     * @since 1.1.0
     * @version 1.1.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public function renderAboutPage(): void
    {

        Logger::logEvent("view_about_page");

        wp_enqueue_style(Functions::get_plugin_slug("-about"));

        $plugin = get_plugin_data(
            FORCE_REINSTALL_FILE
        );

        echo Template::get_template(Functions::get_plugin_slug("admin-about"), compact("plugin"), "admin-about.php");
    }

}
