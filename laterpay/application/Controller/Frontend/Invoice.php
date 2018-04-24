<?php

/**
 * LaterPay invoice controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Frontend_Invoice extends LaterPay_Controller_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_invoice_indicator' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'the_invoice_indicator' ),
            ),
            'laterpay_enqueue_scripts' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'add_frontend_scripts' ),
            ),
        );
    }

    /**
     * Callback to generate a LaterPay invoice indicator button within the theme that can be freely positioned.
     *
     *
     * @wp-hook laterpay_invoice_indicator
     * @param LaterPay_Core_Event $event
     * @return void
     */
    public function the_invoice_indicator( LaterPay_Core_Event $event ) {
        $event->set_echo( true );
        $event->set_result( $this->get_text_view( 'frontend/partials/widget/invoice-indicator' ) );

        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-invoice-indicator' );
    }

    /**
     * Load LaterPay Javascript libraries.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_scripts() {
        wp_register_script(
            'laterpay-yui',
            $this->config->get( 'laterpay_yui_js' ),
            array(),
            null,
            false // LaterPay YUI scripts *must* be loaded asynchronously from the HEAD
        );
        wp_register_script(
            'laterpay-invoice-indicator',
            $this->config->get( 'js_url' ) . 'laterpay-invoice-indicator.js',
            null,
            $this->config->get( 'version' ),
            true
        );

        // pass localized strings and variables to script
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $balance_url = $client->get_controls_balance_url();

        wp_localize_script(
            'laterpay-invoice-indicator',
            'lpInvoiceIndicatorVars',
            array(
                'lpBalanceUrl'  => $balance_url,
            )
        );
    }
}
