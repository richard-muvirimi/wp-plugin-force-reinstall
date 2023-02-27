<?php

namespace Rich4rdMuvirimi\ForceReinstall\Views;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

if (!defined('WPINC')) {
    die(); // Exit if accessed directly.
}

/**
 * Provide a admin area view for the plugin
 * This file is used to mark up the admin-facing aspects of the plugin.
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Views
 *
 * @link http://richard.co.zw
 * @since 1.1.0
 * @version 1.1.1
 */
?>

<div class="<?php esc_attr_e(FORCE_REINSTALL_SLUG); ?> notice notice-info is-dismissible">
    <div>
        <div class="<?php esc_attr_e(FORCE_REINSTALL_SLUG); ?>-prompt">
            <?php printf(__('Please consider enabling usage collection for %s. This helps us improve our product and provide better features to you in the future.', FORCE_REINSTALL_SLUG), __('Force Reinstall', FORCE_REINSTALL_SLUG)); ?>
        </div>
        <div class="<?php esc_attr_e(FORCE_REINSTALL_SLUG); ?>-button">
            <a class="button btn-yield" href="#"
               data-nonce="<?php esc_attr_e(wp_create_nonce(Functions::get_plugin_slug('-analytics-enable'))); ?>"
               data-action="<?php esc_attr_e('analytics-enable'); ?>">
                <?php _e('Enable', FORCE_REINSTALL_SLUG); ?>
            </a>
            <a class="button btn-remind" href="#"
               data-nonce="<?php esc_attr_e(wp_create_nonce(Functions::get_plugin_slug('-analytics-remind'))); ?>"
               data-action="<?php esc_attr_e('analytics-remind'); ?>">
                <span><?php _e('Remind me later', FORCE_REINSTALL_SLUG); ?></span>
            </a>
            <a class="button btn-cancel" href="#"
               data-nonce="<?php esc_attr_e(wp_create_nonce(Functions::get_plugin_slug('-analytics-cancel'))); ?>"
               data-action="<?php esc_attr_e('analytics-cancel'); ?>">
                <span><?php _e('Never', FORCE_REINSTALL_SLUG); ?></span>
            </a>
        </div>
    </div>
</div>
