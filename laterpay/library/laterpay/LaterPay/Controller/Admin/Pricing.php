<?php

class LaterPay_Controller_Admin_Pricing extends LaterPay_Controller_Abstract
{

	/**
	 * @see LaterPay_Controller_Abstract::load_assets()
	 */
    public function load_assets() {
        parent::load_assets();
        global $laterpay_version;

        // load page-specific CSS
        wp_register_style(
            'laterpay-select2',
            LATERPAY_ASSETS_PATH . '/css/vendor/select2.min.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style( 'laterpay-select2' );

        // load page-specific JS
        wp_register_script(
            'laterpay-select2',
            LATERPAY_ASSETS_PATH . '/js/vendor/select2.min.js',
            array( 'jquery' ),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-backend-pricing',
            LATERPAY_ASSETS_PATH . '/js/laterpay-backend-pricing.js',
            array( 'jquery', 'laterpay-select2' ),
            $laterpay_version,
            true
        );
        wp_enqueue_script( 'laterpay-select2' );
        wp_enqueue_script( 'laterpay-backend-pricing' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-pricing',
            'lpVars',
            array( 'locale' => get_locale() )
        );
    }

	/**
	 * @see LaterPay_Controller_Abstract::load_assets()
	 */
    public function render_page() {
        $this->load_assets();

        $Currency = new LaterPay_Model_Currency();
        $LaterPay_Category_Model = new LaterPay_Model_Category();
        $Categories = $LaterPay_Category_Model->get_categories_prices();
        $Currencies = $Currency->get_currencies();

        $this->assign( 'Categories',             $Categories );
        $this->assign( 'Currencies',             $Currencies );
        $this->assign( 'currency',               get_option( 'laterpay_currency' ) );
        $this->assign( 'plugin_is_in_live_mode', get_option( 'laterpay_plugin_is_in_live_mode' ) == 1 );
        $this->assign( 'global_default_price',   LaterPay_Helper_View::format_number( (float) get_option( 'laterpay_global_price' ), 2 ) );
        $this->assign( 'top_nav',                $this->get_menu() );
        $this->assign( 'admin_menu',             LaterPay_Helper_View::get_admin_menu() );

        $this->render( 'backend/tabs/pricing' );
    }

    /**
     * Process Ajax requests from pricing tab
     *
     * @return  void
     */
    public static function process_ajax_requests() {
        // save changes in submitted form
        if ( isset( $_POST['form'] ) ) {
            // check for required privileges to perform action
            if ( ! LaterPay_Helper_User::can( 'laterpay_edit_plugin_settings' ) ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __( 'You don´t have sufficient user privileges to do this.', 'laterpay' )
                    )
                );
                die;
            }

            if ( function_exists( 'check_admin_referer' ) ) {
                check_admin_referer( 'laterpay_form' );
            }

            switch ( $_POST['form'] ) {
                case 'currency_form':
                    self::_update_currency();
                    break;

                case 'global_price_form':
                    self::_update_global_default_price();
                    break;

                case 'price_category_form':
                    self::_update_category_default_price();
                    break;

                case 'price_category_form_delete':
                    self::_delete_category_default_price();
                    break;

                default:
                    echo Zend_Json::encode(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                    die;
            }
        }

        // return categories that match a given search term
        if ( isset( $_GET['term'] ) ) {
            $LaterPay_Category_Model = new LaterPay_Model_Category();
            if ( isset( $_GET['get'] ) && $_GET['get'] ) {
                echo Zend_Json::encode(
                    $LaterPay_Category_Model->get_categories_by_term( $_GET['term'], 1 )
                );
            } else {
                if ( isset( $_GET['category'] ) ) {
                    echo Zend_Json::encode(
                        $LaterPay_Category_Model->get_categories_without_price_by_term( $_GET['term'], 10, (int) $_GET['category'] )
                    );
                } else {
                    echo Zend_Json::encode(
                        $LaterPay_Category_Model->get_categories_without_price_by_term( $_GET['term'], 10 )
                    );
                }
            }
            die;
        }
        // invalid request
        echo Zend_Json::encode(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
            )
        );
        die;
    }

    /**
     * Update the currency used for all prices
     *
     * @return  void
     */
    protected static function _update_currency() {
        update_option( 'laterpay_currency', $_POST['laterpay_currency'] );

        echo Zend_Json::encode(
            array(
                'success'           => true,
                'laterpay_currency' => get_option( 'laterpay_currency' ),
                'message'           => sprintf(
                                            __( 'The currency for this website is %s now.', 'laterpay' ),
                                            get_option( 'laterpay_currency' )
                                        )
            )
        );
        die;
    }

    /**
     * Update the global price, which is by default applied to all posts
     *
     * @return  void
     */
    protected static function _update_global_default_price() {
        $delocalized_global_price = (float) str_replace( ',', '.', $_POST['laterpay_global_price'] );

        if ( $delocalized_global_price > 5 || $delocalized_global_price < 0 ) {
            echo Zend_Json::encode(
                array(
                    'success'               => false,
                    'laterpay_global_price' => get_option( 'laterpay_global_price' ),
                    'message'               => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' )
                )
            );
            die;
        }

        update_option('laterpay_global_price', $delocalized_global_price);
        $global_price       = LaterPay_Helper_View::format_number( (float) get_option( 'laterpay_global_price' ), 2 );
        $Currency           = new LaterPay_Model_Currency();
        $currency_name = $Currency->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );

        echo Zend_Json::encode(
            array(
                'success'               => true,
                'laterpay_global_price' => $global_price,
                'message'               => sprintf(
                                                __( 'The global default price for all posts is %s %s now.', 'laterpay' ),
                                                $global_price,
                                                $currency_name
                                            )
            )
        );
        die;
    }

    /**
     * Update the category price, which is by default applied to all posts in a given category
     *
     * @return  void
     */
    protected static function _update_category_default_price() {
        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );

        if ( $delocalized_category_price > 5 || $delocalized_category_price < 0 ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __( 'The price you tried to set is not within the allowed range of 0 to 5.00.', 'laterpay' )
                )
            );
            die;
        }

        if ( ! empty( $_POST['category_id'] ) ) {
            self::_update_existing_category_default_price();
            die;
        } else {
            self::_set_new_category_default_price();
            die;
        }
    }

    /**
     * Update the category price, if a category price is already defined for a given category
     *
     * @return  void
     */
    protected static function _update_existing_category_default_price() {
        $LaterPay_Category_Model      = new LaterPay_Model_Category();
        $id_category                = $LaterPay_Category_Model->get_category_id_by_name( $_POST['category'] );
        $id                         = $LaterPay_Category_Model->get_price_id_by_category_id( $id_category );

        $Currency                   = new LaterPay_Model_Currency();
        $currency_name              = $Currency->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );
        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );

        if ( empty( $id ) && empty( $id_category ) ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' )
                )
            );
            die;
        } else if ( ! empty( $id_category ) && $id_category != $_POST['category_id'] ) {
            $LaterPay_Category_Model->delete_prices_by_category_id( $_POST['category_id'] );
            $id = $LaterPay_Category_Model->get_price_id_by_category_id( $_POST['category_id'] );

            if ( $id ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                    )
                );
            } else {
                $LaterPay_Category_Model->set_category_price( $id_category, $delocalized_category_price );

                $category_price             = $LaterPay_Category_Model->get_price_by_category_id( $id_category );
                $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );

                echo Zend_Json::encode(
                    array(
                        'success'       => true,
                        'category'      => $_POST['category'],
                        'price'         => $formatted_category_price,
                        'currency'      => get_option( 'laterpay_currency' ),
                        'category_id'   => $id_category,
                        'message'       => sprintf(
                                                __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                                                $_POST['category'],
                                                $formatted_category_price,
                                                $currency_name
                                            )
                    )
                );
                die;
            }
        }

        $LaterPay_Category_Model->set_category_price( $id_category, $delocalized_category_price, $id );

        $category_price             = $LaterPay_Category_Model->get_price_by_category_id( $id_category );
        $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );

        echo Zend_Json::encode(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $_POST['price'],
                'currency'      => get_option('laterpay_currency'),
                'category_id'   => $id_category,
                'message'       => sprintf(
                                        __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                                        $formatted_category_price,
                                        $_POST['price'],
                                        $currency_name
                                    )
            )
        );
    }

    /**
     * Set the category price, if a given category does not have a category price yet
     *
     * @return  void
     */
    protected static function _set_new_category_default_price() {
        $LaterPay_Category_Model  = new LaterPay_Model_Category();
        $check                  = $LaterPay_Category_Model->check_existence_of_category_by_name( $_POST['category'] );
        $id_category            = $LaterPay_Category_Model->get_category_id_by_name( $_POST['category'] );
        if ( ! empty( $check ) || empty( $id_category ) ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' )
                )
            );
            die;
        }

        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );
        $Currency       = new LaterPay_Model_Currency();
        $currency_name  = $Currency->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );

        $LaterPay_Category_Model->set_category_price( $id_category, $delocalized_category_price );

        $category_price             = $LaterPay_Category_Model->get_price_by_category_id( $id_category );
        $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );

        echo Zend_Json::encode(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $formatted_category_price,
                'currency'      => get_option( 'laterpay_currency' ),
                'category_id'   => $id_category,
                'message'       => sprintf(
                                        __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                                        $_POST['category'],
                                        $formatted_category_price,
                                        $currency_name
                                    )
            )
        );
    }

    /**
     * Delete the category price for a given category
     *
     * @return  void
     */
    protected static function _delete_category_default_price() {
        $LaterPay_Category_Model = new LaterPay_Model_Category();
        $LaterPay_Category_Model->delete_prices_by_category_id( $_POST['category_id'] );

        $id = $LaterPay_Category_Model->get_price_id_by_category_id( $_POST['category_id'] );
        if ( empty( $id ) ) {
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __( 'The default price for this category was deleted.', 'laterpay' )
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                )
            );
        }
        die;
    }

}
