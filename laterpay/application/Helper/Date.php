<?php

class LaterPay_Helper_Date
{

    /**
     * Get a 'before' search, starting at 23:59:59.
     *
     * @param int $day
     *
     * @return array $after
     */
    public static function get_date_query_before_end_of_day( $day ) {
        $time_str = strtotime(
            sprintf( '-%d days', $day ),
            current_time( 'timestamp' )
        );

        return array(
            'day'   => date( 'd', $time_str ),
            'month' => date( 'm', $time_str ),
            'year'  => date( 'Y', $time_str ),
            'hour'  => 23,
            'minute'=> 59,
            'second'=> 59
        );
    }

    /**
     * Get an 'after' search, starting at 00:00:00.
     *
     * @param int $day
     *
     * @return array $after
     */
    public static function get_date_query_after_start_of_day( $day ) {
        $time_str = strtotime(
            sprintf( '-%d days', $day ),
            current_time( 'timestamp' )
        );

        return array(
            'day'   => date( 'd', $time_str ),
            'month' => date( 'm', $time_str ),
            'year'  => date( 'Y', $time_str ),
            'hour'  => 0,
            'minute'=> 0,
            'second'=> 0
        );
    }

}
