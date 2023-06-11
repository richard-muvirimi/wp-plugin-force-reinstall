<?php

/**
 * Plugin helper functions
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Helpers;

/**
 * Class to handle plugin helper functions
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Functions
{

    /**
     * Get unique plugin slug
     *
     * @param string $suffix
     *
     * @return string
     * @since 1.0.3
     * @version 1.0.6
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_plugin_slug(string $suffix = ''): string
    {
        return FORCE_REINSTALL_SLUG . $suffix;
    }

    /**
     * Get a url targeting self
     *
     * @param array $arguments
     * @return string
     * @since 1.0.1
     */
    public static function force_reinstall_target_self(array $arguments): string
    {

        $input = filter_input_array(INPUT_GET);
        if (is_array($input) === false) {
            $input = array();
        }

        $arguments = array_merge(
            array(
                "action" => Functions::get_plugin_slug(),
                Functions::get_plugin_slug( "-nonce") => wp_create_nonce(Functions::get_plugin_slug())
            ),
            $input,
            $arguments
        );

        return add_query_arg($arguments, admin_url(get_current_screen()->base . ".php"));
    }

}
