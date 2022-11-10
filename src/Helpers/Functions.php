<?php

/**
 * Plugin helper functions
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Helpers;

/**
 * Class to handle plugin translations
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Functions {

	/**
	 * URL separator character
	 *
	 * @var string
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.6
	 * @version 1.0.6
	 */
	public static $URL_SEPARATOR = "/";

	/**
	 * Get unique plugin slug
	 *
	 * @param string $suffix
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.3
	 * @version 1.0.6
	 *
	 * @return string
	 */
	public static function get_plugin_slug( $suffix = '' ) {
		return FORCE_REINSTALL_SLUG . $suffix;
	}

	/**
	 * Get a url targetting self
	 *
	 * @param array $arguments
	 * @since 1.0.1
	 * @return string
	 */
	public static function force_reinstall_target_self($arguments)
	{

		$input = filter_input_array(INPUT_GET);
		if (is_array($input) === false){
			$input = array();
		}

		$arguments = array_merge(
			array(
				"action" => FORCE_REINSTALL_SLUG,
				FORCE_REINSTALL_SLUG . "-nonce" => wp_create_nonce(FORCE_REINSTALL_SLUG)
			),
			$input,
			$arguments
		);

		return add_query_arg($arguments, admin_url(get_current_screen()->base . ".php") );
	}

	/**
	 * Get other templates (e.g. product attributes) passing attributes and including the file.
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 */
	public static function get_template( $template_name, $args = array(), $template_path = '' ) {

		$template_path = self::get_views_path( $template_path );

		$template_path = apply_filters( FORCE_REINSTALL_SLUG . '-template', $template_path, $template_name, $args );

		extract( $args );

		ob_start();
		if ( $template_path ) {
			include $template_path;
		}
		return ob_get_clean();
	}

	/**
	 * Get the views path
	 *
	 * @param string $path
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public static function get_views_path( $path ) {
		return plugin_dir_path( FORCE_REINSTALL_FILE ) . 'src' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR  . ltrim( $path, '\\/' );
	}

	/**
	 * Get the scripts path
	 *
	 * @param string $path
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public static function get_script_path( $path ) {
		return self::get_views_path( 'js' . DIRECTORY_SEPARATOR  . $path );
	}

	/**
	 * Get the styles path
	 *
	 * @param string $path
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public static function get_style_path( $path ) {
		return self::get_views_path( 'css' . DIRECTORY_SEPARATOR  . $path );
	}
	
	/**
	 * Get the views url
	 *
	 * @param string $url
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.6
	 *
	 * @return string
	 */
	public static function get_views_url( $url ) {
		return plugin_dir_url( FORCE_REINSTALL_FILE ) . 'src/Views' . self::$URL_SEPARATOR  . ltrim( $url, '\\/' );
	}

	/**
	 * Get the scripts url
	 *
	 * @param string $url
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.6
	 *
	 * @return string
	 */
	public static function get_script_url( $url ) {
		return self::get_views_url( 'js' . self::$URL_SEPARATOR  . $url );
	}

	/**
	 * Get the styles url
	 *
	 * @param string $url
	 *
	 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
	 * @since 1.0.0
	 * @version 1.0.6
	 *
	 * @return string
	 */
	public static function get_style_url( $url ) {
		return self::get_views_url( 'css' . self::$URL_SEPARATOR  . $url );
	}

}
