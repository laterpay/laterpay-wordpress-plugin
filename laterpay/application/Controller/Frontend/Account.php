<?php

/**
 * LaterPay account controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Frontend_Account extends LaterPay_Controller_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_account_links' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_account_links' ),
            ),
            'laterpay_enqueue_scripts' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'add_frontend_scripts' ),
            ),
        );
    }

    /**
     * Callback to render LaterPay account links by making an API request to /controls/links.
     * (see https://laterpay.net/developers/docs/inpage-api#GET/controls/links)
     *
     * @wp-hook laterpay_account_links
     *
     * @var $show         'show' attribute for the API request as documented in the LaterPay API docs
     * @var $css          'css' attribute for the API request as documented in the LaterPay API docs
     * @var $next         'next' attribute for the API request as documented in the LaterPay API docs
     * @var $forcelang    'forcelang' attribute for the API request as documented in the LaterPay API docs
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function render_account_links( LaterPay_Core_Event $event ) {
        list( $css, $forcelang, $show, $next ) = $event->get_arguments() + array(
            $this->config->get( 'css_url' ) . 'laterpay-account-links.css',
            substr( get_locale(), 0, 2 ),
            'lg',
            is_singular() ? get_permalink() : home_url(),
        );

        // create account links URL with passed parameters
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $links_url = $client->get_account_links_url( $show, $css, $next, $forcelang );
        // get Merchant ID
        $is_live = get_option( 'laterpay_plugin_is_in_live_mode' );
        $merchant_id = $is_live ? get_option( 'laterpay_live_merchant_id' ) : get_option( 'laterpay_sandbox_merchant_id' );

        $view_args = array(
            'links_url'   => $links_url,
            'next'        => urlencode( $next ),
            'merchant_id' => $merchant_id,
        );

        $this->assign( 'laterpay_account', $view_args );

        $event->set_echo( true );
        $event->set_result( laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/account-links' ) ) );
        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-account-links' );
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
            'laterpay-account-links',
            $this->config->get( 'js_url' ) . 'laterpay-account-links.js',
            null,
            $this->config->get( 'version' ),
            true
        );
    }
}
