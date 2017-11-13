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
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_post_content' => array(
                array( 'laterpay_on_plugin_is_working', 300 ),
                array( 'modify_post_content', 250 ),
            ),
            'laterpay_posts' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'prefetch_post_access', 10 ),
                array( 'hide_free_posts_with_premium_content' ),
                array( 'hide_paid_posts', 999 ),
            ),
            'laterpay_attachment_image_attributes' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'encrypt_image_source' ),
            ),
            'laterpay_attachment_get_url' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'encrypt_attachment_url' ),
            ),
            'laterpay_attachment_prepend' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'prepend_attachment' ),
            ),
            'laterpay_enqueue_scripts' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'add_frontend_stylesheets', 20 ),
                array( 'add_frontend_scripts' ),
            ),
            'laterpay_post_teaser' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'generate_post_teaser' ),
            ),
            'laterpay_feed_content' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'generate_feed_content' ),
            ),
            'laterpay_teaser_content_mode' => array(
                array( 'get_teaser_mode' ),
            ),
            'wp_ajax_laterpay_post_load_purchased_content' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_purchased_content' ),
            ),
            'wp_ajax_nopriv_laterpay_post_load_purchased_content' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_purchased_content' ),
            ),
            'wp_ajax_laterpay_post_rate_purchased_content' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_rate_purchased_content' ),
            ),
            'wp_ajax_nopriv_laterpay_post_rate_purchased_content' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_rate_purchased_content' ),
            ),
            'wp_ajax_laterpay_post_rating_summary' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_rating_summary' ),
            ),
            'wp_ajax_nopriv_laterpay_post_rating_summary' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_rating_summary' ),
            ),
            'wp_ajax_laterpay_redeem_voucher_code' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_redeem_voucher_code' ),
            ),
            'wp_ajax_nopriv_laterpay_redeem_voucher_code' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_redeem_voucher_code' ),
            ),
            'wp_ajax_laterpay_load_files' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_files' ),
            ),
            'wp_ajax_nopriv_laterpay_load_files' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_load_files' ),
            ),
        );
    }

    /**
     * Ajax method to get the cached article.
     * Required, because there could be a price change in LaterPay and we always need the current article price.
     *
     * @wp-hook wp_ajax_laterpay_post_load_purchased_content, wp_ajax_nopriv_laterpay_post_load_purchased_content
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     * @throws LaterPay_Core_Exception_PostNotFound
     *
     * @return void
     */
    public function ajax_load_purchased_content( LaterPay_Core_Event $event ) {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_post_load_purchased_content' ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'action' );
        }

        if ( ! isset( $_GET['post_id'] ) ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'post_id' );
        }

        global $post;

        $post_id = absint( $_GET['post_id'] );
        $post    = get_post( $post_id );

        if ( $post === null ) {
            throw new LaterPay_Core_Exception_PostNotFound( $post_id );
        }

        if ( ! is_user_logged_in() && ! LaterPay_Helper_Post::has_access_to_post( $post ) ) {
            // check access to paid post for not logged in users only and prevent
            $event->stop_propagation();
            return;
        } else if ( is_user_logged_in() && LaterPay_Helper_User::preview_post_as_visitor( $post ) ) {
            // return, if user is logged in and 'preview_as_visitor' is activated
            $event->stop_propagation();
            return;
        }

        // call 'the_post' hook to enable modification of loaded data by themes and plugins
        do_action_ref_array( 'the_post', array( &$post ) );

        $content = apply_filters( 'the_content', $post->post_content );
        $content = str_replace( ']]>', ']]&gt;', $content );
        $event->set_result( $content );
    }

    /**
     * Ajax method to rate purchased content.
     *
     * @wp-hook wp_ajax_laterpay_post_rate_purchased_content, wp_ajax_nopriv_laterpay_post_rate_purchased_content
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_FormValidation
     *
     * @return void
     */
    public function ajax_rate_purchased_content( LaterPay_Core_Event $event ) {
        $post_rating_form = new LaterPay_Form_PostRating( $_POST );
        $event->set_result(
            array(
                'success' => false,
            )
        );

        if ( ! $post_rating_form->is_valid() ) {
            throw new LaterPay_Core_Exception_FormValidation( get_class( $post_rating_form ), $post_rating_form->get_errors() );
        }

        $post_id       = $post_rating_form->get_field_value( 'post_id' );
        $rating_value  = $post_rating_form->get_field_value( 'rating_value' );
        $is_user_voted = LaterPay_Helper_Rating::check_if_user_voted_post_already( $post_id );

        if ( $is_user_voted ) {
            $event->set_result(
                array(
                    'success' => false,
                )
            );
            return;
        }

        // update rating data with submitted rating
        $rating       = LaterPay_Helper_Rating::get_post_rating_data( $post_id );
        $rating_index = (string) $rating_value;
        $rating[ $rating_index ] += 1;

        update_post_meta( $post_id, 'laterpay_rating', $rating );
        LaterPay_Helper_Rating::set_user_voted( $post_id );

        $event->set_result(
            array(
                'success' => true,
                'message' => __( 'Thank you very much for rating!', 'laterpay' ),
            )
        );
    }

    /**
     * Ajax method to get rating summary.
     *
     * @wp-hook wp_ajax_laterpay_post_rating_summary, wp_ajax_nopriv_laterpay_post_rating_summary
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     * @throws LaterPay_Core_Exception_PostNotFound
     *
     * @return void
     */
    public function ajax_load_rating_summary( LaterPay_Core_Event $event ) {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_post_rating_summary' ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'action' );
        }

        if ( ! isset( $_GET['post_id'] ) ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'post_id' );
        }

        $post_id = absint( $_GET['post_id'] );
        $post    = get_post( $post_id );

        if ( $post === null ) {
            throw new LaterPay_Core_Exception_PostNotFound( $post_id );
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

        $event->set_result( LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/rating-summary' ) ) );
    }

    /**
     * Ajax method to redeem voucher code.
     *
     * @wp-hook wp_ajax_laterpay_redeem_voucher_code, wp_ajax_nopriv_laterpay_redeem_voucher_code
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     *
     * @return void
     */
    public function ajax_redeem_voucher_code( LaterPay_Core_Event $event ) {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_redeem_voucher_code' ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'action' );
        }

        if ( ! isset( $_GET['code'] ) ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'code' );
        }

        if ( ! isset( $_GET['link'] ) ) {
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'link' );
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
            // prepare URL before use
            $data       = array(
                'voucher' => $code,
                'link'    => $is_gift ? home_url() : esc_url_raw( $_GET['link'] ),
                'price'   => $code_data['price'],
            );

            // get new purchase URL
            $url = LaterPay_Helper_TimePass::get_laterpay_purchase_link( $pass_id, $data );

            if ( $url ) {
                $event->set_result(
                    array(
                        'success' => true,
                        'pass_id' => $pass_id,
                        'price'   => LaterPay_Helper_View::format_number( $code_data['price'] ),
                        'url'     => $url,
                    )
                );
            }
            return;
        }

        $event->set_result(
            array(
                'success' => false,
            )
        );
    }

    /*
     * Encrypt image source to prevent direct access.
     *
     * @wp-hook wp_get_attachment_image_attributes
     *
     * @param LaterPay_Core_Event $event
     *
     * @var array        $attr Attributes for the image markup
     * @var WP_Post      $post Image attachment post
     * @var string|array $size Requested size
     *
     * @return mixed
     */
    public function encrypt_image_source( LaterPay_Core_Event $event ) {
        list( $attr, $post, $size ) = $event->get_arguments() + array( '', '', '' );
        $attr                           = $event->get_result();
        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;

        if ( is_admin() && ! $is_ajax_and_caching_is_active ) {
            return;
        }

        $is_purchasable = LaterPay_Helper_Pricing::is_purchasable( $post->ID );
        if ( $is_purchasable && $post->ID === get_the_ID() ) {
            $access         = LaterPay_Helper_Post::has_access_to_post( $post );
            $attr           = $event->get_result();
            $attr['src']    = LaterPay_Helper_File::get_encrypted_resource_url(
                $post->ID,
                $attr['src'],
                $access,
                'attachment'
            );
        }

        $event->set_result( $attr );
    }

    /**
     * Encrypt attachment URL to prevent direct access.
     *
     * @wp-hook wp_get_attachment_url
     *
     * @param LaterPay_Core_Event $event
     * @var string $url     URL for the given attachment
     * @var int    $post_id Attachment ID
     *
     * @return string
     */
    public function encrypt_attachment_url( LaterPay_Core_Event $event ) {
        list( $url, $post_id ) = $event->get_arguments() + array( '', '' );
        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;

        if ( is_admin() && ! $is_ajax_and_caching_is_active ) {
            return;
        }

        // get current post
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        if ( $post === null ) {
            return;
        }

        $url = $event->get_result();

        $is_purchasable = LaterPay_Helper_Pricing::is_purchasable( $post->ID );
        if ( $is_purchasable && $post->ID === $post_id ) {
            $access = LaterPay_Helper_Post::has_access_to_post( $post );

            // prevent from exec, if attachment is an image and user does not have access
            if ( ! $access && strpos( $post->post_mime_type, 'image' ) !== false ) {
                $event->set_result( '' );
                return;
            }

            // encrypt attachment URL
            $url = LaterPay_Helper_File::get_encrypted_resource_url(
                $post_id,
                $url,
                $access,
                'attachment'
            );
        }

        $event->set_result( $url );
    }

    /**
     * Prevent prepending of attachment before paid content.
     *
     * @wp-hook prepend_attachment
     *
     * @param LaterPay_Core_Event $event
     * @var string $attachment The attachment HTML output
     *
     * @return void
     */
    public function prepend_attachment( LaterPay_Core_Event $event ) {
        $attachment = $event->get_result();

        // get current post
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        if ( $post === null ) {
            return;
        }

        $is_purchasable          = LaterPay_Helper_Pricing::is_purchasable( $post->ID );
        $access                  = LaterPay_Helper_Post::has_access_to_post( $post );
        $preview_post_as_visitor = LaterPay_Helper_User::preview_post_as_visitor( $post );;
        if ( $is_purchasable && ! $access || $preview_post_as_visitor ) {
            $event->set_result( '' );
            return;
        }

        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;
        if ( $is_ajax_and_caching_is_active ) {
            $event->set_result( '' );
            return;
        }
        $event->set_result( $attachment );
    }

    /**
     * Hide free posts with premium content from the homepage
     *
     * @wp-hook the_posts
     * @param LaterPay_Core_Event $event
     *
     * @return array $posts
     */
    public function hide_free_posts_with_premium_content( LaterPay_Core_Event $event ) {
        $posts = (array) $event->get_result();

        // check if current page is a homepage and hide free posts option enabled
        if ( ! get_option( 'laterpay_hide_free_posts' ) || ! is_home() || ! is_front_page() ) {
            return;
        }

        // loop through query and find free posts with premium content
        foreach ( $posts as $key => $post ) {
            if ( has_shortcode( $post->post_content, 'laterpay_premium_download' ) && ! LaterPay_Helper_Pricing::is_purchasable( $post->ID ) ) {
                unset( $posts[ $key ] );
            }
        }

        $event->set_result( array_values( $posts ) );
    }

    /**
     * Prefetch the post access for posts in the loop.
     *
     * In archives or by using the WP_Query-Class, we can prefetch the access
     * for all posts in a single request instead of requesting every single post.
     *
     * @wp-hook the_posts
     *
     * @param LaterPay_Core_Event $event
     *
     * @return array $posts
     */
    public function prefetch_post_access( LaterPay_Core_Event $event ) {
        $posts = (array) $event->get_result();
        // prevent exec if admin
        if ( is_admin() ) {
            return;
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

        // check access for subscriptions
        $subscriptions = LaterPay_Helper_Subscription::get_tokenized_ids();

        foreach ( $subscriptions as $subscription ) {
            // add a tokenized subscription id to the array of posts to be queried for access, if it's not loaded already
            if ( ! array_key_exists( $subscription, LaterPay_Helper_Post::get_access_state() ) ) {
                $post_ids[] = $subscription;
            }
        }

        if ( empty( $post_ids ) ) {
            return;
        }

        $this->logger->info(
            __METHOD__,
            array( 'post_ids' => $post_ids )
        );

        $access_result = LaterPay_Helper_Request::laterpay_api_get_access( $post_ids );

        if ( empty( $access_result ) || ! array_key_exists( 'articles', $access_result ) ) {
            return;
        }

        foreach ( $access_result['articles'] as $post_id => $state ) {
            LaterPay_Helper_Post::set_access_state( $post_id, (bool) $state['access'] );
        }
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
     * @param LaterPay_Core_Event $event
     * @internal WP_Embed $wp_embed
     */
    public function modify_post_content( LaterPay_Core_Event $event ) {
        global $wp_embed;

        $content = $event->get_result();

        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        if ( $post === null ) {
            $event->stop_propagation();
            return;
        }

        // check, if user has access to content (because he already bought it)
        $access = LaterPay_Helper_Post::has_access_to_post( $post );

        // caching and Ajax
        $caching_is_active = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax           = defined( 'DOING_AJAX' ) && DOING_AJAX;

        // check, if user has admin rights
        $user_has_unlimited_access = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );
        $preview_post_as_visitor   = LaterPay_Helper_User::preview_post_as_visitor( $post );

        // switch to 'admin' mode and load the correct content, if user can read post statistics
        if ($user_has_unlimited_access && ! $preview_post_as_visitor ) {
            $access = true;
        }

        // set necessary arguments
        $event->set_arguments(
            array(
                'post'       => $post,
                'access'     => $access,
                'is_cached'  => $caching_is_active,
                'is_ajax'    => $is_ajax,
                'is_preview' => $preview_post_as_visitor,
            )
        );

        // maybe add ratings
        if ( get_option( 'laterpay_ratings' ) ) {
            $ratings_event = new LaterPay_Core_Event();
            $ratings_event->set_echo( false );
            $ratings_event->set_arguments( $event->get_arguments() );
            $ratings_event->set_argument( 'content', $content );
            laterpay_event_dispatcher()->dispatch( 'laterpay_show_rating_form', $ratings_event );
            $content = $ratings_event->get_result();
        }

        // stop propagation
        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            $event->stop_propagation();
            return;
        }

        // generate teaser
        $teaser_event = new LaterPay_Core_Event();
        $teaser_event->set_echo( false );
        laterpay_event_dispatcher()->dispatch( 'laterpay_post_teaser', $teaser_event );
        $teaser_content = $teaser_event->get_result();

        // generate overlay content
        $number_of_words = LaterPay_Helper_String::determine_number_of_words( $content );
        $overlay_content = LaterPay_Helper_String::truncate(  $content, $number_of_words , array( 'html' => true, 'words' => true ) );
        $event->set_argument( 'overlay_content', $overlay_content );

        // set teaser argument
        $event->set_argument( 'teaser', $teaser_content );
        $event->set_argument( 'content', $content );

        // get values for output states
        $teaser_mode_event = new LaterPay_Core_Event();
        $teaser_mode_event->set_echo( false );
        $teaser_mode_event->set_argument( 'post_id', $post->ID );
        laterpay_event_dispatcher()->dispatch( 'laterpay_teaser_content_mode', $teaser_mode_event );
        $teaser_mode = $teaser_mode_event->get_result();

        // return the teaser content on non-singular pages (archive, feed, tax, author, search, ...)
        if ( ! is_singular() && ! $is_ajax ) {
            // prepend hint to feed items that reading the full content requires purchasing the post
            if ( is_feed() ) {
                $feed_event = new LaterPay_Core_Event();
                $feed_event->set_echo( false );
                $feed_event->set_argument( 'post', $post );
                $feed_event->set_argument( 'teaser_content', $teaser_content );
                laterpay_event_dispatcher()->dispatch( 'laterpay_feed_content', $feed_event );
                $content = $feed_event->get_result();
            } else {
                $content = $teaser_content;
            }

            $event->set_result( $content );
            $event->stop_propagation();
            return;
        }

        if ( ! $access ) {
            // show proper teaser
            switch ($teaser_mode) {
                case '1':
                    // add excerpt of full content, covered by an overlay with a purchase button
                    $overlay_event = new LaterPay_Core_Event();
                    $overlay_event->set_echo( false );
                    $overlay_event->set_arguments( $event->get_arguments() );
                    laterpay_event_dispatcher()->dispatch( 'laterpay_explanatory_overlay', $overlay_event );
                    $content = $teaser_content . $overlay_event->get_result();
                    break;
                case '2':
                    // add excerpt of full content, covered by an overlay with a purchase button
                    $overlay_event = new LaterPay_Core_Event();
                    $overlay_event->set_echo( false );
                    $overlay_event->set_arguments( $event->get_arguments() );
                    laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_overlay', $overlay_event );
                    $content = $teaser_content . $overlay_event->get_result();
                    break;
                default:
                    // add teaser content plus a purchase link after the teaser content
                    $link_event = new LaterPay_Core_Event();
                    $link_event->set_echo( false );
                    laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_link', $link_event );
                    $content = $teaser_content . $link_event->get_result();
                    break;
            }
        } else {
            // encrypt files contained in premium posts
            $content = LaterPay_Helper_File::get_encrypted_content( $post->ID, $content, $access );
            $content = $wp_embed->autoembed( $content );
        }

        $event->set_result( $content );
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

        // apply colors config
        LaterPay_Helper_View::apply_colors( 'laterpay-post-view' );

        // apply purchase overlay config
        LaterPay_Helper_Appearance::add_overlay_styles( 'laterpay-post-view' );
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
                'i18n'                  => array(
                    'alert'             => __( 'In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay' ),
                    'validVoucher'      => __( 'Voucher code accepted.', 'laterpay' ),
                    'invalidVoucher'    => __( ' is not a valid voucher code!', 'laterpay' ),
                    'codeTooShort'      => __( 'Please enter a six-digit voucher code.', 'laterpay' ),
                    'generalAjaxError'  => __( 'An error occurred. Please try again.', 'laterpay' ),
                ),
                'default_currency'      => $this->config->get( 'currency.code' ),
            )
        );

        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-post-view' );
    }

    /**
     * Hide paid posts from access in the loop.
     *
     * In archives or by using the WP_Query-Class, we can prefetch the access
     * for all posts in a single request instead of requesting every single post.
     *
     * @wp-hook the_posts
     *
     * @param LaterPay_Core_Event $event
     *
     */
    public function hide_paid_posts( LaterPay_Core_Event $event ) {
        if (true === LaterPay_Helper_Request::isLpApiAvailability())
        {
            return;
        }

        $posts    = (array) $event->get_result();
        $behavior = (int) get_option( 'laterpay_api_fallback_behavior', 0 );

        if (2 === $behavior) {
            $result = array();
            $count = 0;

            foreach ( $posts as $post ) {
                $paid = LaterPay_Helper_Pricing::get_post_price( $post->ID ) !== 0;
                if ( ! $paid ) {
                    $result[] = $post;
                } else {
                    $count++;
                }
            }

            $context = array(
                'hidden' => $count,
            );

            laterpay_get_logger()->info( __METHOD__, $context );

            $event->set_result( $result );
        }
    }

    /**
     * @param LaterPay_Core_Event $event
     */
    public function generate_post_teaser( LaterPay_Core_Event $event ) {
        global $wp_embed;
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        if ( $post === null ) {
            return;
        }
        // get the teaser content
        $teaser_content = get_post_meta( $post->ID, 'laterpay_post_teaser', true );
        // generate teaser content, if it's empty
        if ( ! $teaser_content ) {
            $teaser_content = LaterPay_Helper_Post::add_teaser_to_the_post( $post );
        }

        // autoembed
        $teaser_content = $wp_embed->autoembed( $teaser_content );
        // add paragraphs to teaser content through wpautop
        $teaser_content = wpautop( $teaser_content );
        // get_the_content functionality for custom content
        $teaser_content = LaterPay_Helper_Post::get_the_content( $teaser_content, $post->ID );

        // assign all required vars to the view templates
        $view_args = array(
            'teaser_content' => $teaser_content,
        );

        $this->assign( 'laterpay', $view_args );
        $html = $event->get_result();
        $html .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/teaser' ) );

        $event->set_result( $html );
    }

    /**
     * @param LaterPay_Core_Event $event
     */
    public function generate_feed_content( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }
        if ( $event->has_argument( 'teaser_content' ) ) {
            $teaser_content = $event->get_argument( 'teaser_content' );
        } else {
            $teaser_content = '';
        }
        if ( $event->has_argument( 'hint' ) ) {
            $feed_hint = $event->get_argument( 'feed_hint' );
        } else {
            $feed_hint = __( '&mdash; Visit the post to buy its full content for {price} {currency} &mdash; {teaser_content}', 'laterpay' );
        }
        $post_id = $post->ID;
        // get pricing data
        $currency   = $this->config->get( 'currency.code' );
        $price      = LaterPay_Helper_Pricing::get_post_price( $post_id );

        $html = $event->get_result();
        $html .= str_replace( array( '{price}', '{currency}', '{teaser_content}' ), array( $price, $currency, $teaser_content ), $feed_hint );

        $event->set_result( $html );
    }

    /**
     * Setup default teaser content preview mode
     *
     * @param LaterPay_Core_Event $event
     */
    public function get_teaser_mode( LaterPay_Core_Event $event ) {
        $event->set_result( get_option( 'laterpay_teaser_mode' ) );
    }

    /**
     * Ajax callback to load a file through a script to prevent direct access.
     *
     * @wp-hook wp_ajax_laterpay_load_files, wp_ajax_nopriv_laterpay_load_files
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function ajax_load_files( LaterPay_Core_Event $event ) {
        $file_helper = new LaterPay_Helper_File();
        $file_helper->load_file( $event );
    }
}
