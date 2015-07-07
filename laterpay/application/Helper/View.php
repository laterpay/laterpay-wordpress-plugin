<?php

/**
 * LaterPay view helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_View
{

    /**
     * @var string
     */
    public static $pluginPage = 'laterpay-plugin';

    /**
     * Helper function to render a plugin backend navigation tab link.
     *
     * @param array $page array(
     *                      'url'   => String
     *                      'title' => String
     *                      'cap'   => String
     *                      'data'  => Array|String     // optional
     *                    )
     *
     * @return string $link
     */
    public static function get_admin_menu_link( $page ) {
        $query_args = array(
            'page' => $page['url'],
        );
        $href = admin_url( 'admin.php' );
        $href = add_query_arg( $query_args, $href );

        $data = '';
        if ( isset( $page['data'] ) ) {
            $data = json_encode( $page['data'] );
            $data = 'data="' . esc_attr( $data ) . '"';
        }

        return laterpay_sanitize_output( '<a href="' . $href . '" ' . $data . ' class="lp_navigation-tabs__link">' . $page['title'] . '</a>' );
    }

    /**
     * Get links to be rendered in the plugin backend navigation.
     *
     * @return array
     */
    public static function get_admin_menu() {
        $menu = array();

        // @link http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table
        // cap "activate_plugins"   => Super Admin, Admin
        // cap "moderate_comments"  => Super Admin, Admin, Editor

        $menu['dashboard'] = array(
            'url'       => 'laterpay-plugin',
            'title'     => __( 'Dashboard', 'laterpay' ),
            'cap'       => 'moderate_comments',
            /* MOVED: #797 Comment out sales statistics
            'submenu'   => array(
                'name'      => 'time_passes',
                'url'       => 'laterpay-timepass-dashboard-tab',
                'cap'       => 'moderate_comments',
                'title'     => __( 'Time Passes', 'laterpay' ),
                'data'      => array(
                    'view'      => 'time-passes',
                    'label'     => __( 'Standard KPIs', 'laterpay' ),
                ),
            ),*/
        );

        $menu['pricing'] = array(
            'url'   => 'laterpay-pricing-tab',
            'title' => __( 'Pricing', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $menu['appearance'] = array(
            'url'   => 'laterpay-appearance-tab',
            'title' => __( 'Appearance', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $menu['account'] = array(
            'url'   => 'laterpay-account-tab',
            'title' => __( 'Account', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        // modify laterpay menu
        $menu = apply_filters( 'modify_menu', $menu );

        return $menu;
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
            if ( isset( $statistic[ $date ] ) ) {
                $result .= $statistic[ $date ][ $type ];
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
        $is_in_live_mode            = get_option( 'laterpay_plugin_is_in_live_mode' );
        $sandbox_api_key            = get_option( 'laterpay_sandbox_api_key' );
        $live_api_key               = get_option( 'laterpay_live_api_key' );
        $is_in_visible_test_mode    = get_option( 'laterpay_is_in_visible_test_mode' );
        if ( ! function_exists( 'wp_get_current_user' ) ) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        // check, if plugin operates in live mode and Live API key exists
        if ( $is_in_live_mode && empty( $live_api_key ) ) {
            return false;
        }

        // check, if plugin is not in live mode and Sandbox API key exists
        if ( ! $is_in_live_mode && empty( $sandbox_api_key ) ) {
            return false;
        }

        // check, if plugin is not in live mode and is in visible test mode
        if ( ! $is_in_live_mode && $is_in_visible_test_mode ) {
            return true;
        }

        // check, if plugin is not in live mode and current user has sufficient capabilities
        if ( ! $is_in_live_mode && ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get current plugin mode.
     *
     * @return string $mode
     */
    public static function get_plugin_mode() {
        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        return $mode;
    }

    /**
     * Remove extra spaces from string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function remove_extra_spaces( $string ) {
        $string = trim( preg_replace( '/>\s+</', '><', $string ) );
        $string = preg_replace( '/\n\s*\n/', '', $string );

        return $string;
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
                $formatted = number_format_i18n( $number / 1000, 1 ) . __( 'k', 'laterpay' ); // k -> short for kilo (thousands)
            }
        } else {
            // format count values
            if ( $number < 10000 ) {
                $formatted = number_format( $number );
            } else {
                // reduce values above 10,000 to thousands and format them with one digit
                $formatted = number_format( $number / 1000, 1 ) . __( 'k', 'laterpay' ); // k -> short for kilo (thousands)
            }
        }

        return $formatted;
    }

    /**
     * Number normalization
     *
     * @param $number
     *
     * @return float
     */
    public static function normalize( $number ) {
        global $wp_locale;

        $number = str_replace( $wp_locale->number_format['thousands_sep'], '', (string) $number );
        $number = str_replace( $wp_locale->number_format['decimal_point'], '.', $number );

        return (float) $number;
    }

    /**
     * Check, if purchase link should be hidden.
     *
     * @return bool
     */
    public static function purchase_link_is_hidden() {
        $is_hidden = get_option( 'laterpay_only_time_pass_purchases_allowed' ) && get_option( 'laterpay_teaser_content_only' );

        return $is_hidden;
    }

    /**
     * Check, if purchase button should be hidden.
     *
     * @return bool
     */
    public static function purchase_button_is_hidden() {
        $is_hidden = get_option( 'laterpay_only_time_pass_purchases_allowed' );

        return $is_hidden;
    }
}
