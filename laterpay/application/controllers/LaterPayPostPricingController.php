<?php

class LaterPayPostPricingController extends LaterPayAbstractController {

    public function load_assets() {
        parent::load_assets();
        $this->loadPostPricingStyles();
        $this->loadPostPricingScripts();
    }

    /**
     * Load page-specific CSS
     */
    public function loadPostPricingStyles() {
        global $laterpay_version;

        wp_register_style(
            'laterpay-post-edit',
            LATERPAY_ASSETS_PATH . '/css/laterpay-post-edit.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style('laterpay-post-edit');
    }

    /**
     * Load page-specific JS
     */
    public function loadPostPricingScripts() {
        global $laterpay_version;

        wp_register_script(
            'laterpay-d3',
            LATERPAY_ASSETS_PATH . '/js/vendor/d3.min.js',
            array(),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-d3-dynamic-pricing-widget',
            LATERPAY_ASSETS_PATH . '/js/d3.dynamic.widget.js',
            array('laterpay-d3'),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-post-edit',
            LATERPAY_ASSETS_PATH . '/js/laterpay-post-edit.js',
            array('laterpay-d3', 'laterpay-d3-dynamic-pricing-widget', 'jquery'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-d3');
        wp_enqueue_script('laterpay-d3-dynamic-pricing-widget');
        wp_enqueue_script('laterpay-post-edit');

        // pass localized strings and variables to scripts
        wp_localize_script(
            'laterpay-post-edit',
            'laterpay_post_edit',
            array(
                'ajaxUrl'                   => admin_url('admin-ajax.php'),
                'globalDefaultPrice'        => (float) get_option('laterpay_global_price'),
                'locale'                    => get_locale(),
                'i18nTeaserError'           => __('Paid posts require some teaser content. Please fill in the Teaser Content field.', 'laterpay'),
                'i18nAddDynamicPricing'     => __('Add dynamic pricing', 'laterpay'),
                'i18nRemoveDynamicPricing'  => __('Remove dynamic pricing', 'laterpay'),
                'l10n_print_after'          => 'jQuery.extend(lpVars, laterpay_post_edit)',
            )
        );
        wp_localize_script(
            'laterpay-d3-dynamic-pricing-widget',
            'laterpay_d3_dynamic_pricing_widget',
            array(
                'currency'          => get_option('laterpay_currency'),
                'i18nDefaultPrice'  => __('default price', 'laterpay'),
                'i18nDays'          => __('days', 'laterpay'),
                'l10n_print_after'  => 'jQuery.extend(lpVars, laterpay_d3_dynamic_pricing_widget)',
            )
        );
    }

    /**
     * Render editor for teaser content
     *
     * @param object $object post object
     *
     * @access public
     */
    public function teaserContentBox( $object ) {
        if (!LaterPayUserHelper::can('laterpay_edit_teaser_content', $object)) {
            return;
        }
        $settings = array(
            'wpautop'         => 1,
            'media_buttons'   => 1,
            'textarea_name'   => 'teaser-content',
            'textarea_rows'   => 8,
            'tabindex'        => null,
            'editor_css'      => '',
            'editor_class'    => '',
            'teeny'           => 1,
            'dfw'             => 1,
            'tinymce'         => 1,
            'quicktags'       => 1
        );
        $content = get_post_meta($object->ID, 'Teaser content', true);
        $editor_id = 'postcueeditor';

        echo "<dfn>" . __("This is shown to visitors, who haven't purchased the article yet. Use an excerpt of the article that makes them want to buy the whole thing!", 'laterpay') . "</dfn>";

        wp_editor($content, $editor_id, $settings);

        echo '<input type="hidden" name="laterpay_teaser_content_box_nonce" value="' . wp_create_nonce(plugin_basename(__FILE__ )) . '" />';
    }

    /**
     * Save teaser content
     *
     * @param integer $post_id post id
     *
     * @access public
     */
    public function saveTeaserContentBox( $post_id ) {
        if ( !isset($_POST['laterpay_teaser_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_teaser_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !LaterPayUserHelper::can('laterpay_edit_teaser_content', $post_id) ) {
            return $post_id;
        }

        $meta_value     = get_post_meta($post_id, 'Teaser content', true);
        $new_meta_value = $_POST['teaser-content'];

        $this->setPostMeta($meta_value, $new_meta_value, $post_id, 'Teaser content');
    }

    /**
     * Render form for post-specific pricing
     *
     * @param object $object post object
     *
     * @access public
     */
    public function pricingPostContentBox( $object ) {
        if (!LaterPayUserHelper::can('laterpay_edit_individual_price', $object)) {
            return;
        }

        $post_specific_price = get_post_meta($object->ID, 'Pricing Post', true);
        // TODO: optimize the current approach:
        // If it's an existing value, if should have been saved with decimals,
        // so '0' is a crappy way of knowing that 'Pricing Post' has never been set
        if ($post_specific_price == 0) {
            $post_specific_price = null;
        } else {
            $post_specific_price = (float) $post_specific_price;
        }

        // category default price data
        $category_price_data    = null;
        $post_default_category  = null;
        $category_default_price = null;
        $categories_of_post     = wp_get_post_categories($object->ID);
        if ( !empty($categories_of_post) ) {
            $LaterPayModelCategory  = new LaterPayModelCategory();
            $category_price_data    = $LaterPayModelCategory->getCategoryPriceDataByCategoryIds($categories_of_post);
            $post_default_category  = (int) get_post_meta($object->ID, 'laterpay_post_default_category', true);
            // if the post has a category defined from which to use the category default price then let's get that price
            if ( $post_default_category > 0 ) {
                $category_default_price = (float) $LaterPayModelCategory->getPriceByCategoryId($post_default_category);
            }
        }

        // global default price
        $global_default_price = get_option('laterpay_global_price');
        // TODO: optimize the current approach:
        // If it's an existing value, if should have been saved with decimals,
        // so '0' is a crappy way of knowing that 'Pricing Post' has never been set
        if ( $global_default_price == 0 ) {
            $global_default_price = null;
        } else {
            $global_default_price = (float) $global_default_price;
        }

        $post_price_type = get_post_meta($object->ID, 'Pricing Post Type', true);
        switch ($post_price_type) {
            // backwards compatibility: Pricing Post Type used to be stored as 0 or 1; TODO: remove with release 1.0
            case '0':
            case '1':
            case 'individual price':
                $price = $post_specific_price;
                break;

            case 'individual price, dynamic':
                // current price
                $price = LaterPayPostContentController::getAdvancedPrice($GLOBALS['post']);
                break;

            case 'category default price':
                $price = $category_default_price;
                break;

            case 'global default price':
                $price = $global_default_price;
                break;

            default:
                // new posts should use the global default price or 0, if there's no global default price
                if ( is_null($global_default_price) ) {
                    $price =  0.00;
                    $post_price_type = 'individual price';
                } else {
                    $price = $global_default_price;
                    $post_price_type = 'global default price';
                }

                break;
        }

        // return dynamic pricing widget start values
        if ( !get_post_meta($object->ID, 'laterpay_start_price', true) ) {
            $dynamic_pricing_data = array(
                array( 'x' => 0,  'y' => 1.8 ),
                array( 'x' => 13, 'y' => 1.8 ),
                array( 'x' => 18, 'y' => 0.2 ),
                array( 'x' => 30, 'y' => 0.2 )
            );
        } elseif ( get_post_meta($object->ID, 'laterpay_transitional_period_end_after_days', true) == 0 ) {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float) get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float) get_post_meta($object->ID, 'laterpay_change_start_price_after_days', true),
                    'y' => (float) get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float) get_post_meta($object->ID, 'laterpay_reach_end_price_after_days', true),
                    'y' => (float) get_post_meta($object->ID, 'laterpay_end_price', true)
                )
            );
        } else {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float) get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float) get_post_meta($object->ID, 'laterpay_change_start_price_after_days', true),
                    'y' => (float) get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float) get_post_meta($object->ID, 'laterpay_transitional_period_end_after_days', true),
                    'y' => (float) get_post_meta($object->ID, 'laterpay_end_price', true)
                ),
                array(
                    'x' => (float) get_post_meta($object->ID, 'laterpay_reach_end_price_after_days', true),
                    'y' => (float) get_post_meta($object->ID, 'laterpay_end_price', true)
                )
            );
        }

        echo '<input type="hidden" name="laterpay_pricing_post_content_box_nonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

        // TODO: wrap everything in one variable?
        $this->assign('laterpay_post_price_type',       $post_price_type);
        $this->assign('laterpay_price',                 $price);
        $this->assign('laterpay_currency',              get_option('laterpay_currency'));
        $this->assign('laterpay_category_prices',       $category_price_data);
        $this->assign('laterpay_post_default_category', (int) $post_default_category);
        $this->assign('laterpay_global_default_price',  $global_default_price);
        $this->assign('laterpay_dynamic_pricing_data',  Zend_Json::encode($dynamic_pricing_data));

        $this->render('partials/postPricingForm');
    }

    /**
     * Process Ajax requests from account tab
     *
     * @access public
     */
    public static function process_ajax_requests() {
        if (isset($_POST['form'])) {
            // check for required privileges to perform action
            if ( !LaterPayUserHelper::can('laterpay_edit_individual_price') ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __('You don´t have sufficient user privileges to do this.', 'laterpay')
                    )
                );
                die;
            }

            switch ( $_POST['form'] ) {
                case 'laterpay_get_category_prices':
                    self::_getCategoryPrices($_POST['category_ids']);
                    break;
                default:
                    echo Zend_Json::encode(
                        array(
                            'success' => false,
                            'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                        )
                    );
            }
        }
        die;
    }

    /**
     * Process Ajax request for prices of applied categories
     *
     * @access protected
     */
    protected static function _getCategoryPrices( $category_ids ) {
        $LaterPayModelCategory  = new LaterPayModelCategory();
        $categories_price_data    = $LaterPayModelCategory->getCategoryPriceDataByCategoryIds($category_ids);
        echo Zend_Json::encode($categories_price_data);
    }

    /**
     * Save post-specific pricing
     *
     * @param integer $post_id post id
     *
     * @access public
     */
    public function savePricingPostContentBox( $post_id ) {
        if ( !isset($_POST['laterpay_pricing_post_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_pricing_post_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !LaterPayUserHelper::can('laterpay_edit_individual_price', $post_id) ) {
            return $post_id;
        }

        $delocalized_price = (float) str_replace(',', '.', $_POST['post-price']);

        $this->setPostMeta(
            get_post_meta($post_id, 'Pricing Post', true),
            $delocalized_price,
            $post_id,
            'Pricing Post'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'Pricing Post Type', true),
            stripslashes($_POST['post_price_type']),
            $post_id,
            'Pricing Post Type'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_post_default_category', true),
            stripslashes($_POST['laterpay_post_default_category']),
            $post_id,
            'laterpay_post_default_category'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_start_price', true),
            stripslashes($_POST['laterpay_start_price']),
            $post_id,
            'laterpay_start_price'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_end_price', true),
            stripslashes($_POST['laterpay_end_price']),
            $post_id,
            'laterpay_end_price'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_change_start_price_after_days', true),
            stripslashes($_POST['laterpay_change_start_price_after_days']),
            $post_id,
            'laterpay_change_start_price_after_days'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_transitional_period_end_after_days', true),
            stripslashes($_POST['laterpay_transitional_period_end_after_days']),
            $post_id,
            'laterpay_transitional_period_end_after_days'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_reach_end_price_after_days', true),
            stripslashes($_POST['laterpay_reach_end_price_after_days']),
            $post_id,
            'laterpay_reach_end_price_after_days'
        );
    }

    /**
     * Set post meta data
     *
     * @param string  $meta_value     old meta value
     * @param string  $new_meta_value new meta value
     * @param integer $post_id        post id
     * @param string  $name           meta name
     *
     * @access public
     */
    public function setPostMeta( $meta_value, $new_meta_value, $post_id, $name ) {
        if ( '' == $new_meta_value ) {
            delete_post_meta($post_id, $name);
        } elseif ( $new_meta_value != $meta_value ) {
            add_post_meta($post_id, $name, $new_meta_value, true) || update_post_meta($post_id, $name, $new_meta_value);
        }
    }

}
