<?php

/**
 * LaterPay request helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Request {

    /**
     * API status
     * @var bool
     */
    protected static $lp_api_availability;

    /**
     * Check API status
     *
     * @return bool
     */
    public static function isLpApiAvailability()
    {
        // Check if access check is disabled and current page is home page.
        if ( LaterPay_Helper_Pricing::is_access_check_disabled_on_home() ) {
            return false;
        }

        if ( null === self::$lp_api_availability ) {
            $client_options = LaterPay_Helper_Config::get_php_client_options();
            $action         = (int) get_option( 'laterpay_api_fallback_behavior', 0 );
            $behavior       = LaterPay_Controller_Admin_Settings::get_laterpay_api_options();
            $client         = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
            );

            self::$lp_api_availability = $client->check_health();
        }

        return (bool)self::$lp_api_availability;
    }

    /**
     * Check, if the current request is an Ajax request.
     *
     * @return bool
     */
    public static function is_ajax() {
        $server = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ? sanitize_text_field( $_SERVER['HTTP_X_REQUESTED_WITH'] ) : ''; // WPCS: input var ok.
        return ( 'xmlhttprequest' === ! empty( $server ) && strtolower( $server ) );
    }

    /**
     * Get current URL.
     *
     * @return string $url
     */
    public static function get_current_url() {
        $ssl = ( isset( $_SERVER['HTTPS'] ) && 'on' === sanitize_text_field( $_SERVER['HTTPS'] ) ); // WPCS: input var ok.
        // Check for Cloudflare Universal SSL / flexible SSL
        $http_cf_Visitor = isset( $_SERVER['HTTP_CF_VISITOR'] ) ? sanitize_text_field( $_SERVER['HTTP_CF_VISITOR'] ) : ''; // WPCS: input var ok.
        if ( ! empty( $http_cf_Visitor ) && strpos( $http_cf_Visitor, 'https' ) !== false ) {
            $ssl = true;
        }
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : ''; // WPCS: input var ok.

        // process Ajax requests
        if ( self::is_ajax() ) {
            $url    = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : ''; // WPCS: input var ok.
            $parts  = wp_parse_url( $url );

            if ( ! empty( $parts ) ) {
                $uri = $parts['path'];
                if ( ! empty( $parts['query'] ) ) {
                    $uri .= '?' . $parts['query'];
                }
            }
        }

        $uri = preg_replace( '/lptoken=.*?($|&)/', '', $uri );

        $uri = preg_replace( '/ts=.*?($|&)/', '', $uri );
        $uri = preg_replace( '/hmac=.*?($|&)/', '', $uri );

        $uri = preg_replace( '/&$/', '', $uri );

        if ( $ssl ) {
            $pageURL = 'https://';
        } else {
            $pageURL = 'http://';
        }
        $serverPort = isset( $_SERVER['SERVER_PORT'] )? absint( $_SERVER['SERVER_PORT'] ) : ''; // WPCS: input var ok.
        $serverName = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : ''; // WPCS: input var ok.
        if ( 'localhost' === $serverName && function_exists( 'site_url' ) ) {
            $serverName = (str_replace( array('http://', 'https://'), '', site_url() )) ; // WP function

            $cf_Visitor = isset( $_SERVER['HTTP_CF_VISITOR'] ) ? sanitize_text_field( $_SERVER['HTTP_CF_VISITOR'] ) : ''; // WPCS: input var ok.

            // overwrite port on Heroku
            if ( ! empty( $cf_Visitor ) && strpos( $cf_Visitor, 'https' ) !== false ) {
                $serverPort = 443;
            } else {
                $serverPort = 80;
            }
        }
        if ( ! $ssl && 80 !== absint( $serverPort ) ) {
            $pageURL .= $serverName . ':' . $serverPort . $uri;
        } elseif ( $ssl && 443 !== absint( $serverPort ) ) {
            $pageURL .= $serverName . ':' . $serverPort . $uri;
        } else {
            $pageURL .= $serverName . $uri;
        }

        return $pageURL;
    }

    /**
     * Set cookie with token.
     *
     * @see LaterPay_Client::set_token()
     */
    public static function laterpay_api_set_token( $token, $redirect = false ) {
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $client->set_token( $token, $redirect );
    }

    /**
     * Check, if user has access to a given item / given array of items.
     *
     * @see LaterPay_Client::get_access()
     */
    public static function laterpay_api_get_access( $article_ids, $product_key = null )
    {
        $result = array();
        $action = (int) get_option( 'laterpay_api_fallback_behavior', 0 );

        // Check if access check is disabled and current page is home page.
        if ( LaterPay_Helper_Pricing::is_access_check_disabled_on_home() ) {
            return $result;
        }

        try {
            $client_options = LaterPay_Helper_Config::get_php_client_options();
            $client = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
            );

            $result = $client->get_access($article_ids, $product_key);

            // Check if current config is valid or not.
            $health_check = json_decode( $client->check_health( true ), true );

            // Change plugin mode to test, if invalid account configuration.
            if ( false === $health_check['is_valid'] ) {
                // Activate test mode.
                update_option( 'laterpay_plugin_is_in_live_mode', '0' );

                // Recheck plugin is working, to disable the paywall.
                $event = new LaterPay_Core_Event();
                $event->set_echo( false );
                laterpay_event_dispatcher()->dispatch( 'laterpay_on_plugin_is_working', $event );

                return false;
            }

            // Possible value of status is ok or error in case of wrong params which means api is working.
            if (
                ( is_array( $result ) && array_key_exists( 'status', $result ) ) ||
                ( empty( $result ) && ( ! empty( $health_check ) && array_key_exists( 'status', $health_check ) ) )
            ) {
                self::$lp_api_availability = true;
            } else {
                throw new Exception( 'Unable to reach LaterPay API' );
            }

        } catch (Exception $exception) {

            unset( $exception );

            $result['articles'] = array();

            switch ($action) {
                case 0:
                case 1:
                    $access = (bool)$action;
                    break;
                default:
                    $access = false;
                    break;
            }

            foreach ($article_ids as $id) {
                $result['articles'][$id] = array('access' => $access);
            }

            self::$lp_api_availability = false;
        }

        return $result;
    }

    /**
     * URL encode parameters.
     *
     * @param array $params Parameters to be added to URL.
     *
     * @return array|string
     */
    public static function laterpay_encode_url_params( $params ) {

        if ( ! empty( $params ) && is_array( $params ) ) {
            return array_map( 'rawurlencode', $params );
        }

        return $params;
    }
}
