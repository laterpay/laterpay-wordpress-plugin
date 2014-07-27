<?php

class LaterPayViewHelper
{

    public static $pluginPage = 'laterpay-plugin';

    public static $adminMenu = array(
        'get_started'   => array( 'url' => 'laterpay-getstarted-tab',   'title' => __('Get started', 'laterpay') ),
        'pricing'       => array( 'url' => 'laterpay-pricing-tab',      'title' => __('Pricing', 'laterpay') ),
        'appearance'    => array( 'url' => 'laterpay-appearance-tab',   'title' => __('Appearance', 'laterpay') ),
        'account'       => array( 'url' => 'laterpay-account-tab',      'title' => __('Account', 'laterpay') ),
    );

    /**
     * Get date of next day
     *
     * @param string $date
     *
     * @return string
     */
    protected static function get_next_day( $date ) {
        $nextDay = date( 'Y-m-d', mktime(
                date( 'H', strtotime( $date ) ),
                date( 'i', strtotime( $date ) ),
                date( 's', strtotime( $date ) ),
                date( 'm', strtotime( $date ) ),
                date( 'd', strtotime( $date ) ) + 1,
                date( 'Y', strtotime( $date ) )
            ) );

        return $nextDay;
    }

    /**
     * Get date 30 days ago
     *
     * @param string $date
     *
     * @return string
     */
    protected static function get_last_30_days( $date ) {
        $last30Day = date( 'Y-m-d', mktime(
                date( 'H', strtotime( $date ) ),
                date( 'i', strtotime( $date ) ),
                date( 's', strtotime( $date ) ),
                date( 'm', strtotime( $date ) ),
                date( 'd', strtotime( $date ) ) - 30,
                date( 'Y', strtotime( $date ) )
            ) );

        return $last30Day;
    }

    public static function get_days_statistics_as_string( $statistic, $type = 'quantity', $delimiter = ',' ) {
        $today = date('Y-m-d');
        $date = self::get_last_30_days( date( $today ) );

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
     * Check if plugin is fully functional
     *
     * @return boolean
     */
    public static function plugin_is_working() {
        if ( get_option( 'laterpay_plugin_is_activated' ) != 1 ) {
            return false;
        }

        $modeIsLive = get_option( 'laterpay_plugin_is_in_live_mode' );
        $sandboxKey = get_option( 'laterpay_sandbox_api_key' );
        $liveKey    = get_option( 'laterpay_live_api_key' );
        if ( ! function_exists( 'wp_get_current_user' ) ) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }
        if ( ($modeIsLive && empty( $liveKey )) || (! $modeIsLive && empty( $sandboxKey )) || (! $modeIsLive && ! LaterPayUserHelper::can( 'laterpay_read_post_statistics', null, false )) ) {
            return false;
        }

        return true;
    }

    /**
     * Get number based on locale format
     *
     * @param double $number
     * @param int    $decimals
     *
     * @return string
     */
    public static function format_number( $number, $decimals = 2 ) {
        global $wp_locale;

        $delocalized_number = str_replace( ',', '.', $number );

        $formatted = number_format(
            (float) $delocalized_number,
            absint( $decimals ),
            $wp_locale->number_format['decimal_point'],
            $wp_locale->number_format['thousands_sep']
        );

        return $formatted;
    }

}
