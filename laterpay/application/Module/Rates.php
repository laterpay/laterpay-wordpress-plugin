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
     * @see LaterPay_Core_Event_SubscriberInterface::get_shared_events()
     */
    public static function get_shared_events() {
        return array();
    }

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_show_rating_form' => array(
                array( 'add_rating_form' ),
            ),
            'laterpay_post_rating' => array(
                array( 'show_summary_rating_placeholder', 0 ),
            ),
        );
    }

    /**
     * Add rating form to content
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function add_rating_form( LaterPay_Core_Event $event ) {
        $post = $event->get_argument( 'post' );

        // ger current content
        $content = $event->get_argument( 'content' );

        /** Add rating form if post purchased */
        if ( $event->get_argument( 'access' ) && ! $event->get_argument( 'is_preview' ) ) {
            $user_has_already_voted = LaterPay_Helper_Rating::check_if_user_voted_post_already( $post->ID );
            // append rating form to content, if content rating is enabled
            if ( ! $user_has_already_voted ) {
                $view_args = array(
                    'post_id' => $post->ID,
                );
                $this->assign( 'laterpay', $view_args );
                $rating_form = LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/rating-form' ) );
                $content = $rating_form . $content;
            }
        }

        $event->set_result( $content );
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
    public function show_summary_rating_placeholder( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        if ( $post === null ) {
            return;
        }

        $show_post_ratings = get_option( 'laterpay_ratings' );
        $content = $event->get_result();
        if ( $show_post_ratings ) {
            $content .= '<div id="lp_js_postRatingPlaceholder"></div>';
        }

        $event->set_result( $content );
    }
}
