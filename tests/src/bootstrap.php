<?php
/**
 * Phpunit bootstrap file for running tests
 *
 */

$root = __DIR__;

do {
    $root = dirname($root);

    // break if we don't find the file
    if (strrpos($root, DIRECTORY_SEPARATOR, intval(strpos($root, PATH_SEPARATOR))) < 3) {
        throw new Exception("Base Plugin file not found");
    }
} while (!file_exists($root . DIRECTORY_SEPARATOR . 'force-reinstall.php'));

/**
 * WP loaded check constant
 */
const WPINC = 'wp-includes';

/**
 * Reference to this file
 */
define("FORCE_REINSTALL_FILE", $root . DIRECTORY_SEPARATOR . 'force-reinstall.php');

/**
 * Method stubs
 */
if (!function_exists('register_activation_hook')) {
    /**
     * Register activation hook stub
     *
     * @param string $file
     * @param callable $callable
     * @return void
     */
    function register_activation_hook(string $file, callable $callable): void
    {
    }
}
if (!function_exists('register_deactivation_hook')) {
    /**
     * Register deactivation hook stub
     *
     * @param string $file
     * @param callable $callable
     * @return void
     */
    function register_deactivation_hook(string $file, callable $callable): void
    {
    }
}
if (!function_exists('register_uninstall_hook')) {
    /**
     * Register uninstall hook stub
     *
     * @param string $file
     * @param callable $callable
     * @return void
     */
    function register_uninstall_hook(string $file, callable $callable): void
    {
    }
}
if (!function_exists('plugin_basename')) {

    function plugin_basename(string $file)
    {
        return basename($file, ".php") . "/" . basename($file);
    }
}

/**
 * Load constants
 */
$content = file_get_contents(FORCE_REINSTALL_FILE);

preg_match('/#region\sConstants(.*)#endregion\sConstants/s', $content, $matches);

eval($matches[1]);

// clean up
unset($content, $matches);
