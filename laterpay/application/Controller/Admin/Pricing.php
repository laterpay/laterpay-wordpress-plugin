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
            $this->config->get( 'css_url' ). 'vendor/select2.min.css',
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

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-pricing',
            'lpVars',
            array( 'locale' => get_locale() )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        $category_price_model           = new LaterPay_Model_CategoryPrice();
        $categories_with_defined_price  = $category_price_model->get_categories_with_defined_price();
        $currency_model                 = new LaterPay_Model_Currency();
        $currencies                     = $currency_model->get_currencies();

        $this->assign( 'categories_with_defined_price', $categories_with_defined_price );
        $this->assign( 'currencies',                    $currencies );
        $this->assign( 'standard_currency',             get_option( 'laterpay_currency' ) );
        $this->assign( 'plugin_is_in_live_mode',        get_option( 'laterpay_plugin_is_in_live_mode' ) == 1 );
        $this->assign( 'global_default_price',          LaterPay_Helper_View::format_number( (float) get_option( 'laterpay_global_price' ), 2 ) );
        $this->assign( 'top_nav',                       $this->get_menu() );
        $this->assign( 'admin_menu',                    LaterPay_Helper_View::get_admin_menu() );

        $this->render( 'backend/pricing' );
    }

    /**
     * Process Ajax requests from pricing tab.
     *
     * @return  void
     */
    public function process_ajax_requests() {
        // save changes in submitted form
        if ( isset( $_POST['form'] ) ) {
            // check for required capabilities to perform action
            if ( ! current_user_can( 'edit_plugins' ) ) {
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
                    $this->update_category_default_price();
                    break;

                case 'price_category_form_delete':
                    $this->delete_category_default_price();
                    break;
                case 'laterpay_get_category_prices':
                    if( !array_key_exists( 'category_ids', $_POST ) )
                        $_POST[ 'category_ids' ] = array();
                    $this->get_category_prices( $_POST['category_ids'] );

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
            if ( isset( $_GET['get'] ) && $_GET['get'] ) {
                wp_send_json(
                    $category_price_model->get_categories_by_term( $_GET['term'], 1 )
                );
            } else {
                if ( isset( $_GET['category'] ) ) {
                    wp_send_json(
                        $category_price_model->get_categories_without_price_by_term( $_GET['term'], 10, (int) $_GET['category'] )
                    );
                } else {
                    wp_send_json(
                        $category_price_model->get_categories_without_price_by_term( $_GET['term'], 10 )
                    );
                }
            }
            die;
        }

        // invalid request
        wp_send_json(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
            )
        );
    }

    /**
     * Update the currency used for all prices.
     *
     * @return  void
     */
    protected function update_currency() {
        update_option( 'laterpay_currency', $_POST['laterpay_currency'] );

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
     * Update the global price, which is by default applied to all posts.
     *
     * @return  void
     */
    protected function update_global_default_price() {
        $delocalized_global_price = (float) str_replace( ',', '.', $_POST['laterpay_global_price'] );

        if ( $delocalized_global_price > 5 || $delocalized_global_price < 0 ) {
            wp_send_json(
                array(
                    'success'               => false,
                    'laterpay_global_price' => get_option( 'laterpay_global_price' ),
                    'message'               => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' )
                )
            );
        }

        update_option( 'laterpay_global_price', $delocalized_global_price );

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
                'success'               => true,
                'laterpay_global_price' => $localized_global_price,
                'message'               => $message,
            )
        );
    }

    /**
     * Update the category price, which is by default applied to all posts in a given category.
     *
     * @return  void
     */
    protected function update_category_default_price() {
        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );

        if ( $delocalized_category_price > 5 || ( $delocalized_category_price < 0.05 && $delocalized_category_price != 0 ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'The price you tried to set is not within the allowed range of 0 to 5.00.', 'laterpay' )
                )
            );
        }

        if ( ! empty( $_POST['category_id'] ) ) {
            $this->update_existing_category_default_price();
            die;
        } else {
            $this->set_new_category_default_price();
            die;
        }
    }

    /**
     * Update the category price, if a category price is already defined for a given category.
     *
     * @return  void
     */
    protected function update_existing_category_default_price() {
        $category_price_model       = new LaterPay_Model_CategoryPrice();
        $category_id                = $category_price_model->get_category_id_by_name( $_POST['category'] );
        $category_price_id          = $category_price_model->get_price_id_by_category_id( $category_id );

        $currency_model             = new LaterPay_Model_Currency();
        $currency_name              = $currency_model->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );

        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );

        if ( empty( $category_price_id ) && empty( $category_id ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' )
                )
            );
        } else if ( ! empty( $category_id ) && $category_id != $_POST['category_id'] ) {
            $category_price_model->delete_prices_by_category_id( $_POST['category_id'] );
            $category_price_id = $category_price_model->get_price_id_by_category_id( $_POST['category_id'] );

            if ( $category_price_id ) {
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                    )
                );
            } else {
                $category_price_model->set_category_price( $category_id, $delocalized_category_price );

                $category_price             = $category_price_model->get_price_by_category_id( $category_id );
                $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );

                wp_send_json(
                    array(
                        'success'       => true,
                        'category'      => $_POST['category'],
                        'price'         => $formatted_category_price,
                        'currency'      => get_option( 'laterpay_currency' ),
                        'category_id'   => $category_id,
                        'message'       => sprintf(
                            __( 'All posts in category %s have a default price of %s %s now.', 'laterpay' ),
                            $_POST['category'],
                            $formatted_category_price,
                            $currency_name
                        )
                    )
                );
            }
        }

        $category_price_model->set_category_price( $category_id, $delocalized_category_price, $category_price_id );

        $category_price             = $category_price_model->get_price_by_category_id( $category_id );
        $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );

        wp_send_json(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $_POST['price'],
                'currency'      => get_option('laterpay_currency'),
                'category_id'   => $category_id,
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
     * Set the category price, if a given category does not have a category price yet.
     *
     * @return  void
     */
    protected function set_new_category_default_price() {
        $category_price_model       = new LaterPay_Model_CategoryPrice();
        $category_doesnt_exist      = $category_price_model->check_existence_of_category_by_name( $_POST['category'] );
        $category_id                = $category_price_model->get_category_id_by_name( $_POST['category'] );
        $delocalized_category_price = (float) str_replace( ',', '.', $_POST['price'] );

        if ( ! empty( $category_doesnt_exist ) || empty( $category_id ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'There is no such category on this website.', 'laterpay' )
                )
            );
        } else {
            $category_price_model->set_category_price( $category_id, $delocalized_category_price );
        }

        $category_price             = $category_price_model->get_price_by_category_id( $category_id );
        $formatted_category_price   = LaterPay_Helper_View::format_number( (float) $category_price, 2 );
        $currency_model             = new LaterPay_Model_Currency();
        $currency_name              = $currency_model->get_currency_name_by_iso4217_code( get_option( 'laterpay_currency' ) );

        wp_send_json(
            array(
                'success'       => true,
                'category'      => $_POST['category'],
                'price'         => $formatted_category_price,
                'currency'      => get_option( 'laterpay_currency' ),
                'category_id'   => $category_id,
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
     * Delete the category price for a given category.
     *
     * @return void
     */
    protected function delete_category_default_price() {
        $category_id = absint( $_POST['category_id'] );

        // delete the category_price
        $category_price_model = new LaterPay_Model_CategoryPrice();
        $success = $category_price_model->delete_prices_by_category_id( $category_id );

        if ( ! $success ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                )
            );
        }

        // get all post with the deleted $category_id and loop through them
        $posts = LaterPay_Helper_Pricing::get_posts_with_price_by_category_id( $category_id );
        foreach ( $posts as $post ) {
            // check if the post has LaterPay pricing data
            $post_price = get_post_meta( $post->ID, 'laterpay_post_prices', true );
            if ( ! is_array( $post_price ) ) {
                continue;
            }
            // check if the post uses a category default price
            if ( $post_price[ 'type' ] !== 'category default price' ) {
                continue;
            }

            // check if the post has the deleted category_id as category default price
            if ( (int) $post_price[ 'category_id' ] !== $category_id ) {
                continue;
            }

            // get all post_categories
            $post_categories = wp_get_post_categories( $post->ID );

            if ( empty( $post_categories ) ) {
                // apply the global default price as new price, if no other post categories are found
                LaterPay_Helper_Pricing::apply_global_default_price_to_post( $post->ID );
            } else {
                // load all category prices by the given category_ids
                $laterpay_category_model    = new LaterPay_Model_CategoryPrice();
                $category_price_data        = $laterpay_category_model->get_category_price_data_by_category_ids( $post_categories );

                if ( count( $category_price_data ) < 1 ) {
                    // no other category prices found for this post
                    LaterPay_Helper_Pricing::apply_global_default_price_to_post( $post->ID );
                } else {
                    // find the category with the highest price and assign its category_id to the post
                    $price = 0;
                    foreach ( $category_price_data as $data ) {
                        if ( $data->category_price > $price ) {
                            $price          = $data->category_price;
                            $category_id    = $data->category_id;
                        }
                    }

                    LaterPay_Helper_Pricing::apply_category_default_price_to_post( $post->ID, $category_id, true );
                }
            }
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => __( 'The default price for this category was deleted.', 'laterpay' ),
            )
        );
    }

    /**
     * Process Ajax request for prices of applied categories.
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

}
