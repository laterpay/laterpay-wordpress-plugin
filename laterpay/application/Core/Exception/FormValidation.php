<?php

/**
 * LaterPay form validation exception.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Exception_FormValidation extends LaterPay_Core_Exception
{
    public function __construct( $message = '' ) {
        if ( ! $message ) {
            $message = __( 'Form data are invalid', 'laterpay' );
        }
        parent::__construct( $message );
    }
}
