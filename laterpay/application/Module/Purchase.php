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
        return array();
    }

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_loaded' => array(
                array( 'buy_post' ),
                array( 'create_token' ),
            ),
            'laterpay_purchase_button' => array(
                array( 'on_purchase_button' ),
                array( 'is_purchasable', 100 ),
                array( 'purchase_button_position', 0 ),
            ),
            'laterpay_purchase_overlay' => array(
                array( 'on_purchase_overlay' ),
                array( 'is_purchasable', 100 ),
            ),
            'laterpay_purchase_link' => array(
                array( 'on_purchase_link' ),
                array( 'is_purchasable', 100 ),
            ),
            'laterpay_post_content' => array(
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
        $view_args = array_merge( array(
                'post_id'                         => $post->ID,
                'link'                            => LaterPay_Helper_Post::get_laterpay_purchase_link( $post->ID ),
                'currency'                        => get_option( 'laterpay_currency' ),
                'price'                           => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
                'purchase_button_is_hidden'       => LaterPay_Helper_View::purchase_button_is_hidden(),
                'attributes'                      => array(),
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
            $content = the_content();
        }
        $revenue_model              = LaterPay_Helper_Pricing::get_post_revenue_model( $post->ID );
        $only_time_passes_allowed   = get_option( 'laterpay_only_time_pass_purchases_allowed' );

        $view_args = array(
            'content'                           => $content,
            'overlay_content'                   => $this->generate_overlay_content( $revenue_model, $only_time_passes_allowed ),
            'only_time_pass_purchases_allowed'  => $only_time_passes_allowed, // FIXME: #612 move to Timepass Module
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
        $currency                       = get_option( 'laterpay_currency' );
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
                'purchase_link_is_hidden'               => LaterPay_Helper_View::purchase_link_is_hidden(),
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
     * @param  string  $revenue_model       LaterPay revenue model applied to content
     * @param  boolean $time_passes_only    can posts be purchased individually, or only by time passes?
     *
     * @return array $overlay_content
     */
    protected function generate_overlay_content( $revenue_model, $time_passes_only = false ) { // FIXME: #612 move timepass into module
        // determine overlay title to show
        if ( $revenue_model == 'sis' || $time_passes_only ) {
            $overlay_title = __( 'Read Now', 'laterpay' );
        } else {
            $overlay_title = __( 'Read Now, Pay Later', 'laterpay' );
        }

        // determine benefits to show
        if ( $time_passes_only ) {
            $overlay_benefits = array(
                array(
                    'title' => __( 'Buy Time Pass', 'laterpay' ),
                    'text'  => __( 'Buy a LaterPay time pass and pay with a payment method you trust.', 'laterpay' ),
                    'class' => 'lp_benefit--buy-now',
                ),
                array(
                    'title' => __( 'Read Immediately', 'laterpay' ),
                    'text'  => __( 'Immediately access your content. <br>A time pass is not a subscription, it expires automatically.', 'laterpay' ),
                    'class' => 'lp_benefit--use-immediately',
                ),
            );
        } else if ( ! $time_passes_only && $revenue_model == 'sis' ) {
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
                    'text'  => __( 'Buy with LaterPay until you reach a total of 5 Euro.<br> Only then do you have to register and pay.', 'laterpay' ),
                    'class' => 'lp_benefit--pay-later',
                ),
            );
        }

        $overlay_content = array(
            'title'      => $overlay_title,
            'benefits'   => $overlay_benefits,
        );

        return $overlay_content;
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
        if ( ! LaterPay_Helper_Pricing::is_purchasable() ) {
            $event->stop_propagation();
        }
    }

    /**
     * Save purchase in purchase history.
     *
     * @wp-hook template_redirect
     * @return void
     */
    public function buy_post() {
        // return, if the request was not a redirect after a purchase
        if ( ! isset( $_GET['buy'] ) ) {
            return;
        }

        // data to create and hash-check the URL
        $get_post_id        = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null;
        $get_id_currency    = isset( $_GET['id_currency'] ) ? sanitize_text_field( $_GET['id_currency'] ) : null;
        $get_price          = isset( $_GET['price'] ) ? sanitize_text_field( $_GET['price'] ) : null;
        $get_date           = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : null;
        $get_ip             = isset( $_GET['ip'] ) ? sanitize_text_field( $_GET['ip'] ) : null;
        $get_revenue_model  = isset( $_GET['revenue_model'] ) ? sanitize_text_field( $_GET['revenue_model'] ) : null;
        $get_buy            = isset( $_GET['buy'] ) ? sanitize_text_field( $_GET['buy'] ) : null;
        $get_hash           = isset( $_GET['hash'] ) ? sanitize_text_field( $_GET['hash'] ) : null;

        $url_data = array(
            'post_id'       => $get_post_id,
            'id_currency'   => $get_id_currency,
            'price'         => $get_price,
            'date'          => $get_date,
            'buy'           => $get_buy,
            'ip'            => $get_ip,
            'revenue_model' => $get_revenue_model,
        );

        if ( isset( $_GET['download_attached'] ) ) {
            $url_data['download_attached'] = sanitize_text_field( $_GET['download_attached'] );
        }
        $url = $this->get_after_purchase_redirect_url( $url_data );
        $hash = LaterPay_Helper_Pricing::get_hash_by_url( $url );
        // update lptoken, if we got it
        if ( isset( $_GET['lptoken'] ) ) {
            LaterPay_Helper_Request::laterpay_api_set_token( sanitize_text_field( $_GET['lptoken'] ) );
        }

        $post_id = absint( $_GET['post_id'] );
        if ( isset( $_GET['download_attached'] ) ) {
            $post_id = absint( $_GET['download_attached'] );
        }

        // check, if the parameters of $_GET are valid and not manipulated
        if ( $hash === $_GET['hash'] ) {
            $data = array(
                'post_id'       => $post_id,
                'id_currency'   => $get_id_currency,
                'price'         => $get_price,
                'date'          => $get_date,
                'ip'            => $get_ip,
                'hash'          => $get_hash,
                'revenue_model' => $get_revenue_model,
            );

//            $this->logger->info(
//                __METHOD__ . ' - set payment history',
//                $data
//            );

            $payment_history_model = new LaterPay_Model_Payment_History();
            $payment_history_model->set_payment_history( $data );
        }

        $redirect_url = get_permalink( $get_post_id );

        // prepare attachment URL for download
        if ( isset( $_GET['download_attached'] ) ) {
            $post_id = absint( $_GET['download_attached'] );
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

        wp_redirect( $redirect_url );
        // exit script after redirect was set
        exit;
    }

    /**
     * Generate the URL to which the user is redirected to after buying a given post.
     *
     * @param array $data
     *
     * @return string $url
     */
    protected function get_after_purchase_redirect_url( array $data ) {
        $url = isset( $data['post_id'] ) ? get_permalink( $data['post_id'] ) : get_permalink();

        if ( ! $url ) {
            return $url;
        }

        $url = add_query_arg( $data, $url );

        return $url;
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

        if ( isset( $_GET['lptoken'] ) ) {
            LaterPay_Helper_Request::laterpay_api_set_token( sanitize_text_field( $_GET['lptoken'] ), true );
        }

        LaterPay_Helper_Request::laterpay_api_acquire_token();
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
        $html = '';
        // add the purchase button as very first element of the content, if it is not positioned manually
        if ( (bool) get_option( 'laterpay_purchase_button_positioned_manually' ) == false ) {
            $html .= '<div class="lp_purchase-button-wrapper">';
            $button_event = new LaterPay_Core_Event();
            $button_event->set_echo( false );
            laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_button', $button_event );
            $html .= $button_event->get_result();
            $html .= '</div>';

            $content = $html . $content;
        }

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
}
