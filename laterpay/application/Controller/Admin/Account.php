<?php

/**
 * LaterPay account controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Account extends LaterPay_Controller_Admin_Base {
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_laterpay_account' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'process_ajax_requests' ),
                array( 'laterpay_on_ajax_user_can_activate_plugins', 200 ),
            ),
            'wp_ajax_laterpay_disable_plugin' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'disable_plugin', 400 ),
            ),
            'wp_ajax_laterpay_validate_cred_region' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_validate_cred_region', 400 ),
            ),
        );
    }

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // Get data for GA.
        $merchant_key = LaterPay_Controller_Admin::get_merchant_id_for_ga();
        $site_url     = get_site_url();

        LaterPay_Controller_Admin::register_common_scripts( 'account' );

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-account',
            $this->config->js_url . 'laterpay-backend-account.js',
            array( 'jquery', 'laterpay-common' ),
            $this->config->version,
            true
        );

        wp_enqueue_script( 'laterpay-backend-account' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-account',
            'lpVars',
            array(
                'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
                'i18nApiKeyInvalid'     => __( 'The API key you entered is not a valid LaterPay API key!', 'laterpay' ),
                'i18nMerchantIdInvalid' => __( 'The Merchant ID you entered is not a valid LaterPay Merchant ID!', 'laterpay' ),
                'i18nPreventUnload'     => __( 'LaterPay does not work properly with invalid API credentials.', 'laterpay' ),
                'gaData'                => array(
                    'sandbox_merchant_id' => ( ! empty( $merchant_key ) ) ? esc_js( $merchant_key ) : '',
                    'site_url'            => ( ! empty( $site_url ) ) ? esc_url( $site_url ) : '',
                ),
                'reset_cache_nonce'     => wp_create_nonce( 'reset_cache_nonce' ),
                'validate_cred_nonce'   => wp_create_nonce( 'validate_cred_nonce' ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $view_args = array(
            'sandbox_merchant_id'               => get_option( 'laterpay_sandbox_merchant_id' ),
            'sandbox_api_key'                   => get_option( 'laterpay_sandbox_api_key' ),
            'live_merchant_id'                  => get_option( 'laterpay_live_merchant_id' ),
            'live_api_key'                      => get_option( 'laterpay_live_api_key' ),
            'region'                            => get_option( 'laterpay_region' ),
            'credentials_url_eu'                => 'https://web.laterpay.net/dialog/entry/?redirect_to=/merchant/add#/signup',
            'credentials_url_us'                => 'https://web.uselaterpay.com/dialog/entry/?redirect_to=/merchant/add#/signup',
            'plugin_is_in_live_mode'            => $this->config->get( 'is_in_live_mode' ),
            'account_obj'                       => $this,
            'admin_menu'                        => LaterPay_Helper_View::get_admin_menu(),
        );

        if ( false === get_option( 'laterpay_show_cache_msg' ) ) {
            update_option( 'laterpay_show_cache_msg', 0 );
        }

        $this->assign( 'laterpay', $view_args );

        $this->render( 'backend/account' );
    }

    /**
     * Process Ajax requests from account tab.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     *
     * @return void
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
            case 'laterpay_sandbox_merchant_id':
                $event->set_argument( 'is_live', false );
                self::update_merchant_id( $event );
                break;

            case 'laterpay_sandbox_api_key':
                $event->set_argument( 'is_live', false );
                self::update_api_key( $event );
                break;

            case 'laterpay_live_merchant_id':
                $event->set_argument( 'is_live', true );
                self::update_merchant_id( $event );
                break;

            case 'laterpay_live_api_key':
                $event->set_argument( 'is_live', true );
                self::update_api_key( $event );
                break;

            case 'laterpay_plugin_mode':
                self::update_plugin_mode( $event );
                break;

            case 'laterpay_region_change':
                self::change_region( $event );
                break;

            default:
                break;
        }
    }

    /**
     * Update LaterPay Merchant ID, required for making test transactions against Sandbox or Live environments.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected static function update_merchant_id( LaterPay_Core_Event $event ) {
        $is_live = null;
        if ( $event->has_argument( 'is_live' ) ) {
            $is_live = $event->get_argument( 'is_live' );
        }
        $merchant_id_form = new LaterPay_Form_MerchantId( $_POST ); // phpcs:ignore
        $merchant_id      = $merchant_id_form->get_field_value( 'merchant_id' );
        $merchant_id_type = $is_live ? 'live' : 'sandbox';

        if ( ! $merchant_id_form->is_valid( $_POST ) ) { // phpcs:ignore
            $event->set_result(
                array(
                    'success' => false,
                    'message' => sprintf(
                        __( 'The Merchant ID you entered is not a valid LaterPay %s Merchant ID!', 'laterpay' ),
                        ucfirst( $merchant_id_type )
                    ),
                )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $merchant_id_form ), $merchant_id_form->get_errors() );
        }

        if ( strlen( $merchant_id ) === 0 ) {
            update_option( sprintf( 'laterpay_%s_merchant_id', $merchant_id_type ), '' );
            $event->set_result(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( 'The %s Merchant ID has been removed.', 'laterpay' ),
                        ucfirst( $merchant_id_type )
                    ),
                )
            );
            return;
        }

        update_option( sprintf( 'laterpay_%s_merchant_id', $merchant_id_type ), $merchant_id );
        $event->set_result(
            array(
                'success' => true,
                'message' => sprintf(
                    __( '%s Merchant ID verified and saved.', 'laterpay' ),
                    ucfirst( $merchant_id_type )
                ),
            )
        );
        return;
    }

    /**
     * Update LaterPay API Key, required for making test transactions against Sandbox or Live environments.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected static function update_api_key( LaterPay_Core_Event $event ) {
        $is_live = null;
        if ( $event->has_argument( 'is_live' ) ) {
            $is_live = $event->get_argument( 'is_live' );
        }
        $api_key_form     = new LaterPay_Form_ApiKey( $_POST ); // phpcs:ignore
        $api_key          = $api_key_form->get_field_value( 'api_key' );
        $api_key_type     = $is_live ? 'live' : 'sandbox';
        $transaction_type = $is_live ? 'REAL' : 'TEST';

        if ( ! $api_key_form->is_valid( $_POST ) ) { // phpcs:ignore
            $event->set_result(
                array(
                    'success' => false,
                    'message' => sprintf(
                        __( 'The Merchant ID you entered is not a valid LaterPay %s Merchant ID!', 'laterpay' ),
                        ucfirst( $api_key_type )
                    ),
                )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $api_key_form ), $api_key_form->get_errors() );
        }

        if ( strlen( $api_key ) === 0 ) {
            update_option( sprintf( 'laterpay_%s_api_key', $api_key_type ), '' );
            $event->set_result(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( 'The %s API key has been removed.', 'laterpay' ),
                        ucfirst( $api_key_type )
                    ),
                )
            );
            return;
        }

        update_option( sprintf( 'laterpay_%s_api_key', $api_key_type ), $api_key );
        $event->set_result(
            array(
                'success' => true,
                'message' => sprintf(
                    __( 'Your %s API key is valid. You can now make %s transactions.', 'laterpay' ),
                    ucfirst( $api_key_type ), $transaction_type
                ),
            )
        );
        return;
    }

    /**
     * Toggle LaterPay plugin mode between TEST and LIVE.
     *
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected static function update_plugin_mode( LaterPay_Core_Event $event ) {
        $plugin_mode_form = new LaterPay_Form_PluginMode();

        if ( ! $plugin_mode_form->is_valid( $_POST ) ) { // phpcs:ignore
            array(
                'success' => false,
                'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $plugin_mode_form ), $plugin_mode_form->get_errors() );
        }

        $plugin_mode = $plugin_mode_form->get_field_value( 'plugin_is_in_live_mode' );
        $result      = update_option( 'laterpay_plugin_is_in_live_mode', $plugin_mode );

        if ( $result ) {
            if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
                update_option( 'laterpay_show_cache_msg', 1 );
                $event->set_result(
                    array(
                        'success'   => true,
                        'mode'      => 'live',
                        'message'   => __( 'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.', 'laterpay' ),
                    )
                );
                return;
            }

            $event->set_result(
                array(
                    'success'   => true,
                    'mode'      => 'test',
                    'message'   => __( 'The LaterPay plugin is in invisible TEST mode now. Payments are only simulated and not actually booked.', 'laterpay' ),
                )
            );
            return;
        }

        $event->set_result(
            array(
                'success'   => false,
                'mode'      => 'test',
                'message'   => __( 'The LaterPay plugin needs valid API credentials to work.', 'laterpay' ),
            )
        );
    }

    protected static function change_region( LaterPay_Core_Event $event ) {
        $region_form = new LaterPay_Form_Region();

        if ( ! $region_form->is_valid( $_POST ) ) { // phpcs:ignore
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
                )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $region_form ), $region_form->get_errors() );
        }

        $region = $region_form->get_field_value( 'laterpay_region' );
        $result = update_option( 'laterpay_region', $region );

        if ( ! $result ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'Failed to change region settings.', 'laterpay' ),
                )
            );
            return;
        }

        // Update LaterPay Google Analytics Tracking Value According to current region.
        $regional_settings = LaterPay_Helper_Config::get_regional_settings();
        $lp_is_plugin_live = LaterPay_Helper_View::is_plugin_in_live_mode();

        if ( $lp_is_plugin_live ) {
            $lp_config_id = $regional_settings['tracking_ua_id.live'];
        } else {
            $lp_config_id = $regional_settings['tracking_ua_id.sandbox'];
        }

        $lp_tracking                      = get_option( 'laterpay_tracking_data', array() );
        $lp_tracking['laterpay_ga_ua_id'] = $lp_config_id;

        update_option( 'laterpay_tracking_data', $lp_tracking );

        $event->set_result(
            array(
                'success' => true,
                'creds'   => LaterPay_Helper_Config::prepare_sandbox_creds(),
                'message' => __( 'The LaterPay region was changed successfully.', 'laterpay' ),
            )
        );
    }

    /**
     * Erase data and disable plugin.
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function disable_plugin( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer('plugin_disable_nonce', 'security' );

        LaterPay_Helper_Config::erase_plugin_data();

        if ( ! laterpay_check_is_vip() ) {
            $plugin_name = laterpay_get_plugin_config()->get( 'plugin_base_name' );
            deactivate_plugins( $plugin_name );
            update_option( 'recently_activated', array( $plugin_name => time() ) + (array) get_option( 'recently_activated' ) );

            $event->set_result(
                array(
                    'success' => true,
                    'is_vip'  => false,
                    'message' => esc_html__( 'LaterPay has been successfully uninstalled. It can be re-activated from the plugins page.', 'laterpay' ),
                )
            );
        } else {
            $event->set_result(
                array(
                    'success' => true,
                    'is_vip'  => true,
                    'message' => esc_html__( 'LaterPay data has been erased successfully.', 'laterpay' ),
                )
            );
        }
    }

    /**
     * Validate API Key, Merchant ID and Region combination
     *
     * @wp-hook wp_ajax_laterpay_validate_cred_region
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function ajax_validate_cred_region( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'validate_cred_nonce', 'security' );

        $current_region = get_option( 'laterpay_region', 'us' );
        $client         = $this->get_client_instance_for_config();

        // Check if current config is valid or not.
        $first_response = json_decode( $client->check_health( true ), true );

        // Check credential combination in other region.
        if ( false === $first_response['is_valid'] ) {
            if ( 'us' === $current_region ) {
                $regional_settings = LaterPay_Helper_Config::get_regional_settings_by_param( 'eu' );
                $region_text       = 'EURO (€)';
            } else {
                $regional_settings = LaterPay_Helper_Config::get_regional_settings_by_param( 'us' );
                $region_text       = 'USD ($)';
            }

            // Temporarily change API endpoint to check with other region.
            $client = $this->get_client_instance_for_config( $regional_settings['api.live_backend_api_url'], $regional_settings['api.live_dialog_api_url'] );

            // Check if the config is valid or not with other region.
            $signature_response = json_decode( $client->check_health( true ), true );

            $is_valid = $signature_response['is_valid'];

            update_option( 'laterpay_plugin_is_in_live_mode', '0' );
            if ( $is_valid ) {
                $event->set_result(
                    array(
                        'success' => false,
                        'mode'    => 'test',
                        'message' => sprintf( __( 'Your LaterPay account is restricted to sell content in %s. Please update your currency or contact sales@laterpay.net.', 'laterpay' ), $region_text ),
                    )
                );
            } else {
                $event->set_result(
                    array(
                        'success' => false,
                        'mode'    => 'test',
                        'message' => __( 'The LaterPay plugin needs valid API credentials to work.', 'laterpay' ),
                    )
                );
            }

            return;
        }

        $event->set_result(
            array(
                'success' => true,
            )
        );

        return;
    }

    /**
     * Get a new client instance based on region.
     *
     * @param string $api_root API Endpoint.
     * @param string $web_root Web Dialog URL.
     *
     * @return LaterPay_Client
     */
    private function get_client_instance_for_config( $api_root = '', $web_root = '' ) {
        // Get current client options.
        $client_options = LaterPay_Helper_Config::get_php_client_options();

        // If region endpoints are passed the use it.
        $api_root = empty( $api_root ) ? $client_options['api_root'] : $api_root;
        $web_root = empty( $web_root ) ? $client_options['web_root'] : $web_root;

        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $api_root,
            $web_root,
            $client_options['token_name']
        );

        return $client;
    }
}
