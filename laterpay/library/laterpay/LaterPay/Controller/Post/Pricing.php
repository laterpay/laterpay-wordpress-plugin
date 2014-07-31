<?php

class LaterPay_Controller_Post_Pricing extends LaterPay_Controller_Abstract
{

	/**
	 * @see LaterPay_Controller_Abstract::load_assets()
	 */
	public function load_assets() {
        parent::load_assets();
        $this->load_stylesheets();
        $this->load_scripts();
    }

    /**
     * Load page-specific CSS.
     *
     * @return  void
     */
    public function load_stylesheets() {

        wp_register_style(
            'laterpay-post-edit',
            $this->config->css_url . 'laterpay-post-edit.css',
            array(),
            $this->config->version
        );
        wp_enqueue_style( 'laterpay-post-edit' );
    }

    /**
     * Load page-specific JS.
     *
     * @return  void
     */
    public function load_scripts() {

        wp_register_script(
            'laterpay-d3',
            $this->config->js_url . '/vendor/d3.min.js',
            array(),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-d3-dynamic-pricing-widget',
            $this->config->js_url . '/d3.dynamic.widget.js',
            array( 'laterpay-d3' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-post-edit',
            $this->config->js_url . '/laterpay-post-edit.js',
            array( 'laterpay-d3', 'laterpay-d3-dynamic-pricing-widget', 'jquery' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-d3' );
        wp_enqueue_script( 'laterpay-d3-dynamic-pricing-widget' );
        wp_enqueue_script( 'laterpay-post-edit' );

        // pass localized strings and variables to scripts
        wp_localize_script(
            'laterpay-post-edit',
            'laterpay_post_edit',
            array(
                'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
                'globalDefaultPrice'        => (float) get_option( 'laterpay_global_price' ),
                'locale'                    => get_locale(),
                'i18nTeaserError'           => __( 'Paid posts require some teaser content. Please fill in the Teaser Content field.', 'laterpay' ),
                'i18nAddDynamicPricing'     => __( 'Add dynamic pricing', 'laterpay' ),
                'i18nRemoveDynamicPricing'  => __( 'Remove dynamic pricing', 'laterpay' ),
                'l10n_print_after'          => 'jQuery.extend(lpVars, laterpay_post_edit)',
            )
        );
        wp_localize_script(
            'laterpay-d3-dynamic-pricing-widget',
            'laterpay_d3_dynamic_pricing_widget',
            array(
                'currency'          => get_option( 'laterpay_currency' ),
                'i18nDefaultPrice'  => __( 'default price', 'laterpay' ),
                'i18nDays'          => __( 'days', 'laterpay' ),
                'l10n_print_after'  => 'jQuery.extend(lpVars, laterpay_d3_dynamic_pricing_widget)',
            )
        );
    }

	/**
	 * Add teaser content editor to add / edit post page.
	 *
	 * @wp-hook admin_menu
	 *
	 * @return  void
	 */
	public function add_teaser_content_box() {
		add_meta_box( 'laterpay_teaser_content',
		              __( 'Teaser Content', 'laterpay' ),
		              array( $this, 'render_teaser_content_box' ),
		              'post',
		              'normal',
		              'high'
		);
	}

    /**
     * Callback function of add_meta_box to render the editor for teaser content.
     *
     * @param   WP_Post $post post object
     *
     * @return  void
     */
    public function render_teaser_content_box( $post ) {
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post ) ) {
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
            'quicktags'       => 1,
        );
        $content = get_post_meta( $post->ID, 'Teaser content', true );
        $editor_id = 'postcueeditor';

        echo '<dfn>' . __("This is shown to visitors, who haven't purchased the article yet. Use an excerpt of the article that makes them want to buy the whole thing! ", 'laterpay' ) . '</dfn>';

        wp_editor( $content, $editor_id, $settings );

        echo '<input type="hidden" name="laterpay_teaser_content_box_nonce" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
    }

    /**
     * Save teaser content.
     *
     * @wp-hook save_post
     *
     * @param   int $post_id
     *
     * @return  int $post_id
     */
    public function save_teaser_content_box( $post_id ) {
        if ( ! isset( $_POST['laterpay_teaser_content_box_nonce'] ) || ! wp_verify_nonce( $_POST['laterpay_teaser_content_box_nonce'], plugin_basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post_id ) ) {
            return $post_id;
        }

        $meta_value     = get_post_meta( $post_id, 'Teaser content', true );
        $new_meta_value = $_POST['teaser-content'];

        $this->set_post_meta( $meta_value, $new_meta_value, $post_id, 'Teaser content' );
	    return $post_id;
    }




	/**
	 * Add pricing form to add / edit post page.
	 *
	 * @wp-hook admin_menu
	 *
	 * @return  void
	 */
	public function add_post_pricing_form() {
		add_meta_box( 'laterpay_pricing_post_content',
		              __( 'Pricing for this Post', 'laterpay' ),
		              array( $this, 'render_post_pricing_form' ),
		              'post',
		              'side',
		              'high'
		);
	}


	/**
     * Callback for add_meta_box to render form for pricing of post.
     *
     * @param   WP_Post $post
     *
     * @return  void
     */
    public function render_post_pricing_form( $post ) {
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post ) ) {
            return;
        }

