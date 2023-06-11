<?php

namespace Rich4rdMuvirimi\ForceReinstall\Helpers;

use ParagonIE\ConstantTime\Base64UrlSafe;

/**
 * Class to handle plugin templating
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Template
{
    /**
     * URL separator character
     *
     * @var string
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     * @since 1.0.6
     * @version 1.0.6
     */
    public static $URL_SEPARATOR = "/";

    /**
     * Get the scripts path
     *
     * @param string $path
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_script_path(string $path): string
    {
        return self::get_views_path('js' . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get the views path
     *
     * @param string $path
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_views_path(string $path): string
    {
        return plugin_dir_path(FORCE_REINSTALL_FILE) . 'src' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }

    /**
     * Get other templates (e.g. product attributes) passing attributes and including the file.
     *
     * @param string $template_name Template name.
     * @param array $args Arguments. (default: array).
     * @param string $template_path Template path. (default: '').
     * @return string
     */
    public static function get_template(string $template_name, array $args = array(), string $template_path = ''): string
    {

        $template_path = self::get_views_path($template_path);

        $template_path = apply_filters(Functions::get_plugin_slug( '-template'), $template_path, $template_name, $args);

        extract($args);

        ob_start();
        if ($template_path) {
            include $template_path;
        }
        return ob_get_clean();
    }

    /**
     * Get the scripts url
     *
     * @param string $url
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.6
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_script_url(string $url): string
    {
        return self::get_views_url('js' . self::$URL_SEPARATOR . $url);
    }

    /**
     * Get the views url
     *
     * @param string $url
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.6
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_views_url(string $url): string
    {
        return plugin_dir_url(FORCE_REINSTALL_FILE) . 'src' . self::$URL_SEPARATOR . 'Views' . self::$URL_SEPARATOR . ltrim($url, '\\/');
    }

    /**
     * Get the styles path
     *
     * @param string $path
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_style_path(string $path): string
    {
        return self::get_views_path('css' . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get the styles url
     *
     * @param string $url
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.6
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_style_url(string $url): string
    {
        return self::get_views_url('css' . self::$URL_SEPARATOR . $url);
    }

    /**
     * Get the images url
     *
     * @param string $url
     *
     * @return string
     * @since 1.0.0
     * @version 1.0.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_image_url(string $url): string
    {
        return self::get_views_url('img' . self::$URL_SEPARATOR . $url);
    }

        /**
     * Get the images path
     *
     * @param string $path
     *
     * @return string
     * @since 1.1.8
     * @version 1.1.8
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_image_path(string $path): string
    {
        return self::get_views_path('img' . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get the files base64
     *
     * @param string $path
     * @param string $prefix
     *
     * @return string
     * @since 1.1.8
     * @version 1.1.8
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function get_file_base64(string $path, string $prefix = ""): string
    {
        return $prefix . base64_encode(file_get_contents($path));
    }
}
