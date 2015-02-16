<?php

/**
 * LaterPay browser helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Browser
{

    /**
     * @var Browscap $browscap library
     */
    protected static $browscap = null;

    /**
     * @var array|null $browser information
     */
    protected static $browser = null;

    /**
     * Return object of all browscap library.
     *
     * @return object
     */
    public static function php_browscap() {

        $config = laterpay_get_plugin_config();

        if ( empty( self::$browscap ) ) {
            self::$browscap = new Browscap( $config->get( 'cache_dir' ) );
            self::$browscap->doAutoUpdate = $config->get( 'browscap.autoupdate' );
            if ( $config->has( 'browscap.manually_updated_copy' ) ) {
                self::$browscap->localFile = $config->get( 'browscap.manually_updated_copy' );
            }
            self::$browscap->silent = $config->get( 'browscap.silent' );
        }

        return self::$browscap;
    }

    /**
     * Return array of all browser infos.
     *
     * @usage $browserInfo = php_browser_info();
     *
     * @return array
     */
    public static function php_browser_info() {
        if ( is_null( self::$browser ) ) {
            self::$browser = self::php_browscap()->getBrowser( NULL, true );
        }

        return (array)self::$browser;
    }

    /**
     * Conditional to test for cookie support.
     *
     * @return bool
     */
    public static function browser_supports_cookies() {
        $browserInfo = self::php_browser_info();
        if ( empty($browserInfo) ) {
            return true;
        }
        if ( isset( $browserInfo['Cookies'] ) ) {
            if ( $browserInfo['Cookies'] == 1 || $browserInfo['Cookies'] == 'true' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conditional to test for crawler
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_crawler( $version = '' ) {
        $browserInfo = self::php_browser_info();
        if ( empty($browserInfo) ) {
            return false;
        }
        if ( isset( $browserInfo['Crawler'] ) && ($browserInfo['Crawler'] == 1 || $browserInfo['Crawler'] == 'true') ) {
            if ( $version == '' ) :
                return true;
            elseif ( $browserInfo['MajorVer'] == $version ) :
                return true;
            else :
                return false;
            endif;
        } else {
            return false;
        }
    }

}
