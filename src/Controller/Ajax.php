<?php

/**
 * File for the Ajax controller
 *
 * All ajax functionality to be handled in one place
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard Muvirimi <rich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */

namespace Rich4rdMuvirimi\ForceReinstall\Controller;

use Rich4rdMuvirimi\ForceReinstall\Helpers\Functions;

/**
 * Ajax side controller
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Controller
 *
 * @author Richard <erich4rdmuvirimi@gmail.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Ajax extends BaseController {

	 /**
     * Set reminder for half a year and send redirect link
     * 
     * @since 1.0.2
     *
     * @return void
     */
    public function ajaxDoRate()
    {
        if (check_ajax_referer(Functions::get_plugin_slug("-rate"), "_ajax_nonce", false) !== false) {

            //remind again in three months
            set_transient(Functions::get_plugin_slug( "-rate"), true, YEAR_IN_SECONDS / 2);

            echo wp_send_json(array(
                "redirect" => sprintf( "https://wordpress.org/support/plugin/%s/reviews/", FORCE_REINSTALL_SLUG)
            ), 200);
        }
    }

    /**
     * Remind again in a week
     * 
     * @since 1.0.2
     *
     * @return void
     */
    public function ajaxDoRemind()
    {

        if (check_ajax_referer(Functions::get_plugin_slug("-remind"), "_ajax_nonce", false) !== false) {

            //remind after a week
            set_transient(Functions::get_plugin_slug( "-rate"), true, WEEK_IN_SECONDS);

            echo wp_send_json(array(
                "success" => true
            ), 200);
        }
    }

    /**
     * Remind in a year
     * 
     * @since 1.0.2
     *
     * @return void
     */
    public function ajaxDoCancel()
    {
        if (check_ajax_referer(Functions::get_plugin_slug("-cancel"), "_ajax_nonce", false) !== false) {

            set_transient(Functions::get_plugin_slug( "-rate"), true, YEAR_IN_SECONDS);

            echo wp_send_json(array(
                "success" => true
            ), 200);
        }
    }

}
