<?php

class LaterPay_Controller_Admin_Account extends LaterPay_Controller_Abstract
{

    /**
     * @see LaterPay_Controller_Abstract::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-account',
            $this->config->js_url . 'laterpay-backend-account.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-backend-account' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-account',
            'lpVars',
            array(
                'i18nApiKeyInvalid'         => __( 'The API key you entered is not a valid LaterPay API key! ', 'laterpay' ),
                'i18nMerchantIdInvalid'     => __( 'The Merchant ID you entered is not a valid LaterPay Merchant ID! ', 'laterpay' ),
                'i18nLiveApiDataRequired'   => __( 'Switching into Live mode requires a valid Live Merchant ID and Live API Key.', 'laterpay' ),
                'i18nPreventUnload'         => __( 'LaterPay does not work properly with invalid API credentials.', 'laterpay' ),
            )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        $this->assign( 'sandbox_merchant_id',    get_option( 'laterpay_sandbox_merchant_id' ) );
        $this->assign( 'sandbox_api_key',        get_option( 'laterpay_sandbox_api_key' ) );
        $this->assign( 'live_merchant_id',       get_option( 'laterpay_live_merchant_id' ) );
        $this->assign( 'live_api_key',           get_option( 'laterpay_live_api_key' ) );
        $this->assign( 'plugin_is_in_live_mode', get_option( 'laterpay_plugin_is_in_live_mode' ) == 1 );
        $this->assign( 'top_nav',                $this->get_menu() );
        $this->assign( 'admin_menu',             LaterPay_Helper_View::get_admin_menu() );

        $this->render( 'backend/account' );
    }

    /**
     * Process Ajax requests from account tab.
     *
     * @return void
     */
    public static function process_ajax_requests() {
        if ( isset( $_POST['form'] ) ) {
            // check for required capabilities to perform action
            if ( ! current_user_can( 'edit_plugins' ) ) {
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => __( "You don't have sufficient user capabilities to do this.", 'laterpay' )
                    )
                );
            }
            if ( function_exists( 'check_admin_referer' ) ) {
                check_admin_referer( 'laterpay_form' );
            }

            switch ( $_POST['form'] ) {
                case 'laterpay_sandbox_merchant_id':
                    self::_update_sandbox_merchant_id();
                    break;

                case 'laterpay_sandbox_api_key':
                    self::_update_sandbox_api_key();
                    break;

                case 'laterpay_live_merchant_id':
                    self::_update_live_merchant_id();
                    break;

                case 'laterpay_live_api_key':
                    self::_update_live_api_key();
                    break;

                case 'laterpay_plugin_mode':
                    self::_update_plugin_mode();
                    break;

                default:
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                    die;
            }
        }
    }

    /**
     * Update LaterPay Sandbox Merchant ID, required for making test transactions against Sandbox environment.
     *
     * @return void
     */
    protected static function _update_sandbox_merchant_id() {
        $sandbox_merchant_id = wp_strip_all_tags( $_POST['laterpay_sandbox_merchant_id'], true );

        if ( self::is_valid_merchant_id( $sandbox_merchant_id ) ) {
            update_option( 'laterpay_sandbox_merchant_id', $sandbox_merchant_id );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'Sandbox Merchant ID verified and saved.', 'laterpay' )
                )
            );
        } elseif ( strlen( $sandbox_merchant_id ) == 0 ) {
            update_option( 'laterpay_sandbox_merchant_id', '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'The Sandbox Merchant ID has been removed.', 'laterpay' )
                )
            );
        } else {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID! ', 'laterpay' )
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Sandbox API Key, required for making test transactions against Sandbox environment.
     *
     * @return void
     */
    protected static function _update_sandbox_api_key() {
        $sandbox_api_key = wp_strip_all_tags( $_POST['laterpay_sandbox_api_key'], true );

        if ( self::is_valid_api_key( $sandbox_api_key ) ) {
            update_option( 'laterpay_sandbox_api_key', $sandbox_api_key );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'Your Sandbox API key is valid. You can now make TEST transactions.', 'laterpay' )
                )
            );
        } elseif ( strlen( $sandbox_api_key ) == 0 ) {
            update_option( 'laterpay_sandbox_api_key', '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'The Sandbox API key has been removed.', 'laterpay' )
                )
            );
        } else {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The API key you entered is not a valid LaterPay Sandbox API key! ', 'laterpay' )
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Live Merchant ID, required for making real transactions against production environment.
     *
     * @return void
     */
    protected static function _update_live_merchant_id() {
        $live_merchant_id = wp_strip_all_tags( $_POST['laterpay_live_merchant_id'], true );

        if ( self::is_valid_merchant_id( $live_merchant_id ) ) {
            update_option( 'laterpay_live_merchant_id', $live_merchant_id );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'Live Merchant ID verified and saved.', 'laterpay' )
                )
            );
        } elseif ( strlen( $live_merchant_id ) == 0 ) {
            update_option( 'laterpay_live_merchant_id', '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'The Live Merchant ID has been removed.', 'laterpay' )
                )
            );
        } else {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The Merchant ID you entered is not a valid LaterPay Live Merchant ID! ', 'laterpay' )
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Live API Key, required for making real transactions against production environment.
     *
     * @return void
     */
    protected static function _update_live_api_key() {
        $live_api_key = wp_strip_all_tags( $_POST['laterpay_live_api_key'], true );

        if ( self::is_valid_api_key( $live_api_key ) ) {
            update_option( 'laterpay_live_api_key', $live_api_key );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'Live API key verified and saved. You can now make REAL transactions.', 'laterpay' )
                )
            );
        } elseif ( strlen( $live_api_key ) == 0 ) {
            update_option( 'laterpay_live_api_key', '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'The Live API key has been removed.', 'laterpay' )
                )
            );
        } else {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The API key you entered is not a valid LaterPay Live API key! ', 'laterpay' )
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay plugin mode (test or live).
     *
     * @return void
     */
    protected static function _update_plugin_mode() {
        $plugin_mode    = absint( $_POST['plugin_is_in_live_mode'] );
        $result         = update_option( 'laterpay_plugin_is_in_live_mode', $plugin_mode );

        if ( $result ) {
            if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
                wp_send_json(
                    array(
                        'success' => true,
                        'message' => __( 'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.', 'laterpay' ),
                    )
                );
            } else {
                wp_send_json(
                    array(
                        'success' => true,
                        'message' => __( 'The LaterPay plugin is in TEST mode now. Payments are only simulated and not actually booked.', 'laterpay' ),
                    )
                );
            }
        } else {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                )
            );
        }
        die;
    }


    /**
     * Validate format of LaterPay Merchant ID (uuid).
     *
     * Format: 22 characters, alphanumeric with upper- and lowercase characters
     * Special feature: our demo LaterPay Merchant ID also contains a hyphen :-)
     *
     * @param string|int $merchant_id
     *
     * @return int
     */
    public static function is_valid_merchant_id( $merchant_id ) {
        return preg_match( '/[a-zA-Z0-9\-]{22}/', $merchant_id );
    }

    /**
     * Validate format of LaterPay API key (shared secret).
     *
     * Format: 32 characters, alphanumeric with only lowercase characters
     *
     * @param string|int $api_key
     *
     * @return int
     */
    public static function is_valid_api_key( $api_key ) {
        return preg_match( '/[a-z0-9]{32}/', $api_key );
    }

}
