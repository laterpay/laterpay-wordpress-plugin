<?php

class LaterPay_Controller_Admin_Post_Metabox extends LaterPay_Controller_Abstract
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
     * @return void
     */
    public function load_stylesheets() {
        wp_register_style(
            'laterpay-post-edit',
            $this->config->get( 'css_url' ) . 'laterpay-post-edit.css',
            array(),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'laterpay-post-edit' );
    }

    /**
     * Load page-specific JS.
     *
     * @return void
     */
    public function load_scripts() {
        wp_register_script(
            'laterpay-d3',
            $this->config->get( 'js_url' ) . '/vendor/d3.min.js',
            array(),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-d3-dynamic-pricing-widget',
            $this->config->get( 'js_url' ) . '/d3.dynamic.widget.js',
            array( 'laterpay-d3' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-post-edit',
            $this->config->get( 'js_url' ) . '/laterpay-post-edit.js',
            array( 'laterpay-d3', 'laterpay-d3-dynamic-pricing-widget', 'jquery' ),
            $this->config->get( 'version' ),
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
     * @wp-hook add_meta_boxes
     *
     * @return void
     */
    public function add_meta_boxes() {
        $post_types = $this->config->get( 'content.enabled_post_types' );

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'lp_post-teaser',
                __( 'Teaser Content', 'laterpay' ),
                array( $this, 'render_teaser_content_box' ),
                $post_type,
                'normal',
                'high'
            );

            add_meta_box(
                'lp_post-pricing',
                __( 'Pricing for this Post', 'laterpay' ),
                array( $this, 'render_post_pricing_form' ),
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Callback function of add_meta_box to render the editor for teaser content.
     *
     * @param WP_Post $post
     *
     * @return void
     */
    public function render_teaser_content_box( $post ) {
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post ) ) {

            $this->logger->warning(
                __METHOD__ . ' - current user can not edit teaser content',
                array(
                    'post'          => $post,
                    'current_user'  => wp_get_current_user()
                )
            );

            return;
        }

        $settings = array(
            'wpautop'         => 1,
            'media_buttons'   => 1,
            'textarea_name'   => 'laterpay_post_teaser',
            'textarea_rows'   => 8,
            'tabindex'        => null,
            'editor_css'      => '',
            'editor_class'    => '',
            'teeny'           => 1,
            'dfw'             => 1,
            'tinymce'         => 1,
            'quicktags'       => 1,
        );
        $content = get_post_meta( $post->ID, 'laterpay_post_teaser', true );
        $editor_id = 'postcueeditor';

        echo '<dfn>' . __( "This is shown to visitors, who haven't purchased the article yet. Use an excerpt of the article that makes them want to buy the whole thing! ", 'laterpay' ) . '</dfn>';
        wp_editor( $content, $editor_id, $settings );
        echo '<input type="hidden" name="laterpay_teaser_content_box_nonce" value="' . wp_create_nonce( $this->config->get( 'plugin_base_name' ) ) . '" />';
    }

    /**
     * Check the permissions on saving the metaboxes.
     *
     * @wp-hook save_post
     *
     * @param int $post_id
     *
     * @return bool true|false
     */
    protected function has_permission( $post_id ) {
        // autosave -> do nothing
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        // Ajax -> do nothing
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return false;
        }

        // no post found -> do nothing
        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        // check if the current post type is enabled
        if ( ! in_array( $post->post_type, $this->config->get( 'content.enabled_post_types' ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Save teaser content.
     *
     * @wp-hook save_post, edit_attachment
     *
     * @param int $post_id
     *
     * @return void
     */
    public function save_teaser_content_box( $post_id ) {
        // nonce not valid -> do nothing
        if ( ! isset( $_POST['laterpay_teaser_content_box_nonce'] ) || ! wp_verify_nonce( $_POST['laterpay_teaser_content_box_nonce'], $this->config->get( 'plugin_base_name' ) ) ) {
            return;
        }

        // no rights to edit -> do nothing
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post_id ) ) {
            return;
        }

        if ( ! $this->has_permission( $post_id ) ) {
            return;
        }

        if ( isset( $_POST['laterpay_post_teaser'] ) && ! empty( $_POST[ 'laterpay_post_teaser' ] ) ) {
            // we're using the wpautop() in frontend on the 'the_content' filter
            $new_meta_value = $_POST['laterpay_post_teaser'];
        } else {
            $post = get_post( $post_id );
            $new_meta_value = LaterPay_Helper_String::truncate(
                $post->post_content,
                $this->config->get( 'content.auto_generated_teaser_content_word_count' ),
                array (
                    'html'  => true,
                    'words' => true,
                )
            );
        }

        $this->set_post_meta(
            'laterpay_post_teaser',
            $new_meta_value,
            $post_id
        );
    }

    /**
     * Callback for add_meta_box to render form for pricing of post.
     *
     * @param WP_Post $post
     *
     * @return void
     */
    public function render_post_pricing_form( $post ) {
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post ) ) {
            return;
        }

        $post_prices = get_post_meta( $post->ID, 'laterpay_post_prices', true );
        if ( ! is_array( $post_prices ) ) {
            $post_prices = array();
        }

        $post_default_category              = array_key_exists( 'category_id',      $post_prices ) ? (int) $post_prices[ 'category_id' ] : 0;
        $post_revenue_model                 = array_key_exists( 'revenue_model',    $post_prices ) ? $post_prices[ 'revenue_model' ] : 'ppu';

        // category default price data
        $category_price_data    = null;
        $category_default_price = null;
        $category_default_price_revenue_model = null;
        $categories_of_post     = wp_get_post_categories( $post->ID );
        if ( ! empty( $categories_of_post ) ) {
            $laterpay_category_model    = new LaterPay_Model_CategoryPrice();
            $category_price_data        = $laterpay_category_model->get_category_price_data_by_category_ids( $categories_of_post );
            // if the post has a category defined from which to use the category default price then let's get that price
            if ( $post_default_category > 0 ) {
                $category_default_price_revenue_model = (string) $laterpay_category_model->get_revenue_model_by_category_id( $post_default_category );
            }
        }

		// get price data
        $global_default_price               = get_option( 'laterpay_global_price' );
        $global_default_price_revenue_model = get_option( 'laterpay_global_price_revenue_model' );

        $price              = LaterPay_Helper_Pricing::get_post_price( $post->ID );
        $post_price_type    = LaterPay_Helper_Pricing::get_post_price_type( $post->ID );

        // set post revenue model according to the selected price type
        if ( $post_price_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) {
            $post_revenue_model = $category_default_price_revenue_model;
        } elseif ( $post_price_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE ) {
            $post_revenue_model = $global_default_price_revenue_model;
        }

        //set dynamic price data with defaults or values was already set
        $dynamic_pricing_data = LaterPay_Helper_Pricing::get_dynamic_prices($post);
		
        //set limits to use them with javascript
        $dynamic_pricing_limits = array(
            'price_revenue_ppu_min' => LaterPay_Helper_Pricing::price_revenue_ppu_min,
            'price_revenue_ppu_max' => LaterPay_Helper_Pricing::price_revenue_ppu_max,
            'price_revenue_ppusis_max' => LaterPay_Helper_Pricing::price_revenue_ppusis_max,
            'price_revenue_sis_min'=> LaterPay_Helper_Pricing::price_revenue_sis_min,
            'price_revenue_sis_max'=> LaterPay_Helper_Pricing::price_revenue_sis_max,
            'price_ppu_end'=> LaterPay_Helper_Pricing::price_ppu_end,
            'price_ppusis_end'=> LaterPay_Helper_Pricing::price_ppusis_end,
            'price_sis_end'=> LaterPay_Helper_Pricing::price_sis_end,
            'price_start_day'=> LaterPay_Helper_Pricing::price_start_day,
            'price_end_day'=> LaterPay_Helper_Pricing::price_end_day
        );

        echo '<input type="hidden" name="laterpay_pricing_post_content_box_nonce" value="' . wp_create_nonce( $this->config->plugin_base_name ) . '" />';

        $this->assign( 'laterpay_post_price_type',                      $post_price_type );
        $this->assign( 'laterpay_post_revenue_model',                   $post_revenue_model );
        $this->assign( 'laterpay_price',                                $price );
        $this->assign( 'laterpay_currency',                             get_option( 'laterpay_currency' ) );
        $this->assign( 'laterpay_category_prices',                      $category_price_data );
        $this->assign( 'laterpay_post_default_category',                (int) $post_default_category );
        $this->assign( 'laterpay_global_default_price',                 $global_default_price );
        $this->assign( 'laterpay_dynamic_pricing_data',                 json_encode( $dynamic_pricing_data ) );
		$this->assign( 'laterpay_dynamic_pricing_limits',               json_encode( $dynamic_pricing_limits ) );
        $this->assign( 'laterpay_global_default_price_revenue_model',   $global_default_price_revenue_model );
        $this->assign( 'laterpay_category_default_price_revenue_model', $category_default_price_revenue_model );

        $this->render( 'backend/partials/post_pricing_form' );
    }

    /**
     * Save laterpay post data.
     *
     * @wp-hook save_post, edit_attachments
     *
     * @param int $post_id
     *
     * @return void
     */
    public function save_laterpay_post_data( $post_id ) {

        if ( ! $this->has_permission( $post_id ) ) {
            return;
        }

        // no post found -> do nothing
        $post = get_post( $post_id );
        if ( $post === null ) {
            return;
        }

        $post_form = new LaterPay_Form_Post( $_POST );
        $condition = array(
            'verify_nonce' => array(
                'action' => $this->config->get( 'plugin_base_name' )
            )
        );
        $post_form->add_validation( 'laterpay_teaser_content_box_nonce', $condition );

        // nonce not valid -> do nothing
        if ( $post_form->is_valid() ) {

            // no rights to edit laterpay_edit_teaser_content -> do nothing
            if ( LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post_id ) ) {

                $teaser = $post_form->get_field_value( 'laterpay_post_teaser' );

                if ( $teaser ) {
                    $new_meta_value = wpautop( $teaser );
                } else {
                    $new_meta_value = LaterPay_Helper_String::truncate(
                        $post->post_content,
                        $this->config->get( 'content.auto_generated_teaser_content_word_count' ),
                        array (
                            'html'  => true,
                            'words' => true,
                        )
                    );
                }

                $this->set_post_meta(
                    'laterpay_post_teaser',
                    $new_meta_value,
                    $post_id
                );
            }

            // no rights to edit laterpay_edit_individual_price -> do nothing
            if ( LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post_id ) ) {

                // postmeta values array
                $meta_values = array();

                // apply global default price, if pricing type is not defined
                $post_price_type = $post_form->get_field_value( 'post_price_type' );
                $type = $post_price_type ? $post_price_type : LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;
                $meta_values[ 'type' ] = $type;

                // apply (static) individual price
                if ( $type === LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE && $post_form->get_field_value( 'post-price' ) ) {
                    $meta_values[ 'price' ] = $post_form->get_field_value( 'post-price' );
                    $meta_values[ 'revenue_model' ] = $post_form->get_field_value( 'post_revenue_model' );
                }

                // apply dynamic individual price
                if ( $type === LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) {
                    
					if ( isset( $_POST[ 'laterpay_start_price' ] ) && isset( $_POST[ 'laterpay_end_price' ] ) ){
                        list($meta_values[ 'start_price' ],$meta_values[ 'end_price' ]) = LaterPay_Helper_Pricing::adjust_dynamic_price_points( $_POST[ 'laterpay_start_price' ], $_POST[ 'laterpay_end_price' ] );
                    }

                    if ( $post_form->get_field_value( 'laterpay_change_start_price_after_days' ) ) {
                        $meta_values[ 'change_start_price_after_days' ] = $post_form->get_field_value( 'laterpay_change_start_price_after_days' );
                    }

                    if ( $post_form->get_field_value( 'laterpay_transitional_period_end_after_days' ) ) {
                        $meta_values[ 'transitional_period_end_after_days' ] = $post_form->get_field_value( 'laterpay_transitional_period_end_after_days' );
                    }

                    if ( $post_form->get_field_value( 'laterpay_reach_end_price_after_days' ) ) {
                        $meta_values[ 'reach_end_price_after_days' ] = $post_form->get_field_value( 'laterpay_reach_end_price_after_days' );
                    }
                }

                // apply category default price of given category
                if ( $type === LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) {
                    if ( $post_form->get_field_value( 'laterpay_post_default_category' ) ) {
                        $category_id = $post_form->get_field_value( 'laterpay_post_default_category' );
                        $meta_values[ 'category_id' ] = $category_id;
                    }
                }

                $this->set_post_meta(
                    'laterpay_post_prices',
                    $meta_values,
                    $post_id
                );
            }
        }
    }

    /**
     * Set post meta data.
     *
     * @param string  $name meta name
     * @param string  $meta_value new meta value
     * @param integer $post_id post id
     *
     * @return bool|int false failure, post_meta_id on insert / update, or true on success
     */
    public function set_post_meta( $name, $meta_value, $post_id ) {
        if ( empty( $meta_value ) ) {
            return delete_post_meta( $post_id, $name );
        } else {
            return update_post_meta( $post_id, $name, $meta_value );
        }
    }
	
}
