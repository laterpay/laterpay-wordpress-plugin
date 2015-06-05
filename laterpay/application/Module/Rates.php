<?php

/**
 * LaterPay TimePasses class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Module_Rates extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_post_content' => array(
                array( 'modify_post_content' ),
            ),
        );
    }

    /**
     * Modify the post content of paid posts.
     *
     * @wp-hook the_content
     *
     * @param LaterPay_Core_Event $event
     *
     * @return string $content
     */
    public function modify_post_content( LaterPay_Core_Event $event ) {
        $post = get_post();
        if ( $post === null ) {
            return;
        }
        // do nothing, if post is not in the enabled post types
        if ( ! $this->is_enabled_post_type( $post->post_type ) ) {
            return;
        }
        $post_id = $post->ID;
        // get pricing data
        $price                          = LaterPay_Helper_Pricing::get_post_price( $post_id );

        // return the full content, if no price was found for the post
        if ( $price == 0 ) {
            return;
        }
        // check, if user has admin rights
        $user_has_unlimited_access      = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );
        $preview_post_as_visitor        = LaterPay_Helper_User::preview_post_as_visitor( $post );
        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            return;
        }

        $content = $event->get_result();

        // get values for output states
        $show_post_ratings              = get_option( 'laterpay_ratings' );
        $user_can_read_statistics       = LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id );

        // caching and Ajax
        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;
        $preview_post_as_visitor        = LaterPay_Helper_User::preview_post_as_visitor( $post );

        // check, if user has access to content (because he already bought it)
        $access = LaterPay_Helper_Post::has_access_to_post( $post );

        // switch to 'admin' mode and load the correct content, if user can read post statistics
        if ( $user_can_read_statistics ) {
            $access = true;
        }
        /**
         * return the full encrypted content, if ...
         * ...the post was bought by a user
         * ...and logged_in_user does not preview the post as visitor
         * ...and caching is not activated or caching is activated and content is loaded via Ajax request
         */
        if ( $access && ! $preview_post_as_visitor && ( ! $caching_is_active || $is_ajax_and_caching_is_active ) && $show_post_ratings ) {
            $user_has_already_voted = LaterPay_Helper_Rating::check_if_user_voted_post_already( $post_id );
            // append rating form to content, if content rating is enabled
            $view_args = array(
                'post_id'                   => $post_id,
                'user_has_already_voted'    => $user_has_already_voted,
            );
            $this->assign( 'laterpay', $view_args );
            $content .= LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/rating-form' ) );
        }

        $event->set_result( $content );
    }
}
