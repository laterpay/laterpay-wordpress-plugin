<?php

/**
 * LaterPay contributions controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Contributions extends LaterPay_Controller_Admin_Base {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_laterpay_contributions' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'process_ajax_requests' ),
                array( 'laterpay_on_ajax_user_can_activate_plugins', 200 ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        LaterPay_Controller_Admin::register_common_scripts( 'contributions' );

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-contributions',
            $this->config->js_url . 'laterpay-backend-contributions.js',
            array( 'jquery', 'laterpay-common' ),
            $this->config->version,
            true
        );

        wp_enqueue_script( 'laterpay-backend-contributions' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-contributions',
            'lpVars',
            array(
                'region' => get_option( 'laterpay_region', 'us' ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        // View data for laterpay/views/backend/contributions.php.
        $view_args = array(
            'plugin_is_in_live_mode'     => $this->config->get( 'is_in_live_mode' ),
            'admin_menu'                 => LaterPay_Helper_View::get_admin_menu(),
            'contributions_obj'          => $this,
            'live_key'                   => get_option( 'laterpay_live_merchant_id', '' ),
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( 'backend/contributions' );
    }

    /**
     * Process Ajax requests from contributions tab.
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     *
     */
    public static function process_ajax_requests( LaterPay_Core_Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        $submitted_form_value = filter_input( INPUT_POST, 'form', FILTER_SANITIZE_STRING );
        if ( null === $submitted_form_value ) {
            // invalid request
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'form' );
        }

        if ( function_exists( 'check_admin_referer' ) ) {
            check_admin_referer( 'laterpay_form' );
        }

        switch ( $submitted_form_value ) {
            default:
                break;
        }
    }



}
