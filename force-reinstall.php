<?php

/**
 * The plugin bootstrap file
 *
 * @link              http://richard.co.zw
 * @since             1.0.0
 * @package           Force_Reinstall
 *
 * @wordpress-plugin
 * Plugin Name:       Force Reinstall
 * Plugin URI:        https://github.com/richard-muvirimi/wp-plugin-force-reinstall
 * Description:       Easily force a Plugin or Theme reinstall from WordPress.org
 * Version:           1.0.5
 * Author:            Richard Muvirimi
 * Author URI:        http://richard.co.zw
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       force-reinstall
 * Domain Path:       /languages
 */

use Rich4rdMuvirimi\ForceReinstall\ForceReinstall;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

 /**
  * The plugin slug, one source of truth for context
  */
  define( 'FORCE_REINSTALL_SLUG', 'force-reinstall' );

  /**
   * Plugin version number
   */
  define( 'FORCE_REINSTALL_VERSION', '1.0.5' );

  /**
   * Reference to this file, and this file only, (well, plugin entry point)
   */
   define( 'FORCE_REINSTALL_FILE', __FILE__ );

  /**
   * Plugin name as known to WordPress
   */
  define( 'FORCE_REINSTALL_NAME', plugin_basename( FORCE_REINSTALL_FILE ) );

/**
 * Load composer
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * And away we go
 */
ForceReinstall::instance()->run();
