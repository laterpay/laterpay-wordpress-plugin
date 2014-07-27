<?php

class LaterPayRequestHelper
{

    public static $clear_hmac = true;
    public static $clear_lptoken = true;

    public static function is_ajax() {
        return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
    }

    /**
     * Get current URL
     *
     * @return string of current URL
     */
    public static function get_current_url() {
        $ssl = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on';
        $uri = $_SERVER['REQUEST_URI'];

        // process Ajax requests
        if ( self::is_ajax() ) {
            $url    = $_SERVER['HTTP_REFERER'];
            $parts  = parse_url( $url );

            if ( ! empty( $parts ) ) {
                $uri = $parts['path'];
                if ( ! empty( $parts['query'] ) ) {
                    $uri .= '?' . $parts['query'];
                }
            }
        }

        if ( self::$clear_lptoken ) {
            $uri = preg_replace( '/lptoken=.*?($|&)/', '', $uri );
        }
        if ( self::$clear_hmac ) {
            $uri = preg_replace( '/ts=.*?($|&)/', '', $uri );
            $uri = preg_replace( '/hmac=.*?($|&)/', '', $uri );
        }
        $uri = preg_replace( '/&$/', '', $uri );

        if ( $ssl ) {
            $pageURL = 'https://';
        } else {
            $pageURL = 'http://';
        }
        if ( ! $ssl && $_SERVER['SERVER_PORT'] != '80' ) {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $uri;
        } else if ( $ssl && $_SERVER['SERVER_PORT'] != '443' ) {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $uri;
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'] . $uri;
        }

        return $pageURL;
    }

}
