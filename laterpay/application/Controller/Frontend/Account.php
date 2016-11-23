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
                array( 'is_page_secure', 100 ),
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
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        // add iframe placeholder
        $event->set_echo( true );
        $event->set_result( laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/account-links' ) ) );

        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-account-links' );

        wp_localize_script(
            'laterpay-account-links',
            'lpVars',
            array(
                'iframeLink' => $client->get_account_links( $show, $css, $next, $forcelang ),
                'loginLink'  => $client->get_login_dialog_url( $next ),
                'logoutLink' => $client->get_logout_dialog_url( $next, true ),
                'signupLink' => $client->get_signup_dialog_url( $next ),
            )
        );
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
            'laterpay-account-links',
            $this->config->get( 'js_url' ) . 'laterpay-account-links.js',
            null,
            $this->config->get( 'version' ),
            true
        );
    }

    public function is_page_secure( LaterPay_Core_Event $event ) {
        if ( ! is_ssl() ) {
            $event->stop_propagation();
        }
    }
}
