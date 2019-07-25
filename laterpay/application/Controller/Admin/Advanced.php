<?php

/**
 * LaterPay advanced controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Advanced extends LaterPay_Controller_Admin_Base {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_laterpay_advanced' => array(
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

        LaterPay_Controller_Admin::register_common_scripts( 'advanced' );

        // Add thickbox to display modal.
        add_thickbox();

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-advanced',
            $this->config->js_url . 'laterpay-backend-advanced.js',
            array( 'jquery', 'laterpay-common' ),
            $this->config->version,
            true
        );

        wp_enqueue_script( 'laterpay-backend-advanced' );

        $nonce        = wp_create_nonce( 'plugin_disable_nonce' );
        $wisdom_nonce = wp_create_nonce( 'wisdom_goodbye_form' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-advanced',
            'lpVars',
            array(
                'region'               => get_option( 'laterpay_region', 'us' ),
                'liveKeyAvailable'     => empty( get_option( 'laterpay_live_merchant_id', '' ) ) ? 'false' : 'true',
                'plugin_disable_nonce' => $nonce,
                'wisdom_survey_nonce'  => $wisdom_nonce,
                'modal'                => array(
                    'id'    => 'lp_plugin_disable_modal_id',
                    'title' => ( laterpay_check_is_vip() ) ? esc_html__( 'Delete Plugin Data', 'laterpay' ) : esc_html__( 'Deactivate Plugin & Delete Data', 'laterpay' ),
                ),
                'pluginsUrl'           => admin_url( 'plugins.php' ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        // get current tracking status.
        $lp_wisdom_data         = get_option( 'lp_wisdom_tracking_info' );
        $wisdom_tracking_info   = get_option( 'wisdom_allow_tracking' );
        $lp_wisdom_data_enabled = 0;

        if ( false !== $wisdom_tracking_info ) {
            $lp_wisdom_data_enabled = isset( $wisdom_tracking_info['laterpay'] ) ? 1 : 0;
            // get current wisdom option value and update accordingly.
            if ( 1 === $lp_wisdom_data_enabled ) {
                $lp_wisdom_data_enabled           = 1;
                $lp_wisdom_data['wisdom_opt_out'] = 0;
            } else {
                $lp_wisdom_data_enabled           = 0;
                unset( $lp_wisdom_data['wisdom_opt_out'] );
            }
            update_option( 'lp_wisdom_tracking_info', $lp_wisdom_data );
        }

        // View data for laterpay/views/backend/advanced.php.
        $view_args = array(
            'plugin_is_in_live_mode'     => $this->config->get( 'is_in_live_mode' ),
            'is_wisdom_tracking_allowed' => $lp_wisdom_data_enabled,
            'admin_menu'                 => LaterPay_Helper_View::get_admin_menu(),
            'advanced_obj'               => $this,
            'live_key'                   => get_option( 'laterpay_live_merchant_id', '' ),
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( 'backend/advanced' );
    }

    /**
     * Process Ajax requests from advanced tab.
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

            case 'laterpay_wisdom_optinout':
                self::change_tracking_status( $event );
                break;

            default:
                break;
        }
    }

    /**
     * Toggle LaterPay tracking
     *
     * @return void
     * @throws LaterPay_Core_Exception_FormValidation
     *
     */
    protected static function change_tracking_status( LaterPay_Core_Event $event ) {
        $plugin_tracking_mode_form = new LaterPay_Form_TrackingMode();

        if ( ! $plugin_tracking_mode_form->is_valid( $_POST ) ) { // phpcs:ignore
            array(
                'success' => false,
                'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $plugin_tracking_mode_form ), $plugin_tracking_mode_form->get_errors() );
        }

        $plugin_tracking_mode = $plugin_tracking_mode_form->get_field_value( 'is_wisdom_tracking_allowed' );

        // get current option setting value and disable tracking.
        $lp_wisdom_info                   = get_option( 'lp_wisdom_tracking_info' );
        $lp_wisdom_info['wisdom_opt_out'] = $plugin_tracking_mode;

        // unset plugin info from wisdom tracking if disallowed.
        if ( 0 === $plugin_tracking_mode ) {
            unset( $lp_wisdom_info['wisdom_opt_out'] );
            self::unset_wisdom_tracking_info();
        } else {
            $wisdom_tracking_info = get_option( 'wisdom_allow_tracking' );
            if ( false !== $wisdom_tracking_info ) {
                if ( ! isset( $wisdom_tracking_info['laterpay'] ) ) {
                    $wisdom_tracking_info['laterpay'] = 'laterpay';
                }
            } else {
                $wisdom_tracking_info = [
                    'laterpay' => 'laterpay',
                ];
            }
            update_option( 'wisdom_allow_tracking', $wisdom_tracking_info );
            laterpay_start_plugin_tracking()->do_tracking( true );
        }

        $result = update_option( 'lp_wisdom_tracking_info', $lp_wisdom_info );

        if ( $result ) {
            $event->set_result(
                array(
                    'success' => true,
                    'message' => __( 'Updated LaterPay tracking mode.', 'laterpay' ),
                )
            );

            return;
        }
    }

    /**
     * Unset laterpay wisdom tracking options.
     */
    public static function unset_wisdom_tracking_info() {
        // all wisdom tracking options.
        $wisdom_tracking_options = [
            'wisdom_notification_times',
            'wisdom_allow_tracking',
            'wisdom_block_notice',
            'wisdom_admin_emails',
            'wisdom_last_track_time',
        ];

        // loop through each option and unset laterpay info.
        foreach ( $wisdom_tracking_options as $wisdom_option ) {
            $current_wisdom_option = get_option( $wisdom_option );
            if ( false !== $current_wisdom_option ) {
                if ( isset( $current_wisdom_option['laterpay'] ) ) {
                    unset( $current_wisdom_option['laterpay'] );
                    update_option( $wisdom_option, $current_wisdom_option );
                }
            }
        }
    }
}
