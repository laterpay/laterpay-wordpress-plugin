<?php

/**
 * LaterPay post helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Post
{

    /**
     * Contains the access state for all loaded posts.
     *
     * @var array
     */
    private static $access = array();

    /**
     * Set state for the particular post $id.
     *
     * @param string    $id
     * @param bool      $state
     */
    public static function set_access_state( $id, $state ) {
        self::$access[ $id ] = $state;
    }

    /**
     * Return the access state for all loaded posts.
     *
     * @return array
     */
    public static function get_access_state() {
        return self::$access;
    }

    /**
     * Check, if user has access to a post.
     *
     * @param WP_Post $post
     * @param bool    $is_attachment
     * @param null    $main_post_id
     *
     * @return boolean success
     */
    public static function has_access_to_post( WP_Post $post, $is_attachment = false, $main_post_id = null ) {
        $post_id = $post->ID;

        laterpay_get_logger()->info(
            __METHOD__,
            array( 'post' => $post, 'access_list' => self::$access )
        );

        // check, if parent post has access with time passes
        $parent_post        = $is_attachment ? $main_post_id : $post_id;
        $time_passes_list   = LaterPay_Helper_TimePass::get_time_passes_list_by_post_id( $parent_post );
        $time_passes        = LaterPay_Helper_TimePass::get_tokenized_time_pass_ids( $time_passes_list );
        foreach ( $time_passes as $time_pass ) {
            if ( array_key_exists( $time_pass, self::$access ) && self::$access[ $time_pass ] ) {
                return true;
            }
        }

        // check access for the particular post
        if ( array_key_exists( $post_id, self::$access ) ) {
            return (bool) self::$access[ $post_id ];
        }

        $price = LaterPay_Helper_Pricing::get_post_price( $post->ID );

        if ( $price > 0 ) {
            $result = LaterPay_Helper_Request::laterpay_api_get_access( array_merge( array( $post_id ), $time_passes ) );

            if ( empty( $result ) || ! array_key_exists( 'articles', $result ) ) {
                laterpay_get_logger()->warning(
                    __METHOD__ . ' - post not found.',
                    array( 'result' => $result )
                );

                return false;
            }

            $has_access = false;

            foreach ( $result['articles'] as $article_key => $article_access ) {
                $access = (bool) $article_access['access'];
                self::$access[ $article_key ] = $access;
                if ( $access ) {
                    $has_access = true;
                }
            }

            if ( $has_access ) {
                laterpay_get_logger()->info(
                    __METHOD__ . ' - post has access.',
                    array( 'result' => $result )
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Check, if gift code was purchased successfully and user has access.
     *
     * @return mixed return false if gift card is incorrect or doesn't exist, access data otherwise
     */
    public static function has_purchased_gift_card() {
        if ( isset( $_COOKIE['laterpay_purchased_gift_card'] ) ) {
            // get gift code and unset session variable
            $cookies = isset( $_COOKIE['laterpay_purchased_gift_card'] ) ? sanitize_text_field( $_COOKIE['laterpay_purchased_gift_card'] ) : '';
            list( $code, $time_pass_id ) = explode( '|', $cookies );
            // create gift code token
            $code_key = '[#' . $code . ']';

            // check, if gift code was purchased successfully and user has access
            $result = LaterPay_Helper_Request::laterpay_api_get_access( array( $code_key ) );

            if ( empty( $result ) || ! array_key_exists( 'articles', $result ) ) {
                laterpay_get_logger()->warning(
                    __METHOD__ . ' - post not found.',
                    array( 'result' => $result )
                );

                return false;
            }

            // return access data, if all is ok
            if ( array_key_exists( $code_key, $result['articles'] ) ) {
                $access = (bool) $result['articles'][ $code_key ]['access'];
                self::$access[ $code_key ] = $access;

                laterpay_get_logger()->info(
                    __METHOD__ . ' - post has access.',
                    array( 'result' => $result )
                );

                return array(
                    'access'  => $access,
                    'code'    => $code,
                    'pass_id' => $time_pass_id,
                );
            }
        }

        return false;
    }

    /**
     * Get the LaterPay purchase link for a post.
     *
     * @param int $post_id
     * @param int $current_post_id optional for attachments
     *
     * @return string url || empty string, if something went wrong
     */
    public static function get_laterpay_purchase_link( $post_id, $current_post_id = null ) {
        $post = get_post( $post_id );
        if ( $post === null ) {
            return '';
        }

        // re-set the post_id
        $post_id = $post->ID;

        $currency       = get_option( 'laterpay_currency' );
        $price          = LaterPay_Helper_Pricing::get_post_price( $post_id );
        $revenue_model  = LaterPay_Helper_Pricing::get_post_revenue_model( $post_id );

        $currency_model = new LaterPay_Model_Currency();
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        // data to register purchase after redirect from LaterPay
        $remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
        $url_params = array(
            'post_id'       => $post_id,
            'id_currency'   => $currency_model->get_currency_id_by_iso4217_code( $currency ),
            'price'         => $price,
            'date'          => time(),
            'buy'           => 'true',
            'ip'            => ip2long( $remote_addr ),
            'revenue_model' => LaterPay_Helper_Pricing::get_post_revenue_model( $post_id ),
        );

        if ( $post->post_type == 'attachment' ) {
            $url_params['post_id']           = $current_post_id;
            $url_params['download_attached'] = $post_id;
        }

        // get current post link
        $link = get_permalink( $url_params['post_id'] );

        // cut params from link and merge with other params
        $parsed_link = parse_url( $link );
        if ( isset( $parsed_link['query'] ) ) {
            parse_str( $parsed_link['query'], $link_params );
            $url_params = array_merge( $link_params, $url_params );
            list( $link, $last ) = explode( '?', $link );
        }

        // parameters for LaterPay purchase form
        $params = array(
            'article_id' => $post_id,
            'pricing'    => $currency . ( $price * 100 ),
            'url'        => $link . '?' . $client->sign_and_encode( $url_params, $link ),
            'title'      => $post->post_title,
        );

        laterpay_get_logger()->info(
            __METHOD__, $params
        );

        if ( $revenue_model === 'sis' ) {
            // Single Sale purchase
            return $client->get_buy_url( $params );
        } else {
            // Pay-per-Use purchase
            return $client->get_add_url( $params );
        }
    }

    /**
     * Prepare the purchase button.
     *
     * @wp-hook laterpay_purchase_button
     *
     * @param WP_Post  $post
     * @param null|int $current_post_id optional for attachments
     *
     * @return array
     */
    public static function the_purchase_button_args( WP_Post $post, $current_post_id = null ) {
        // render purchase button for administrator always in preview mode, too prevent accidental purchase by admin.
        $preview_mode = LaterPay_Helper_User::preview_post_as_visitor( $post );
        if ( current_user_can( 'administrator' ) ) {
            $preview_mode = true;
        }

        $is_in_visible_test_mode = get_option( 'laterpay_is_in_visible_test_mode' ) && ! get_option( 'laterpay_plugin_is_in_live_mode' );

        $view_args = array(
            'post_id'                   => $post->ID,
            'link'                      => LaterPay_Helper_Post::get_laterpay_purchase_link( $post->ID, $current_post_id ),
            'currency'                  => get_option( 'laterpay_currency' ),
            'price'                     => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
            'preview_post_as_visitor'   => $preview_mode,
            'is_in_visible_test_mode'   => $is_in_visible_test_mode,
        );

        laterpay_get_logger()->info(
            __METHOD__,
            $view_args
        );

        return $view_args;
    }

    /**
     * Add teaser to the post or update it.
     *
     * @param WP_Post $post
     * @param null $teaser teaser data
     * @param bool $need_update
     *
     * @return string $new_meta_value teaser content
     */
    public static function add_teaser_to_the_post( WP_Post $post, $teaser = null, $need_update = true ) {
        if ( $teaser ) {
            $new_meta_value = $teaser;
        } else {
            $new_meta_value = LaterPay_Helper_String::truncate(
                preg_replace( '/\s+/', ' ', strip_shortcodes( $post->post_content ) ),
                get_option( 'laterpay_teaser_content_word_count' ),
                array(
                    'html'  => true,
                    'words' => true,
                )
            );
        }

        if ( $need_update ) {
            update_post_meta( $post->ID, 'laterpay_post_teaser', $new_meta_value );
        }

        return $new_meta_value;
    }

    /**
     * Collect content of benefits overlay.
     *
     * @param  string  $revenue_model       LaterPay revenue model applied to content
     * @param  boolean $time_passes_only    can posts be purchased individually, or only by time passes?
     *
     * @return array $overlay_content
     */
    public static function collect_overlay_content( $revenue_model, $time_passes_only = false ) {
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
     * Hide paid posts from access in the loop.
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
    public static function hide_paid_posts( $posts ) {
        $api_available  = LaterPay_Helper_Request::laterpay_api_check_availability();
        $behavior       = (int) get_option( 'laterpay_api_fallback_behavior', 0 );

        if ( ! $api_available && $behavior == 2 ) {
            $result = array();
            $count = 0;

            foreach ( $posts as $post ) {
                $paid = LaterPay_Helper_Pricing::get_post_price( $post->ID ) != 0;
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

            return $result;
        }

        return $posts;
    }
}