        $post_specific_price = get_post_meta( $post->ID, 'Pricing Post', true );
        // TODO: optimize the current approach:
        // If it's an existing value, if should have been saved with decimals,
        // so '0' is a crappy way of knowing that 'Pricing Post' has never been set
        if ( $post_specific_price == 0 ) {
            $post_specific_price = null;
        } else {
            $post_specific_price = (float) $post_specific_price;
        }

        // category default price data
        $category_price_data    = null;
        $post_default_category  = null;
        $category_default_price = null;
        $categories_of_post     = wp_get_post_categories( $post->ID );
        if ( ! empty( $categories_of_post ) ) {
            $LaterPay_Category_Model  = new LaterPay_Model_Category();
            $category_price_data    = $LaterPay_Category_Model->get_category_price_data_by_category_ids( $categories_of_post );
            $post_default_category  = (int) get_post_meta( $post->ID, 'laterpay_post_default_category', true );
            // if the post has a category defined from which to use the category default price then let's get that price
            if ( $post_default_category > 0 ) {
                $category_default_price = (float) $LaterPay_Category_Model->get_price_by_category_id( $post_default_category );
            }
        }

        // global default price
        $global_default_price = get_option( 'laterpay_global_price' );
        // TODO: optimize the current approach:
        // If it's an existing value, if should have been saved with decimals,
        // so '0' is a crappy way of knowing that 'Pricing Post' has never been set
        if ( $global_default_price == 0 ) {
            $global_default_price = null;
        } else {
            $global_default_price = (float) $global_default_price;
        }

        $post_price_type = get_post_meta( $post->ID, 'Pricing Post Type', true );
        switch ( $post_price_type ) {
            // backwards compatibility: Pricing Post Type used to be stored as 0 or 1; TODO: remove with release 1.0
            case '0':
            case '1':
            case 'individual price':
                $price = $post_specific_price;
                break;

            case 'individual price, dynamic':
                // current price
                $price = LaterPay_Controller_Post_Content::get_dynamic_price( $GLOBALS['post'] );
                break;

            case 'category default price':
                $price = $category_default_price;
                break;

            case 'global default price':
                $price = $global_default_price;
                break;

            default:
                // new posts should use the global default price or 0, if there's no global default price
                if ( is_null( $global_default_price ) ) {
                    $price =  0.00;
                    $post_price_type = 'individual price';
                } else {
                    $price = $global_default_price;
                    $post_price_type = 'global default price';
                }

                break;
        }

