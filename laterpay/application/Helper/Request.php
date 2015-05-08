<?php

/**
 * LaterPay request helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Request {
    protected static $lp_api_availability = null;

    /**
     * Check, if the current request is an Ajax request.
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
     * Check, if the API is available.
     *
     * @return bool
     */
    public static function laterpay_api_check_availability() {
        if ( ! isset( self::$lp_api_availability ) ) {
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

            laterpay_get_logger()->info(
                __METHOD__, array(
                    'api_available'                   => self::$lp_api_availability,
                    'laterpay_api_fallback_behavior' => $behavior[ $action ],
                )
            );
        }

        return self::$lp_api_availability;
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

        $context        = array(
            'token'    => $token,
            'redirect' => $redirect,
        );

        laterpay_get_logger()->info( __METHOD__, $context );

        $client->set_token( $token, $redirect );
    }

    /**
     * Check, if user has access to a given item / given array of items.
     *
     * @see LaterPay_Client::get_access()
     */
    public static function laterpay_api_get_access( $article_ids, $product_key = null ) {
        $result = array();

        if ( self::laterpay_api_check_availability() ) {
            $client_options = LaterPay_Helper_Config::get_php_client_options();
            $client         = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
            );

            $result = $client->get_access( $article_ids, $product_key );
        } else {
            $action             = (int) get_option( 'laterpay_api_fallback_behavior', 0 );
            $result['articles'] = array();

            switch ( $action ) {
                case 0:
                case 1:
                    $access = (bool) $action;
                    break;
                default:
                    $access = false;
                    break;
            }

            foreach ( $article_ids as $id ) {
                $result['articles'][ $id ] = array( 'access' => $access );
            }
        }

        $context = array(
            'article_ids'   => $article_ids,
            'product_key'   => $product_key,
            'result'        => $result,
        );

        laterpay_get_logger()->info( __METHOD__, $context );

        return $result;
    }

    /**
     * Update token.
     *
     * @see LaterPay_Client::acquire_token()
     */
    public static function laterpay_api_acquire_token() {
        if ( self::laterpay_api_check_availability() ) {
            $client_options = LaterPay_Helper_Config::get_php_client_options();
            $client         = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
            );

            if ( ! $client->has_token() ) {
                laterpay_get_logger()->debug( 'RESOURCE:: No token found. Acquiring token' );

                $client->acquire_token();
            }
        } else {
            laterpay_get_logger()->debug( 'RESOURCE:: No token found. API is not available' );
        }
    }
}
