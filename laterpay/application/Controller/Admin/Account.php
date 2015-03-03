<?php

/**
 * LaterPay account controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
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
                'i18nApiKeyInvalid'     => __( 'The API key you entered is not a valid LaterPay API key!', 'laterpay' ),
                'i18nMerchantIdInvalid' => __( 'The Merchant ID you entered is not a valid LaterPay Merchant ID!', 'laterpay' ),
                'i18nPreventUnload'     => __( 'LaterPay does not work properly with invalid API credentials.', 'laterpay' ),
            )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        $view_args = array(
            'sandbox_merchant_id'               => get_option( 'laterpay_sandbox_merchant_id' ),
            'sandbox_api_key'                   => get_option( 'laterpay_sandbox_api_key' ),
            'live_merchant_id'                  => get_option( 'laterpay_live_merchant_id' ),
            'live_api_key'                      => get_option( 'laterpay_live_api_key' ),
            'plugin_is_in_live_mode'            => $this->config->get( 'is_in_live_mode' ),
            'plugin_is_in_visible_test_mode'    => get_option( 'laterpay_is_in_visible_test_mode' ),
            'top_nav'                           => $this->get_menu(),
            'admin_menu'                        => LaterPay_Helper_View::get_admin_menu(),
        );

        $this->assign( 'laterpay', $view_args );

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
            if ( ! current_user_can( 'activate_plugins' ) ) {
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
                    self::update_merchant_id();
                    break;

                case 'laterpay_sandbox_api_key':
                    self::update_api_key();
                    break;

                case 'laterpay_live_merchant_id':
                    self::update_merchant_id( true );
                    break;

                case 'laterpay_live_api_key':
                    self::update_api_key( true );
                    break;

                case 'laterpay_plugin_mode':
                    self::update_plugin_mode();
                    break;

                case 'laterpay_test_mode':
                    self::update_plugin_visibility_in_test_mode();
                    break;

                default:
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                        )
                    );
            }
        }
    }

    /**
     * Update LaterPay Merchant ID, required for making test transactions against Sandbox or Live environments.
     *
     * @param null $is_live
     *
     * @return void
     */
    protected static function update_merchant_id( $is_live = null ) {
        $merchant_id_form   = new LaterPay_Form_MerchantId( $_POST );
        $merchant_id        = $merchant_id_form->get_field_value( 'merchant_id' );
        $merchant_id_type   = $is_live ? 'live' : 'sandbox';

        if ( $merchant_id_form->is_valid() ) {
            update_option( sprintf( 'laterpay_%s_merchant_id', $merchant_id_type ), $merchant_id );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( '%s Merchant ID verified and saved.', 'laterpay' ),
                        ucfirst( $merchant_id_type )
                    ),
                )
            );
        } elseif ( strlen( $merchant_id ) == 0 ) {
            update_option( sprintf( 'laterpay_%s_merchant_id', $merchant_id_type ), '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( 'The %s Merchant ID has been removed.', 'laterpay' ),
                        ucfirst( $merchant_id_type )
                    ),
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
                'message' => sprintf(
                    __( 'The Merchant ID you entered is not a valid LaterPay %s Merchant ID!', 'laterpay' ),
                    ucfirst( $merchant_id_type )
                ),
            )
        );
    }

    /**
     * Update LaterPay API Key, required for making test transactions against Sandbox or Live environments.
     *
     * @param null $is_live
     *
     * @return void
     */
    protected static function update_api_key( $is_live = null ) {
        $api_key_form       = new LaterPay_Form_ApiKey( $_POST );
        $api_key            = $api_key_form->get_field_value( 'api_key' );
        $api_key_type       = $is_live ? 'live' : 'sandbox';
        $transaction_type   = $is_live ? 'REAL' : 'TEST';

        if ( $api_key_form->is_valid() ) {
            update_option( sprintf( 'laterpay_%s_api_key', $api_key_type ), $api_key );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( 'Your %s API key is valid. You can now make %s transactions.', 'laterpay' ),
                        ucfirst( $api_key_type ), $transaction_type
                    ),
                )
            );
        } elseif ( strlen( $api_key ) == 0 ) {
            update_option( sprintf( 'laterpay_%s_api_key', $api_key_type ), '' );
            wp_send_json(
                array(
                    'success' => true,
                    'message' => sprintf(
                        __( 'The %s API key has been removed.', 'laterpay' ),
                        ucfirst( $api_key_type )
                    ),
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
                'message' => sprintf(
                    __( 'The API key you entered is not a valid LaterPay %s API key!', 'laterpay' ),
                    ucfirst( $api_key_type )
                ),
            )
        );

    }

    /**
     * Toggle LaterPay plugin mode between TEST and LIVE.
     *
     * @return void
     */
    protected static function update_plugin_mode() {
        $plugin_mode_form = new LaterPay_Form_PluginMode();

        if ( ! $plugin_mode_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
                )
            );
        }

        $plugin_mode    = $plugin_mode_form->get_field_value( 'plugin_is_in_live_mode' );
        $result         = update_option( 'laterpay_plugin_is_in_live_mode', $plugin_mode );

        if ( $result ) {
            // delete dashboard cache directory after mode was changed
            LaterPay_Helper_File::delete_directory( laterpay_get_plugin_config()->get( 'cache_dir' ) . 'cron/' );

            if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
                wp_send_json(
                    array(
                        'success'   => true,
                        'mode'      => 'live',
                        'message'   => __( 'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.', 'laterpay' ),
                    )
                );
            } elseif ( get_option( 'plugin_is_in_visible_test_mode' ) ) {
                wp_send_json(
                    array(
                        'success'   => true,
                        'mode'      => 'test',
                        'message'   => __( 'The LaterPay plugin is in visible TEST mode now. Payments are only simulated and not actually booked.', 'laterpay' ),
                    )
                );
            }

            wp_send_json(
                array(
                    'success'   => true,
                    'mode'      => 'test',
                    'message'   => __( 'The LaterPay plugin is in invisible TEST mode now. Payments are only simulated and not actually booked.', 'laterpay' ),
                )
            );
        }

        wp_send_json(
            array(
                'success'   => false,
                'mode'      => 'test',
                'message'   => __( 'The LaterPay plugin needs valid API credentials to work.', 'laterpay' ),
            )
        );
    }

    /**
     * Toggle LaterPay plugin test mode between INVISIBLE and VISIBLE.
     *
     * @return void
     */
    public static function update_plugin_visibility_in_test_mode() {
        $plugin_test_mode_form = new LaterPay_Form_TestMode();

        if ( ! $plugin_test_mode_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success'   => false,
                    'mode'      => 'test',
                    'message'   => __( 'An error occurred. Incorrect data provided.', 'laterpay' ),
                )
            );
        }

        $is_in_visible_test_mode = $plugin_test_mode_form->get_field_value( 'plugin_is_in_visible_test_mode' );
        $has_invalid_credentials = $plugin_test_mode_form->get_field_value( 'invalid_credentials' );

        if ( $has_invalid_credentials ) {
            update_option( 'laterpay_is_in_visible_test_mode', 0 );

            wp_send_json(
                array(
                    'success'   => false,
                    'mode'      => 'test',
                    'message'   => __( 'The LaterPay plugin needs valid API credentials to work.', 'laterpay' ),
                )
            );
        }

        update_option( 'laterpay_is_in_visible_test_mode', $is_in_visible_test_mode );

        if ( $is_in_visible_test_mode ) {
            $message = __( 'The plugin is in <strong>visible</strong> test mode now.', 'laterpay' );
        } else {
            $message = __( 'The plugin is in <strong>invisible</strong> test mode now.', 'laterpay' );
        }

        wp_send_json(
            array(
                'success'   => true,
                'mode'      => 'test',
                'message'   => $message,
            )
        );
    }
}