        // return dynamic pricing widget start values
        if ( ! get_post_meta( $post->ID, 'laterpay_start_price', true ) ) {
            $dynamic_pricing_data = array(
                array( 'x' => 0,  'y' => 1.8 ),
                array( 'x' => 13, 'y' => 1.8 ),
                array( 'x' => 18, 'y' => 0.2 ),
                array( 'x' => 30, 'y' => 0.2 )
            );
        } elseif ( get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) == 0 ) {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_start_price', true )
                ),
                array(
                    'x' => (float) get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true ),
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_start_price', true )
                ),
                array(
                    'x' => (float) get_post_meta( $post->ID, 'laterpay_reach_end_price_after_days', true ),
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_end_price', true )
                )
            );
        } else {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_start_price', true )
                ),
                array(
                    'x' => (float) get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true ),
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_start_price', true )
                ),
                array(
                    'x' => (float) get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ),
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_end_price', true )
                ),
                array(
                    'x' => (float) get_post_meta( $post->ID, 'laterpay_reach_end_price_after_days', true ),
                    'y' => (float) get_post_meta( $post->ID, 'laterpay_end_price', true )
                )
            );
        }

        echo '<input type="hidden" name="laterpay_pricing_post_content_box_nonce" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';

        $this->assign( 'laterpay_post_price_type',       $post_price_type );
        $this->assign( 'laterpay_price',                 $price );
        $this->assign( 'laterpay_currency',              get_option( 'laterpay_currency' ) );
        $this->assign( 'laterpay_category_prices',       $category_price_data );
        $this->assign( 'laterpay_post_default_category', (int) $post_default_category );
        $this->assign( 'laterpay_global_default_price',  $global_default_price );
        $this->assign( 'laterpay_dynamic_pricing_data',  Zend_Json::encode( $dynamic_pricing_data ) );

        $this->render( 'backend/partials/post/pricing/form' );
    }

    /**
     * Process Ajax request for prices of applied categories.
     *
     * @param   Array $category_ids
     *
     * @return  void
     */
    protected static function _get_category_prices( $category_ids ) {
        $LaterPay_Category_Model = new LaterPay_Model_Category();
        $categories_price_data = $LaterPay_Category_Model->get_category_price_data_by_category_ids( $category_ids );

        echo Zend_Json::encode( $categories_price_data );
    }

    /**
     * Save pricing of post.
     *
     * @wp-hook save_post
     *
     * @param   int $post_id
     *
     * @return  int $post_id
     */
    public function save_post_pricing_form( $post_id ) {
        if ( ! isset( $_POST['laterpay_pricing_post_content_box_nonce'] ) || ! wp_verify_nonce( $_POST['laterpay_pricing_post_content_box_nonce'], plugin_basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post_id ) ) {
            return $post_id;
        }

        $delocalized_price = (float) str_replace( ',', '.', $_POST['post-price'] );

        $this->set_post_meta(
            get_post_meta( $post_id, 'Pricing Post', true ),
            $delocalized_price,
            $post_id,
            'Pricing Post'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'Pricing Post Type', true ),
            stripslashes( $_POST['post_price_type'] ),
            $post_id,
            'Pricing Post Type'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_post_default_category', true ),
            stripslashes( $_POST['laterpay_post_default_category'] ),
            $post_id,
            'laterpay_post_default_category'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_start_price', true ),
            stripslashes( $_POST['laterpay_start_price'] ),
            $post_id,
            'laterpay_start_price'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_end_price', true ),
            stripslashes( $_POST['laterpay_end_price'] ),
            $post_id,
            'laterpay_end_price'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_change_start_price_after_days', true ),
            stripslashes( $_POST['laterpay_change_start_price_after_days'] ),
            $post_id,
            'laterpay_change_start_price_after_days'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_transitional_period_end_after_days', true ),
            stripslashes( $_POST['laterpay_transitional_period_end_after_days'] ),
            $post_id,
            'laterpay_transitional_period_end_after_days'
        );
        $this->set_post_meta(
            get_post_meta( $post_id, 'laterpay_reach_end_price_after_days', true ),
            stripslashes( $_POST['laterpay_reach_end_price_after_days'] ),
            $post_id,
            'laterpay_reach_end_price_after_days'
        );

	    return $post_id;
    }

    /**
     * Set post meta data.
     *
     * @param   string  $meta_value     old meta value
     * @param   string  $new_meta_value new meta value
     * @param   integer $post_id        post id
     * @param   string  $name           meta name
     *
     * @return  void
     */
    public function set_post_meta( $meta_value, $new_meta_value, $post_id, $name ) {
        if ( '' == $new_meta_value ) {
            delete_post_meta( $post_id, $name );
        } elseif ( $new_meta_value != $meta_value ) {
            add_post_meta( $post_id, $name, $new_meta_value, true ) || update_post_meta( $post_id, $name, $new_meta_value );
        }
    }

    /**
     * Process Ajax requests from account tab.
     *
     * @return  void
     */
    public static function process_ajax_requests() {
        if ( isset( $_POST['form'] ) ) {
            // check for required privileges to perform action
            if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price' ) ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __( 'You don´t have sufficient user privileges to do this.', 'laterpay' )
                    )
                );
                die;
            }

            switch ( $_POST['form'] ) {
                case 'laterpay_get_category_prices':
                    self::_get_category_prices( $_POST['category_ids'] );
                    break;
                default:
                    echo Zend_Json::encode(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
            }
        }
        die;
    }

}
