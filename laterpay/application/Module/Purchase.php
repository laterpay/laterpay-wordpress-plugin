<?php

/**
 * LaterPay Purchase class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Module_Purchase extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_shared_events()
     */
    public static function get_shared_events() {
        return array(
            'laterpay_is_purchasable' => array(
                array( 'is_purchasable' ),
            ),
            'laterpay_on_view_purchased_post_as_visitor' => array(
                array( 'on_view_purchased_post_as_visitor' ),
            ),
        );
    }

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_loaded' => array(
                array( 'buy_post', 10 ),
                array( 'set_token', 5 )
            ),
            'laterpay_purchase_button' => array(
                array( 'laterpay_on_preview_post_as_admin', 200 ),
                array( 'laterpay_on_view_purchased_post_as_visitor', 200 ),
                array( 'is_purchasable', 100 ),
                array( 'on_purchase_button' ),
                array( 'purchase_button_position', 0 ),
            ),
            'laterpay_purchase_overlay' => array(
                array( 'laterpay_on_view_purchased_post_as_visitor', 200 ),
                array( 'is_purchasable', 100 ),
                array( 'on_purchase_overlay' ),
            ),
            'laterpay_purchase_overlay_content' => array(
                array( 'on_purchase_overlay_content' ),
                array( 'is_purchasable', 100 ),
            ),
            'laterpay_purchase_link' => array(
                array( 'laterpay_on_preview_post_as_admin', 200 ),
                array( 'laterpay_on_view_purchased_post_as_visitor', 200 ),
                array( 'is_purchasable', 100 ),
                array( 'on_purchase_link' ),
            ),
            'laterpay_post_content' => array(
                array( 'laterpay_on_view_purchased_post_as_visitor', 200 ),
                array( 'is_purchasable', 100 ),
                array( 'modify_post_content', 5 ),
            ),
            'laterpay_check_user_access' => array(
                array( 'check_user_access' ),
            ),
        );
    }

    /**
     * Renders LaterPay purchase button
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_purchase_button( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }

        $current_post_id = null;
        if ( $event->has_argument( 'post' ) ) {
            $current_post_id = $event->get_argument( 'current_post' );
        }

        // create account links URL with passed parameters
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $back_url    = get_permalink( $current_post_id ? $current_post_id : $post->ID );
        $content_ids = LaterPay_Helper_Post::get_content_ids( $post->ID );

        $view_args = array_merge( array(
                'post_id'           => $post->ID,
                'link'              => LaterPay_Helper_Post::get_laterpay_purchase_link( $post->ID, $current_post_id ),
                'currency'          => $this->config->get( 'currency.default' ),
                'price'             => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
                'notification_text' => __( 'I already bought this', 'laterpay' ),
                'identify_url'      => $client->get_identify_url( $back_url, $content_ids ),
                'attributes'        => array(),
            ),
            $event->get_arguments()
        );

        $this->assign( 'laterpay', $view_args );
        $html = $this->get_text_view( 'frontend/partials/widget/purchase-button' );

        $event->set_result( $html )
            ->set_arguments( $view_args );
    }

    /**
     * Renders LaterPay purchase overlay
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_purchase_overlay( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }
        if ( $event->has_argument( 'content' ) ) {
            $content = $event->get_argument( 'content' );
        } else {
            $content = get_the_content();
        }
        $revenue_model              = LaterPay_Helper_Pricing::get_post_revenue_model( $post->ID );
        // get overlay content
        $overlay_content_event = new LaterPay_Core_Event( array($revenue_model) );
        $overlay_content_event->set_echo( false );
        laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_overlay_content', $overlay_content_event );

        $view_args = array(
            'content'                           => $content,
            'overlay_content'                   => (array) $overlay_content_event->get_result(),
        );

        $this->assign( 'laterpay', $view_args );
        $html = $this->get_text_view( 'frontend/partials/widget/purchase-overlay' );

        $event->set_result( $html );
    }

    /**
     * Renders LaterPay purchase link
     *
     * @param LaterPay_Core_Event $event
     */
    public function on_purchase_link( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }
        $post_id = $post->ID;
        // get pricing data
        $currency                       = $this->config->get( 'currency.default' );
        $price                          = LaterPay_Helper_Pricing::get_post_price( $post_id );
        $revenue_model                  = LaterPay_Helper_Pricing::get_post_revenue_model( $post_id );

        // get purchase link
        $purchase_link                  = LaterPay_Helper_Post::get_laterpay_purchase_link( $post_id );

        $view_args = array_merge( array(
            'post_id'                               => $post_id,
            'currency'                              => $currency,
            'price'                                 => $price,
            'revenue_model'                         => $revenue_model,
            'link'                                  => $purchase_link,
            'attributes'                            => array(),
            ),
            $event->get_arguments()
        );
        $this->assign( 'laterpay', $view_args );
        $html = $this->get_text_view( 'frontend/partials/widget/purchase-link' );

        $event->set_result( $html )
              ->set_arguments( $view_args );
    }

    /**
     * Collect content of benefits overlay.
     *
     * @param LaterPay_Core_Event $event
     * @var string                $revenue_model       LaterPay revenue model applied to content
     *
     * @return array $overlay_content
     */
    public function on_purchase_overlay_content( LaterPay_Core_Event $event ) {
        list( $revenue_model ) = $event->get_arguments() + array( 'sis' );
        // determine overlay title to show
        if ( $revenue_model == 'sis' ) {
            $overlay_title = __( 'Read Now', 'laterpay' );
        } else {
            $overlay_title = __( 'Read Now, Pay Later', 'laterpay' );
        }

        if ( $revenue_model == 'sis' ) {
            $overlay_benefits = array(
                array(
                    'title' => __( 'Buy Now', 'laterpay' ),
                    'text'  => __( 'Buy this post now with LaterPay and <br>pay with a payment method you trust.', 'laterpay' ),
                    'class' => 'lp_benefit--buy-now',
                ),
                array(
                    'title' => __( 'Read Immediately', 'laterpay' ),
                    'text'  => __( 'Immediately access your purchase. <br>You only buy this post. No subscription, no fees.', 'laterpay' ),
                    'class' => 'lp_benefit--use-immediately',
                ),
            );
        } else {
            $overlay_benefits = array(
                array(
                    'title' => __( 'Buy Now', 'laterpay' ),
                    'text'  => __( 'Just agree to pay later.<br> No upfront registration and payment.', 'laterpay' ),
                    'class' => 'lp_benefit--buy-now',
                ),
                array(
                    'title' => __( 'Read Immediately', 'laterpay' ),
                    'text'  => __( 'Access your purchase immediately.<br> You are only buying this article, not a subscription.', 'laterpay' ),
                    'class' => 'lp_benefit--use-immediately',
                ),
                array(
                    'title' => __( 'Pay Later', 'laterpay' ),
                    'text'  => __( sprintf( 'Buy with LaterPay until you reach a total of %s %s.<br> Only then do you have to register and pay.', $this->config->get( 'currency.ppu_max' ), $this->config->get( 'currency.default' ) ), 'laterpay' ),
                    'class' => 'lp_benefit--pay-later',
                ),
            );
        }

        $action_event = new LaterPay_Core_Event();
        $action_event->set_echo( false );
        laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_button', $action_event );

        $overlay_content = array(
            'title'      => $overlay_title,
            'benefits'   => $overlay_benefits,
            'action'     => (string) $action_event->get_result(),
        );

        $event->set_result( $overlay_content );
    }

    /**
     * Check if user has access to the post
     *
     * @wp-hook laterpay_check_user_access
     * @param LaterPay_Core_Event $event
     *
     * @return bool $has_access
     */
    public function check_user_access( LaterPay_Core_Event $event ) {
        list( $has_access, $post_id ) = $event->get_arguments() + array( '', '' );
        $event->set_result( false );
        $event->set_echo( false );

        // get post
        if ( ! isset( $post_id ) ) {
            $post = get_post();
        } else {
            $post = get_post( $post_id );
        }

        if ( $post === null ) {
            $event->set_result( (bool) $has_access );
            return;
        }

        $user_has_unlimited_access = LaterPay_Helper_User::can( 'laterpay_has_full_access_to_content', $post );

        // user has unlimited access
        if ( $user_has_unlimited_access ) {
            $event->set_result( true );
            return;
        }

        // user has access to the post
        if ( LaterPay_Helper_Post::has_access_to_post( $post ) ) {
            $event->set_result( true );
            return;
        }

    }

    /**
     * Stops bubbling if content is not purchasable
     *
     * @param LaterPay_Core_Event $event
     */
    public function is_purchasable( LaterPay_Core_Event $event ) {
        if ( $event->has_argument( 'post' ) ) {
            $post = $event->get_argument( 'post' );
        } else {
            $post = get_post();
        }
        $post_id = $post->ID;
        if ( ! LaterPay_Helper_Pricing::is_purchasable( $post_id ) ) {
            $event->stop_propagation();
        }
    }

    /**
     * Save purchase in purchase history.
     *
     * @wp-hook template_redirect
     *
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

        $client_options    = LaterPay_Helper_Config::get_php_client_options();
        $laterpay_client   = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        if ( LaterPay_Client_Signing::verify( $request->get_param( 'hmac' ), $laterpay_client->get_api_key(), $request->get_data( 'get' ), get_permalink(), $request_method ) ) {
            // check token
            if ( $lptoken = $request->get_param( 'lptoken' ) ) {
                $laterpay_client->set_token( $lptoken );
            }

            // prepare attachment URL for download
            if ( $download_attached = $request->get_param( 'download_attached' ) ) {
                $post    = get_post( $download_attached );
                $access  = LaterPay_Helper_Post::has_access_to_post( $post );
                $attachment_url = LaterPay_Helper_File::get_encrypted_resource_url(
                    $download_attached,
                    wp_get_attachment_url( $download_attached ),
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

            wp_redirect( get_permalink( $request->get_param( 'post_id' ) ) );
            // exit script after redirect was set
            exit;
        }
    }

    /**
     * Set Laterpay token if it was provided after redirect and not processed by purchase functions.
     */
    public function set_token() {
        $request_method    = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) : '';
        $request           = new LaterPay_Core_Request();

        $client_options    = LaterPay_Helper_Config::get_php_client_options();
        $laterpay_client   = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        // check token and set if necessary
        if ( $lptoken = $request->get_param('lptoken') ) {
            if ( LaterPay_Client_Signing::verify( $request->get_param( 'hmac' ), $laterpay_client->get_api_key(), $request->get_data( 'get' ), get_permalink(), $request_method ) ) {
                // set token
                $laterpay_client->set_token($lptoken);
            }

            wp_redirect( get_permalink( $request->get_param( 'post_id' ) ) );
            // exit script after redirect was set
            exit;
        }
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
        $content = $event->get_result();

        // add the purchase button as very first element of the content, if it is not positioned manually
        if ( (bool) get_option( 'laterpay_purchase_button_positioned_manually' ) === false ) {
            $button_event = new LaterPay_Core_Event();
            $button_event->set_echo( false );
            laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_button', $button_event );
            $content = $button_event->get_result() . $content;
        }

        // show rating summary if it's enabled
        $rating_event = new LaterPay_Core_Event();
        $rating_event->set_echo( false );
        laterpay_event_dispatcher()->dispatch( 'laterpay_post_rating', $rating_event );
        $content = $rating_event->get_result() . $content;

        $event->set_result( $content );
    }

    /**
     * @param LaterPay_Core_Event $event
     */
    public function purchase_button_position( LaterPay_Core_Event $event ) {
        $html = $event->get_result();
        // add the purchase button as very first element of the content, if it is not positioned manually
        if ( (bool) get_option( 'laterpay_purchase_button_positioned_manually' ) == false ) {
            $html = '<div class="lp_purchase-button-wrapper">' . $html . '</div>';
        }

        $event->set_result( $html );
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
        if ( $post instanceof WP_Post && LaterPay_Helper_Post::has_access_to_post( $post ) && ! $preview_post_as_visitor ) {
            $event->stop_propagation();
        }
    }
}
