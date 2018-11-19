<?php

/**
 * LaterPay option helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Option {

    /**
     * Update LaterPay Option.
     *
     * @param string $option_name  Option Name.
     * @param mixed  $option_value Option Value.
     *
     * @return bool
     */
    public static function update_laterpay_option( $option_name, $option_value ) {
        return update_option( $option_name, $option_value );
    }

}
