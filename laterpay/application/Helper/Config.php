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

    /**
     * Get options for LaterPay PHP client
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
