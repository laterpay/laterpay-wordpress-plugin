<?php

class LaterPay_Helper_Pricing
{

    const META_KEY = 'laterpay_post_prices';

    /**
     * Return all posts that have a price applied.
     *
     * @return array
     */
    public static function get_all_posts_with_price() {
        $post_args = array(
            'meta_query'        => array( array( 'meta_key' => self::META_KEY ) ),
            'posts_per_page'    => '-1',
        );
        $posts = get_posts( $post_args );

        return $posts;
    }

    /**
     * Return all posts with a given category_id that have a price applied.
     *
     * @param int $category_id
     *
     * @return array
     */
    public static function get_posts_with_price_by_category_id( $category_id ) {
        $post_args = array(
            'meta_query'        => array( array( 'meta_key' => self::META_KEY ) ),
            'cat'               => $category_id,
            'posts_per_page'    => '-1'
        );
        $posts = get_posts( $post_args );

        return $posts;
    }

    /**
     * Apply the global default price to a post.
     *
     * @param int $post_id
     *
     * @return booleantrue|false
     */
    public static function apply_global_default_price_to_post( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        if ( $global_default_price == 0 ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        $post_prices = array();
        $post_prices[ 'type' ] = 'global default price';

        return update_post_meta( $post_id, self::META_KEY, $post_prices );
    }

    /**
     * Apply a given category default price to a given post.
     *
     * @param   int     $post_id
     * @param   int     $category_id
     * @param   boolean $strict - checks if the given category_id is assigned to the post_id
     *
     * @return  boolean true|false
     */
    public static function apply_category_default_price_to_post( $post_id, $category_id, $strict = false ) {
        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        // check if the post has the given category_id
        if ( $strict && ! has_category( $category_id, $post ) ) {
            return false;
        }

        $post_price = array(
            'type'          => 'category default price',
            'category_id'   => (int) $category_id
        );

        return update_post_meta( $post_id, self::META_KEY, $post_price );
    }

    /**
     * Get post price, depending on price type applied to post.
     *
     * @param int $post_id
     *
     * @return float $price
     */
    public static function get_post_price( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        $cache_key = 'laterpay_post_price_' . $post_id;

        $price = wp_cache_get( $cache_key, 'laterpay' );
        if ( !! $price ) {
            return $price;
        }

        $post = get_post( $post_id );
        $post_prices = get_post_meta( $post_id, self::META_KEY, true );
        if ( ! is_array( $post_prices ) ) {
            $post_prices = array();
        }
        $post_price_type    = array_key_exists( 'type', $post_prices ) ? $post_prices[ 'type' ] : '';
        $category_id        = array_key_exists( 'category_id', $post_prices ) ? $post_prices[ 'category_id' ] : '';

        $price = 0;
        switch ( $post_price_type ) {
            case 'individual price':
                $price = array_key_exists( 'price', $post_prices ) ? $post_prices[ 'price' ] : '';
                break;

            case 'individual price, dynamic':
                $price = self::get_dynamic_price( $post, $post_prices );
                break;

            case 'category default price':
                $LaterPay_Category_Model    = new LaterPay_Model_CategoryPrice();
                $price                      = $LaterPay_Category_Model->get_price_by_category_id( (int) $category_id );
                break;

            case 'global default price':
                $price = $global_default_price;
                break;

            default:
                if ( $global_default_price > 0 ) {
                    $price = $global_default_price;
                } else {
                    $price = 0;
                }
                break;
        }

        $price = (float) $price;

        // add the price to the current post cache
        wp_cache_set( $cache_key, $price, 'laterpay' );

        return (float) $price;
    }

    /**
     * Get the current price for a post with dynamic pricing scheme defined.
     *
     * @param WP_Post $post
     * @param array   $post_prices see post_meta 'laterpay_post_prices'
     *
     * @return float price
     */
    public static function get_dynamic_price( $post, $post_prices ) {
        if ( function_exists( 'date_diff' ) ) {
            $date_time = new DateTime( date( 'Y-m-d' ) );
            $days_since_publication = $date_time->diff( new DateTime( date( 'Y-m-d', strtotime( $post->post_date ) ) ) )->format( '%a' );
        } else {
            $d1 = strtotime( date( 'Y-m-d' ) );
            $d2 = strtotime( $post->post_date );
            $diff_secs = abs( $d1 - $d2 );
            $days_since_publication = floor( $diff_secs / ( 3600 * 24 ) );
        }

        if ( $post_prices[ 'change_start_price_after_days' ] >= $days_since_publication ) {
            $price = $post_prices[ 'start_price' ];
        } else {
            if ( $post_prices[ 'transitional_period_end_after_days' ] <= $days_since_publication ||
                 $post_prices[ 'transitional_period_end_after_days' ] == 0
                ) {
                $price = $post_prices[ 'end_price' ];
            } else {    // transitional period between start and end of dynamic price change
                $price = self::calculate_transitional_price( $post_prices, $days_since_publication );
            }
        }

        $rounded_price = round( $price, 2 );
        if ( $rounded_price < 0.05 ) {
            $rounded_price = 0;
        }

        return $rounded_price;
    }

    /**
     * Calculate transitional price between start price and end price based on linear equation.
     *
     * @param  array $post_prices  postmeta see 'laterpay_post_prices'
     * @param  int   $days_since_publication
     *
     * @return float
     */
    private static function calculate_transitional_price( $post_prices, $days_since_publication ) {
        $end_price          = $post_prices[ 'end_price' ];
        $start_price        = $post_prices[ 'start_price' ];
        $days_until_end     = $post_prices[ 'transitional_period_end_after_days' ];
        $days_until_start   = $post_prices[ 'change_start_price_after_days' ];

        $coefficient = ( $end_price - $start_price ) / ( $days_until_end - $days_until_start );

        return $start_price + ( $days_since_publication - $days_until_start ) * $coefficient;
    }

}
