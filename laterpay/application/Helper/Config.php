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
                'token_name'              => 'token',
                'sandbox_merchant_id'     => '984df2b86250447793241a',
                'sandbox_api_key'         => '57791c777baa4cea94c4ec074184e06d',
            ),
            'currency' => array(
                'default'                 => 'EUR',
                'default_price'           => 0.29,
                'ppu_min'                 => 0.05,
                'ppu_only_limit'          => 1.48,
                'ppu_max'                 => 5.00,
                'sis_min'                 => 1.49,
                'sis_only_limit'          => 5.01,
                'sis_max'                 => 149.99,
                'dynamic_start'           => 13,
                'dynamic_end'             => 18,
            )
        ),
        'us' => array(
            'api' => array(
                'sandbox_backend_api_url' => 'https://api.sandbox.uselaterpaytest.com',
                'sandbox_dialog_api_url'  => 'https://web.sandbox.uselaterpaytest.com',
                'live_backend_api_url'    => 'https://api.uselaterpay.com',
                'live_dialog_api_url'     => 'https://web.uselaterpay.com',
                'merchant_backend_url'    => 'https://web.uselaterpay.com/merchant',
                'token_name'              => 'token',
                'sandbox_merchant_id'     => 'xswcBCpR6Vk6jTPw8si7KN',
                'sandbox_api_key'         => '22627fa7cbce45d394a8718fd9727731',
            ),
            'currency' => array(
                'default'                 => 'USD',
                'default_price'           => 0.29,
                'ppu_min'                 => 0.05,
                'ppu_only_limit'          => 1.48,
                'ppu_max'                 => 5.00,
                'sis_min'                 => 2.99,
                'sis_only_limit'          => 5.01,
                'sis_max'                 => 149.99,
                'dynamic_start'           => 13,
                'dynamic_end'             => 18,
            )
        )
    );

    /**
     * Get regional settings
     *
     * @return array|null
     */
    public static function get_regional_settings( $one_dimension = true ) {
        $region = get_option( 'laterpay_region', 'eu' );

        // region correction
        if ( ! isset( self::$regional_settings[ $region ] ) ) {
            update_option( 'laterpay_region', 'eu' );
            $region = 'eu';
        }

        // get all settings
        $settings = self::$regional_settings[ $region ];

        // convert to 1 dimensional array for config
        if ( $one_dimension ) {
            // temporal 1 dimensional array
            $temp = array();

            // build 1 dim array
            foreach ( $settings as $parent_key => $options ) {
                foreach ( $options as $key => $value ) {
                    $temp_key = $parent_key . '.' . $key;
                    $temp[ $temp_key ] = $value;
                }
            }

            $settings = $temp;
        }

        return $settings;
    }

    /**
     * Get settings section for current region
     *
     * @param $section
     *
     * @return array|null
     */
    public static function get_section( $section ) {
        // get unformatted regional settings
        $settings = self::get_regional_settings( false );

        return isset( $settings[ $section ] ) ? $settings[ $section ] : null;
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
            foreach ( self::$regional_settings as $region => $settings ) {
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
}
