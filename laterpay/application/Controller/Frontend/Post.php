<?php

/**
 * LaterPay post controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Frontend_Post extends LaterPay_Controller_Base
{

    /**
     * Ajax method to get the cached article.
     * Required, because there could be a price change in LaterPay and we always need the current article price.
     *
     * @wp-hook wp_ajax_laterpay_post_load_purchased_content, wp_ajax_nopriv_laterpay_post_load_purchased_content
     *
     * @return void
     */
    public function ajax_load_purchased_content() {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_post_load_purchased_content' ) {
            wp_die();
        }

        if ( ! isset( $_GET['nonce'] )
             || ! wp_verify_nonce( sanitize_text_field( $_GET['nonce'] ), sanitize_text_field( $_GET['action'] ) ) ) {
            wp_die();
        }

        if ( ! isset( $_GET['post_id'] ) ) {
            wp_die();
        }

        global $post;

        $post_id    = absint( $_GET['post_id'] );
        $post       = get_post( $post_id );

        if ( $post === null ) {
            wp_die();
        }

        if ( ! is_user_logged_in() && ! LaterPay_Helper_Post::has_access_to_post( $post ) ) {
            // check access to paid post for not logged in users only and prevent
            wp_die();
        } else if ( is_user_logged_in() && LaterPay_Helper_User::preview_post_as_visitor( $post ) ) {
            // return, if user is logged in and 'preview_as_visitor' is activated
            wp_die();
        }

        // call 'the_post' hook to enable modification of loaded data by themes and plugins
        do_action_ref_array( 'the_post', array( &$post ) );

        $content = apply_filters( 'the_content', $post->post_content );
        $content = str_replace( ']]>', ']]&gt;', $content );

        echo laterpay_sanitized( $content );
        // return Ajax content
        exit;
    }

    /**
     * Ajax method to rate purchased content.
     *
     * @wp-hook wp_ajax_laterpay_post_rate_purchased_content, wp_ajax_nopriv_laterpay_post_rate_purchased_content
     *
     * @return void
     */
    public function ajax_rate_purchased_content() {
        $post_rating_form = new LaterPay_Form_PostRating( $_POST );

        if ( $post_rating_form->is_valid( $_POST ) ) {
            $post_id       = $post_rating_form->get_field_value( 'post_id' );
            $rating_value  = $post_rating_form->get_field_value( 'rating_value' );
            $is_user_voted = LaterPay_Helper_Rating::check_if_user_voted_post_already( $post_id );

            if ( $is_user_voted ) {
                wp_send_json(
                    array(
                        'success' => false,
                    )
                );
            }

            // update rating data with submitted rating
            $rating       = LaterPay_Helper_Rating::get_post_rating_data( $post_id );
            $rating_index = (string) $rating_value;
            $rating[ $rating_index ] += 1;

            update_post_meta( $post_id, 'laterpay_rating', $rating );
            LaterPay_Helper_Rating::set_user_voted( $post_id );

            wp_send_json(
                array(
                    'success' => true,
                    'message' => __( 'Thank you very much for rating!', 'laterpay' ),
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
            )
        );
    }

    /**
     * Ajax method to get rating summary.
     *
     * @wp-hook wp_ajax_laterpay_post_rating_summary, wp_ajax_nopriv_laterpay_post_rating_summary
     *
     * @return void
     */
    public function ajax_load_rating_summary() {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_post_rating_summary' ) {
            wp_die();
        }

        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['nonce'] ), sanitize_text_field( $_GET['action'] ) ) ) {
            wp_die();
        }

        if ( ! isset( $_GET['post_id'] ) ) {
            wp_die();
        }

        $post_id = absint( $_GET['post_id'] );
        $post    = get_post( $post_id );

        if ( $post === null ) {
            wp_die();
        }

        // get post rating summary
        $summary_post_rating     = LaterPay_Helper_Rating::get_summary_post_rating_data( $post_id );
        // round $aggregated_post_rating to closest 0.5
        $aggregated_post_rating  = $summary_post_rating['votes'] ? number_format( round( 2 * $summary_post_rating['rating'] / $summary_post_rating['votes'] ) / 2, 1 ) : 0;
        $post_rating_data        = LaterPay_Helper_Rating::get_post_rating_data( $post_id );
        $maximum_number_of_votes = max( $post_rating_data );

        // assign variables to the view templates
        $view_args = array(
            'post_rating_data'       => $post_rating_data,
            'post_aggregated_rating' => $aggregated_post_rating,
            'post_summary_votes'     => $summary_post_rating['votes'],
            'maximum_number_of_votes' => $maximum_number_of_votes,
        );
        $this->assign( 'laterpay', $view_args );

        echo laterpay_sanitized( LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/rating-summary' ) ) );
        // return Ajax content
        exit;
    }

    /**
     * Ajax method to redeem voucher code.
     *
     * @wp-hook wp_ajax_laterpay_redeem_voucher_code, wp_ajax_nopriv_laterpay_redeem_voucher_code
     *
     * @return void
     */
    public function ajax_redeem_voucher_code() {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_redeem_voucher_code' ) {
            wp_die();
        }

        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['nonce'] ), sanitize_text_field( $_GET['action'] ) ) ) {
            wp_die();
        }

        if ( ! isset( $_GET['code'] ) || ! isset( $_GET['link'] ) ) {
            wp_die();
        }

        // check, if voucher code exists and time pass is available for purchase
        $is_gift     = true;
        $code        = sanitize_text_field( $_GET['code'] );
        $code_data   = LaterPay_Helper_Voucher::check_voucher_code( $code, $is_gift );
        if ( ! $code_data ) {
            $is_gift     = false;
            $can_be_used = true;
            $code_data   = LaterPay_Helper_Voucher::check_voucher_code( $code, $is_gift );
        } else {
            $can_be_used = LaterPay_Helper_Voucher::check_gift_code_usages_limit( $code );
        }

        // if gift code data exists and usage limit is not exceeded
        if ( $code_data && $can_be_used ) {
            // update gift code usage
            if ( $is_gift ) {
                LaterPay_Helper_Voucher::update_gift_code_usages( $code );
            }
            // get new URL for this time pass
            $pass_id    = $code_data['pass_id'];
            // get price, delocalize it, and format it
            $price      = number_format( LaterPay_Helper_View::normalize( $code_data['price'] ), 2 );
            // prepare URL before use
            $data       = array(
                'link'    => $is_gift ? home_url() : esc_url_raw( $_GET['link'] ),
                'price'   => $price,
            );

            // get new purchase URL
            $url = LaterPay_Helper_TimePass::get_laterpay_purchase_link( $pass_id, $data );

            wp_send_json(
                array(
                    'success' => true,
                    'pass_id' => $pass_id,
                    'price'   => LaterPay_Helper_View::format_number( $price ),
                    'url'     => $url,
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
            )
        );
    }

    /**
     * Save time pass info after purchase.
     *
     * @wp-hook template_reditect
     *
     * @return  void
     */
    public function buy_time_pass() {
        $request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) : '';
        $request        = new LaterPay_Core_Request();
        $pass_id        = $request->get_param( 'pass_id' );
        $link           = $request->get_param( 'link' );

        if ( ! isset( $pass_id ) || ! isset( $link ) ) {
            return;
        }

        $id_currency    = $request->get_param( 'id_currency' );
        $price          = $request->get_param( 'price' );
        $date           = $request->get_param( 'date' );
        $ip             = $request->get_param( 'ip' );
        $revenue_model  = $request->get_param( 'revenue_model' );
        $voucher        = $request->get_param( 'voucher' );
        $hmac           = $request->get_param( 'hmac' );
        $lptoken        = $request->get_param( 'lptoken' );

        $pass_id = LaterPay_Helper_TimePass::get_untokenized_time_pass_id( $pass_id );
        $post_id = 0;
        $post    = get_post();
        if ( $post !== null ) {
            $post_id = $post->ID;
        }

        $client_options  = LaterPay_Helper_Config::get_php_client_options();
        $laterpay_client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        if ( LaterPay_Client_Signing::verify( $hmac, $laterpay_client->get_api_key(), $request->get_data( 'get' ), get_permalink(), $request_method ) ) {
            // check token
            if ( ! empty( $lptoken ) ) {
                $laterpay_client->set_token( $lptoken );
            } elseif ( ! $laterpay_client->has_token() ) {
                $laterpay_client->acquire_token();
            }

            // process vouchers
            if ( ! LaterPay_Helper_Voucher::check_voucher_code( $voucher ) ) {
                if ( ! LaterPay_Helper_Voucher::check_voucher_code( $voucher, true ) ) {
                    // save the pre-generated gift code as valid voucher code now that the purchase is complete
                    $gift_cards = LaterPay_Helper_Voucher::get_time_pass_vouchers( $pass_id, true );
                    $gift_cards[ $voucher ] = 0;
                    LaterPay_Helper_Voucher::save_pass_vouchers( $pass_id, $gift_cards, true, true );
                    // set cookie to store information that gift card was purchased
                    setcookie(
                        'laterpay_purchased_gift_card',
                        $voucher . '|' . $pass_id,
                        time() + 30,
                        '/'
                    );
                } else {
                    // update gift code statistics
                    LaterPay_Helper_Voucher::update_voucher_statistic( $pass_id, $voucher, true );
                }
            } else {
                // update voucher statistics
                LaterPay_Helper_Voucher::update_voucher_statistic( $pass_id, $voucher );
            }

            // save payment history
            $data = array(
                'id_currency'   => $id_currency,
                'post_id'       => $post_id,
                'price'         => $price,
                'date'          => $date,
                'ip'            => $ip,
                'hash'          => $hmac,
                'revenue_model' => $revenue_model,
                'pass_id'       => $pass_id,
                'code'          => $voucher,
            );

            $this->logger->info(
                __METHOD__ . ' - set payment history',
                $data
            );

            $payment_history_model = new LaterPay_Model_Payment_History();
            $payment_history_model->set_payment_history( $data );
        }

        wp_redirect( $link );
        // exit script after redirect was set
        exit;
    }

    /**
     * Save purchase in purchase history.
     *
     * @wp-hook template_redirect
     * @return void
     */
    public function buy_post() {
        $request_method    = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) : '';
        $request           = new LaterPay_Core_Request();
        $buy               = $request->get_param( 'buy' );

        // return, if the request was not a redirect after a purchase
        if ( ! isset( $buy ) ) {
            return;
        }

        $post_id           = $request->get_param( 'post_id' );
        $id_currency       = $request->get_param( 'id_currency' );
        $price             = $request->get_param( 'price' );
        $date              = $request->get_param( 'date' );
        $ip                = $request->get_param( 'ip' );
        $revenue_model     = $request->get_param( 'revenue_model' );
        $hmac              = $request->get_param( 'hmac' );
        $lptoken           = $request->get_param( 'lptoken' );
        $download_attached = $request->get_param( 'download_attached' );

        $request         = new LaterPay_Core_Request();
        $client_options  = LaterPay_Helper_Config::get_php_client_options();
        $laterpay_client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        if ( $download_attached ) {
            $post_id = $download_attached;
        }

        if ( LaterPay_Client_Signing::verify( $hmac, $laterpay_client->get_api_key(), $request->get_data( 'get' ), get_permalink(), $request_method ) ) {
            // check token
            if ( ! empty( $lptoken ) ) {
                $laterpay_client->set_token( $lptoken );
            } elseif ( ! $laterpay_client->has_token() ) {
                $laterpay_client->acquire_token();
            }

            $data = array(
                'post_id'       => $post_id,
                'id_currency'   => $id_currency,
                'price'         => $price,
                'date'          => $date,
                'ip'            => $ip,
                'hash'          => $hmac,
                'revenue_model' => $revenue_model,
            );

            $this->logger->info(
                __METHOD__ . ' - set payment history',
                $data
            );

            $payment_history_model = new LaterPay_Model_Payment_History();
            $payment_history_model->set_payment_history( $data );
        }

        // prepare attachment URL for download
        if ( $download_attached ) {
            $post    = get_post( $post_id );
            $access  = LaterPay_Helper_Post::has_access_to_post( $post );

            $attachment_url = LaterPay_Helper_File::get_encrypted_resource_url(
                $post_id,
                wp_get_attachment_url( $post_id ),
                $access,
                'attachment'
            );

            // set cookie to notify post that we need to start attachment download
            setcookie(
                'laterpay_download_attached',
                $attachment_url,
                time() + 60,
                '/'
            );
        }

        wp_redirect( get_permalink( $post_id ) );
        // exit script after redirect was set
        exit;
    }

    /**
     * Update incorrect token or create one, if it doesn't exist.
     *
     * @wp-hook template_redirect
     *
     * @return void
     */
    public function create_token() {
        $browser_supports_cookies   = LaterPay_Helper_Browser::browser_supports_cookies();
        $browser_is_crawler         = LaterPay_Helper_Browser::is_crawler();

        // don't assign tokens to crawlers and other user agents that can't handle cookies
        if ( ! $browser_supports_cookies || $browser_is_crawler ) {
            return;
        }

        // don't check or create the 'lptoken' on single pages with non-purchasable posts
        if ( is_single() && ! LaterPay_Helper_Pricing::is_purchasable( ) ) {
            return;
        }

        $context = array(
            'support_cookies'   => $browser_supports_cookies,
            'is_crawler'        => $browser_is_crawler,
        );

        $this->logger->info(
            __METHOD__,
            $context
        );

        if ( isset( $_GET['lptoken'] ) ) {
            LaterPay_Helper_Request::laterpay_api_set_token( sanitize_text_field( $_GET['lptoken'] ), true );
        }

        LaterPay_Helper_Request::laterpay_api_acquire_token();
    }

    /**
     * Check if user has access to the post
     *
     * @wp-hook laterpay_check_user_access
     *
     * @return bool $has_access
     */
    public function check_user_access( $has_access, $post_id = null ) {
        // get post
        if ( ! isset( $post_id ) ) {
            $post = get_post();
        } else {
            $post = get_post( $post_id );
        }

        if ( $post === null ) {
            return (bool) $has_access;
        }

        $user_has_unlimited_access = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );

        // user has unlimited access
        if ( $user_has_unlimited_access ) {
            return true;
        }

        // user has access to the post
        if ( LaterPay_Helper_Post::has_access_to_post( $post ) ) {
            return true;
        }

        return false;
    }

    /**
     * Encrypt image source to prevent direct access.
     *
     * @wp-hook wp_get_attachment_image_attributes
     *
     * @param array        $attr Attributes for the image markup
     * @param WP_Post      $post Image attachment post
     * @param string|array $size Requested size
     *
     * @return mixed
     */
    public function encrypt_image_source( $attr, $post, $size ) {
        $is_purchasable = LaterPay_Helper_Pricing::is_purchasable( $post->ID );
        if ( $is_purchasable ) {
            $access         = LaterPay_Helper_Post::has_access_to_post( $post );
            $attr['src']    = LaterPay_Helper_File::get_encrypted_resource_url(
                $post->ID,
                $attr['src'],
                $access,
                'attachment'
            );
        }

        return $attr;
    }

    /**
     * Encrypt attachment URL to prevent direct access.
     *
     * @wp-hook wp_get_attachment_url
     *
     * @param string $url     URL for the given attachment
     * @param int    $post_id Attachment ID
     *
     * @return string
     */
    public function encrypt_attachment_url( $url, $post_id ) {
        // get current post
        $current_post = get_post();
        if ( $current_post === null ) {
            return $url;
        }

        $is_purchasable = LaterPay_Helper_Pricing::is_purchasable( $current_post->ID );
        if ( $is_purchasable && $current_post->ID === $post_id ) {
            $access = LaterPay_Helper_Post::has_access_to_post( $current_post );

            // prevent from exec, if attachment is an image and user does not have access
            if ( ! $access && strpos( $current_post->post_mime_type, 'image' ) !== false ) {
                return '';
            }

            // encrypt attachment URL
            $url = LaterPay_Helper_File::get_encrypted_resource_url(
                $post_id,
                $url,
                $access,
                'attachment'
            );
        }

        return $url;
    }

    /**
     * Prevent prepending of attachment before paid content.
     *
     * @wp-hook prepend_attachment
     *
     * @param string $attachment The attachment HTML output
     *
     * @return string
     */
    public function prepend_attachment( $attachment ) {
        // get current post
        $current_post = get_post();
        if ( $current_post === null ) {
            return $attachment;
        }

        $is_purchasable          = LaterPay_Helper_Pricing::is_purchasable( $current_post->ID );
        $access                  = LaterPay_Helper_Post::has_access_to_post( $current_post );
        $preview_post_as_visitor = LaterPay_Helper_User::preview_post_as_visitor( $current_post );;
        if ( $is_purchasable && ! $access || $preview_post_as_visitor ) {
            return '';
        }

        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;
        if ( $is_ajax_and_caching_is_active ) {
            return '';
        }

        return $attachment;
    }

    /**
     * Hide free posts with premium content from the homepage
     *
     * @wp-hook the_posts
     *
     * @return array $posts
     */
    public function hide_free_posts_with_premium_content($posts) {
        // check if current page is a homepage and hide free posts option enabled
        $is_homepage = is_front_page() && is_home();
        if ( ! get_option( 'laterpay_hide_free_posts' ) || ! $is_homepage ) {
            return $posts;
        }

        // loop through query and find free posts with premium content
        foreach ( $posts as $key => $post ) {
            if ( has_shortcode( $post->post_content, 'laterpay_premium_download' ) && ! LaterPay_Helper_Pricing::is_purchasable( $post->ID ) ) {
                unset( $posts[ $key ] );
            }
        }

        return array_values( $posts );
    }

    /**
     * Prefetch the post access for posts in the loop.
     *
     * In archives or by using the WP_Query-Class, we can prefetch the access
     * for all posts in a single request instead of requesting every single post.
     *
     * @wp-hook the_posts
     *
     * @param array $posts
     *
     * @return array $posts
     */
    public function prefetch_post_access( $posts ) {
        // prevent exec if admin
        if ( is_admin() ) {
            return $posts;
        }

        $post_ids = array();
        // as posts can also be loaded by widgets (e.g. recent posts and popular posts), we loop through all posts
        // and bundle them in one API request to LaterPay, to avoid the overhead of multiple API requests
        foreach ( $posts as $post ) {
            // add a post_ID to the array of posts to be queried for access, if it's purchasable and not loaded already
            if ( ! array_key_exists( $post->ID, LaterPay_Helper_Post::get_access_state() ) && LaterPay_Helper_Pricing::get_post_price( $post->ID ) != 0 ) {
                $post_ids[] = $post->ID;
            }
        }

        // check access for time passes
        $time_passes = LaterPay_Helper_TimePass::get_tokenized_time_pass_ids();

        foreach ( $time_passes as $time_pass ) {
            // add a tokenized time pass id to the array of posts to be queried for access, if it's not loaded already
            if ( ! array_key_exists( $time_pass, LaterPay_Helper_Post::get_access_state() ) ) {
                $post_ids[] = $time_pass;
            }
        }

        if ( empty( $post_ids ) ) {
            return $posts;
        }

        $this->logger->info(
            __METHOD__,
            array( 'post_ids' => $post_ids )
        );

        $access_result = LaterPay_Helper_Request::laterpay_api_get_access( $post_ids );

        if ( empty( $access_result ) || ! array_key_exists( 'articles', $access_result ) ) {
            return $posts;
        }

        foreach ( $access_result['articles'] as $post_id => $state ) {
            LaterPay_Helper_Post::set_access_state( $post_id, (bool) $state['access'] );
        }

        return $posts;
    }

    /**
     * Check, if LaterPay is enabled for the given post type.
     *
     * @param string $post_type
     *
     * @return bool true|false
     */
    protected function is_enabled_post_type( $post_type ) {
        if ( ! in_array( $post_type, $this->config->get( 'content.enabled_post_types' ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check, if the current page is a login page.
     *
     * @return boolean
     */
    public static function is_login_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
    }

    /**
     * Check, if the current page is the cron page.
     *
     * @return boolean
     */
    public static function is_cron_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-cron.php' ) );
    }

    /**
     * Callback to generate a LaterPay purchase button within the theme that can be freely positioned.
     * When doing this, you should set config option 'laterpay_purchase_button_positioned_manually' to TRUE to disable the default
     * rendering of a purchase button at the beginning of the post content, thus avoiding multiple purchase buttons
     * on the post page.
     *
     * @wp-hook laterpay_purchase_button
     *
     * @return void
     */
    public function the_purchase_button() {
        // check, if the current post is purchasable
        if ( ! LaterPay_Helper_Pricing::is_purchasable() ) {
            return;
        }

        $post = get_post();
        $preview_post_as_visitor   = LaterPay_Helper_User::preview_post_as_visitor( $post );
        $user_has_unlimited_access = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );

        // check, if we are in 'preview post as admin' mode
        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            return;
        }

        // check, if the current post was already purchased and current user is not an admin
        if ( LaterPay_Helper_Post::has_access_to_post( $post ) && ! $preview_post_as_visitor ) {
            return;
        }

        // check, if the current post is rendered in visible test mode
        $is_in_visible_test_mode = get_option( 'laterpay_is_in_visible_test_mode' )
                                    && ! $this->config->get( 'is_in_live_mode' );

        $view_args = array(
            'post_id'                         => $post->ID,
            'link'                            => LaterPay_Helper_Post::get_laterpay_purchase_link( $post->ID ),
            'currency'                        => get_option( 'laterpay_currency' ),
            'price'                           => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
            'preview_post_as_visitor'         => $preview_post_as_visitor,
            'purchase_button_is_hidden'       => LaterPay_Helper_View::purchase_button_is_hidden(),
            'is_in_visible_test_mode'        => $is_in_visible_test_mode,
        );

        $this->logger->info(
            __METHOD__,
            $view_args
        );

        $this->assign( 'laterpay_widget', $view_args );

        echo laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/purchase-button' ) );
    }

    /**
     * Callback to render a widget with the available LaterPay time passes within the theme
     * that can be freely positioned.
     *
     * @wp-hook laterpay_time_passes
     *
     * @param string $introductory_text     additional text rendered at the top of the widget
     * @param string $call_to_action_text   additional text rendered after the time passes and before the voucher code input
     * @param int    $time_pass_id          id of one time pass to be rendered instead of all time passes
     *
     * @return void
     */
    public function the_time_passes_widget( $introductory_text = '', $call_to_action_text = '', $time_pass_id = null ) {
        $is_homepage                     = is_front_page() && is_home();
        $show_widget_on_free_posts       = get_option( 'laterpay_show_time_passes_widget_on_free_posts' );
        $time_passes_positioned_manually = get_option( 'laterpay_time_passes_positioned_manually' );

        // prevent execution, if the current post is not the given post and we are not on the homepage,
        // or the action was called a second time,
        // or the post is free and we can't show the time pass widget on free posts
        if ( LaterPay_Helper_Pricing::is_purchasable() === false && ! $is_homepage ||
             did_action( 'laterpay_time_passes' ) > 1 ||
             LaterPay_Helper_Pricing::is_purchasable() === null && ! $show_widget_on_free_posts
        ) {
            return;
        }

        // don't display widget on a search or multiposts page, if it is positioned automatically
        if ( ! is_singular() && ! $time_passes_positioned_manually ) {
            return;
        }

        // get time passes list
        $time_passes_with_access = $this->get_time_passes_with_access();

        // check, if we are on the homepage or on a post / page page
        if ( $is_homepage ) {
            $time_passes_list = LaterPay_Helper_TimePass::get_time_passes_list_by_post_id(
                null,
                $time_passes_with_access,
                true
            );
        } else {
            $time_passes_list = LaterPay_Helper_TimePass::get_time_passes_list_by_post_id(
                get_the_ID(),
                $time_passes_with_access,
                true
            );
        }

        if ( isset( $time_pass_id ) ) {
            if ( in_array( $time_pass_id, $time_passes_with_access ) ) {
                return;
            }
            $time_passes_list = array( LaterPay_Helper_TimePass::get_time_pass_by_id( $time_pass_id, true ) );
        }

        // don't render the widget, if there are no time passes
        if ( count( $time_passes_list ) === 0 ) {
            return;
        }

        // check, if the time passes to be rendered have vouchers
        $has_vouchers = LaterPay_Helper_Voucher::passes_have_vouchers( $time_passes_list );

        $view_args = array(
           'passes_list'                    => $time_passes_list,
           'has_vouchers'                   => $has_vouchers,
           'time_pass_introductory_text'    => $introductory_text,
           'time_pass_call_to_action_text'  => $call_to_action_text,
        );

        $this->logger->info(
            __METHOD__,
            $view_args
        );

        $this->assign( 'laterpay_widget', $view_args );

        echo laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/time-passes' ) );
    }

    /**
     * Modify the post content of paid posts.
     *
     * Depending on the configuration, the content of paid posts is modified and several elements are added to the content:
     * If the user is an admin, a statistics pane with performance data for the current post is shown.
     * LaterPay purchase button is shown before the content.
     * Depending on the settings in the appearance tab, only the teaser content or the teaser content plus an excerpt of
     * the full content is returned for user who have not bought the post.
     * A LaterPay purchase link or a LaterPay purchase button is shown after the content.
     *
     * @wp-hook the_content
     *
     * @param string $content
     * @internal WP_Embed $wp_embed
     *
     * @return string $content
     */
    public function modify_post_content( $content ) {
        global $wp_embed;

        $post = get_post();
        if ( $post === null ) {
            return $content;
        }

        $post_id = $post->ID;

        // return the full content, if post is not in the enabled post types
        if ( ! $this->is_enabled_post_type( $post->post_type ) ) {
            $context = array(
                'post'                  => $post,
                'supported_post_types'  => $this->config->get( 'content.enabled_post_types' )
            );

            $this->logger->info(
                __METHOD__ . ' - post_type not supported ',
                $context
            );

            return $content;
        }

        // get pricing data
        $currency                       = get_option( 'laterpay_currency' );
        $price                          = LaterPay_Helper_Pricing::get_post_price( $post_id );
        $revenue_model                  = LaterPay_Helper_Pricing::get_post_revenue_model( $post_id );

        // assign variables to time passes partial
        $timepasses_positioned_manually = get_option( 'laterpay_time_passes_positioned_manually' );
        $view_args = array(
            'time_passes_positioned_manually' => $timepasses_positioned_manually,
        );
        $this->assign( 'laterpay', $view_args );

        // return the full content, if no price was found for the post
        if ( $price == 0 ) {
            $context = array(
                'post'  => $post,
                'price' => $price,
            );

            $this->logger->info(
                __METHOD__ . ' - post is not purchasable',
                $context
            );

            // append time passes to content
            $content .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );

            return $content;
        }

        // get the teaser content
        $teaser_content = get_post_meta( $post_id, 'laterpay_post_teaser', true );
        // generate teaser content, if it's empty
        if ( ! $teaser_content ) {
            $teaser_content = LaterPay_Helper_Post::add_teaser_to_the_post( $post );
        }
        // add paragraphs to teaser content through wpautop
        $teaser_content = wpautop( $teaser_content );

        // check, if user has admin rights
        $user_has_unlimited_access      = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );
        $preview_post_as_visitor        = LaterPay_Helper_User::preview_post_as_visitor( $post );

        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            // append time passes to content
            $content .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );

            return $content;
        }

        // get purchase link
        $purchase_link                  = LaterPay_Helper_Post::get_laterpay_purchase_link( $post_id );

        // get values for output states
        $teaser_content_only            = get_option( 'laterpay_teaser_content_only' );
        $show_post_ratings              = get_option( 'laterpay_ratings' );
        $user_has_already_voted         = LaterPay_Helper_Rating::check_if_user_voted_post_already( $post_id );
        $user_can_read_statistics       = LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id );
        $only_time_passes_allowed       = get_option( 'laterpay_only_time_pass_purchases_allowed' );
        $is_in_visible_test_mode        = get_option( 'laterpay_is_in_visible_test_mode' )
                                            && ! $this->config->get( 'is_in_live_mode' );

        // caching and Ajax
        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;

        // check, if user has access to content (because he already bought it)
        $access = LaterPay_Helper_Post::has_access_to_post( $post );

        // switch to 'admin' mode and load the correct content, if user can read post statistics
        if ( $user_can_read_statistics ) {
            $access = true;
        }

        // encrypt files contained in premium posts
        $content = LaterPay_Helper_File::get_encrypted_content( $post_id, $content, $access );
        $content = $wp_embed->autoembed( $content );

        // assign all required vars to the view templates
        $view_args = array(
            'post_id'                               => $post_id,
            'content'                               => $content,
            'teaser_content'                        => $wp_embed->autoembed( $teaser_content ),
            'teaser_content_only'                   => $teaser_content_only,
            'currency'                              => $currency,
            'price'                                 => $price,
            'revenue_model'                         => $revenue_model,
            'link'                                  => $purchase_link,
            'preview_post_as_visitor'               => $preview_post_as_visitor,
            'user_has_already_voted'                => $user_has_already_voted,
            'show_post_ratings'                     => $show_post_ratings,
            'purchase_link_is_hidden'               => LaterPay_Helper_View::purchase_link_is_hidden(),
            'time_passes_positioned_manually'       => $timepasses_positioned_manually,
            'purchase_button_positioned_manually'   => get_option( 'laterpay_purchase_button_positioned_manually' ),
            'only_time_pass_purchases_allowed'      => $only_time_passes_allowed,
            'is_in_visible_test_mode'               => $is_in_visible_test_mode,
        );
        $this->assign( 'laterpay', $view_args );

        // start collecting the output
        $html = '';

        // return the teaser content on non-singular pages (archive, feed, tax, author, search, ...)
        if ( ! is_singular() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            // prepend hint to feed items that reading the full content requires purchasing the post
            if ( is_feed() ) {
                $html .= sprintf(
                    __( '&mdash; Visit the post to buy its full content for %s %s &mdash; ', 'laterpay' ),
                    LaterPay_Helper_View::format_number( $price ),
                    $currency
                );
            }

            $html .= $this->get_text_view( 'frontend/partials/post/teaser' );
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );

            return $html;
        }

        /**
         * return the full encrypted content, if ...
         * ...the post was bought by a user
         * ...and logged_in_user does not preview the post as visitor
         * ...and caching is not activated or caching is activated and content is loaded via Ajax request
         */
        if ( $access && ! $preview_post_as_visitor && ( ! $caching_is_active || $is_ajax_and_caching_is_active ) ) {

            $context = array(
                'post'                          => $post,
                'access'                        => $access,
                'preview_post_as_visitor'       => $preview_post_as_visitor,
                'caching_is_active'             => $caching_is_active,
                'is_ajax_and_caching_is_active' => $is_ajax_and_caching_is_active,
            );

            $this->logger->info(
                __METHOD__ . ' - returned full encrypted content',
                $context
            );

            // append rating form to content, if content rating is enabled
            if ( $show_post_ratings ) {
                $content .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/rating-form' ) );
            }

            $content .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );

            return $content;
        }

        // add the purchase button as very first element of the content, if it is not positioned manually
        if ( (bool) get_option( 'laterpay_purchase_button_positioned_manually' ) === false ) {
            $html .= '<div class="lp_purchase-button-wrapper">';
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/purchase-button' ) );
            $html .= '</div>';
        }

        // add the teaser content
        $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/teaser' ) );

        if ( $only_time_passes_allowed ) {
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/after-teaser' ) );
        }

        if ( $teaser_content_only ) {
            // add teaser content plus a purchase link after the teaser content
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/purchase-link' ) );
        } else {
            // add excerpt of full content, covered by an overlay with a purchase button
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/overlay-with-purchase-button' ) );
        }

        if ( $caching_is_active ) {
            // if caching is enabled, wrap the teaser in a div, so it can be replaced with the full content,
            // if the post is / has already been purchased
            $html = '<div id="lp_js_postContentPlaceholder">' . $html . '</div>';
            $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );
            return $html;
        }

        // append time passes to content
        $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/time-passes' ) );

        return $html;
    }

    /**
     * Load the LaterPay identify iframe in the footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function modify_footer() {
        if ( ! is_singular() || ! LaterPay_Helper_Pricing::is_purchasable() ) {
            $this->logger->warning( __METHOD__ . ' - !is_singular or post is not purchasable' );
            return;
        }

        $this->logger->info( __METHOD__ );

        $client_options  = LaterPay_Helper_Config::get_php_client_options();
        $laterpay_client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $identify_link = $laterpay_client->get_identify_url();

        // assign all required vars to the view templates
        $view_args = array(
            'post_id'       => get_the_ID(),
            'identify_link' => $identify_link,
        );

        $this->assign( 'laterpay', $view_args );

        echo laterpay_sanitized( $this->get_text_view( 'frontend/partials/identify-iframe' ) );
    }

    /**
     * Get time passes that have access to the current posts.
     *
     * @return array of time pass ids with access
     */
    protected function get_time_passes_with_access() {
        $access                     = LaterPay_Helper_Post::get_access_state();
        $time_passes_with_access    = array();

        // get time passes with access
        foreach ( $access as $access_key => $access_value ) {
            // if access was purchased
            if ( $access_value === true ) {
                $access_key_exploded = explode( '_', $access_key );
                // if this is time pass key - store time pass id
                if ( $access_key_exploded[0] === LaterPay_Helper_TimePass::PASS_TOKEN ) {
                    $time_passes_with_access[] = $access_key_exploded[1];
                }
            }
        }

        return $time_passes_with_access;
    }

    /**
     * Render time pass HTML.
     *
     * @param array $pass
     *
     * @return string
     */
    public function render_time_pass( $pass = array() ) {
        $is_in_visible_test_mode = get_option( 'laterpay_is_in_visible_test_mode' ) && ! $this->config->get( 'is_in_live_mode' );

        $defaults = array(
            'pass_id'     => 0,
            'title'       => LaterPay_Helper_TimePass::get_default_options( 'title' ),
            'description' => LaterPay_Helper_TimePass::get_description(),
            'price'       => LaterPay_Helper_TimePass::get_default_options( 'price' ),
            'url'         => '',
        );

        $laterpay_pass = array_merge( $defaults, $pass );
        if ( ! empty( $laterpay_pass['pass_id'] ) ) {
            $laterpay_pass['url'] = LaterPay_Helper_TimePass::get_laterpay_purchase_link( $laterpay_pass['pass_id'] );
        }

        $laterpay_pass['preview_post_as_visitor'] = LaterPay_Helper_User::preview_post_as_visitor( get_post() );
        $laterpay_pass['is_in_visible_test_mode'] = $is_in_visible_test_mode;

        $args = array(
            'standard_currency' => get_option( 'laterpay_currency' ),
        );
        $this->assign( 'laterpay',      $args );
        $this->assign( 'laterpay_pass', $laterpay_pass );

        $string = $this->get_text_view( 'backend/partials/time-pass' );

        return $string;
    }

    /**
     * Load LaterPay stylesheets.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_stylesheets() {
        $this->logger->info( __METHOD__ );

        wp_register_style(
            'laterpay-post-view',
            $this->config->css_url . 'laterpay-post-view.css',
            array(),
            $this->config->version
        );

        // always enqueue 'laterpay-post-view' to ensure that LaterPay shortcodes have styling
        wp_enqueue_style( 'laterpay-post-view' );
    }

    /**
     * Load LaterPay Javascript libraries.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_scripts() {
        $this->logger->info( __METHOD__ );

        wp_register_script(
            'laterpay-yui',
            $this->config->get( 'laterpay_yui_js' ),
            array(),
            null,
            false // LaterPay YUI scripts *must* be loaded asynchronously from the HEAD
        );
        wp_register_script(
            'laterpay-peity',
            $this->config->get( 'js_url' ) . 'vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-post-view',
            $this->config->get( 'js_url' ) . 'laterpay-post-view.js',
            array( 'jquery', 'laterpay-peity' ),
            $this->config->get( 'version' ),
            true
        );
        $post = get_post();
        wp_localize_script(
            'laterpay-post-view',
            'lpVars',
            array(
                'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
                'post_id'               => ! empty( $post ) ? $post->ID : false,
                'debug'                 => (bool) $this->config->get( 'debug_mode' ),
                'caching'               => (bool) $this->config->get( 'caching.compatible_mode' ),
                'nonces'                => array(
                    'content'           => wp_create_nonce( 'laterpay_post_load_purchased_content' ),
                    'statistic'         => wp_create_nonce( 'laterpay_post_statistic_render' ),
                    'tracking'          => wp_create_nonce( 'laterpay_post_track_views' ),
                    'rating'            => wp_create_nonce( 'laterpay_post_rating_summary' ),
                    'voucher'           => wp_create_nonce( 'laterpay_redeem_voucher_code' ),
                    'gift'              => wp_create_nonce( 'laterpay_get_gift_card_actions' ),
                    'premium'           => wp_create_nonce( 'laterpay_get_premium_shortcode_link' ),
                ),
                'i18n'                  => array(
                    'alert'             => __( 'In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay' ),
                    'validVoucher'      => __( 'Voucher code accepted.', 'laterpay' ),
                    'invalidVoucher'    => __( ' is not a valid voucher code!', 'laterpay' ),
                    'codeTooShort'      => __( 'Please enter a six-digit voucher code.', 'laterpay' ),
                    'generalAjaxError'  => __( 'An error occurred. Please try again.', 'laterpay' ),
                ),
                'default_currency'      => get_option( 'laterpay_currency' ),
            )
        );

        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-post-view' );
    }
}
