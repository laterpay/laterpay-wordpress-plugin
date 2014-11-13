<?php

class LaterPay_Helper_Rating
{
    /**
     * Array of rating indexes
     *
     * @var array
     */
    protected static $rating_indexes = array( '1', '2', '3', '4', '5' );

    /**
     * Set post rating data
     *
     * @param int $post_id
     *
     * @return void
     */
    public static function set_post_rating_data( $post_id ) {
        $rating_array = array();
        // set rating array
        foreach ( self::$rating_indexes as $index ) {
            $rating_array[$index] = 0;
        }
        // set rating data to the postmeta
        update_post_meta( $post_id, 'laterpay_rating', $rating_array );
    }

    /**
     * Get post rating data and set it if no exist
     *
     * @param $post_id
     *
     * @return array
     */
    public static function get_post_rating_data( $post_id ) {
        if ( ! get_post_meta( $post_id, 'laterpay_rating' ) ) {
            self::set_post_rating_data( $post_id );
        }

        $rating_data = get_post_meta( $post_id, 'laterpay_rating' );

        return $rating_data[0];
    }

    /**
     * Get summary post rating data
     *
     * @param  int $post_id
     *
     * @return array
     */
    public static function get_summary_post_rating_data( $post_id ) {
        $rating_data    = self::get_post_rating_data( $post_id );
        $votes_count    = 0;
        $summary_rating = 0;

        foreach( $rating_data as $index => $value ) {
            $summary_rating += (int) $index * $value;
            $votes_count    += $value;
        }

        return array( 'rating' => $summary_rating, 'votes' => $votes_count );
    }

    /**
     * Check if user voted post already
     *
     * @param  int $post_id
     *
     * @return bool
     */
    public static function check_if_user_voted_post_already( $post_id ) {
        $user_id          = LaterPay_Helper_Statistics::get_user_unique_id();
        $users_voted_data = get_post_meta( $post_id, 'laterpay_users_voted');
        if ( ! $users_voted_data ) {
            return false;
        }
        $users_voted      = $users_voted_data[0];
        return in_array( $user_id, $users_voted );
    }

    /**
     * Add user id to the array of users that already voted post
     *
     * @param $post_id
     *
     * @return void
     */
    public static function set_user_voted( $post_id ) {
        $user_id          = LaterPay_Helper_Statistics::get_user_unique_id();
        $users_voted_data = get_post_meta( $post_id, 'laterpay_users_voted');
        $users_voted      = $users_voted_data ? $users_voted_data[0] : array();
        $users_voted[]    = $user_id;
        update_post_meta( $post_id, 'laterpay_users_voted', $users_voted );
    }
}