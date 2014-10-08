<?php

class LaterPay_Helper_Pricing
{
    const TYPE_GLOBAL_DEFAULT_PRICE     = 'global default price';
    const TYPE_CATEGORY_DEFAULT_PRICE   = 'category default price';
    const TYPE_INDIVIDUAL_PRICE         = 'individual price';
    const TYPE_INDIVIDUAL_DYNAMIC_PRICE = 'individual price, dynamic';

    const META_KEY = 'laterpay_post_prices';

    /**
     * Check, if the current post or a given post is purchasable.
     *
     * @param null|WP_Post $post
     *
     * @return bool true|false
     */
    public static function is_purchasable( $post = null ) {
        if ( ! is_a( $post, 'WP_POST' ) ) {
            // load the current post in $GLOBAL['post']
            $post = get_post();
            if ( $post === null ) {
                return false;
            }
        }

        // check, if the current post price is not 0
        $price = LaterPay_Helper_Pricing::get_post_price( $post->ID );
        if ( $price == 0 ) {
            return false;
        }

        return true;
    }

    /**
     * Return all posts that have a price applied.
     *
     * @return array
     */
    public static function get_all_posts_with_price() {
        $post_args = array(
            'meta_query'        => array( array( 'meta_key' => LaterPay_Helper_Pricing::META_KEY ) ),
            'posts_per_page'    => '-1',
        );
        $posts = get_posts( $post_args );

        return $posts;
    }

    /**
     * Return all post_ids with a given category_id that have a price applied.
     *
     * @param int $category_id
     *
     * @return array
     */
    public static function get_post_ids_with_price_by_category_id( $category_id ) {
        $config     = laterpay_get_plugin_config();
        $post_args  = array(
            'fields'            => 'ids',
            'meta_query'        => array( array( 'meta_key' => LaterPay_Helper_Pricing::META_KEY ) ),
            'cat'               => $category_id,
            'posts_per_page'    => '-1',
            'post_type'         => $config->get( 'content.enabled_post_types' ),
        );
        $posts      = get_posts( $post_args );

        return $posts;
    }

    /**
     * Apply the global default price to a post.
     *
     * @param int $post_id
     *
     * @return boolean true|false
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

        $post_price = array();
        $post_price[ 'type' ] = LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;

        return update_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, $post_price );
    }


    /**
     * Apply the 'category default price' to all posts with a 'global default price' by a given category_id.
     *
     * @param integer $category_id
     *
     * @return array $updated_post_ids all updated post_ids
     */
    public static function apply_category_price_to_posts_with_global_price( $category_id ) {
        $updated_post_ids   = array();
        $post_ids           = LaterPay_Helper_Pricing::get_post_ids_with_price_by_category_id( $category_id );

        foreach ( $post_ids as $post_id ) {
            $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
            if ( ! is_array( $post_price ) ) {
                continue;
            }

            // check, if the post uses a global default price
            if ( ! array_key_exists( 'type', $post_price ) || $post_price[ 'type' ] !== LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE ) {
                continue;
            }

            $success = LaterPay_Helper_Pricing::apply_category_default_price_to_post( $post_id, $category_id, true );
            if ( $success ) {
                $updated_post_ids[] = $post_id;
            }
        }

        return $updated_post_ids;
    }

    /**
     * Apply a given category default price to a given post.
     *
     * @param int     $post_id
     * @param int     $category_id
     * @param boolean $strict - checks, if the given category_id is assigned to the post_id
     *
     * @return boolean true|false
     */
    public static function apply_category_default_price_to_post( $post_id, $category_id, $strict = false ) {
        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        // check, if the post has the given category_id
        if ( $strict && ! has_category( $category_id, $post ) ) {
            return false;
        }

        $post_price = array(
            'type'          => LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE,
            'category_id'   => (int) $category_id
        );

        return update_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, $post_price );
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

        // checks if the price is in cache and returns it
        $price = wp_cache_get( $cache_key, 'laterpay' );
        if ( $price ) {
            return $price;
        }

