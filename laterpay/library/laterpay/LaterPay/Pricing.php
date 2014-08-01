<?php

class LaterPay_Helper_Pricing {
    /**
     * Get current price for post with dynamic pricing scheme defined.
     *
     * @param WP_Post $post
     *
     * @return float price
     */
    public static function get_dynamic_price( $post ) {
        if ( function_exists( 'date_diff' ) ) {
            $date_time = new DateTime( date( 'Y-m-d' ) );
            $days_since_publication = $date_time->diff( new DateTime( date( 'Y-m-d', strtotime( $post->post_date ) ) ) )->format( '%a' );
        } else {
            $d1 = strtotime( date( 'Y-m-d' ) );
            $d2 = strtotime( $post->post_date );
            $diff_secs = abs( $d1 - $d2 );
            $days_since_publication = floor( $diff_secs / ( 3600 * 24 ) );
        }

        if ( self::is_before_transitional_period( $post, $days_since_publication ) ) {
            $price = get_post_meta( $post->ID, 'laterpay_start_price', true );
        } else {
            if ( self::is_after_transitional_period( $post, $days_since_publication ) ) {
                $price = get_post_meta( $post->ID, 'laterpay_end_price', true );
            } else {    // transitional period between start and end of dynamic price change
                $price = self::calculate_transitional_price( $post, $days_since_publication );
            }
        }

        $rounded_price = round( $price, 2 );
        if ( $rounded_price < 0.05 ) {
            $rounded_price = 0;
        }

        return $rounded_price;
    }

    /**
     * Check if current date is after set date for end of dynamic price change.
     *
     * @param object $post
     * @param int    $days_since_publication
     *
     * @return boolean
     */
    private static function is_after_transitional_period( $post, $days_since_publication ) {
        return get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) <= $days_since_publication || get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) == 0;
    }

    /**
     * Check if current date is before set date for end of dynamic price change.
     *
     * @param object $post
     * @param int    $days_since_publication
     *
     * @return boolean
     */
    private static function is_before_transitional_period( $post, $days_since_publication ) {
        return get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true ) >= $days_since_publication;
    }

    /**
     * Calculate transitional price between start price and end price based on linear equation.
     *
     * @param WP_Post $post
     * @param int  $days_since_publication
     *
     * @return float
     */
    private static function calculate_transitional_price( $post, $days_since_publication ) {
        $end_price          = get_post_meta( $post->ID, 'laterpay_end_price', true );
        $start_price        = get_post_meta( $post->ID, 'laterpay_start_price', true );
        $days_until_end     = get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true );
        $days_until_start   = get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true );

        $coefficient = ( $end_price - $start_price ) / ( $days_until_end - $days_until_start );

        return $start_price + ( $days_since_publication - $days_until_start ) * $coefficient;
    }

}