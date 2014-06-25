<?php

class AccountController extends AbstractController {

    public function loadAssets() {
        parent::loadAssets();
        global $laterpay_version;

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-account',
            LATERPAY_ASSET_PATH . '/static/js/laterpay-backend-account.js',
            array( 'jquery' ),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-backend-account');
    }

    /**
     * Render HTML for account tab
     *
     * @access public
     */
    public function page() {
        $this->loadAssets();

        $this->render('accountView');
    }

    /**
     * Process Ajax requests from account tab
     *
     * @access public
     */
    public static function pageAjax() {
        if (isset($_POST['form'])) {
            // check for required privileges to perform action
            if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __('You don´t have sufficient user privileges to do this.', 'laterpay')
                    )
                );
                die;
            }

            if ( function_exists('check_admin_referer') ) {
                check_admin_referer('laterpay_form');
            }

            switch ( $_POST['form'] ) {
                case 'laterpay_sandbox_merchant_id':
                    self::_updateSandboxMerchantId();
                    break;

                case 'laterpay_sandbox_api_key':
                    self::_updateSandboxApiKey();
                    break;

                case 'laterpay_live_merchant_id':
                    self::_updateLiveMerchantId();
                    break;

                case 'laterpay_live_api_key':
                    self::_updateLiveApiKey();
                    break;

                case 'plugin_mode_is_live':
                    self::_updatePluginMode();
                    break;

                default:
                    echo Zend_Json::encode(
                        array(
                            'success' => false,
                            'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                        )
                    );
                    die;
            }
        }
    }

    /**
     * Update LaterPay Sandbox Merchant ID, required for making test transactions against Sandbox environment
     *
     * @access protected
     */
    protected static function _updateSandboxMerchantId() {
        if ( self::isValidMerchantId($_POST['laterpay_sandbox_merchant_id']) ) {
            update_option('laterpay_sandbox_merchant_id', $_POST['laterpay_sandbox_merchant_id']);
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('Sandbox Merchant ID verified and saved.', 'laterpay')
                )
            );
        } elseif ( strlen($_POST['laterpay_sandbox_merchant_id']) == 0 ) {
            update_option('laterpay_sandbox_merchant_id', '');
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('The Sandbox Merchant ID has been removed.', 'laterpay')
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID!', 'laterpay')
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Sandbox API Key, required for making test transactions against Sandbox environment
     *
     * @access protected
     */
    protected static function _updateSandboxApiKey() {
        if ( self::isValidApiKey($_POST['laterpay_sandbox_api_key']) ) {
            update_option('laterpay_sandbox_api_key', $_POST['laterpay_sandbox_api_key']);
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('Sandbox API key verified and saved. You can now make TEST transactions.', 'laterpay')
                )
            );
        } elseif ( strlen($_POST['laterpay_sandbox_api_key']) == 0 ) {
            update_option('laterpay_sandbox_api_key', '');
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('The Sandbox API key has been removed.', 'laterpay')
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('The API key you entered is not a valid LaterPay Sandbox API key!', 'laterpay')
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Live Merchant ID, required for making real transactions against production environment
     *
     * @access protected
     */
    protected static function _updateLiveMerchantId() {
        if ( self::isValidMerchantId($_POST['laterpay_live_merchant_id']) ) {
            update_option('laterpay_live_merchant_id', $_POST['laterpay_live_merchant_id']);
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('Live Merchant ID verified and saved.', 'laterpay')
                )
            );
        } elseif ( strlen($_POST['laterpay_live_merchant_id']) == 0 ) {
            update_option('laterpay_live_merchant_id', '');
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('The Live Merchant ID has been removed.', 'laterpay')
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('The Merchant ID you entered is not a valid LaterPay Live Merchant ID!', 'laterpay')
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Live API Key, required for making real transactions against production environment
     *
     * @access protected
     */
    protected static function _updateLiveApiKey() {
        if ( self::isValidApiKey($_POST['laterpay_live_api_key']) ) {
            update_option('laterpay_live_api_key', $_POST['laterpay_live_api_key']);
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('Live API key verified and saved. You can now make REAL transactions.', 'laterpay')
                )
            );
        } elseif ( strlen($_POST['laterpay_live_api_key']) == 0 ) {
            update_option('laterpay_live_api_key', '');
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('The Live API key has been removed.', 'laterpay')
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('The API key you entered is not a valid LaterPay Live API key!', 'laterpay')
                )
            );
        }
        die;
    }

    /**
     * Update LaterPay Plugin Mode
     * 
     * @access protected
     */
    protected static function _updatePluginMode() {
        $result = update_option('laterpay_plugin_mode_is_live', $_POST['plugin_mode_is_live']);
        if ( $result ) {
            if ( get_option('laterpay_plugin_mode_is_live') ) {
                echo Zend_Json::encode(
                    array(
                        'success' => true,
                        'message' => __('The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.', 'laterpay')
                    )
                );
            } else {
                echo Zend_Json::encode(
                    array(
                        'success' => true,
                        'message' => __('The LaterPay plugin is in TEST mode now. Payments are only simulated and not actually booked.', 'laterpay')
                    )
                );
            }
        }
        die;
    }


    /**
     * Validate format of LaterPay Merchant ID
     *
     * @access public
     */
    public static function isValidMerchantId( $merchant_id ) {
        return preg_match('/[a-zA-Z0-9]{22}/', $merchant_id);
    }

    /**
     * Validate format of LaterPay API key
     *
     * @access public
     */
    public static function isValidApiKey( $api_key ) {
        return preg_match('/[a-z0-9]{32}/', $api_key);
    }

}