        $post = get_post( $post_id );
        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }
        $post_price_type    = array_key_exists( 'type', $post_price ) ? $post_price[ 'type' ] : '';
        $category_id        = array_key_exists( 'category_id', $post_price ) ? $post_price[ 'category_id' ] : '';

        switch ( $post_price_type ) {
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
                $price = array_key_exists( 'price', $post_price ) ? $post_price[ 'price' ] : '';
                break;

            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
                $price = LaterPay_Helper_Pricing::get_dynamic_price( $post, $post_price );
                break;

            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
                $LaterPay_Category_Model    = new LaterPay_Model_CategoryPrice();
                $price                      = $LaterPay_Category_Model->get_price_by_category_id( (int) $category_id );
                break;

            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
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
     * Get the post price type. Returns global default price or individual price, if no valid type is set.
     *
     * @param int $post_id
     *
     * @return string $post_price_type
     */
    public static function get_post_price_type( $post_id ) {
        $cache_key = 'laterpay_post_price_type_' . $post_id;

        // get the price from the cache, if it exists
        $post_price_type = wp_cache_get( $cache_key, 'laterpay' );
        if ( $post_price_type ) {
            return $post_price_type;
        }

        $post       = get_post( $post_id );
        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }
        $post_price_type = array_key_exists( 'type', $post_price ) ? $post_price['type'] : '';

        // set a price type (global default price or individual price), if the returned post price type is invalid
        switch ( $post_price_type ) {
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                break;

            default:
                $global_default_price = get_option( 'laterpay_global_price' );
                if ( $global_default_price > 0 ) {
                    $post_price_type = LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;
                } else {
                    $post_price_type = LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE;
                }
                break;
        }

        // cache the post price type
        wp_cache_set( $cache_key, $post_price_type, 'laterpay' );

        return (string) $post_price_type;
    }

    /**
     * Get the current price for a post with dynamic pricing scheme defined.
     *
     * @param WP_Post $post
     * @param array   $post_price see post_meta 'laterpay_post_prices'
     *
     * @return float price
     */
    public static function get_dynamic_price( $post, $post_price ) {
        if ( function_exists( 'date_diff' ) ) {
            $date_time = new DateTime( date( 'Y-m-d' ) );
            $days_since_publication = $date_time->diff( new DateTime( date( 'Y-m-d', strtotime( $post->post_date ) ) ) )->format( '%a' );
        } else {
            $d1 = strtotime( date( 'Y-m-d' ) );
            $d2 = strtotime( $post->post_date );
            $diff_secs = abs( $d1 - $d2 );
            $days_since_publication = floor( $diff_secs / ( 3600 * 24 ) );
        }

        if ( $post_price[ 'change_start_price_after_days' ] >= $days_since_publication ) {
            $price = $post_price[ 'start_price' ];
        } else {
            if ( $post_price[ 'transitional_period_end_after_days' ] <= $days_since_publication ||
                 $post_price[ 'transitional_period_end_after_days' ] == 0
                ) {
                $price = $post_price[ 'end_price' ];
            } else {    // transitional period between start and end of dynamic price change
                $price = LaterPay_Helper_Pricing::calculate_transitional_price( $post_price, $days_since_publication );
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
     * @param array $post_price postmeta see 'laterpay_post_prices'
     * @param int   $days_since_publication
     *
     * @return float
     */
    private static function calculate_transitional_price( $post_price, $days_since_publication ) {
        $end_price          = $post_price[ 'end_price' ];
        $start_price        = $post_price[ 'start_price' ];
        $days_until_end     = $post_price[ 'transitional_period_end_after_days' ];
        $days_until_start   = $post_price[ 'change_start_price_after_days' ];

        $coefficient = ( $end_price - $start_price ) / ( $days_until_end - $days_until_start );

        return $start_price + ( $days_since_publication - $days_until_start ) * $coefficient;
    }

    /**
     * Get revenue model of post price (Pay-per-Use or Single Sale).
     *
     * @param int $post_id
     *
     * @return string $revenue_model
     */
    public static function get_post_revenue_model( $post_id ) {
        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );

        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }

        $post_price_type = array_key_exists( 'type', $post_price ) ? $post_price['type'] : '';

        $revenue_model = '';

        // set a price type (global default price or individual price), if the returned post price type is invalid
        switch ( $post_price_type ) {
            // Dynamic Price does currently not support Single Sale as revenue model
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
                $revenue_model = 'ppu';
                break;

            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
                if ( array_key_exists( 'revenue_model', $post_price ) ) {
                    $revenue_model = $post_price['revenue_model'];
                }
                break;

            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
                if ( array_key_exists( 'category_id', $post_price ) ) {
                    $category_model = new LaterPay_Model_CategoryPrice( );
                    $revenue_model = $category_model->get_revenue_model_by_category_id( $post_price[ 'category_id' ] );
                }
                break;

            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                $revenue_model = get_option( 'laterpay_global_price_revenue_model', 'ppu' );
                break;
        }

        // fallback in case the revenue_model is not correct
        if ( ! in_array( $revenue_model, array( 'ppu', 'ssi' ) ) ) {
            $price = LaterPay_Helper_Pricing::get_post_price_type( $post_id );

            if ( $price <= 5.00 && $price > 0.05 ) {
                $revenue_model = 'ppu';
            } else if ( $price > 5.00 && $price < 149.99 ) {
                $revenue_model = 'ssi';
            }
        }

        return $revenue_model;
    }

}
