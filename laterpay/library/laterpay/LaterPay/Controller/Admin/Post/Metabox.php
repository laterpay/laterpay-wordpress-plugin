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
	 * @return  void
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
	 * @return  void
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
	 * @return  void
	 */
	public function add_meta_boxes() {

        $post_types = $this->config->get( 'content.allowed_post_types' );

        foreach( $post_types as $post_type ) {

            add_meta_box(
                'laterpay_post_teaser',
                __( 'Teaser Content', 'laterpay' ),
                array( $this, 'render_teaser_content_box' ),
                $post_type,
                'normal',
                'high'
            );

            add_meta_box( 
                'laterpay_post_pricing',
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
	 * @param   WP_Post $post
	 * @return  void
	 */
	public function render_teaser_content_box( $post ) {
		if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post ) ) {
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

		echo '<dfn>' . __("This is shown to visitors, who haven't purchased the article yet. Use an excerpt of the article that makes them want to buy the whole thing! ", 'laterpay' ) . '</dfn>';
		wp_editor( $content, $editor_id, $settings );
		echo '<input type="hidden" name="laterpay_teaser_content_box_nonce" value="' . wp_create_nonce( $this->config->get( 'plugin_base_name' ) ) . '" />';
	}

	/**
	 * Save teaser content.
	 *
	 * @wp-hook save_post
	 * @param   int $post_id
	 * @return  void
	 */
	public function save_teaser_content_box( $post_id ) {

        // nonce, not valid -> do nothing
        if ( ! isset( $_POST['laterpay_teaser_content_box_nonce'] ) || ! wp_verify_nonce( $_POST['laterpay_teaser_content_box_nonce'], $this->config->get( 'plugin_base_name' ) ) ) {
            return;
        }

        // Autosave -> do nothing
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Ajax -> do nothing
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        // no post found -> do nothing
        $post = get_post( $post_id );
        if( $post === null ){
            return;
        }

		// no rights to edit -> do nothing
		if ( ! LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post_id ) ) {
			return;
		}

		if( isset( $_POST['laterpay_post_teaser'] ) ) {
            $new_meta_value = $_POST['laterpay_post_teaser'];
        }
        else {
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
	 * @param   WP_Post $post
	 *
	 * @return  void
	 */
	public function render_post_pricing_form( $post ) {
		if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post ) ) {
			return;
		}

		$post_specific_price = get_post_meta( $post->ID, 'laterpay_post_pricing', true );

		/**
         * TODO: optimize the current approach:
         * If it's an existing value, if should have been saved with decimals,
         * so '0' is a crappy way of knowing that 'Pricing Post' has never been set
         */
		if ( $post_specific_price == 0 ) {
			$post_specific_price = null;
		} 
        else {
			$post_specific_price = (float) $post_specific_price;
		}

		// category default price data
		$category_price_data    = null;
		$post_default_category  = null;
		$category_default_price = null;
		$categories_of_post     = wp_get_post_categories( $post->ID );
		if ( ! empty( $categories_of_post ) ) {
			$laterpay_category_model  = new LaterPay_Model_Category();
			$category_price_data    = $laterpay_category_model->get_category_price_data_by_category_ids( $categories_of_post );
			$post_default_category  = (int) get_post_meta( $post->ID, 'laterpay_post_default_category', true );
			// if the post has a category defined from which to use the category default price then let's get that price
			if ( $post_default_category > 0 ) {
				$category_default_price = (float) $laterpay_category_model->get_price_by_category_id( $post_default_category );
			}
		}

		// global default price
		$global_default_price = get_option( 'laterpay_global_price' );

		/**
         * TODO: optimize the current approach:
         * If it's an existing value, if should have been saved with decimals,
         * so '0' is a crappy way of knowing that 'Pricing Post' has never been set
         */
		if ( $global_default_price == 0 ) {
			$global_default_price = null;
		}
        else {
			$global_default_price = (float) $global_default_price;
		}

		$post_price_type = get_post_meta( $post->ID, 'laterpay_post_pricing_type', true );
		switch ( $post_price_type ) {
			// backwards compatibility: Pricing Post Type used to be stored as 0 or 1; TODO: remove with release 1.0
			case '0':
			case '1':
			case 'individual price':
				$price = $post_specific_price;
				break;

			case 'individual price, dynamic':
				// current price
				$price = LaterPay_Helper_Pricing::get_dynamic_price( $GLOBALS['post'] );
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
		}
        elseif ( get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) == 0 ) {
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
		}
        else {
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

		echo '<input type="hidden" name="laterpay_pricing_post_content_box_nonce" value="' . wp_create_nonce( $this->config->plugin_base_name ) . '" />';

		$this->assign( 'laterpay_post_price_type',       $post_price_type );
		$this->assign( 'laterpay_price',                 $price );
		$this->assign( 'laterpay_currency',              get_option( 'laterpay_currency' ) );
		$this->assign( 'laterpay_category_prices',       $category_price_data );
		$this->assign( 'laterpay_post_default_category', (int) $post_default_category );
		$this->assign( 'laterpay_global_default_price',  $global_default_price );
		$this->assign( 'laterpay_dynamic_pricing_data',  json_encode( $dynamic_pricing_data ) );

		$this->render( 'backend/partials/post/pricing/form' );

	}

	/**
	 * Save pricing of post.
	 *
	 * @wp-hook save_post
	 *
	 * @param   int $post_id
	 * @return  void
	 */
	public function save_post_pricing_form( $post_id ) {

        // nonce, not valid -> do nothing
        if ( ! isset( $_POST['laterpay_pricing_post_content_box_nonce'] ) || ! wp_verify_nonce( $_POST['laterpay_pricing_post_content_box_nonce'], $this->config->plugin_base_name ) ) {
			return;
		}

        // check for required privileges to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post_id ) ) {
            return;
        }

        // Autosave -> do nothing
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Ajax -> do nothing
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

		$delocalized_price = (float) str_replace( ',', '.', $_POST['post-price'] );

		$this->set_post_meta(
            'laterpay_post_pricing',
			$delocalized_price,
			$post_id
		);

		$this->set_post_meta(
            'laterpay_post_pricing_type',
			stripslashes( $_POST['post_price_type'] ),
			$post_id
		);

		$this->set_post_meta(
            'laterpay_post_default_category',
			stripslashes( $_POST['laterpay_post_default_category'] ),
			$post_id
		);

        $this->set_post_meta(
            'laterpay_start_price',
			stripslashes( $_POST['laterpay_start_price'] ),
			$post_id
		);

		$this->set_post_meta(
            'laterpay_end_price',
			stripslashes( $_POST['laterpay_end_price'] ),
			$post_id
		);

		$this->set_post_meta(
            'laterpay_change_start_price_after_days',
			absint( $_POST['laterpay_change_start_price_after_days'] ),
			$post_id
		);

		$this->set_post_meta(
            'laterpay_transitional_period_end_after_days',
            absint( $_POST['laterpay_transitional_period_end_after_days'] ),
			$post_id

		);

		$this->set_post_meta(
            'laterpay_reach_end_price_after_days',
            absint( $_POST['laterpay_reach_end_price_after_days'] ),
			$post_id
		);

	}

	/**
	 * Set post meta data.
	 *
     * @param   string  $name           meta name
	 * @param   string  $new_meta_value new meta value
	 * @param   integer $post_id        post id
	 *
	 * @return  bool|int false failure, post_meta_id on insert/update or true on success
	 */
	public function set_post_meta( $name, $new_meta_value, $post_id ) {
        if ( $new_meta_value === '' ) {
			return delete_post_meta( $post_id, $name );
		}
        else {
            return update_post_meta( $post_id, $name, $new_meta_value );
		}
	}

}
