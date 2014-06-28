<?php

class ViewHelper {
    /**
     * Get date of next day
     *
     * @param type $date
     *
     * @return type
     */
    protected static function getNextDay( $date ) {
        $nextDay = date('Y-m-d', mktime(
                date('H', strtotime($date)),
                date('i', strtotime($date)),
                date('s', strtotime($date)),
                date('m', strtotime($date)),
                date('d', strtotime($date)) + 1,
                date('Y', strtotime($date))
            ) );

        return $nextDay;
    }

    /**
     * Get date 30 days ago
     *
     * @param date $date
     *
     * @return date
     */
    protected static function getLast30Day( $date ) {
        $last30Day = date('Y-m-d', mktime(
                date('H', strtotime($date)),
                date('i', strtotime($date)),
                date('s', strtotime($date)),
                date('m', strtotime($date)),
                date('d', strtotime($date)) - 30,
                date('Y', strtotime($date))
            ) );

        return $last30Day;
    }

    public static function getDaysStatisticAsString( $statistic, $type = 'quantity', $delimiter = ',' ) {
        $today = date('Y-m-d');
        $date = self::getLast30Day(date($today));

        $result = '';
        while ( $date <= $today ) {
            if ( $result !== '' ) {
                $result .= $delimiter;
            }
            if ( isset($statistic[$date]) ) {
                $result .= $statistic[$date][$type];
            } else {
                $result .= '0';
            }
            $date = self::getNextDay($date);
        }

        return $result;
    }

    /**
     * Check if plugin is fully functional
     *
     * @return boolean
     */
    public static function isPluginAvailable() {
        if ( get_option( 'laterpay_activate' ) != 1 ) {
            return false;
        }

        $modeIsLive = get_option( 'laterpay_plugin_mode_is_live' );
        $sandboxKey = get_option( 'laterpay_sandbox_api_key' );
        $liveKey    = get_option( 'laterpay_live_api_key' );
        if(!function_exists('wp_get_current_user')) {
            include_once(ABSPATH . "wp-includes/pluggable.php"); 
        }
        if ( ($modeIsLive && empty($liveKey)) || (!$modeIsLive && empty($sandboxKey)) || (!$modeIsLive && !current_user_can('manage_options')) ) {
            return false;
        }

        return true;
    }

    /**
     * Get number based on locale format
     *
     * @param number  $number
     * @param int     $decimals
     *
     * @return number
     */
    public static function formatNumber( $number, $decimals = 2 ) {
        global $wp_locale;

        $formatted = number_format(
            $number,
            absint($decimals),
            $wp_locale->number_format['decimal_point'],
            $wp_locale->number_format['thousands_sep']
        );

        return $formatted;
    }

}
