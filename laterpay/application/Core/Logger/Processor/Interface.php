<?php
/**
 * LaterPay Core Logger Processor Interface.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://laterpay.net/developers/plugins-and-libraries
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
