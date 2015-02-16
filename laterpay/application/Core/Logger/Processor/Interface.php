<?php

/**
 * LaterPay core logger processor interface.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
interface LaterPay_Core_Logger_Processor_Interface
{

    /**
    * @param  array $record
    *
    * @return array $record
    */
    public function process( array $record );

}
