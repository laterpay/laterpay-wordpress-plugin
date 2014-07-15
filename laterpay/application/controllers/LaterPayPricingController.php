<?php

class LaterPayPricingController extends LaterPayAbstractController {
    public function loadAssets() {
        parent::loadAssets();
        global $laterpay_version;

        // load page-specific CSS
        wp_register_style(
            'laterpay-select2',
            LATERPAY_ASSETS_PATH . '/css/vendor/select2.min.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style('laterpay-select2');

        // load page-specific JS
        wp_register_script(
            'laterpay-select2',
            LATERPAY_ASSETS_PATH . '/js/vendor/select2.min.js',
            array('jquery'),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-backend-pricing',
            LATERPAY_ASSETS_PATH . '/js/laterpay-backend-pricing.js',
            array('jquery', 'laterpay-select2'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-select2');
        wp_enqueue_script('laterpay-backend-pricing');
    }

    /**
     * Render HTML for and assign variables to pricing tab
     *
     * @access public
     */
    public function page() {
        $this->loadAssets();

        $Currency = new LaterPayModelCurrency();
        $LaterPayModelCategory = new LaterPayModelCategory();
        $Categories = $LaterPayModelCategory->getCategoriesPrices();
        $Currencies = $Currency->getCurrencies();

        $this->assign('Categories',             $Categories);
        $this->assign('Currencies',             $Currencies);
        $this->assign('currency',               get_option('laterpay_currency'));
        $this->assign('plugin_is_in_live_mode', get_option('laterpay_plugin_is_in_live_mode') == 1);
        $this->assign('global_default_price',   LaterPayViewHelper::formatNumber((float)get_option('laterpay_global_price'), 2));

        $this->render('pluginBackendPricingTab');
    }

    /**
     * Process Ajax requests from pricing tab
     *
     * @access public
     */
    public static function pageAjax() {
        // save changes in submitted form
        if ( isset($_POST['form']) ) {
            // check for required privileges to perform action
            if ( !UserHelper::isAllowed('laterpay_edit_plugin_settings') ) {
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
                case 'currency_form':
                    self::_updateCurrency();
                    break;

                case 'global_price_form':
                    self::_updateGlobalDefaultPrice();
                    break;

                case 'price_category_form':
                    self::_updateCategoryDefaultPrice();
                    break;

                case 'price_category_form_delete':
                    self::_deleteCategoryDefaultPrice();
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

        // return categories that match a given search term
        if ( isset($_GET['term']) ) {
            $LaterPayModelCategory = new LaterPayModelCategory();
            if ( isset($_GET['get']) && $_GET['get'] ) {
                echo Zend_Json::encode(
                    $LaterPayModelCategory->getCategoriesByTerm($_GET['term'], 1)
                );
            } else {
                if (isset($_GET['category'])) {
                    echo Zend_Json::encode(
                        $LaterPayModelCategory->getCategoriesNoPriceByTerm($_GET['term'], 10, (int)$_GET['category'])
                    );
                } else {
                    echo Zend_Json::encode(
                        $LaterPayModelCategory->getCategoriesNoPriceByTerm($_GET['term'], 10)
                    );
                }
            }
            die;
        }
    }

    /**
     * Update the currency used for all prices
     *
     * @access protected
     */
    protected static function _updateCurrency() {
        update_option('laterpay_currency', $_POST['laterpay_currency']);

        echo Zend_Json::encode(
            array(
                'success'           => true,
                'laterpay_currency' => get_option('laterpay_currency'),
                'message'           => sprintf(
                                            __('The currency for this website is %s now.', 'laterpay'),
                                            get_option('laterpay_currency')
                                        )
            )
        );
        die;
    }

    /**
     * Update the global price, which is by default applied to all posts
     *
     * @access protected
     */
    protected static function _updateGlobalDefaultPrice() {
        $delocalized_global_price = (float)str_replace(',', '.', $_POST['laterpay_global_price']);

        if ($delocalized_global_price > 5 || $delocalized_global_price < 0) {
            echo Zend_Json::encode(
                array(
                    'success'               => false,
                    'laterpay_global_price' => get_option('laterpay_global_price'),
                    'message'               => __('The price you tried to set is not within the allowed range of 0 to 5.00.', 'laterpay')
                )
            );
            die;
        }

        update_option('laterpay_global_price', $delocalized_global_price);
        $global_price       = LaterPayViewHelper::formatNumber((float)get_option('laterpay_global_price'), 2);
        $Currency           = new LaterPayModelCurrency();
        $currency_full_name = $Currency->getCurrencyFullNameByShortName(get_option('laterpay_currency'));

        echo Zend_Json::encode(
            array(
                'success'               => true,
                'laterpay_global_price' => $global_price,
                'message'               => sprintf(
                                                __('The global default price for all posts is %s %s now.', 'laterpay'),
                                                $global_price,
                                                $currency_full_name
                                            )
            )
        );
        die;
    }

    /**
     * Update the category price, which is by default applied to all posts in a given category
     *
     * @access protected
     */
    protected static function _updateCategoryDefaultPrice() {

        $delocalized_category_price = (float)str_replace(',', '.', $_POST['price']);
        if ( $delocalized_category_price > 5 || $delocalized_category_price < 0 ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('The price you tried to set is not within the allowed range of 0 to 5.00.', 'laterpay')
                )
            );
            die;
        }

        if ( !empty($_POST['category_id']) ) {
            self::_updateExistingCategoryDefaultPrice();
            die;
        } else {
            self::_setNewCategoryDefaultPrice();
            die;
        }
    }

    /**
     * Update the category price, if a category price is already defined for a given category
     *
     * @access protected
     */
    protected static function _updateExistingCategoryDefaultPrice() {
        $LaterPayModelCategory = new LaterPayModelCategory();
        $id_category = $LaterPayModelCategory->getCategoryIdByName($_POST['category']);
        $id = $LaterPayModelCategory->getPriceIdsByCategoryId($id_category);

        $Currency = new LaterPayModelCurrency();
        $currency_full_name = $Currency->getCurrencyFullNameByShortName(get_option('laterpay_currency'));
        $delocalized_category_price = (float)str_replace(',', '.', $_POST['price']);

        if ( empty($id) && empty($id_category) ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('There is no such category on this website.', 'laterpay')
                )
            );
            die;
        } else if ( !empty($id_category) && $id_category != $_POST['category_id'] ) {
            $LaterPayModelCategory->deletePricesByCategoryId($_POST['category_id']);
            $id = $LaterPayModelCategory->getPriceIdsByCategoryId($_POST['category_id']);

            if ( $id ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                    )
                );
            } else {
                $LaterPayModelCategory->setCategoryPrice($id_category, $delocalized_category_price);

                $category_price             = $LaterPayModelCategory->getPriceByCategoryId($id_category);
                $formatted_category_price   = LaterPayViewHelper::formatNumber((float)$category_price, 2);

                echo Zend_Json::encode(
                    array(
                        'success'       => true,
                        'category'      => $_POST['category'],
                        'price'         => $formatted_category_price,
                        'currency'      => get_option('laterpay_currency'),
                        'category_id'   => $id_category,
                        'message'       => sprintf(
                                                __('All posts in category %s have a default price of %s %s now.', 'laterpay'),
                                                $_POST['category'],
                                                $formatted_category_price,
                                                $currency_full_name
                                            )
                    )
                );
                die;
            }
        }

