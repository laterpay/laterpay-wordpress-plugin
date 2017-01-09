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
            'api.sandbox_backend_api_url' => 'https://api.sandbox.laterpaytest.net',
            'api.sandbox_dialog_api_url'  => 'https://web.sandbox.laterpaytest.net',
            'api.live_backend_api_url'    => 'https://api.laterpay.net',
            'api.live_dialog_api_url'     => 'https://web.laterpay.net',
            'api.merchant_backend_url'    => 'https://merchant.laterpay.net/',
            'api.token_name'              => 'token',
            'api.sandbox_merchant_id'     => '984df2b86250447793241a',
            'api.sandbox_api_key'         => '57791c777baa4cea94c4ec074184e06d',
            'currency.default'            => 'EUR',
            'currency.default_price'      => 0.29,
        ),
        'us' => array(
            'api.sandbox_backend_api_url' => 'https://api.sandbox.uselaterpaytest.com',
            'api.sandbox_dialog_api_url'  => 'https://web.sandbox.uselaterpaytest.com',
            'api.live_backend_api_url'    => 'https://api.uselaterpay.com',
            'api.live_dialog_api_url'     => 'https://web.uselaterpay.com',
            'api.merchant_backend_url'    => 'https://merchant.laterpay.net/',
            'api.token_name'              => 'token',
            'api.sandbox_merchant_id'     => '984df2b86250447793241a',
            'api.sandbox_api_key'         => '57791c777baa4cea94c4ec074184e06d',
            'currency.default'            => 'USD',
            'currency.default_price'      => 0.29,
        )
    );

    /**
     * Get regional settings
     *
     * @return array|null
     */
    public static function get_regional_settings() {
        $region = get_option( 'laterpay_region', 'eu' );

        // region correction
        if ( ! isset( self::$regional_settings[ $region ] ) ) {
            update_option( 'laterpay_region', 'eu' );
            $region = 'eu';
        }

        return self::$regional_settings[ $region ];
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
}
