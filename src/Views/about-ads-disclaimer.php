<?php

namespace Rich4rdMuvirimi\ForceReinstall\Views;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

if (!defined('WPINC')) {
    die(); // Exit if accessed directly.
}

/**
 * Provide an about area view for the plugin
 * This file is used to mark up the admin-facing aspects of the plugin.
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Views
 *
 * @link http://richard.co.zw
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.1.0
 * @version 1.1.0
 *
 */

?>
<span>
    <?php _e("Our plugin relies on advertising revenue to continue development and support for our users. By displaying non-intrusive ads in the backend, we can keep our plugin free for all users and ensure its ongoing maintenance. We take care to only show ads that are relevant to our users and respect their privacy. For more information on how we collect and handle data, please refer to our", Functions::get_plugin_slug()) ?>
    <a href="https://site.tyganeutronics.com/privacy-policy">
       <?php _e("privacy policy", Functions::get_plugin_slug()) ?>
    </a>.
</span>
