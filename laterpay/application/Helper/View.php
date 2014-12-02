<?php

class LaterPay_Helper_View
{

    /**
     * @var string
     */
    public static $pluginPage = 'laterpay-plugin';

    /**
     * Get admin menu data.
     *
     * @var array
     */
    public static function get_admin_menu() {
        return array(
            'dashboard'     => array( 'url' => 'laterpay-dashboard-tab',    'title' => __( 'Dashboard <sup class="lp_is-beta">beta</sup>', 'laterpay' ) ),
            'pricing'       => array( 'url' => 'laterpay-pricing-tab',      'title' => __( 'Pricing', 'laterpay' ) ),
            'appearance'    => array( 'url' => 'laterpay-appearance-tab',   'title' => __( 'Appearance', 'laterpay' ) ),
            'account'       => array( 'url' => 'laterpay-account-tab',      'title' => __( 'Account', 'laterpay' ) ),
        );
    }

    /**
     * Get date of next day.
     *
     * @param string $date
     *
     * @return string $nextDay
     */
    protected static function get_next_day( $date ) {
        $next_day = date( 'Y-m-d', mktime(
                date( 'H', strtotime( $date ) ),
                date( 'i', strtotime( $date ) ),
                date( 's', strtotime( $date ) ),
                date( 'm', strtotime( $date ) ),
                date( 'd', strtotime( $date ) ) + 1,
                date( 'Y', strtotime( $date ) )
            ) );

        return $next_day;
    }

    /**
     * Get date a given number of days prior to a given date.
     *
     * @param string $date
     * @param int    $ago number of days ago
     *
     * @return string $prior_date
     */
    protected static function get_date_days_ago( $date, $ago = 30 ) {
        $ago = absint( $ago );
        $prior_date = date( 'Y-m-d', mktime(
                date( 'H', strtotime( $date ) ),
                date( 'i', strtotime( $date ) ),
                date( 's', strtotime( $date ) ),
                date( 'm', strtotime( $date ) ),
                date( 'd', strtotime( $date ) ) - $ago,
                date( 'Y', strtotime( $date ) )
            ) );

        return $prior_date;
    }

    /**
     * Get the statistics data for the last 30 days as string, joined by a given delimiter.
     *
     * @param array  $statistic
     * @param string $type
     * @param string $delimiter
     *
     * @return string
     */
    public static function get_days_statistics_as_string( $statistic, $type = 'quantity', $delimiter = ',' ) {
        $today  = date( 'Y-m-d' );
        $date   = self::get_date_days_ago( date( $today ), 30 );

        $result = '';
        while ( $date <= $today ) {
            if ( $result !== '' ) {
                $result .= $delimiter;
            }
            if ( isset( $statistic[$date] ) ) {
                $result .= $statistic[$date][$type];
            } else {
                $result .= '0';
            }
            $date = self::get_next_day( $date );
        }

        return $result;
    }

    /**
     * Check, if plugin is fully functional.
     *
     * @return bool
     */
    public static function plugin_is_working() {
        $modeIsLive = get_option( 'laterpay_plugin_is_in_live_mode' );
        $sandboxKey = get_option( 'laterpay_sandbox_api_key' );
        $liveKey    = get_option( 'laterpay_live_api_key' );
        if ( ! function_exists( 'wp_get_current_user' ) ) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        // check, if plugin works in live mode and API key exists
        if ( $modeIsLive && empty( $liveKey ) ) {
            return false;
        }

        // check, if plugin is not in live mode and Sandbox API key exists
        if ( ! $modeIsLive && empty( $sandboxKey ) ) {
            return false;
        }

        // check, if plugin is not in live mode and current user has sufficient capabilities
        if ( ! $modeIsLive && ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
            return false;
        }

        return true;
    }

    /**
     * Remove extra spaces from string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function remove_extra_spaces( $string ) {
        return trim( preg_replace( '/>\s+</', '><', $string ) );
    }

    /**
     * Format number based on its type.
     *
     * @param float   $number
     * @param bool    $is_monetary
     *
     * @return string $formatted
     */
    public static function format_number( $number, $is_monetary = true ) {
        // delocalize number
        $number = (float) str_replace( ',', '.', $number );

        if ( $is_monetary ) {
            // format monetary values
            if ( $number < 200 ) {
                // format values up to 200 with two digits
                // 200 is used to make sure the maximum Single Sale price of 149.99 is still formatted with two digits
                $formatted = number_format_i18n( $number, 2 );
            } elseif ( $number >= 200 && $number < 10000 ) {
                // format values between 200 and 10,000 without digits
                $formatted = number_format_i18n( $number, 0 );
            } else {
                // reduce values above 10,000 to thousands and format them with one digit
                $formatted = number_format_i18n( $number / 1000, 1 ) . __( 'k', 'laterpay'); // k -> short for kilo (thousands)
            }
        } else {
            // format count values
            if ( $number < 10000 ) {
                $formatted = number_format( $number );
            } else {
                // reduce values above 10,000 to thousands and format them with one digit
                $formatted = number_format( $number / 1000, 1 ) . __( 'k', 'laterpay'); // k -> short for kilo (thousands)
            }
        }

        return $formatted;
    }
}
