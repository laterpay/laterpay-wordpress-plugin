<?php

class LaterPay_Helper_Pricing
{

    /**
     * Get post price, depending on applied price type of post.
     *
     * @param int $post_id
     *
     * @return float $price
     */
    public static function get_post_price( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        $post = get_post( );
        $post_prices = get_post_meta( $post_id, 'laterpay_post_prices', true );

        $post_price_type    = array_key_exists( 'type', $post_prices ) ? $post_prices[ 'type' ] : '';
        $category_id        = array_key_exists( 'category_id', $post_prices ) ? $post_prices[ 'category_id' ] : '';

        switch ( $post_price_type ) {
            case 'individual price':
                $price = array_key_exists( 'price', $post_prices ) ? $post_prices[ 'price' ] : '';
                break;

            case 'individual price, dynamic':
                $price = LaterPay_Helper_Pricing::get_dynamic_price( $post, $post_prices );
                break;

            case 'category default price':
                $LaterPay_Category_Model  = new LaterPay_Model_CategoryPrice();
                $price                  = $LaterPay_Category_Model->get_price_by_category_id( (int) $category_id );
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

        return (float) $price;
    }

    /**
     * Get current price for posts with dynamic pricing scheme defined.
     *
     * @param WP_Post $post
     * @param array $post_prices see post_meta 'laterpay_post_prices'
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
     * @param   array $post_prices  postmeta see 'laterpay_post_prices'
     * @param   int $days_since_publication
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
