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

}
