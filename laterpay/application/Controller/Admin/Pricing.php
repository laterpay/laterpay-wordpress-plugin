<?php

class LaterPay_Controller_Admin_Pricing extends LaterPay_Controller_Abstract
{

    /**
     * @see LaterPay_Controller_Abstract::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific CSS
        wp_register_style(
            'laterpay-select2',
            $this->config->get( 'css_url' ) . 'vendor/select2.min.css',
            array(),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'laterpay-select2' );

        // load page-specific JS
        wp_register_script(
            'laterpay-select2',
            $this->config->get( 'js_url' ) . 'vendor/select2.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-backend-pricing',
            $this->config->get( 'js_url' ) . 'laterpay-backend-pricing.js',
            array( 'jquery', 'laterpay-select2' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'laterpay-select2' );
        wp_enqueue_script( 'laterpay-backend-pricing' );

        // translations for bulk price editor
        $i18n = array(
            'to'                        => __( 'to', 'laterpay' ),
            'by'                        => __( 'by', 'laterpay' ),
            'toGlobalDefaultPrice'      => __( 'to global default price of', 'laterpay' ),
            'toCategoryDefaultPrice'    => __( 'to category default price of', 'laterpay' ),
        );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-pricing',
            'lpVars',
            array(
                'locale'                => get_locale(),
                'i18n'                  => $i18n,
                'globalDefaultPrice'    => LaterPay_Helper_View::format_number( (float) get_option( 'laterpay_global_price' ), 2 ),
                'defaultCurrency'       => get_option( 'laterpay_currency' ),
                'inCategoryLabel'       => __( 'All posts in category', 'laterpay' ),
            )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        $category_price_model           = new LaterPay_Model_CategoryPrice();
        $categories_with_defined_price  = $category_price_model->get_categories_with_defined_price();

        $bulk_actions = array(
            'set'      => __( 'Set price of', 'laterpay' ),
            'increase' => __( 'Increase price of', 'laterpay' ),
            'reduce'   => __( 'Reduce price of', 'laterpay' ),
            'free'     => __( 'Make free', 'laterpay' ),
            'reset'    => __( 'Reset', 'laterpay'),
        );
        $bulk_selectors = array(
            'all'      => __( 'All posts', 'laterpay' ),
        );
        $bulk_categories            = get_categories();
        $bulk_categories_with_price = LaterPay_Helper_Pricing::get_categories_with_price( $bulk_categories );

        $this->assign( 'categories_with_defined_price',         $categories_with_defined_price );
        $this->assign( 'standard_currency',                     get_option( 'laterpay_currency' ) );
        $this->assign( 'plugin_is_in_live_mode',                $this->config->get( 'is_in_live_mode' ) );
        $this->assign( 'global_default_price',                  LaterPay_Helper_View::format_number( (float) get_option( 'laterpay_global_price' ), 2 ) );
        $this->assign( 'global_default_price_revenue_model',    get_option( 'laterpay_global_price_revenue_model' ) );
        $this->assign( 'top_nav',                               $this->get_menu() );
        $this->assign( 'admin_menu',                            LaterPay_Helper_View::get_admin_menu() );
        $this->assign( 'bulk_actions',                          $bulk_actions );
        $this->assign( 'bulk_selectors',                        $bulk_selectors );
        $this->assign( 'bulk_categories',                       $bulk_categories );
        $this->assign( 'bulk_categories_with_price',            $bulk_categories_with_price );

        $this->render( 'backend/pricing' );
    }

    /**
     * Process Ajax requests from pricing tab.
     *
     * @return void
     */
    public function process_ajax_requests() {
        // save changes in submitted form
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
            switch ( $_POST['form'] ) {
                case 'currency_form':
                    $this->update_currency();
                    break;

                case 'global_price_form':
                    $this->update_global_default_price();
                    break;

                case 'price_category_form':
                    $this->set_category_default_price();
                    break;

                case 'price_category_form_delete':
                    $this->delete_category_default_price();
                    break;

                case 'laterpay_get_category_prices':
                    if ( ! array_key_exists( 'category_ids', $_POST ) ) {
                        $_POST[ 'category_ids' ] = array();
                    }
                    $this->get_category_prices( $_POST['category_ids'] );
                    break;

                case 'bulk_price_form':
                    $this->change_posts_price();
                    break;

                case 'reset_post_publication_date':
                    if ( ! empty( $_POST['post_id'] ) ) {
                        $post = get_post( $_POST['post_id'] );
                        if ( $post != null ) {
                            LaterPay_Helper_Pricing::reset_post_publication_date( $post );
                            wp_send_json(
                                array(
                                    'success' => true,
                                )
                            );

                            return;
                        }
                    }
                    break;

                default:
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
            }
        }

