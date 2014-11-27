<?php

class LaterPay_Helper_Post {

    /**
     * Contains the access state for all loaded posts.
     *
     * @var array
     */
    private static $access = array();

    /**
     * Check, if user has access to a post.
     *
     * @param WP_Post $post
     *
     * @return boolean success
     */
    public static function has_access_to_post( WP_Post $post ) {
        $post_id = $post->ID;

        laterpay_get_logger()->info(
            __METHOD__,
            array( 'post' => $post )
        );

        if ( array_key_exists( $post_id, self::$access ) ) {
            // access was already checked
            return (bool) self::$access[$post_id];
        }

        $price = LaterPay_Helper_Pricing::get_post_price( $post->ID );

        if ( $price > 0 ) {
            $client_options  = LaterPay_Helper_Config::get_php_client_options();
            $laterpay_client = new LaterPay_Client(
                                    $client_options['cp_key'],
                                    $client_options['api_key'],
                                    $client_options['api_root'],
                                    $client_options['web_root'],
                                    $client_options['token_name']
                                );
            $result          = $laterpay_client->get_access( array( $post_id ) );

            if ( empty( $result ) || ! array_key_exists( 'articles', $result ) ) {
                laterpay_get_logger()->warning(
                    __METHOD__ . ' - post not found.',
                    array( 'result' => $result )
                );

                return false;
            }

            if ( array_key_exists( $post_id, $result['articles'] ) ) {
                $access = (bool) $result['articles'][$post_id]['access'];
                self::$access[$post_id] = $access;

                laterpay_get_logger()->info(
                    __METHOD__ . ' - post has access.',
                    array( 'result' => $result )
                );

                return $access;
            }
        }

        return false;
    }

    /**
     * Get the LaterPay purchase link for a post.
     *
     * @param int $post_id
     *
     * @return string url || empty string, if something went wrong
     */
    public static function get_laterpay_purchase_link( $post_id ) {
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
        $url_params = array(
            'post_id'     => $post_id,
            'id_currency' => $currency_model->get_currency_id_by_iso4217_code( $currency ),
            'price'       => $price,
            'date'        => time(),
            'buy'         => 'true',
            'ip'          => ip2long( $_SERVER['REMOTE_ADDR'] ),
        );

        if ( $post->post_type == 'attachment' ) {
            $url_params['post_id']           = get_the_ID();
            $url_params['download_attached'] = $post_id;
        }

        $url  = self::get_after_purchase_redirect_url( $url_params );
        $hash = self::get_hash_by_url( $url );
        $url  = $url . '&hash=' . $hash;

        // parameters for LaterPay purchase form
        $params = array(
            'article_id' => $post_id,
            'pricing'    => $currency . ( $price * 100 ),
            'vat'        => laterpay_get_plugin_config()->get( 'currency.default_vat' ),
            'url'        => $url,
            'title'      => $post->post_title,
        );

        laterpay_get_logger()->info(
            __METHOD__, $params
        );

        if ( $revenue_model == 'sis' ) {
            // Single Sale purchase
            return $client->get_buy_url( $params );
        } else {
            // Pay-per-Use purchase
            return $client->get_add_url( $params );
        }
    }

    /**
     * Return the URL hash for a given URL.
     *
     * @param string $url
     *
     * @return string $hash
     */
    public static function get_hash_by_url( $url ) {
        return md5( md5( $url ) . wp_salt() );
    }

    /**
     * Generate the URL to which the user is redirected to after buying a given post.
     *
     * @param array $data
     *
     * @return string $url
     */
    public static function get_after_purchase_redirect_url( array $data ) {
        $url = get_permalink( $data['post_id'] );

        if ( ! $url ) {
            laterpay_get_logger()->error(
                __METHOD__ . ' could not find an URL for the given post_id.',
                array( 'data' => $data )
            );

            return $url;
        }

        $url = add_query_arg( $data, $url );

        return $url;
    }

    /**
     * Prepare the purchase button.
     *
     * @wp-hook laterpay_purchase_button
     *
     * @param WP_Post $post
     *
     * @return void
     */
    public static function the_purchase_button_args( $post ) {
        // don't render the purchase button, if the current post is not purchasable
        if ( ! LaterPay_Helper_Pricing::is_purchasable( $post ) ) {
            return;
        };

        // don't render the purchase button, if the current post was already purchased
        if ( LaterPay_Helper_Post::has_access_to_post( $post ) ) {
            return;
        };

        // render purchase button for administrator always in preview mode, too prevent accidental purchase by admin.
        $preview_mode = LaterPay_Helper_User::preview_post_as_visitor( $post );
        if ( current_user_can( 'administrator' ) ) {
            $preview_mode = true;
        }
        $view_args = array(
            'post_id'                 => $post->ID,
            'link'                    => LaterPay_Helper_Post::get_laterpay_purchase_link( $post->ID ),
            'currency'                => get_option( 'laterpay_currency' ),
            'price'                   => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
            'preview_post_as_visitor' => $preview_mode,
        );

        laterpay_get_logger()->info(
            __METHOD__,
            $view_args
        );

        return $view_args;
    }
}
