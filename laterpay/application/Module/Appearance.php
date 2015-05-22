<?php
/**
 * LaterPay Appearance class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Module_Appearance extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface {
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_purchase_button' => array(
                array( 'on_preview_post_as_admin', 10 ),
                array( 'on_view_purchased_post_as_visitor', 10 ),
                array( 'on_visible_test_mode', 10 ),
            ),
        );
    }

    /**
     * Stops event bubbling for admin with preview_post_as_visitor option disabled
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_preview_post_as_admin( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        $preview_post_as_visitor   = LaterPay_Helper_User::preview_post_as_visitor( $post );
        $user_has_unlimited_access = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );
        if ( $user_has_unlimited_access && ! $preview_post_as_visitor ) {
            $event->stop_propagation();
        }
        $event->add_argument( 'attributes', array( 'preview_post_as_visitor' => $preview_post_as_visitor ) );
        $event->set_argument( 'preview_post_as_visitor', $preview_post_as_visitor );
    }

    /**
     * Stops event bubbling if the current post was already purchased and current user is not an admin
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_view_purchased_post_as_visitor( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        $preview_post_as_visitor = LaterPay_Helper_User::preview_post_as_visitor( $post );
        if ( LaterPay_Helper_Post::has_access_to_post( $post ) && ! $preview_post_as_visitor ) {
            return;
        }
    }

    /**
     * Checks, if the current post is rendered in visible test mode
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_visible_test_mode( LaterPay_Core_Event $event ) {
        $is_in_visible_test_mode = get_option( 'laterpay_is_in_visible_test_mode' )
                                   && ! $this->config->get( 'is_in_live_mode' );

        $event->add_argument( 'attributes', array( 'is_in_visible_test_mode' => $is_in_visible_test_mode ) );
        $event->set_argument( 'is_in_visible_test_mode', $is_in_visible_test_mode );
    }

    /**
     * Checks, if the current area is admin
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_admin_view( LaterPay_Core_Event $event ) {
        if ( ! is_admin() ) {
            $event->stop_propagation();
        }
    }


}