        // return categories that match a given search term
        if ( isset( $_GET['term'] ) ) {
            $category_price_model = new LaterPay_Model_CategoryPrice();
            $args = array();

            if ( ! empty( $_GET['term'] ) ) {
                $args['name__like'] = $_GET['term'];
            }

            wp_send_json(
               $category_price_model->get_categories_without_price_by_term( $args )
            );
            die;
        }

        // invalid request
        wp_send_json(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
            )
        );
        die;
    }

    /**
     * Update the currency used for all prices.
     *
     * @return void
     */
    protected function update_currency() {
        $currency_form = new LaterPay_Form_Currency();

        if ( ! $currency_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
                )
            );
        }

        update_option( 'laterpay_currency', $currency_form->get_field_value( 'laterpay_currency' ) );

        wp_send_json(
            array(
                'success'           => true,
                'laterpay_currency' => get_option( 'laterpay_currency' ),
                'message'           => sprintf(
                                            __( 'The currency for this website is %s now.', 'laterpay' ),
                                            get_option( 'laterpay_currency' )
                                        )
            )
        );
    }

    /**
     * Update the global price, which by default is applied to all posts.
     *
     * @return void
     */
    protected function update_global_default_price() {
        $global_price_form = new LaterPay_Form_GlobalPrice();

        if ( ! $global_price_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success'                       => false,
                    'laterpay_global_price'         => get_option( 'laterpay_global_price' ),
                    'laterpay_price_revenue_model'  => get_option( 'laterpay_global_price_revenue_model' ),
                    'message'                       => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-149.99.', 'laterpay' )
                )
            );
        }

        $delocalized_global_price   = $global_price_form->get_field_value( 'laterpay_global_price' );
        $global_price_revenue_model = $global_price_form->get_field_value( 'laterpay_global_price_revenue_model' );

        update_option( 'laterpay_global_price', $delocalized_global_price );
        update_option( 'laterpay_global_price_revenue_model', $global_price_revenue_model );

        $global_price           = (float) get_option( 'laterpay_global_price' );
        $localized_global_price = LaterPay_Helper_View::format_number( $global_price, 2 );
        $currency_model         = new LaterPay_Model_Currency();
        $currency_name          = $currency_model->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );

        if ( $global_price == 0 ) {
            $message = __( 'All posts are free by default now.', 'laterpay' );
        } else {
            $message = sprintf(
                            __( 'The global default price for all posts is %s %s now.', 'laterpay' ),
                            $localized_global_price,
                            $currency_name
                        );
        }

        wp_send_json(
            array(
                'success'                       => true,
                'laterpay_global_price'         => $localized_global_price,
                'laterpay_price_revenue_model'  => $global_price_revenue_model,
                'message'                       => $message,
            )
        );
    }

    /**
     * Set the category price, if a given category does not have a category price yet.
     *
     * @return void
     */
    protected function set_category_default_price() {

        $price_category_form = new LaterPay_Form_PriceCategory();

        if ( ! $price_category_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-149.99.', 'laterpay' )
                )
            );
        }

        $post_category_id               = $price_category_form->get_field_value( 'category_id' );
        $category                       = $price_category_form->get_field_value( 'category' );
        $term                           = get_term_by( 'name', $category, 'category' );
        $category_price_revenue_model   = $price_category_form->get_field_value( 'laterpay_category_price_revenue_model' );
        $updated_post_ids               = null;

        if ( ! $term ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                )
            );
        }

        $category_id                  = $term->term_id;
        $category_price_model         = new LaterPay_Model_CategoryPrice();
        $category_price_id            = $category_price_model->get_price_id_by_category_id( $category_id );
        $delocalized_category_price   = $price_category_form->get_field_value( 'price' );

        if ( empty( $category_id ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' )
                )
            );
        }

        if ( ! $post_category_id ) {
            $category_price_model->set_category_price( $category_id, $delocalized_category_price, $category_price_revenue_model );
            $updated_post_ids = LaterPay_Helper_Pricing::apply_category_price_to_posts_with_global_price( $category_id );
        } else {
            $category_price_model->set_category_price( $category_id, $delocalized_category_price, $category_price_revenue_model, $category_price_id );
        }

        $currency_model             = new LaterPay_Model_Currency();
        $currency_name              = $currency_model->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );
        $localized_category_price   = LaterPay_Helper_View::format_number( $delocalized_category_price, 2 );

        wp_send_json(
            array(
                'success'           => true,
                'category'          => $category,
                'price'             => $localized_category_price,
                'currency'          => get_option( 'laterpay_currency' ),
                'category_id'       => $category_id,
                'revenue_model'     => $category_price_revenue_model,
                'updated_post_ids'  => $updated_post_ids,
                'message'           => sprintf(
                                            __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                                            $category,
                                            $localized_category_price,
                                            $currency_name
                                        ),
            )
        );
    }

    /**
     * Delete the category price for a given category.
     *
     * @return void
     */
    protected function delete_category_default_price() {
        $price_category_delete_form = new LaterPay_Form_PriceCategory();

        if ( ! $price_category_delete_form->is_valid( $_POST ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'Error occurred. Incorrect data provided.', 'laterpay' )
                )
            );
        }

        $category_id = $price_category_delete_form->get_field_value( 'category_id' );

        // delete the category_price
        $category_price_model   = new LaterPay_Model_CategoryPrice();
        $success                = $category_price_model->delete_prices_by_category_id( $category_id );

        if ( ! $success ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                )
            );
        }

        // get all posts with the deleted $category_id and loop through them
        $post_ids = LaterPay_Helper_Pricing::get_post_ids_with_price_by_category_id( $category_id );
        foreach ( $post_ids as $post_id ) {
            // check, if the post has LaterPay pricing data
            $post_price = get_post_meta( $post_id, 'laterpay_post_prices', true );
            if ( ! is_array( $post_price ) ) {
                continue;
            }

            // check, if the post uses a category default price
            if ( $post_price[ 'type' ] !== LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) {
                continue;
            }

            // check, if the post has the deleted category_id as category default price
            if ( (int) $post_price[ 'category_id' ] !== $category_id ) {
                continue;
            }

            // get all post_categories
            $post_categories = wp_get_post_categories( $post_id );

            if ( empty( $post_categories ) ) {
                // apply the global default price as new price, if no other post categories are found
                LaterPay_Helper_Pricing::apply_global_default_price_to_post( $post_id );
            } else {
                // load all category prices by the given category_ids
                $laterpay_category_model    = new LaterPay_Model_CategoryPrice();
                $category_price_data        = $laterpay_category_model->get_category_price_data_by_category_ids( $post_categories );

                if ( count( $category_price_data ) < 1 ) {
                    // no other category prices found for this post
                    LaterPay_Helper_Pricing::apply_global_default_price_to_post( $post_id );
                } else {
                    // find the category with the highest price and assign its category_id to the post
                    $price = 0;
                    foreach ( $category_price_data as $data ) {
                        if ( $data->category_price > $price ) {
                            $price          = $data->category_price;
                            $category_id    = $data->category_id;
                        }
                    }

                    LaterPay_Helper_Pricing::apply_category_default_price_to_post( $post_id, $category_id, true );
                }
            }
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => sprintf(
                                __( 'The default price for category %s was deleted.', 'laterpay' ),
                                $price_category_delete_form->get_field_value( 'category' )
                            ),
            )
        );
    }

    /**
     * Process Ajax requests for prices of applied categories.
     *
     * @param array $category_ids
     *
     * @return void
     */
    protected function get_category_prices( $category_ids ) {
        $categories_price_data = array();

        if ( is_array( $category_ids ) && count( $category_ids ) > 0 ) {
            $category_price_model   = new LaterPay_Model_CategoryPrice();
            $categories_price_data  = $category_price_model->get_category_price_data_by_category_ids( $category_ids );
        }

        wp_send_json( $categories_price_data );
    }

    /**
     * Update post prices in bulk.
     *
     * This function does not change the price type of a post.
     * It gets the price type of each post to be updated and updates the associated individual price, category default
     * price, or global default price.
     * It also ensures that the resulting price and revenue model is valid.
     *
     * @return void
     */
    protected function change_posts_price() {
        $bulk_price_form = new LaterPay_Form_BulkPrice( $_POST );

        if ( $bulk_price_form->is_valid() ) {
            // get scope of posts to be processed from selector
            $posts                = null;
            $category_price_model = new LaterPay_Model_CategoryPrice();
            $selector             = $bulk_price_form->get_field_value( 'bulk_selector' );
            $action               = $bulk_price_form->get_field_value( 'bulk_action' );
            $change_unit          = $bulk_price_form->get_field_value( 'bulk_change_unit' );
            $price                = $bulk_price_form->get_field_value( 'bulk_price' );
            $is_percent           = ( $change_unit == 'percent' );
            $default_currency     = get_option( 'laterpay_currency' );
            $update_all           = ( $selector === 'all');
            $category_id          = null;
            // flash message parts
            $message_parts        = array(
                                      'all'         => __( 'The prices of all posts', 'laterpay' ),
                                      'category'    => '',
                                      'have_been'   => __( 'have been', 'laterpay' ),
                                      'action'      => __( 'set', 'laterpay' ),
                                      'preposition' => __( 'to', 'laterpay' ),
                                      'amount'      => '',
                                      'unit'        => '',
                                  );

            if ( ! $update_all ) {
                $category_id = $bulk_price_form->get_field_value( 'bulk_category' );

                if ( $category_id === null ) {
                    $category_id = $bulk_price_form->get_field_value( 'bulk_category_with_price' );
                }

                if ( $category_id !== null) {
                    $category_name             = get_the_category_by_ID( $category_id );
                    $posts                     = LaterPay_Helper_Pricing::get_post_ids_with_price_by_category_id( $category_id );
                    $message_parts['category'] = sprintf( __( '%s \'%s\'', 'laterpay' ), str_replace( '_', ' ', $selector ), $category_name );
                }
            } else {
                $posts = LaterPay_Helper_Pricing::get_all_posts_with_price();
            }

            $price     = ( $price === null ) ? 0 : $price;
            $new_price = LaterPay_Helper_Pricing::ensure_valid_price( $price );

            // pre-post-processing actions - correct global and categories default prices, set flash message parts
            // run exactly once, independent of actual number of posts
            switch ( $action ) {
                case 'set':
                    $this->update_global_and_categories_prices_with_new_price( $new_price );
                    // set flash message parts
                    $message_parts['action']        = __( 'set', 'laterpay' );
                    $message_parts['preposition']   = __( 'to', 'laterpay' );
                    $message_parts['amount']        = LaterPay_Helper_View::format_number( LaterPay_Helper_Pricing::ensure_valid_price( $new_price ), 2 );
                    $message_parts['unit']          = $default_currency;
                    break;

                case 'increase':
                case 'reduce':
                    $is_reduction                   = ( $action === 'reduce' );

                    // process global price
                    $global_price                   = get_option( 'laterpay_global_price' );
                    $change_amount                  = $is_percent ? $global_price * $price / 100 : $price;
                    $new_price                      = $is_reduction ? $global_price - $change_amount : $global_price + $change_amount;
                    $global_price_revenue           = LaterPay_Helper_Pricing::ensure_valid_revenue_model( get_option( 'laterpay_global_price_revenue_model' ), $new_price );
                    update_option( 'laterpay_global_price', LaterPay_Helper_Pricing::ensure_valid_price( $new_price ) );
                    update_option( 'laterpay_global_price_revenue_model', $global_price_revenue );

                    // process category default prices
                    $categories                     = $category_price_model->get_categories_with_defined_price();
                    if ( $categories ) {
                        foreach ( $categories as $category ) {
                            $change_amount          = $is_percent ? $category->category_price * $price / 100 : $price;
                            $new_price              = $is_reduction ? $category->category_price - $change_amount : $category->category_price + $change_amount;
                            $new_price              = LaterPay_Helper_Pricing::ensure_valid_price( $new_price );
                            $revenue_model          = LaterPay_Helper_Pricing::ensure_valid_revenue_model( $category->revenue_model, $new_price );
                            $category_price_model->set_category_price( $category->category_id, $new_price, $revenue_model, $category->id );
                        }
                    }

                    // set flash message parts
                    $message_parts['action']        = $is_reduction ? __( 'decreased', 'laterpay' ) : __( 'increased', 'laterpay' );
                    $message_parts['preposition']   = __( 'by', 'laterpay' );
                    $message_parts['amount']        = $is_percent ? $price : LaterPay_Helper_View::format_number( $price, 2 );
                    $message_parts['unit']          = $is_percent ? '%' : $change_unit;
                    break;

                case 'free':
                    if ( ! $update_all && $category_id !== null ) {
                        $category_price_id = $category_price_model->get_price_id_by_category_id( $category_id );
                        $category_price_model->set_category_price( $category_id, $new_price, 'ppu', $category_price_id );
                    } elseif ( $update_all ) {
                        $this->update_global_and_categories_prices_with_new_price( $new_price );
                    }
                    $message_parts['all']           = __( 'All posts', 'laterpay' );
                    $message_parts['action']        = __( 'made free', 'laterpay' );
                    $message_parts['preposition']   = '';
                    break;

                case 'reset':
                    $message_parts['action']            = __( 'reset', 'laterpay' );
                    if ( $update_all ) {
                        $category_price_model->delete_all_category_prices();
                        $new_price                      = get_option( 'laterpay_global_price' );
                        // set flash message parts
                        $message_parts['preposition']   = __( 'to global default price of', 'laterpay' );
                        $message_parts['amount']        = LaterPay_Helper_View::format_number( $new_price, 2 );
                        $message_parts['unit']          = $default_currency;
                    } else {
                        $new_price                      = $category_price_model->get_price_by_category_id( $category_id );
                        // set flash message parts
                        $message_parts['preposition']   = __( 'to category default price of', 'laterpay' );
                        $message_parts['amount']        = LaterPay_Helper_View::format_number( $new_price, 2 );
                        $message_parts['unit']          = $default_currency;
                    }
                    break;

                default:
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                    break;
            }

            // update post prices
            if ( $posts ) {
                foreach ( $posts as $post ) {
                    $post_id                = is_int( $post ) ? $post : $post->ID;
                    $post_meta              = get_post_meta( $post_id, 'laterpay_post_prices', true );
                    $meta_values            = $post_meta ? $post_meta : array();

                    $current_revenue_model  = isset( $meta_values['revenue_model'] ) ? $meta_values['revenue_model'] : 'ppu';
                    $current_post_price     = LaterPay_Helper_Pricing::get_post_price( $post_id );
                    $current_post_type      = LaterPay_Helper_Pricing::get_post_price_type( $post_id );
                    $post_type_is_global    = ( $current_post_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE );
                    $post_type_is_category  = ( $current_post_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE );
                    $is_individual          = ( ! $post_type_is_global && ! $post_type_is_category );

                    $new_price              = LaterPay_Helper_Pricing::ensure_valid_price( $price );

                    switch ( $action ) {
                        case 'increase':
                        case 'reduce':
                            if ( $is_individual ) {
                                $is_reduction   = ( $action === 'reduce' );
                                $change_amount  = $is_percent ? $current_post_price * $price / 100 : $price;
                                $new_price      = $is_reduction ? $current_post_price - $change_amount : $current_post_price + $change_amount;
                            }
                            break;

                        case 'free':
                            if ( ! $update_all && ! $is_individual ) {
                                $meta_values['type']        = LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE;
                                $meta_values['category_id'] = $category_id;
                                $new_price                  = $category_price_model->get_price_by_category_id( $category_id );
                            }
                            break;

                        case 'reset':
                            if ( $update_all ) {
                                $meta_values['type']        = LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;
                                $new_price                  = get_option( 'laterpay_global_price' );
                            } else {
                                $meta_values['type']        = LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE;
                                $meta_values['category_id'] = $category_id;
                                $new_price                  = $category_price_model->get_price_by_category_id( $category_id );
                            }
                            break;

                        default:
                            break;
                    }

                    // make sure the price is within the allowed range
                    $meta_values['price']           = LaterPay_Helper_Pricing::ensure_valid_price( $new_price );
                    // adjust revenue model to new price, if required
                    $meta_values['revenue_model']   = LaterPay_Helper_Pricing::ensure_valid_revenue_model(
                                                            $current_revenue_model,
                                                            $meta_values['price']
                                                      );

                    // save updated pricing data
                    update_post_meta(
                        $post_id,
                        'laterpay_post_prices',
                        $meta_values
                    );
                }
            }

            // render flash message
            wp_send_json(
                array(
                    'success' => true,
                    'message' => trim( preg_replace( '/\s+/', ' ', join( ' ', $message_parts ) ) ) . '.',
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
            )
        );
    }

    /**
     * Update global and categories prices with new price
     *
     * @param array $price
     *
     * @return void
     */
    protected function update_global_and_categories_prices_with_new_price( $price ) {
        $global_revenue_model = LaterPay_Helper_Pricing::ensure_valid_revenue_model( get_option( 'laterpay_global_price_revenue_model' ), $price );
        update_option( 'laterpay_global_price', $price );
        update_option( 'laterpay_global_price_revenue_model', $global_revenue_model );

        // update all category prices
        $category_price_model = new LaterPay_Model_CategoryPrice();
        $categories           = $category_price_model->get_categories_with_defined_price();
        $revenue_model        = LaterPay_Helper_Pricing::ensure_valid_revenue_model( 'ppu', $price );
        if ( $categories ) {
            foreach ( $categories as $category ) {
                $category_price_model->set_category_price( $category->category_id, $price, $revenue_model, $category->id );
            }
        }
    }
}