        $LaterPayModelCategory->setCategoryPrice($id_category, $delocalized_category_price, $id);

        $category_price             = $LaterPayModelCategory->getPriceByCategoryId($id_category);
        $formatted_category_price   = LaterPayViewHelper::formatNumber((float)$category_price, 2);

        echo Zend_Json::encode(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $_POST['price'],
                'currency'      => get_option('laterpay_currency'),
                'category_id'   => $id_category,
                'message'       => sprintf(
                                        __('All posts in category %s have a default price of %s %s now.', 'laterpay'),
                                        $formatted_category_price,
                                        $_POST['price'],
                                        $currency_full_name
                                    )
            )
        );
    }

    /**
     * Set the category price, if a given category does not have a category price yet
     *
     * @access protected
     */
    protected static function _setNewCategoryDefaultPrice() {
        $LaterPayModelCategory = new LaterPayModelCategory();
        $check = $LaterPayModelCategory->checkAvailableCategoryByName($_POST['category']);
        $id_category = $LaterPayModelCategory->getCategoryIdByName($_POST['category']);
        if ( !empty($check) || empty($id_category) ) {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('There is no such category on this website.', 'laterpay')
                )
            );
            die;
        }

        $delocalized_category_price = (float)str_replace(',', '.', $_POST['price']);
        $Currency = new LaterPayModelCurrency();
        $currency_full_name = $Currency->getCurrencyFullNameByShortName(get_option('laterpay_currency'));

        $LaterPayModelCategory->setCategoryPrice($id_category, $delocalized_category_price);

        $category_price             = $LaterPayModelCategory->getPriceByCategoryId($id_category);
        $formatted_category_price   = LaterPayViewHelper::formatNumber((float)$category_price, 2);

        echo Zend_Json::encode(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $formatted_category_price,
                'currency'      => get_option('laterpay_currency'),
                'category_id'   => $id_category,
                'message'       => sprintf(
                                        __('All posts in category %s have a default price of %s %s now.', 'laterpay'),
                                        $_POST['category'],
                                        $formatted_category_price,
                                        $currency_full_name
                                    )
            )
        );
    }

    /**
     * Delete the category price for a given category
     *
     * @access protected
     */
    protected static function _deleteCategoryDefaultPrice() {
        $LaterPayModelCategory = new LaterPayModelCategory();
        $LaterPayModelCategory->deletePricesByCategoryId($_POST['category_id']);

        $id = $LaterPayModelCategory->getPriceIdsByCategoryId($_POST['category_id']);
        if ( empty($id) ) {
            echo Zend_Json::encode(
                array(
                    'success' => true,
                    'message' => __('The default price for this category was deleted.', 'laterpay')
                )
            );
        } else {
            echo Zend_Json::encode(
                array(
                    'success' => false,
                    'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                )
            );
        }
        die;
    }

}
