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

        // Enqueue the contributions script.
        wp_enqueue_script( 'laterpay-backend-contributions' );

        // Get data for GA.
        $merchant_key = LaterPay_Controller_Admin::get_merchant_id_for_ga();

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-contributions',
            'lpVars',
            array(
                'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
                'locale'   => get_locale(),
                'currency' => LaterPay_Helper_Config::get_currency_config(),
                'gaData'   => array( // GA Data value.
                    'sandbox_merchant_id' => ( ! empty( $merchant_key ) ) ? $merchant_key : '',
                ),
                'i18n'     => [ // Info/Warning/Error messages.
                    'contribute'            => esc_html__( 'Contribute', 'laterpay' ),
                    'now'                   => esc_html__( 'now', 'laterpay' ),
                    'nowOrPayLater'         => esc_html__( 'now, pay later', 'laterpay' ),
                    'errorCampaignName'     => esc_html__( 'Please enter a Campaign Name above.', 'laterpay' ),
                    'errorCampaignThanks'   => esc_html__( 'Please enter a valid URL.', 'laterpay' ),
                    'errorCustomAmount'     => esc_html__( 'Custom contribution amounts are only available when Show multiple contribution amounts is enabled.', 'laterpay' ),
                    'errorNoAmount'         => esc_html__( 'Please enter a valid contribution amount above.', 'laterpay' ),
                    'errorNoAmountMultiple' => esc_html__( 'Please enter at least two valid contribution amounts above. If you would like to only allow one amount, simply un-check Show multiple contribution amounts.', 'laterpay' ),
                ],
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
            'plugin_is_in_live_mode' => $this->config->get( 'is_in_live_mode' ),
            'admin_menu'             => LaterPay_Helper_View::get_admin_menu(),
            'contributions_obj'      => $this,
            'live_key'               => get_option( 'laterpay_live_merchant_id', '' ),
            'currency'               => LaterPay_Helper_Config::get_currency_config(),
        );

        // Load the contributions page for Merchant.
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

        // Verify form validity.
        if ( function_exists( 'check_admin_referer' ) ) {
            check_admin_referer( 'laterpay_form' );
        }

        // Handle contribution generation form submission.
        switch ( $submitted_form_value ) {
            case 'single_contribution':

                $single_contribution_form = new LaterPay_Form_ContributionSingle( $_POST ); // phpcs:ignore

                // Response for invalid data.
                $event->set_result(
                    array(
                        'success' => false,
                        'errors'  => $single_contribution_form->get_errors(),
                        'message' => __( 'An error occurred when trying to generate the shortcode. Please try again.', 'laterpay' ),
                    )
                );

                if ( ! $single_contribution_form->is_valid() ) {
                    throw new LaterPay_Core_Exception_FormValidation( get_class( $single_contribution_form ), $single_contribution_form->get_errors() );
                }

                // Skip form name, action and nonce value.
                $form_data = $single_contribution_form->get_form_values( true, null, [ 'form', 'action', '_wpnonce' ] );

                // Generate the shortcode.
                $result  = LaterPay_Controller_Frontend_Shortcode::generator( 'contribution', [
                    'name'           => sanitize_text_field( $form_data['contribution_name'] ),
                    'thank_you'      => esc_url_raw( $form_data['thank_you_page'] ),
                    'type'           => 'single',
                    'single_amount'  => (float) $form_data['single_amount'] * 100,
                    'single_revenue' => sanitize_text_field( $form_data['single_revenue'] ),
                ] );
                $message = __( 'Successfully generated code, please paste at desired location.', 'laterpay' );

                // Send response.
                $event->set_result(
                    [
                        'success' => $result['success'],
                        'message' => true === $result['success'] ? $message : $result['message'],
                        'code'    => isset( $result['code'] ) ? $result['code'] : ''
                    ]
                );
                break;

            case 'multiple_contribution':

                $multiple_contribution_form = new LaterPay_Form_ContributionMultiple( $_POST ); // phpcs:ignore

                // Response for invalid data.
                $event->set_result(
                    array(
                        'success' => false,
                        'errors'  => $multiple_contribution_form->get_errors(),
                        'message' => __( 'An error occurred when trying to generate the shortcode. Please try again.', 'laterpay' ),
                    )
                );

                if ( ! $multiple_contribution_form->is_valid() ) {
                    throw new LaterPay_Core_Exception_FormValidation( get_class( $multiple_contribution_form ), $multiple_contribution_form->get_errors() );
                }

                // Skip form name, action and nonce value.
                $form_data = $multiple_contribution_form->get_form_values( true, null, [
                    'form',
                    'action',
                    '_wpnonce'
                ] );

                // Sanitize the all amounts input.
                $filters = [
                    'price'       => FILTER_SANITIZE_STRING,
                    'revenue'     => FILTER_SANITIZE_STRING,
                    'is_selected' => FILTER_VALIDATE_BOOLEAN,
                ];
                $options = [
                    'price'       => [
                        'flags' => FILTER_NULL_ON_FAILURE
                    ],
                    'revenue'     => [
                        'flags' => FILTER_NULL_ON_FAILURE
                    ],
                    'is_selected' => [
                        'flags' => FILTER_NULL_ON_FAILURE
                    ],
                ];

                // Get all amounts.
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each value is sanitized below.
                $all_amounts     = ! empty( $_POST['all_amounts'] ) ? json_decode( wp_unslash( $_POST['all_amounts'] ), true ) : [];
                $filtered_prices = [];

                $selected_amount = 1;
                // Loop through the user input an build an array to be processed by shortcode generator.
                foreach ( $all_amounts as $id => $amount_array ) {
                    foreach ( $amount_array as $key => $value ) {
                        $filtered_prices[$id][ $key ] = filter_var( $value, $filters[ $key ], $options[ $key ] );
                        if ( true === $amount_array['is_selected'] ) {
                            $selected_amount = $id + 1;
                        }
                    }
                }

                $dialog_header      = filter_input( INPUT_POST, 'dialog_header', FILTER_SANITIZE_STRING );
                $dialog_description = filter_input( INPUT_POST, 'dialog_description', FILTER_SANITIZE_STRING );

                // Generate the shortcode.
                $shortcode_data = [
                    'name'            => sanitize_text_field( $form_data['contribution_name'] ),
                    'thank_you'       => esc_url_raw( $form_data['thank_you_page'] ),
                    'type'            => 'multiple',
                    'custom_amount'   => isset ( $_POST['custom_amount'] ) ? (float) $form_data['custom_amount'] * 100 : 'none',
                    'all_amounts'     => array_column( $filtered_prices, 'price' ),
                    'all_revenues'    => array_column( $filtered_prices, 'revenue' ),
                    'selected_amount' => $selected_amount,
                ];

                if ( ! empty( $dialog_header ) ) {
                    $shortcode_data['dialog_header'] = wp_strip_all_tags( $dialog_header );
                }

                if ( ! empty( $dialog_description ) ) {
                    $shortcode_data['dialog_description'] = wp_strip_all_tags( $dialog_description );
                }

                $result = LaterPay_Controller_Frontend_Shortcode::generator( 'contribution', $shortcode_data );
                $message = __( 'Successfully generated code, please paste at desired location.', 'laterpay' );

                // Send response.
                $event->set_result(
                    [
                        'success' => $result['success'],
                        'message' => true === $result['success'] ? $message : $result['message'],
                        'code'    => isset( $result['code'] ) ? $result['code'] : ''
                    ]
                );
                break;

            default:
                break;
        }
    }
}
