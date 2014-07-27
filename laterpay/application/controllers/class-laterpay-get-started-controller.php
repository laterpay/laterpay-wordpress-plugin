<?php

class LaterPay_Get_Started_Controller extends LaterPay_Abstract_Controller
{

    public function load_assets() {
        parent::load_assets();
        global $laterpay_version;

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-getstarted',
            LATERPAY_ASSETS_PATH . '/js/laterpay-backend-getStarted.js',
            array( 'jquery' ),
            $laterpay_version,
            true
        );
        wp_enqueue_script( 'laterpay-backend-getstarted' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-getstarted',
            'lpVars',
            array(
                'locale'                        => get_locale(),
                'i18nOutsideAllowedPriceRange'  => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' ),
                'i18nInvalidMerchantId'         => __( 'The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID! ', 'laterpay' ),
                'i18nInvalidApiKey'             => __( 'The API key you entered is not a valid LaterPay Sandbox API key! ', 'laterpay' ),
            )
        );
    }

    /**
     * Render HTML for and assign currency to get started tab
     *
     * @access public
     */
    public function render_page() {
        $this->load_assets();

        $Currencies = new LaterPay_Currency_Model();

        $this->assign( 'global_default_price',   LaterPay_View_Helper::format_number( (float) LATERPAY_GLOBAL_PRICE_DEFAULT, 2 ) );
        $this->assign( 'Currencies',             $Currencies );
        $this->assign( 'top_nav',                $this->get_menu() );

        $this->render( 'plugin-backend-get-started-tab' );
    }

    /**
     * Process Ajax requests from get started tab
     *
     * @access public
     */
    public static function process_ajax_requests() {
        if ( isset( $_POST['get_started'] ) ) {
            // check for required privileges to perform action
            if ( ! LaterPay_User_Helper::can( 'laterpay_edit_plugin_settings' ) ) {
                echo Zend_Json::encode( array( 'success' => false ) );
                die;
            }

            if ( function_exists('check_admin_referer') ) {
                check_admin_referer( 'laterpay_form' );
            }

            // save initial settings
            update_option( 'laterpay_sandbox_api_key',      $_POST['get_started']['laterpay_sandbox_api_key'] );
            update_option( 'laterpay_sandbox_merchant_id',  $_POST['get_started']['laterpay_sandbox_merchant_id'] );
            update_option( 'laterpay_global_price',         str_replace( ',', '.', $_POST['get_started']['laterpay_global_price'] ) );
            update_option( 'laterpay_currency',             $_POST['get_started']['laterpay_currency'] );
            update_option( 'laterpay_plugin_is_activated',  '1' );

            // automatically dismiss pointer to LaterPay plugin after saving the initial settings
            $current_user_id    = get_current_user_id();
            $dismissed_pointers = explode( ',', (string) get_user_meta( $current_user_id, 'dismissed_wp_pointers', true ) );

            if ( ! in_array( LaterPay_Admin_Controller::ADMIN_MENU_POINTER, $dismissed_pointers ) ) {
                update_user_meta( $current_user_id, 'dismissed_wp_pointers', LaterPay_Admin_Controller::ADMIN_MENU_POINTER );
            }

            echo Zend_Json::encode( array( 'success' => true ) );
            die;
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                )
            );
        }
    }

}
