<?php

class LaterPay_Controller_Admin_Notice extends LaterPay_Controller_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
           'laterpay_admin_notices' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'render_wpengine_notice' ),
            ),
            'wp_ajax_laterpay_save_wpengine_status' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_save_wpengine_notice_status', 400 ),
            ),
           'wp_ajax_laterpay_reset_highlights_data' => array(
               array( 'laterpay_on_admin_view', 200 ),
               array( 'laterpay_on_ajax_send_json', 300 ),
               array( 'ajax_reset_highlights_data', 400 ),
           ),
           'wp_ajax_laterpay_reset_notice_data' => array(
               array( 'laterpay_on_admin_view', 200 ),
               array( 'laterpay_on_ajax_send_json', 300 ),
               array( 'ajax_reset_notice_data', 400 ),
           ),
           'wp_ajax_laterpay_read_tabular_instructions' => array(
               array( 'laterpay_on_admin_view', 200 ),
               array( 'laterpay_on_ajax_send_json', 300 ),
               array( 'ajax_read_tabular_instructions', 400 ),
           ),
        );
    }

    /**
     * Gives warning if hosted env is on wpengine.
     */
    function render_wpengine_notice() {

        // Checks if env is wpengine server
        if ( ! function_exists( 'is_wpe' ) || ! is_wpe() ) {
            return;
        }

        $wp_notice_status = get_option( 'laterpay_wpengine_notice_status' );

        if ( ( ! empty( $wp_notice_status ) ||  '1' === $wp_notice_status ) ) {
            return;
        }

        printf( '<div id="lp_wpengine_notice" class="notice notice-error"> <p>%s ( <b> %s </b> ) %s <a id="wpengn_done_btn" class="lp_wpengn_nbtn" > %s </a> </p> </div>',
            esc_html__( 'Please contact WPEngine customer service to bypass the required cookies', 'laterpay' ),
            esc_html__( 'laterpay_token, laterpay_purchased_gift_card and laterpay_tracking_code', 'laterpay' ),
            esc_html__( 'in order for the plugin to work properly with page-cache.', 'laterpay' ),
            esc_html__( 'Done', 'laterpay' ) );

        // load page-specific JS
        wp_register_script(
            'laterpay-wpengine-notice',
            $this->config->js_url . '/laterpay-wpengine-notice.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );

        $nonce = wp_create_nonce( 'wpengine_cookie_done_nonce' ) ;

        wp_localize_script( 'laterpay-wpengine-notice', 'wpengine_cookie_done_nonce', $nonce );

        wp_enqueue_script( 'laterpay-wpengine-notice' );

        wp_localize_script(
            'laterpay-wpengine-notice',
            'lp_i18n',
            array(
                'SaveWpNoticeData'    => esc_html( esc_js( __( 'Saving Settings '  ,'laterpay' ) ) ),
                'SavedWpNoticeData'   => esc_html( esc_js( __( 'Settings Saved '   ,'laterpay' ) ) ),
                'UnSavedWpNoticeData' => esc_html( esc_js( __( 'Error saving data' ,'laterpay' ) ) ),
            )
        );
    }

    /**
     *  Saves Wp engine notice status.
     *
     * @param LaterPay_Core_Event $event
     */
    function ajax_save_wpengine_notice_status( LaterPay_Core_Event $event ) {

        $result = [];
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer('wpengine_cookie_done_nonce', 'security' );

        $result ['status'] = false;
        $status = filter_input( INPUT_POST, 'status', FILTER_SANITIZE_STRING );

        if ( 'true' === $status ) {

            $result['status'] = update_option( 'laterpay_wpengine_notice_status', '1' );
        }

        $event->set_result( $result );
    }

    /**
     * Update update_highlights_data option.
     *
     * @wp-hook wp_ajax_laterpay_reset_highlights_data
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    function ajax_reset_highlights_data( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer('update_highlights_nonce', 'security' );

        update_option( 'lp_update_highlights', [] );

        $event->set_result(
            array(
                'success' => true,
            )
        );

        return;
    }

    /**
     * Update update_highlights_data option.
     *
     * @wp-hook wp_ajax_laterpay_reset_highlights_data
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    function ajax_reset_notice_data( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'reset_cache_nonce', 'security' );

        update_option( 'laterpay_show_cache_msg', 0 );

        $event->set_result(
            array(
                'success' => true,
            )
        );

        return;
    }

    /**
     * Update update_highlights_data option.
     *
     * @wp-hook wp_ajax_laterpay_read_tabular_instructions
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    function ajax_read_tabular_instructions( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'read_tabular_info_nonce', 'security' );

        $current_info_status = get_option( 'lp_tabular_info', [] );

        $dismissed_page = filter_input( INPUT_POST, 'lppage', FILTER_SANITIZE_STRING );

        if ( ! empty( $dismissed_page ) && ! empty( $current_info_status[ $dismissed_page ] ) ) {
            $current_info_status[ $dismissed_page ] = 0;
            update_option( 'lp_tabular_info', $current_info_status );
        }

        $event->set_result(
            array(
                'success' => true,
            )
        );

        return;
    }
}
