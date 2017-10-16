<?php

/**
 * LaterPay Subscriptions class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Module_Subscriptions extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface {

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
            'laterpay_time_passes' => array(
                array( 'render_subscriptions_list', 15 ),
            ),
            'laterpay_purchase_overlay_content' => array(
                array( 'on_purchase_overlay_content', 6 ),
            ),
        );
    }

    /**
     * Callback to render a LaterPay subscriptions inside time pass widget.
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function render_subscriptions_list( LaterPay_Core_Event $event ) {
        $view_args = array(
            'subscriptions' => LaterPay_Helper_Subscription::get_active_subscriptions(),
        );

        $this->assign( 'laterpay_sub', $view_args );

        // prepare subscriptions layout
        $subscriptions = LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/widget/subscriptions' ) );

        $event->set_argument( 'subscriptions', $subscriptions );
    }

    /**
     * Render subscription HTML.
     *
     * @param array $pass
     *
     * @return string
     */
    public function render_subscription( $args = array() ) {
        $defaults = array(
            'id'          => 0,
            'title'       => LaterPay_Helper_Subscription::get_default_options( 'title' ),
            'description' => LaterPay_Helper_Subscription::get_description(),
            'price'       => LaterPay_Helper_Subscription::get_default_options( 'price' ),
            'url'         => '',
        );

        $args = array_merge( $defaults, $args );

        if ( ! empty( $args['id'] ) ) {
            $args['url'] = LaterPay_Helper_Subscription::get_subscription_purchase_link( $args['id'] );
        }

        $args['preview_post_as_visitor'] = LaterPay_Helper_User::preview_post_as_visitor( get_post() );

        $this->assign( 'laterpay_subscription', $args );
        $this->assign( 'laterpay',      array(
            'standard_currency' => $this->config->get( 'currency.code' ),
        ));

        $string = $this->get_text_view( 'backend/partials/subscription' );

        return $string;
    }

    /**
     * Get subscriptions data
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function on_purchase_overlay_content( LaterPay_Core_Event $event )
    {
        $data = $event->get_result();
        $post = $event->get_argument( 'post' );

        // default value
        $data['subscriptions'] = array();

        $subscriptions = LaterPay_Helper_Subscription::get_subscriptions_list_by_post_id(
            $post->ID,
            null,
            true
        );

        // loop through subscriptions
        foreach ($subscriptions as $subscription) {
            $data['subscriptions'][] = array(
                'title'       => $subscription['title'],
                'description' => $subscription['description'],
                'price'       => LaterPay_Helper_View::format_number( $subscription['price'] ),
                'url'         => LaterPay_Helper_Subscription::get_subscription_purchase_link( $subscription['id'] )
            );
        }

        $event->set_result( $data );
    }
}
