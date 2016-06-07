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
            )
        );
    }

    /**
     * Callback to generate a LaterPay invoice indicator button within the theme that can be freely positioned.
     *
     * @wp-hook laterpay_invoice_indicator
     * @param LaterPay_Core_Event $event
     * @return void
     */
    public function the_invoice_indicator( LaterPay_Core_Event $event ) {

        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $view_args = array(
            'balance_url' => $client->get_controls_balance_url(),
        );

        $this->assign( 'laterpay_invoice', $view_args );

        $event->set_echo( true );
        $event->set_result( laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/invoice-indicator' ) ) );
    }
}
