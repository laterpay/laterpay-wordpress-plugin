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
     * Check if the current request is an Ajax request.
     *
     * @return bool
     */
    public static function is_ajax() {
        $server = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ? sanitize_text_field( $_SERVER['HTTP_X_REQUESTED_WITH'] ) : '';
        return ! empty( $server ) && strtolower( $server ) == 'xmlhttprequest';
    }

    /**
     * Get current URL.
     *
     * @return string $url
     */
    public static function get_current_url() {
        $ssl = isset( $_SERVER['HTTPS'] ) && sanitize_text_field( $_SERVER['HTTPS'] ) == 'on';
        // Check for Cloudflare Universal SSL / flexible SSL
        if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && strpos( $_SERVER['HTTP_CF_VISITOR'], 'https' ) !== false ) {
            $ssl = true;
        }
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';

        // process Ajax requests
        if ( self::is_ajax() ) {
            $url    = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : '';
            $parts  = parse_url( $url );

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
        $serverPort = isset( $_SERVER['SERVER_PORT'] )? absint( $_SERVER['SERVER_PORT'] ) : '';
        $serverName = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : '';
        if ( $serverName == 'localhost' and function_exists( 'site_url' ) ) {
            $serverName = (str_replace( array('http://', 'https://'), '', site_url() )) ; // WP function
            // overwrite port on Heroku
            if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && strpos( $_SERVER['HTTP_CF_VISITOR'], 'https' ) !== false ) {
                $serverPort = 443;
            } else {
                $serverPort = 80;
            }
        }
        if ( ! $ssl && $serverPort != '80' ) {
            $pageURL .= $serverName . ':' . $serverPort . $uri;
        } else if ( $ssl && $serverPort != '443' ) {
            $pageURL .= $serverName . ':' . $serverPort . $uri;
        } else {
            $pageURL .= $serverName . $uri;
        }

        return $pageURL;
    }

    /**
     * Check if the API is available
     * @return bool
     */
    public static function check_laterpay_api_availability() {
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $action         = (int) get_option( 'laterpay_api_fallback_behaviour', 0 );
        $behavior       = LaterPay_Controller_Admin_Settings::get_laterpay_api_options();
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $api_available = $client->check_health( $client_options['api_root'] );
        laterpay_get_logger()->info(
            __METHOD__, array( 'api_available' => $api_available, 'laterpay_api_fallback_behaviour' => $behavior[ $action ] )
        );

        if ( ! $api_available ) {
            switch ( $action ) {
                case 0:
                    // Do Something
                    break;
                case 1:
                    // Do Something
                    break;
                case 2:
                    // Do Something
                    break;
                default:
                    // Do Something
            }
        } else {
            // Do Something
        }

        return $api_available;
    }
}
