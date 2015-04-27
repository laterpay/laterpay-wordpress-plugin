<?php

/**
 * LaterPay rating helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Rating
{
    /**
     * Array of rating indexes.
     *
     * @var array
     */
    protected static $rating_indexes = array( '5', '4', '3', '2', '1' );

    /**
     * Initialize post rating data by creating an array with 0 values.
     *
     * @param int $post_id
     *
     * @return void
     */
    public static function initialize_post_rating_data( $post_id ) {
        $rating_array = array();
        // initialize rating array
        foreach ( self::$rating_indexes as $index ) {
            $rating_array[ $index ] = 0;
        }

        // save rating data in post_meta
        update_post_meta( $post_id, 'laterpay_rating', $rating_array );
    }

    /**
     * Get post rating data, or initialize it, if it doesn't exist.
     *
     * @param $post_id
     *
     * @return array
     */
    public static function get_post_rating_data( $post_id ) {
        if ( ! get_post_meta( $post_id, 'laterpay_rating' ) ) {
            self::initialize_post_rating_data( $post_id );
        }

        $rating_data = get_post_meta( $post_id, 'laterpay_rating' );

        return $rating_data[0];
    }

    /**
     * Get summary post rating data.
     *
     * @param int $post_id
     *
     * @return array
     */
    public static function get_summary_post_rating_data( $post_id ) {
        $rating_data    = self::get_post_rating_data( $post_id );
        $votes_count    = 0;
        $summary_rating = 0;

        foreach ( $rating_data as $index => $value ) {
            $summary_rating += (int) $index * $value;
            $votes_count    += $value;
        }

        return array(
                     'rating'   => $summary_rating,
                     'votes'    => $votes_count,
                    );
    }

    /**
     * Check, if user voted post already.
     *
     * @param int $post_id
     *
     * @return bool
     */
    public static function check_if_user_voted_post_already( $post_id ) {
        $user_id          = LaterPay_Helper_Statistic::get_user_unique_id();
        $users_voted_data = get_post_meta( $post_id, 'laterpay_users_voted' );
        if ( ! $users_voted_data ) {
            return false;
        }
        $users_voted      = $users_voted_data[0];

        return in_array( $user_id, $users_voted );
    }

    /**
     * Add user_id to the array of users that already voted post.
     *
     * @param $post_id
     *
     * @return void
     */
    public static function set_user_voted( $post_id ) {
        $user_id          = LaterPay_Helper_Statistic::get_user_unique_id();
        $users_voted_data = get_post_meta( $post_id, 'laterpay_users_voted' );
        $users_voted      = $users_voted_data ? $users_voted_data[0] : array();
        $users_voted[]    = $user_id;

        update_post_meta( $post_id, 'laterpay_users_voted', $users_voted );
    }

}
