<?php

class LaterPay_Helper_Browser
{

    /**
     * @var Browscap $browscap library
     */
    protected static $browscap = null;

    /**
     * Return array of all browser infos.
     *
     * @usage $browserInfo = php_browser_info();
     *
     * @return array
     */
    public static function php_browser_info() {

	    $config = laterpay_get_plugin_config();

        if ( empty( self::$browscap ) ) {
            self::$browscap = new Browscap( $config->get( 'cache_dir' ) );
            self::$browscap->doAutoUpdate = $config->get( 'browscap.autoupdate' );
            if ( $config->has( 'browscap.manually_updated_copy' ) ) {
                self::$browscap->localFile = $config->get( 'browscap.manually_updated_copy' );
            }
        }

        return self::$browscap->getBrowser( NULL, true );
    }

    /**
     * Return the name of the browser.
     *
     * @return string
     */
    public static function get_browser_name() {
        $browserInfo = self::php_browser_info();

        return $browserInfo['Browser'];
    }

    /**
     * Return the browser version number.
     *
     * @return mixed
     */
    public static function get_browser_version() {
        $browserInfo = self::php_browser_info();

        return $browserInfo['Version'];
    }

    /**
     * Return the browser major version number.
     *
     * @return mixed
     */
    public static function get_browser_major_version() {
        $browserInfo = self::php_browser_info();

        return $browserInfo['MajorVer'];
    }

    /**
     * Conditional to test for any browser.
     *
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public static function is_browser( $name = '', $version = '' ) {
        $browser_info = self::php_browser_info();
        if ( isset( $browser_info['Browser'] ) && strpos( $browser_info['Browser'], $name ) !== false ) {
            if ( $version == '' ) {
                return true;
            } elseif ( $browser_info['MajorVer'] == $version ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Conditional to test for Firefox.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_firefox( $version = '' ) {
        return self::is_browser( 'Firefox', $version );
    }

    /**
     * Conditional to test for Safari.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_safari( $version = '' ) {
        return self::is_browser( 'Safari', $version );
    }

    /**
     * Conditional to test for Chrome.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_chrome( $version = '' ) {
        return self::is_browser( 'Chrome', $version );
    }

    /**
     * Conditional to test for Opera.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_opera( $version = '' ) {
        return self::is_browser( 'Opera', $version );
    }

    /**
     * Conditional to test for IE.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_ie( $version = '' ) {
        return self::is_browser( 'IE', $version );
    }

    /**
     * Conditional to test for mobile devices.
     *
     * @return bool
     */
    public static function is_mobile() {
        $browserInfo = self::php_browser_info();
        if ( isset( $browserInfo['isMobileDevice'] ) ) {
            if ( $browserInfo['isMobileDevice'] == 1 || $browserInfo['isMobileDevice'] == 'true' || strpos( $browserInfo['Device_Type'], 'Mobile' ) !== false ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conditional to test for iPhone.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_iphone( $version = '' ) {
        $browserInfo = self::php_browser_info();
        if ( (isset( $browserInfo['Browser'] ) && $browserInfo['Browser'] == 'iPhone') || strpos( $_SERVER['HTTP_USER_AGENT'], 'iPhone' ) ) {
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

    /**
     * Conditional to test for iPad.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_ipad( $version = '' ) {
        $browserInfo = self::php_browser_info();
        if ( preg_match( '/iPad/', $browserInfo['browser_name_pattern'], $matches ) || strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) ) {
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

    /**
     * Conditional to test for iPod.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_ipod( $version = '' ) {
        $browserInfo = self::php_browser_info();
        if ( preg_match( '/iPod/', $browserInfo['browser_name_pattern'], $matches ) ) {
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

    /**
     * Conditional to test for WinPhone.
     *
     * @param string $version specific browser version
     *
     * @return bool
     */
    public static function is_winphone( $version = '' ) {
        $browserInfo = self::php_browser_info();
        if ( preg_match( '/WinPhone/', $browserInfo['Platform'], $matches ) ) {
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

    /**
     * Conditional to test for JavaScript support.
     *
     * @return bool
     */
    public static function browser_supports_javascript() {
        $browserInfo = self::php_browser_info();
        if ( isset( $browserInfo['JavaScript'] ) ) {
            if ( $browserInfo['JavaScript'] == 1 || $browserInfo['JavaScript'] == 'true' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conditional to test for cookie support.
     *
     * @return bool
     */
    public static function browser_supports_cookies() {
        $browserInfo = self::php_browser_info();
        if ( isset( $browserInfo['Cookies'] ) ) {
            if ( $browserInfo['Cookies'] == 1 || $browserInfo['Cookies'] == 'true' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conditional to test for CSS support.
     *
     * @return bool
     */
    public static function browser_supports_css() {
        $browserInfo = self::php_browser_info();
        if ( isset( $browserInfo['SupportsCss'] ) ) {
            if ( $browserInfo['SupportsCss'] == 1 || $browserInfo['SupportsCss'] == 'true' ) {
                return true;
            }
        }
        if ( isset( $browserInfo['CssVersion'] ) ) {
            if ( $browserInfo['CssVersion'] >= 1 ) {
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
