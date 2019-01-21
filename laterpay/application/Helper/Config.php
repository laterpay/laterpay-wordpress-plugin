<?php

/**
 * LaterPay config helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Config {
    private static $options = array();

    private static $regional_settings = array(
        'eu' => array(
            'api' => array(
                'sandbox_backend_api_url' => 'https://api.sandbox.laterpaytest.net',
                'sandbox_dialog_api_url'  => 'https://web.sandbox.laterpaytest.net',
                'live_backend_api_url'    => 'https://api.laterpay.net',
                'live_dialog_api_url'     => 'https://web.laterpay.net',
                'merchant_backend_url'    => 'https://merchant.laterpay.net/',
                'token_name'              => 'laterpay_token',
                'sandbox_merchant_id'     => '984df2b86250447793241a',
                'sandbox_api_key'         => '57791c777baa4cea94c4ec074184e06d',
            ),
            'currency' => array(
                'code'                    => 'EUR',
                'dynamic_start'           => 13,
                'dynamic_end'             => 18,
                'default_price'           => 0.29,
                'limits' => array(
                    'default' => array(
                        'ppu_min'         => 0.05,
                        'ppu_only_limit'  => 1.48,
                        'ppu_max'         => 5.00,
                        'sis_min'         => 1.49,
                        'sis_only_limit'  => 5.01,
                        'sis_max'         => 149.99
                    )
                )
            ),
            'payment' => array(
                'icons' => array(
                    'sepa',
                    'visa',
                    'mastercard',
                    'paypal'
                )
            ),
            'tracking_ua_id' => array(
                'live'    => 'UA-50448165-3',
                'sandbox' => 'UA-50448165-4'
            )
        ),
        'us' => array(
            'api' => array(
                'sandbox_backend_api_url' => 'https://api.sandbox.uselaterpaytest.com',
                'sandbox_dialog_api_url'  => 'https://web.sandbox.uselaterpaytest.com',
                'live_backend_api_url'    => 'https://api.uselaterpay.com',
                'live_dialog_api_url'     => 'https://web.uselaterpay.com',
                'merchant_backend_url'    => 'https://web.uselaterpay.com/merchant',
                'token_name'              => 'laterpay_token',
                'sandbox_merchant_id'     => 'xswcBCpR6Vk6jTPw8si7KN',
                'sandbox_api_key'         => '22627fa7cbce45d394a8718fd9727731',
            ),
            'currency' => array(
                'code'                    => 'USD',
                'dynamic_start'           => 13,
                'dynamic_end'             => 18,
                'default_price'           => 0.29,
                'limits' => array(
                    'default' => array(
                        'ppu_min'         => 0.05,
                        'ppu_only_limit'  => 1.98,
                        'ppu_max'         => 5.00,
                        'sis_min'         => 1.99,
                        'sis_only_limit'  => 5.01,
                        'sis_max'         => 149.99,
                    )
                )
            ),
            'payment' => array(
                'icons' => array(
                    'visa',
                    'mastercard',
                    'visa-debit',
                    'americanexpress',
                    'discovercard'
                )
            ),
            'tracking_ua_id' => array(
                'live'    => 'UA-50448165-9',
                'sandbox' => 'UA-50448165-10'
            )
        )
    );

    /**
     * Get regional settings
     *
     * @return array
     */
    public static function get_regional_settings() {
        $region = get_option( 'laterpay_region', 'us' );

        // region correction
        if ( ! isset( self::$regional_settings[ $region ] ) ) {
            update_option( 'laterpay_region', 'us' );
            $region = 'us';
        }

        return self::build_settings_list(self::$regional_settings[ $region ]);
    }

    /**
     * Build settings list
     *
     * @return array
     */
    protected static function build_settings_list( $settings, $prefix = '' ) {
        $list = array();

        foreach ( $settings as $key => $value ) {
            $setting_name = $prefix . $key;

            if ( is_array( $value ) ) {
                $list = array_merge( $list, self::build_settings_list( $value, $setting_name . '.' ) );
                continue;
            }

            $list[$setting_name] = $value;
        }

        return $list;
    }

    /**
     * Get settings section for current region
     *
     * @param $section
     *
     * @return array|null
     */
    public static function get_settings_section( $section ) {
        // get regional settings
        $region = get_option( 'laterpay_region', 'us' );

        return isset( self::$regional_settings[ $region ][ $section ] ) ? self::$regional_settings[ $region ][ $section ] : null;
    }

    /**
     * Get currency config
     *
     * @return array
     */
    public static function get_currency_config() {
        $config         = laterpay_get_plugin_config();
        $limits_section = 'currency.limits';

        // get limits
        $currency_limits  = $config->get_section( $limits_section . '.' . 'default' );
        $currency_general = array(
            'code'          => $config->get( 'currency.code' ),
            'dynamic_start' => $config->get( 'currency.dynamic_start' ),
            'dynamic_end'   => $config->get( 'currency.dynamic_end' ),
            'default_price' => $config->get( 'currency.default_price' )
        );

        // process limits keys
        foreach ( $currency_limits as $key => $val ) {
            $key_components = explode( '.', $key );
            $simple_key = end( $key_components );
            $currency_limits[ $simple_key ] = $val;
            unset( $currency_limits[ $key ] );
        }

        return array_merge( $currency_limits, $currency_general );
    }

    /**
     * Get options for LaterPay PHP client.
     *
     * @return array
     */
    public static function get_php_client_options() {
        $config = laterpay_get_plugin_config();

        if ( empty( self::$options ) ) {
            if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
                self::$options['cp_key']   = get_option( 'laterpay_live_merchant_id' );
                self::$options['api_key']  = get_option( 'laterpay_live_api_key' );
                self::$options['api_root'] = $config->get( 'api.live_backend_api_url' );
                self::$options['web_root'] = $config->get( 'api.live_dialog_api_url' );
            } else {
                self::$options['cp_key']   = get_option( 'laterpay_sandbox_merchant_id' );
                self::$options['api_key']  = get_option( 'laterpay_sandbox_api_key' );
                self::$options['api_root'] = $config->get( 'api.sandbox_backend_api_url' );
                self::$options['web_root'] = $config->get( 'api.sandbox_dialog_api_url' );
            }

            self::$options['token_name'] = $config->get( 'api.token_name' );
        }

        return self::$options;
    }

    /**
     * Get actual sandbox creds
     *
     * @return array $creds
     */
    public static function prepare_sandbox_creds() {
        $regional_settings   = self::get_regional_settings();
        $creds_match_default = false;

        $cp_key  = get_option( 'laterpay_sandbox_merchant_id' );
        $api_key = get_option( 'laterpay_sandbox_api_key' );

        // detect if sandbox creds were modified
        if ( $cp_key && $api_key ) {
            foreach ( self::$regional_settings as $settings ) {
                if ( $settings['api'][ 'sandbox_merchant_id' ] === $cp_key &&
                     $settings['api'][ 'sandbox_api_key' ] === $api_key ) {
                    $creds_match_default = true;
                    break;
                }
            }
        } else {
            $creds_match_default = true;
        }

        if ( $creds_match_default ) {
            $cp_key  = $regional_settings[ 'api.sandbox_merchant_id' ];
            $api_key = $regional_settings[ 'api.sandbox_api_key' ];

            update_option( 'laterpay_sandbox_merchant_id', $cp_key );
            update_option( 'laterpay_sandbox_api_key', $api_key );
        }

        return array(
            'cp_key'  => $cp_key,
            'api_key' => $api_key,
        );
    }

    /**
     * Remove LaterPay custom table data.
     * This code won't execute on VIP environment.
     */
    public static function erase_custom_data() {

        global $wpdb;

        $table_terms_price   = $wpdb->prefix . 'laterpay_terms_price';
        $table_history       = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views    = $wpdb->prefix . 'laterpay_post_views';
        $table_time_passes   = $wpdb->prefix . 'laterpay_passes';
        $table_subscriptions = $wpdb->prefix . 'laterpay_subscriptions';

        // remove custom tables
        $sql = "
        DROP TABLE IF EXISTS
            $table_terms_price,
            $table_history,
            $table_post_views,
            $table_time_passes,
            $table_subscriptions
        ;
        ";
        $wpdb->query( $sql );

        $table_usermeta = $wpdb->usermeta;

        // remove user settings from wp_usermeta table
        $sql = "DELETE FROM $table_usermeta WHERE meta_key IN (
                'laterpay_preview_post_as_visitor',
                'laterpay_hide_preview_mode_pane'
        );";
        $wpdb->query( $sql );

        // remove custom capabilities
        LaterPay_Helper_User::remove_custom_capabilities();

        // remove all dismissed LaterPay pointers
        // delete_user_meta can't remove these pointers without damaging other data
        $pointers = LaterPay_Controller_Admin::get_all_pointers();

        if ( ! empty( $pointers ) && is_array( $pointers ) ) {
            $replace_string = 'meta_value';

            foreach ( $pointers as $pointer ) {
                // we need to use prefix ',' before pointer names to remove them properly from string
                $replace_string = "REPLACE($replace_string, ',$pointer', '')";
            }

            $sql = "
            UPDATE
                $table_usermeta
            SET
                meta_value = $replace_string
            WHERE
                meta_key = 'dismissed_wp_pointers'
            ;
        ";

            $wpdb->query( $sql );
        }
    }

    /**
     * Erase plugin data on plugin disable.
     */
    public static function erase_plugin_data() {

        if ( ! laterpay_check_is_vip() ) {
            self::erase_custom_data();
        }

        // remove pricing and voting data from wp_postmeta table
        delete_post_meta_by_key( 'laterpay_post_prices' );
        delete_post_meta_by_key( 'laterpay_post_teaser' );
        delete_post_meta_by_key( 'laterpay_rating' );
        delete_post_meta_by_key( 'laterpay_users_voted' );

        // remove global settings from wp_options table
        delete_option( 'laterpay_live_backend_api_url' );
        delete_option( 'laterpay_live_dialog_api_url' );
        delete_option( 'laterpay_api_merchant_backend_url' );
        delete_option( 'laterpay_sandbox_backend_api_url' );
        delete_option( 'laterpay_sandbox_dialog_api_url' );

        delete_option( 'laterpay_sandbox_api_key' );
        delete_option( 'laterpay_sandbox_merchant_id' );
        delete_option( 'laterpay_live_api_key' );
        delete_option( 'laterpay_live_merchant_id' );
        delete_option( 'laterpay_plugin_is_in_live_mode' );
        delete_option( 'laterpay_is_in_visible_test_mode' );

        delete_option( 'laterpay_enabled_post_types' );

        delete_option( 'laterpay_currency' );
        delete_option( 'laterpay_global_price' );
        delete_option( 'laterpay_global_price_revenue_model' );

        delete_option( 'laterpay_access_logging_enabled' );

        delete_option( 'laterpay_caching_compatibility' );

        delete_option( 'laterpay_teaser_mode' );

        delete_option( 'laterpay_teaser_content_word_count' );

        delete_option( 'laterpay_preview_excerpt_percentage_of_content' );
        delete_option( 'laterpay_preview_excerpt_word_count_min' );
        delete_option( 'laterpay_preview_excerpt_word_count_max' );

        delete_option( 'laterpay_unlimited_access' );

        delete_option( 'laterpay_voucher_codes' );
        delete_option( 'laterpay_subscription_voucher_codes' );
        delete_option( 'laterpay_gift_codes' );
        delete_option( 'laterpay_voucher_statistic' );
        delete_option( 'laterpay_gift_statistic' );
        delete_option( 'laterpay_gift_codes_usages' );
        delete_option( 'laterpay_debugger_enabled' );
        delete_option( 'laterpay_debugger_addresses' );

        delete_option( 'laterpay_purchase_button_positioned_manually' );
        delete_option( 'laterpay_time_passes_positioned_manually' );

        delete_option( 'laterpay_only_time_pass_purchases_allowed' );

        delete_option( 'laterpay_maximum_redemptions_per_gift_code' );

        delete_option( 'laterpay_api_fallback_behavior' );
        delete_option( 'laterpay_api_enabled_on_homepage' );

        delete_option( 'laterpay_main_color' );
        delete_option( 'laterpay_hover_color' );
        delete_option( 'laterpay_require_login' );
        delete_option( 'laterpay_region' );
        delete_option( 'laterpay_plugin_version' );

        // Delete Post Price Display Behaviour Option.
        delete_option( 'laterpay_post_price_behaviour' );

        // Delete laterpay migrated option.
        delete_option( 'laterpay_data_migrated_to_cpt' );

        // Get all terms having meta key _lp_price.
        $args = [
            'hide_empty' => false, // also retrieve terms which are not used yet
            'meta_query' => [ // WPCS: slow query ok.
                [
                    'key'     => '_lp_price',
                    'compare' => '='
                ]
            ]
        ];

        $terms = get_terms( 'category', $args );

        if ( ! empty( $terms ) ) {

            // Delete all termmeta added by LaterPay.
            foreach ( $terms as $term ) {

                delete_term_meta( $term->term_id, '_lp_price' );
                delete_term_meta( $term->term_id, '_lp_revenue_model' );
            }

        }

        // Get all timepasses and subscriptions.
        $args = [
            'post_type'      => [ 'lp_passes', 'lp_subscription' ],
            'posts_per_page' => 300,
            'no_found_rows'  => true,
            'post_status'    => [ 'publish', 'draft' ],
        ];

        $query = new WP_Query ( $args );

        while ( $query->have_posts() ) {

            // Get Post Data and delete it.
            $query->the_post();
            $id = get_the_ID();
            wp_delete_post( $id, true );
        }

        wp_reset_postdata();
    }
}
