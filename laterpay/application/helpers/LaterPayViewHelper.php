<?php

class LaterPayViewHelper {
    public static $pluginPage = 'laterpay-plugin';

    public static $adminMenu = array(
        'get_started'   => array('url' => 'laterpay-getstarted-tab', 'title' => 'Get started'),
        'pricing'       => array('url' => 'laterpay-pricing-tab', 'title' => 'Pricing'),
        'appearance'    => array('url' => 'laterpay-appearance-tab', 'title' => 'Appearance'),
        'account'       => array('url' => 'laterpay-account-tab', 'title' => 'Account'),
    );

    /**
     * Get date of next day
     *
     * @param string $date
     *
     * @return string
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
     * @param string $date
     *
     * @return string
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
        if ( get_option('laterpay_plugin_is_activated') != 1 ) {
            return false;
        }

        $modeIsLive = get_option('laterpay_plugin_is_in_live_mode');
        $sandboxKey = get_option('laterpay_sandbox_api_key');
        $liveKey    = get_option('laterpay_live_api_key');
        if ( !function_exists('wp_get_current_user')) {
            include_once(ABSPATH . 'wp-includes/pluggable.php');
        }
        if ( ($modeIsLive && empty($liveKey)) || (!$modeIsLive && empty($sandboxKey)) || (!$modeIsLive && !LaterPayUserHelper::can('laterpay_read_post_statistics', null, false)) ) {
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

        $delocalized_number = str_replace(',', '.', $number);

        $formatted = number_format(
            $delocalized_number,
            absint($decimals),
            $wp_locale->number_format['decimal_point'],
            $wp_locale->number_format['thousands_sep']
        );

        return $formatted;
    }

}
