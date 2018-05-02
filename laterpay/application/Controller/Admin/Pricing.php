<?php

/**
 * LaterPay pricing controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Pricing extends LaterPay_Controller_Admin_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_laterpay_pricing' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'process_ajax_requests' ),
                array( 'laterpay_on_ajax_user_can_activate_plugins', 200 ),
            ),
            'wp_ajax_laterpay_get_category_prices' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'process_ajax_requests' ),
                array( 'laterpay_on_ajax_user_can_activate_plugins', 200 ),
            ),
            'laterpay_register_passes_cpt' => array(
                array( 'register_passes_cpt' ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

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

        // translations
        $i18n = array(
            // bulk price editor
            'after'                     => __( 'After', 'laterpay' ),
            'make'                      => __( 'Make', 'laterpay' ),
            'free'                      => __( 'free', 'laterpay' ),
            'to'                        => __( 'to', 'laterpay' ),
            'by'                        => __( 'by', 'laterpay' ),
            'toGlobalDefaultPrice'      => __( 'to global default price of', 'laterpay' ),
            'toCategoryDefaultPrice'    => __( 'to category default price of', 'laterpay' ),
            'updatePrices'              => __( 'Update Prices', 'laterpay' ),
            'delete'                    => __( 'Delete', 'laterpay' ),
            // time pass editor
            'confirmDeleteTimepass'     => __( 'Are you sure?', 'laterpay' ),
            'confirmDeleteSubscription' => __( 'Do you really want to discontinue this subscription? If you delete it, it will continue to renew for users who have an active subscription until the user cancels it. Existing subscribers will still have access to the content in their subscription. New users won\'t be able to buy the subscription anymore. Do you want to delete this subscription?', 'laterpay' ),
            'voucherText'               => __( 'reduces the price to', 'laterpay' ),
            'timesRedeemed'             => __( 'times redeemed.', 'laterpay' ),
        );

        // pass localized strings and variables to script
        // time pass with vouchers
        $time_passes_model   = LaterPay_Model_TimePassWP::get_instance();
        $time_passes_list    = $time_passes_model->get_active_time_passes();
        $vouchers_list       = LaterPay_Helper_Voucher::get_all_vouchers();
        $vouchers_statistic  = LaterPay_Helper_Voucher::get_all_vouchers_statistic();

        // subscriptions
        $subscriptions_model = new LaterPay_Model_Subscription();
        $subscriptions_list  = $subscriptions_model->get_active_subscriptions();

        wp_localize_script(
            'laterpay-backend-pricing',
            'lpVars',
            array(
                'locale'                => get_locale(),
                'i18n'                  => $i18n,
                'currency'              => wp_json_encode( LaterPay_Helper_Config::get_currency_config() ),
                'globalDefaultPrice'    => LaterPay_Helper_View::format_number( get_option( 'laterpay_global_price' ) ),
                'inCategoryLabel'       => __( 'All posts in category', 'laterpay' ),
                'time_passes_list'      => $this->get_time_passes_json( $time_passes_list ),
                'subscriptions_list'    => $this->get_subscriptions_json( $subscriptions_list ),
                'vouchers_list'         => wp_json_encode( $vouchers_list ),
                'vouchers_statistic'    => wp_json_encode( $vouchers_statistic ),
                'l10n_print_after'      => 'lpVars.currency = JSON.parse(lpVars.currency);
                                            lpVars.time_passes_list = JSON.parse(lpVars.time_passes_list);
                                            lpVars.subscriptions_list = JSON.parse(lpVars.subscriptions_list);
                                            lpVars.vouchers_list = JSON.parse(lpVars.vouchers_list);
                                            lpVars.vouchers_statistic = JSON.parse(lpVars.vouchers_statistic);',
            )
        );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();
        $category_price_model          = LaterPay_Model_CategoryPriceWP::get_instance();
        $categories_with_defined_price = $category_price_model->get_categories_with_defined_price();

        // time passes and vouchers data
        $time_passes_model              = LaterPay_Model_TimePassWP::get_instance();
        $time_passes_list               = $time_passes_model->get_active_time_passes();
        $vouchers_list                  = LaterPay_Helper_Voucher::get_all_vouchers();
        $vouchers_statistic             = LaterPay_Helper_Voucher::get_all_vouchers_statistic();

        // subscriptions data
        $subscriptions_model            = new LaterPay_Model_Subscription();
        $subscriptions_list             = $subscriptions_model->get_active_subscriptions();

        // bulk price editor data
        $bulk_actions = array(
            'set'      => __( 'Set price of', 'laterpay' ),
            'increase' => __( 'Increase price of', 'laterpay' ),
            'reduce'   => __( 'Reduce price of', 'laterpay' ),
            'free'     => __( 'Make free', 'laterpay' ),
            'reset'    => __( 'Reset', 'laterpay' ),
        );

        $bulk_selectors = array(
            'all'      => __( 'All posts', 'laterpay' ),
        );

        $bulk_categories            = get_categories();
        $bulk_categories_with_price = LaterPay_Helper_Pricing::get_categories_with_price( $bulk_categories );
        $bulk_saved_operations      = LaterPay_Helper_Pricing::get_bulk_operations();

        $view_args = array(
            'top_nav'                               => $this->get_menu(),
            'admin_menu'                            => LaterPay_Helper_View::get_admin_menu(),
            'categories_with_defined_price'         => $categories_with_defined_price,
            'currency'                              => LaterPay_Helper_Config::get_currency_config(),
            'plugin_is_in_live_mode'                => $this->config->get( 'is_in_live_mode' ),
            'global_default_price'                  => get_option( 'laterpay_global_price' ),
            'global_default_price_revenue_model'    => get_option( 'laterpay_global_price_revenue_model' ),
            'passes_list'                           => $time_passes_list,
            'vouchers_list'                         => $vouchers_list,
            'vouchers_statistic'                    => $vouchers_statistic,
            'subscriptions_list'                    => $subscriptions_list,
            'bulk_actions'                          => $bulk_actions,
            'bulk_selectors'                        => $bulk_selectors,
            'bulk_categories'                       => $bulk_categories,
            'bulk_categories_with_price'            => $bulk_categories_with_price,
            'bulk_saved_operations'                 => $bulk_saved_operations,
            'landing_page'                          => get_option( 'laterpay_landing_page' ),
            'only_time_pass_purchases_allowed'      => get_option( 'laterpay_only_time_pass_purchases_allowed' ),
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( 'backend/pricing' );
    }

    /**
     * Process Ajax requests from pricing tab.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     *
     * @return void
     */
    public function process_ajax_requests( LaterPay_Core_Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );

        if ( ! isset( $_POST['form'] ) && ! wp_verify_nonce( $retrieved_nonce, 'laterpay_form' ) ) { // WPCS: input var ok.
            // invalid request
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'form' );
        }

        // save changes in submitted form
        $submitted_form_value = filter_input( INPUT_POST, 'form', FILTER_SANITIZE_STRING );
        switch ( $submitted_form_value ) {
            case 'global_price_form':
                $this->update_global_default_price( $event );
                break;

            case 'price_category_form':
                $this->set_category_default_price( $event );
                break;

            case 'price_category_form_delete':
                $this->delete_category_default_price( $event );
                break;

            case 'laterpay_get_category_prices':
                $category_ids = filter_input( INPUT_POST, 'category_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
                if ( null === $category_ids || ! is_array( $category_ids ) ) {
                    $category_ids = array();
                }

                $categories   = array_map( 'absint', $category_ids );

                $event->set_result( array(
                    'success' => true,
                    'prices'  => $this->get_category_prices( $categories ),
                ));
                break;

            case 'bulk_price_form':
                $this->change_posts_price( $event );
                break;

            case 'bulk_price_form_save':
                $this->save_bulk_operation( $event );
                break;

            case 'bulk_price_form_delete':
                $this->delete_bulk_operation( $event );
                break;

            case 'time_pass_form_save':
                $this->time_pass_save( $event );
                break;

            case 'time_pass_delete':
                $this->time_pass_delete( $event );
                break;

            case 'subscription_form_save':
                $this->subscription_form_save( $event );
                break;

            case 'subscription_delete':
                $this->subscription_delete( $event );
                break;

            case 'generate_voucher_code':
                $this->generate_voucher_code( $event );
                break;

            case 'save_landing_page':
                $this->save_landing_page( $event );
                break;

            case 'laterpay_get_categories_with_price':
                $post_term = filter_input( INPUT_POST, 'term', FILTER_SANITIZE_STRING );
                if ( null === $post_term ) {
                    throw new LaterPay_Core_Exception_InvalidIncomingData( 'term' );
                }

                // return categories that match a given search term
                $category_price_model = LaterPay_Model_CategoryPriceWP::get_instance();
                $args                 = array();
                if ( ! empty( $post_term ) ) {
                    $args['name__like'] = $post_term;
                }
                $event->set_result( array(
                    'success'    => true,
                    'categories' => $category_price_model->get_categories_without_price_by_term( $args ),
                ));
                break;

            case 'laterpay_get_categories':
                // return categories
                $args = array(
                    'hide_empty' => false,
                );
                $post_term = filter_input( INPUT_POST, 'term', FILTER_SANITIZE_STRING );
                if ( null !== $post_term && ! empty( $post_term ) ) {
                    $args['name__like'] = $post_term;
                }

                $event->set_result( array(
                    'success'    => true,
                    'categories' => get_categories( $args ),
                ));
                break;

            case 'change_purchase_mode_form':
                $this->change_purchase_mode( $event );
                break;

            default:
                break;
        }
    }

    /**
     * Update the global price.
     * The global price is applied to every posts by default, if
     * - it is > 0 and
     * - there isn't a more specific price for a given post.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function update_global_default_price( LaterPay_Core_Event $event ) {
        $global_price_form = new LaterPay_Form_GlobalPrice();

        if ( ! $global_price_form->is_valid( $_POST ) ) { // phpcs:ignore
            $event->set_result(
                array(
                    'success'       => false,
                    'price'         => get_option( 'laterpay_global_price' ),
                    'revenue_model' => get_option( 'laterpay_global_price_revenue_model' ),
                    'message'       => __( 'An error occurred. Incorrect data provided.', 'laterpay' ),
                )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $global_price_form ), $global_price_form->get_errors() );
        }

        $delocalized_global_price   = $global_price_form->get_field_value( 'laterpay_global_price' );
        $global_price_revenue_model = $global_price_form->get_field_value( 'laterpay_global_price_revenue_model' );
        $localized_global_price     = LaterPay_Helper_View::format_number( $delocalized_global_price );

        update_option( 'laterpay_global_price', $delocalized_global_price );
        update_option( 'laterpay_global_price_revenue_model', $global_price_revenue_model );

        if ( ! get_option( 'laterpay_global_price' ) ) {
            $message = __( 'All posts are free by default now.', 'laterpay' );
        } else {
            $message = sprintf(
                __( 'The global default price for all posts is %s %s now.', 'laterpay' ),
                $localized_global_price,
                $this->config->get( 'currency.code' )
            );
        }

        $event->set_result(
            array(
                'success'             => true,
                'price'               => number_format( $delocalized_global_price, 2, '.', '' ),
                'localized_price'     => $localized_global_price,
                'revenue_model'       => $global_price_revenue_model,
                'revenue_model_label' => LaterPay_Helper_Pricing::get_revenue_label( $global_price_revenue_model ),
                'message'             => $message,
            )
        );
    }

    /**
     * Set the category price, if a given category does not have a category price yet.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function set_category_default_price( LaterPay_Core_Event $event ) {
        $price_category_form = new LaterPay_Form_PriceCategory();
        if ( ! $price_category_form->is_valid( $_POST ) ) { // phpcs:ignore
            $errors = $price_category_form->get_errors();
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred. Incorrect data provided.', 'laterpay' )
                )
            );
            throw new LaterPay_Core_Exception_FormValidation( get_class( $price_category_form ), $errors['name'], $errors['value'] );
        }

        $post_category_id               = $price_category_form->get_field_value( 'category_id' );
        $category                       = $price_category_form->get_field_value( 'category' );
        $term                           = get_term_by( 'name', $category, 'category' );
        $category_price_revenue_model   = $price_category_form->get_field_value( 'laterpay_category_price_revenue_model' );
        $updated_post_ids               = null;

        if ( ! $term ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                )
            );
            return;
        }

        $category_id                = $term->term_id;
        $category_price_model       = LaterPay_Model_CategoryPriceWP::get_instance();
        $category_price_id          = $category_price_model->get_price_id_by_category_id( $post_category_id );
        $delocalized_category_price = $price_category_form->get_field_value( 'price' );

        if ( empty( $category_id ) ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' ),
                )
            );
            return;
        }

        if ( ! $post_category_id ) {
            $category_price_model->set_category_price(
                $category_id,
                $delocalized_category_price,
                $category_price_revenue_model
            );
            $updated_post_ids = LaterPay_Helper_Pricing::apply_category_price_to_posts_with_global_price( $category_id );
        } else {
            $category_price_model->set_category_price(
                $category_id,
                $delocalized_category_price,
                $category_price_revenue_model,
                $category_price_id
            );
        }

        $localized_category_price = LaterPay_Helper_View::format_number( $delocalized_category_price );
        $currency                 = $this->config->get( 'currency.code' );

        $event->set_result(
            array(
                'success'             => true,
                'category'            => $category,
                'price'               => number_format( $delocalized_category_price, 2, '.', '' ),
                'localized_price'     => $localized_category_price,
                'currency'            => $currency,
                'category_id'         => $category_id,
                'revenue_model'       => $category_price_revenue_model,
                'revenue_model_label' => LaterPay_Helper_Pricing::get_revenue_label( $category_price_revenue_model ),
                'updated_post_ids'    => $updated_post_ids,
                'message'             => sprintf(
                    __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                    $category,
                    $localized_category_price,
                    $currency
                ),
            )
        );
    }

    /**
     * Delete the category price for a given category.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function delete_category_default_price( LaterPay_Core_Event $event ) {
        $price_category_delete_form = new LaterPay_Form_PriceCategory();
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $price_category_delete_form->is_valid( $_POST ) ) { // phpcs:ignore
            throw new LaterPay_Core_Exception_FormValidation( get_class( $price_category_delete_form ), $price_category_delete_form->get_errors() );
        }

        $category_id = $price_category_delete_form->get_field_value( 'category_id' );

        // delete the category_price
        $category_price_model = LaterPay_Model_CategoryPriceWP::get_instance();
        $success              = $category_price_model->delete_prices_by_category_id( $category_id );

        if ( ! $success ) {
            return;
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
            if ( $post_price['type'] !== LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) {
                continue;
            }

            // check, if the post has the deleted category_id as category default price
            if ( (int) $post_price['category_id'] !== $category_id ) {
                continue;
            }

            // update post data
            LaterPay_Helper_Pricing::update_post_data_after_category_delete( $post_id );
        }

        $event->set_result(
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
     * @return array
     */
    protected function get_category_prices( $category_ids ) {
        return LaterPay_Helper_Pricing::get_category_price_data_by_category_ids( $category_ids );
    }

    /**
     * Update post prices in bulk.
     *
     * This function does not change the price type of a post.
     * It gets the price type of each post to be updated and updates the associated individual price, category default
     * price, or global default price.
     * It also ensures that the resulting price and revenue model is valid.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function change_posts_price( LaterPay_Core_Event $event ) {
        $bulk_price_form = new LaterPay_Form_BulkPrice( $_POST ); // phpcs:ignore
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $bulk_price_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $bulk_price_form ), $bulk_price_form->get_errors() );
        }

        $bulk_operation_id = $bulk_price_form->get_field_value( 'bulk_operation_id' );
        if ( $bulk_operation_id !== null ) {
            $operation_data = LaterPay_Helper_Pricing::get_bulk_operation_data_by_id( $bulk_operation_id );
            if ( ! $bulk_price_form->is_valid( $operation_data ) ) {
                return;
            }
        }

        // get scope of posts to be processed from selector
        $posts                = null;
        $category_price_model = LaterPay_Model_CategoryPriceWP::get_instance();
        $selector             = $bulk_price_form->get_field_value( 'bulk_selector' );
        $action               = $bulk_price_form->get_field_value( 'bulk_action' );
        $change_unit          = $bulk_price_form->get_field_value( 'bulk_change_unit' );
        $price                = $bulk_price_form->get_field_value( 'bulk_price' );
        $is_percent           = ( 'percent' === $change_unit );
        $default_currency     = $this->config->get( 'currency.code' );
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

            if ( $category_id !== null ) {
                $category_name             = get_the_category_by_ID( $category_id );
                $posts                     = LaterPay_Helper_Pricing::get_post_ids_with_price_by_category_id( $category_id );
                $message_parts['category'] = sprintf( __( '%s %s', 'laterpay' ), str_replace( '_', ' ', $selector ), $category_name );
            }
        } else {
            $posts = LaterPay_Helper_Pricing::get_all_posts_with_price();
        }

        $price     = ( $price === null ) ? 0 : $price;
        $new_price = LaterPay_Helper_Pricing::ensure_valid_price( $price );

        // pre-post-processing actions - correct global and categories default prices, set flash message parts;
        // run exactly once, independent of actual number of posts
        switch ( $action ) {
            case 'set':
                $this->update_global_and_categories_prices_with_new_price( $new_price );
                // set flash message parts
                $message_parts['action']        = __( 'set', 'laterpay' );
                $message_parts['preposition']   = __( 'to', 'laterpay' );
                $message_parts['amount']        = LaterPay_Helper_View::format_number( LaterPay_Helper_Pricing::ensure_valid_price( $new_price ) );
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
                $message_parts['amount']        = $is_percent ? $price : LaterPay_Helper_View::format_number( $price );
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
                    $message_parts['amount']        = LaterPay_Helper_View::format_number( $new_price );
                    $message_parts['unit']          = $default_currency;
                } else {
                    $new_price                      = $category_price_model->get_price_by_category_id( $category_id );
                    // set flash message parts
                    $message_parts['preposition']   = __( 'to category default price of', 'laterpay' );
                    $message_parts['amount']        = LaterPay_Helper_View::format_number( $new_price );
                    $message_parts['unit']          = $default_currency;
                }
                break;

            default:
                return;
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
                $post_type_is_global    = ( $current_post_type === LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE );
                $post_type_is_category  = ( $current_post_type === LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE );
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

                // make sure the price is within the valid range
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
        $event->set_result(
            array(
                'success' => true,
                'message' => trim( preg_replace( '/\s+/', ' ', implode( ' ', $message_parts ) ) ) . '.',
            )
        );
    }

    /**
     * Update global and category default prices with new price.
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
        $category_price_model = LaterPay_Model_CategoryPriceWP::get_instance();
        $categories           = $category_price_model->get_categories_with_defined_price();
        $revenue_model        = LaterPay_Helper_Pricing::ensure_valid_revenue_model( 'ppu', $price );
        if ( $categories ) {
            foreach ( $categories as $category ) {

                $category_price_model->set_category_price( $category->category_id, $price, $revenue_model, $category->id );

            }
        }
    }

    /**
     * Save bulk operation.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function save_bulk_operation( LaterPay_Core_Event $event ) {
        $save_bulk_operation_form = new LaterPay_Form_BulkPrice( $_POST ); // phpcs:ignore
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $save_bulk_operation_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $save_bulk_operation_form ), $save_bulk_operation_form->get_errors() );
        }

        // create data array
        $data         = $save_bulk_operation_form->get_form_values( true, 'bulk_', array( 'bulk_message' ) );
        $bulk_message = $save_bulk_operation_form->get_field_value( 'bulk_message' );

        $event->set_result(
            array(
                'success' => true,
                'data'    => array(
                    'id'      => LaterPay_Helper_Pricing::save_bulk_operation( $data, $bulk_message ),
                    'message' => $save_bulk_operation_form->get_field_value( 'bulk_message' ),
                ),
                'message' => __( 'Bulk operation saved.', 'laterpay' ),
            )
        );
    }

    /**
     * Delete bulk operation.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function delete_bulk_operation( LaterPay_Core_Event $event ) {
        $remove_bulk_operation_form = new LaterPay_Form_BulkPrice( $_POST ); // phpcs:ignore
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $remove_bulk_operation_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $remove_bulk_operation_form ), $remove_bulk_operation_form->get_errors() );
        }

        $bulk_operation_id = $remove_bulk_operation_form->get_field_value( 'bulk_operation_id' );
        $result = LaterPay_Helper_Pricing::delete_bulk_operation_by_id( $bulk_operation_id );
        if ( $result ) {
            $event->set_result(
                array(
                    'success' => true,
                    'message' => __( 'Bulk operation deleted.', 'laterpay' ),
                )
            );
        }
    }

    /**
     * Render time pass HTML.
     *
     * @param array $args
     *
     * @return string
     */
    public function render_time_pass( $args = array() ) {
        $defaults = LaterPay_Helper_TimePass::get_default_options();
        $args     = array_merge( $defaults, $args );

        $this->assign( 'laterpay_pass', $args );
        $this->assign( 'laterpay',      array(
            'standard_currency' => $this->config->get( 'currency.code' ),
        ));

        $string = $this->get_text_view( 'backend/partials/time-pass' );

        return $string;
    }

    /**
     * Save time pass
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    protected function time_pass_save( LaterPay_Core_Event $event ) {
        $save_time_pass_form = new LaterPay_Form_Pass( $_POST ); // phpcs:ignore
        $time_pass_model     = LaterPay_Model_TimePassWP::get_instance();

        $event->set_result(
            array(
                'success' => false,
                'errors'  => $save_time_pass_form->get_errors(),
                'message' => __( 'An error occurred when trying to save the time pass. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $save_time_pass_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $save_time_pass_form ), $save_time_pass_form->get_errors() );
        }

        $data = $save_time_pass_form->get_form_values( true, null, array( 'voucher_code', 'voucher_price', 'voucher_title' ) );

        // check and set revenue model
        if ( ! isset( $data['revenue_model'] ) ) {
            $data['revenue_model'] = 'ppu';
        }

        // ensure valid revenue model
        $data['revenue_model'] = LaterPay_Helper_Pricing::ensure_valid_revenue_model( $data['revenue_model'], $data['price'] );

        // update time pass data or create new time pass
        $data    = $time_pass_model->update_time_pass( $data );
        $pass_id = $data['pass_id'];

        // default vouchers data
        $vouchers_data = array();

        // set vouchers data
        $voucher_codes = $save_time_pass_form->get_field_value( 'voucher_code' );
        if ( $voucher_codes && is_array( $voucher_codes ) ) {
            $voucher_prices = $save_time_pass_form->get_field_value( 'voucher_price' );
            $voucher_titles = $save_time_pass_form->get_field_value( 'voucher_title' );
            foreach ( $voucher_codes as $idx => $code ) {
                // normalize prices and format with 2 digits in form
                $voucher_price = isset( $voucher_prices[ $idx ] ) ? $voucher_prices[ $idx ] : 0;
                $vouchers_data[ $code ] = array(
                    'price' => number_format( LaterPay_Helper_View::normalize( $voucher_price ), 2, '.', '' ),
                    'title' => isset( $voucher_titles[ $idx ] ) ? $voucher_titles[ $idx ] : '',
                );
            }
        }

        // save vouchers for this pass
        LaterPay_Helper_Voucher::save_pass_vouchers( $pass_id, $vouchers_data );

        $data['category_name']   = get_the_category_by_ID( $data['access_category'] );
        $hmtl_data               = $data;
        $data['price']           = number_format( $data['price'], 2, '.', '' );
        $data['localized_price'] = LaterPay_Helper_View::format_number( $data['price'] );
        $vouchers                = LaterPay_Helper_Voucher::get_time_pass_vouchers( $pass_id );

        $event->set_result(
            array(
                'success'  => true,
                'data'     => $data,
                'vouchers' => $vouchers,
                'html'     => $this->render_time_pass( $hmtl_data ),
                'message'  => __( 'Pass saved.', 'laterpay' ),
            )
        );
    }

    /**
     * Remove time pass by pass_id.
     *
     * @return void
     */
    protected function time_pass_delete( LaterPay_Core_Event $event ) {
        $time_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );
        if ( null !== $time_id ) {
            $time_pass_id    = sanitize_text_field( $time_id );
            $time_pass_model = LaterPay_Model_TimePassWP::get_instance();

            // remove time pass
            $time_pass_model->delete_time_pass_by_id( $time_pass_id );

            // remove vouchers
            LaterPay_Helper_Voucher::delete_voucher_code( $time_pass_id );

            $event->set_result(
                array(
                    'success' => true,
                    'message' => __( 'Time pass deleted.', 'laterpay' ),
                )
            );
        } else {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'The selected pass was deleted already.', 'laterpay' ),
                )
            );
        }
    }

    /**
     * Render time pass HTML.
     *
     * @param array $args
     *
     * @return string
     */
    public function render_subscription( $args = array() ) {
        $defaults = LaterPay_Helper_Subscription::get_default_options();
        $args     = array_merge( $defaults, $args );

        $this->assign( 'laterpay_subscription', $args );
        $this->assign( 'laterpay',      array(
            'standard_currency' => $this->config->get( 'currency.code' ),
        ));

        $string = $this->get_text_view( 'backend/partials/subscription' );

        return $string;
    }

    /**
     * Save subscription
     *
     * @param LaterPay_Core_Event $event
     */
    protected function subscription_form_save( LaterPay_Core_Event $event ) {
        $save_subscription_form = new LaterPay_Form_Subscription( $_POST ); // phpcs:ignore
        $subscription_model     = new LaterPay_Model_Subscription();

        $event->set_result(
            array(
                'success' => false,
                'errors'  => $save_subscription_form->get_errors(),
                'message' => __( 'An error occurred when trying to save the subscription. Please try again.', 'laterpay' ),
            )
        );

        if ( ! $save_subscription_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $save_subscription_form ), $save_subscription_form->get_errors() );
        }

        $data = $save_subscription_form->get_form_values();

        // update subscription data or create new subscriptions
        $data = $subscription_model->update_subscription( $data );

        $data['category_name']   = get_the_category_by_ID( $data['access_category'] );
        $hmtl_data               = $data;
        $data['price']           = number_format( $data['price'], 2, '.', '' );
        $data['localized_price'] = LaterPay_Helper_View::format_number( $data['price'] );

        $event->set_result(
            array(
                'success'  => true,
                'data'     => $data,
                'html'     => $this->render_subscription( $hmtl_data ),
                'message'  => __( 'Subscription saved.', 'laterpay' ),
            )
        );
    }

    /**
     * Remove subscription by id.
     *
     * @param LaterPay_Core_Event $event
     */
    protected function subscription_delete( LaterPay_Core_Event $event ) {
        $subscription_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );
        if ( null !== $subscription_id ) {
            $sub_id             = sanitize_text_field( $subscription_id );
            $subscription_model = new LaterPay_Model_Subscription();

            // remove subscription
            $subscription_model->delete_subscription_by_id( $sub_id );

            $event->set_result(
                array(
                    'success' => true,
                    'message' => __( 'Subscription deleted.', 'laterpay' ),
                )
            );
        } else {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'The selected subscription was deleted already.', 'laterpay' ),
                )
            );
        }
    }

    /**
     * Get JSON array of time passes list with defaults.
     *
     * @return array
     */
    private function get_time_passes_json( $time_passes_list = array() ) {
        $time_passes_array = array( 0 => LaterPay_Helper_TimePass::get_default_options() );

        foreach ( $time_passes_list as $time_pass ) {
            if ( isset( $time_pass['access_category'] ) && $time_pass['access_category'] ) {
                $time_pass['category_name'] = get_the_category_by_ID( $time_pass['access_category'] );
            }
            $time_passes_array[ $time_pass['pass_id'] ] = $time_pass;
        }

        return wp_json_encode( $time_passes_array );
    }

    /**
     * Get JSON array of subscriptions list with defaults.
     *
     * @return array
     */
    private function get_subscriptions_json( $subscriptions_list = array() ) {
        $subscriptions_array = array( 0 => LaterPay_Helper_Subscription::get_default_options() );

        foreach ( $subscriptions_list as $subscription ) {
            if ( isset( $subscription['access_category'] ) && $subscription['access_category'] ) {
                $subscription['category_name'] = get_the_category_by_ID( $subscription['access_category'] );
            }
            $subscriptions_array[ $subscription['id'] ] = $subscription;
        }

        return wp_json_encode( $subscriptions_array );
    }

    /**
     * Get generated voucher code.
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     *
     * @return void
     */
    private function generate_voucher_code( LaterPay_Core_Event $event ) {
        $currency = LaterPay_Helper_Config::get_currency_config();

        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'Incorrect voucher price.', 'laterpay' ),
            )
        );

        $voucher_price = filter_input( INPUT_POST, 'price', FILTER_SANITIZE_STRING );

        if ( null === $voucher_price ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'price' );
        }

        $price = sanitize_text_field( $voucher_price );
        //validates price given for time pass before creating voucher.
        if ( ! ( $price >= $currency['ppu_min'] && $price <= $currency['sis_max'] ) && floatval( 0 ) !== floatval( $price ) ) {
            return;
        }

        // generate voucher code
        $event->set_result(
            array(
                'success' => true,
                'code'    => LaterPay_Helper_Voucher::generate_voucher_code(),
            )
        );
    }

    /**
     * Save landing page URL the user is forwarded to after redeeming a gift card voucher.
     *
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    private function save_landing_page( LaterPay_Core_Event $event ) {
        $landing_page_form  = new LaterPay_Form_LandingPage( $_POST ); // phpcs:ignore

        if ( ! $landing_page_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $landing_page_form ), $landing_page_form->get_errors() );
        }

        // save URL and confirm with flash message, if the URL is valid
        update_option( 'laterpay_landing_page', $landing_page_form->get_field_value( 'landing_url' ) );

        $event->set_result(
            array(
                'success' => true,
                'message' => __( 'Landing page saved.', 'laterpay' ),
            )
        );
    }

    /**
     * Switch plugin between allowing
     * (1) individual purchases and time pass purchases, or
     * (2) time pass purchases only.
     * Do nothing and render an error message, if no time pass is defined when trying to switch to time pass only mode.
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    private function change_purchase_mode( LaterPay_Core_Event $event ) {

        $time_pass_purchase_mode = filter_input( INPUT_POST, 'only_time_pass_purchase_mode', FILTER_SANITIZE_STRING );

        if ( null !== $time_pass_purchase_mode ) {
            $only_time_pass = 1; // allow time pass purchases only
        } else {
            $only_time_pass = 0; // allow individual and time pass purchases
        }

        if ( $only_time_pass === 1 && ! LaterPay_Helper_TimePass::get_time_passes_count() ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'You have to create a time pass, before you can disable individual purchases.', 'laterpay' ),
                )
            );
            return;
        }

        update_option( 'laterpay_only_time_pass_purchases_allowed', $only_time_pass );

        $event->set_result(
            array(
                'success' => true,
            )
        );
    }

    /**
     * Register laterpay passes custom post type.
     *
     * @param LaterPay_Core_Event $event
     */
    public function register_passes_cpt( LaterPay_Core_Event $event ) {

        $args = array(
            'labels'     => array(
                'name'          => __( 'Passes', 'laterpay' ),
                'singular_name' => __( 'Pass', 'laterpay' ),
            ),
            'taxonomies' => array( 'category' ),
        );

        $result = register_post_type( LaterPay_Model_TimePassWP::$timepass_post_type, $args );

        if ( is_wp_error( $result ) ) {
            $event->set_result(
                array(
                    'success' => false,
                    'message' => __( 'Laterpay Passes Post type Registration issue.', 'laterpay' ),
                )
            );
        }
    }
}
