<?php

namespace Rich4rdMuvirimi\ForceReinstall\Helpers;

use Rich4rdMuvirimi\ForceReinstall\Vendor\Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperty;
use Rich4rdMuvirimi\ForceReinstall\Vendor\Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Rich4rdMuvirimi\ForceReinstall\Vendor\Br33f\Ga4\MeasurementProtocol\Dto\Parameter\BaseParameter;
use Rich4rdMuvirimi\ForceReinstall\Vendor\Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Rich4rdMuvirimi\ForceReinstall\Vendor\Br33f\Ga4\MeasurementProtocol\Service;
use ForceReinstall_ClientIP as ClientIP;
use Exception;

/**
 * Class to handle plugin logging functions
 *
 * @package ForceReinstall
 * @subpackage ForceReinstall/Helpers
 *
 * @author Richard Muvirimi <richard@tyganeutronics.com>
 * @since 1.1.0
 * @version 1.1.0
 */
class Logger
{

    /**
     * Log events to Google analytics
     *
     * @param string $event
     * @return void
     * @since 1.1.0
     * @version 1.1.7
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function logEvent(string $event): void
    {

        if (get_option(Functions::get_plugin_slug("-analytics"), "off") === "on") {

            $credentials = self::fetchAnalyticsCredentials();

            if (is_array($credentials)) {

                try {

                    $clientIp = ClientIP::get();

                    $ga4Service = new Service($credentials["MEASUREMENT_PROTOCOL_API_SECRET"], $credentials["MEASUREMENT_ID"]);
                    $ga4Service->setIpOverride($clientIp);
                    $ga4Service->setOptions([
                        'User-Agent' => self::getUserAgent()
                    ]);

                    $baseRequest = new BaseRequest($clientIp);

                    $sessionId = self::getSessionId();

                    $baseRequest->setUserId($sessionId);

                    // Environment Properties
                    $baseRequest->addUserProperty(new UserProperty("php_version", PHP_VERSION));
                    $baseRequest->addUserProperty(new UserProperty("wordpress_version", get_bloginfo("version")));
                    $baseRequest->addUserProperty(new UserProperty("plugin_version", FORCE_REINSTALL_VERSION));

                    $baseEvent = new BaseEvent($event);

                    // Create Base Event
                    $sessionIdParam = new BaseParameter($sessionId);
                    $baseEvent->addParam("engagement_time_msec", $sessionIdParam);

                    $engagementTimeParam = new BaseParameter(self::getEngagementTime());
                    $baseEvent->addParam("session_id", $engagementTimeParam);

                    $baseRequest->addEvent($baseEvent);

                    // Create View Page Event
                    $pageViewEvent = new BaseEvent("page_view");

                    $pageViewParam = new BaseParameter(site_url());
                    $pageViewEvent->addParam("page_location", $pageViewParam);

                    $localeParam = new BaseParameter(get_user_locale());
                    $pageViewEvent->addParam("language", $localeParam);

                    $titleParam = new BaseParameter(get_bloginfo("name"));
                    $pageViewEvent->addParam("page_title", $titleParam);

                    $baseRequest->addEvent($pageViewEvent);

                    // Send
                    $ga4Service->send($baseRequest);

                } catch (Exception $e) {

                }
            }
        }

    }

    /**
     * Fetch analytics credentials
     *
     * @return array|false
     * @since 1.1.0
     * @version 1.1.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function fetchAnalyticsCredentials()
    {
        $analyticsKeys = get_transient(Functions::get_plugin_slug("analytics-keys"));

        if (!is_array($analyticsKeys)) {

            $url = add_query_arg([
                "version" => FORCE_REINSTALL_VERSION
            ],
                "https://tyganeutronics.com/versionify/api/v1/b08cbde0b6f164a25097cdc54825fbe4"
            );

            $response = wp_remote_get($url);

            if (!is_wp_error($response)) {
                $data = wp_remote_retrieve_body($response);

                list("data" => $analyticsKeys) = json_decode($data, true);

                set_transient(Functions::get_plugin_slug("analytics-keys"), $analyticsKeys, DAY_IN_SECONDS);
            }
        }

        return $analyticsKeys;
    }

    /**
     * Get users user agent to forward for analytics collection
     *
     * @return string
     * @since 1.1.0
     * @version 1.1.0
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'), site_url());
    }

    /**
     * Get unique session id
     *
     * @return string
     * @since 1.1.0
     * @version 1.1.8
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function getSessionId(): string
    {

        $cookie_name = Functions::get_plugin_slug("-session-id");

        $unique_id = $_COOKIE[$cookie_name] ?? uniqid("fr-", true);
        $domain = parse_url(site_url(), PHP_URL_HOST);

        if (!headers_sent()){
            setcookie($cookie_name, $unique_id, time() + MONTH_IN_SECONDS, "/", $domain, true, true);
        }

        return $unique_id;

    }

    /**
     * Get engagement millisecond time
     *
     * @return float
     * @since 1.1.0
     * @version 1.1.8
     *
     * @author Richard Muvirimi <richard@tyganeutronics.com>
     */
    public static function getEngagementTime(): float
    {
        $cookie_name = Functions::get_plugin_slug("-session-start");

        $start_time = $_COOKIE[$cookie_name] ?? time();
        $time_now = time();

        $domain = parse_url(site_url(), PHP_URL_HOST);

        // Update the start time cookie
        if (!headers_sent()){
            setcookie($cookie_name, $time_now, time() + HOUR_IN_SECONDS, '/', $domain, true, true);
        }

        // Calculate the engagement time
        return ($time_now - $start_time) * 1000;
    }
}
