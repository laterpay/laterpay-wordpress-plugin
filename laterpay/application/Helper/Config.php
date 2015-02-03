<?php

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
                self::$options['api_root'] = $config->get( 'api.live_url' );
                self::$options['web_root'] = $config->get( 'api.live_web_url' );
            } else {
                self::$options['cp_key']   = get_option( 'laterpay_sandbox_merchant_id' );
                self::$options['api_key']  = get_option( 'laterpay_sandbox_api_key' );
                self::$options['api_root'] = $config->get( 'api.sandbox_url' );
                self::$options['web_root'] = $config->get( 'api.sandbox_web_url' );
            }

            self::$options['token_name'] = $config->get( 'api.token_name' );
        }

        return self::$options;
    }
}
