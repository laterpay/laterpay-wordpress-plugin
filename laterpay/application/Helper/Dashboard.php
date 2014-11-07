<?php

class LaterPay_Helper_Dashboard
{

    /**
     * Checks and sanitize the given interval.
     *
     * @param string $interval    day|week|2-weeks|month
     * @return string $interval
     */
    public static function get_interval( $interval ){
        $allowed_intervals  = array( 'day', 'week', '2-weeks', 'month' );
        $interval           = sanitize_text_field( (string) $interval );
        if ( ! in_array( $interval, $allowed_intervals ) ) {
           $interval = 'week';
        }
        return $interval;
    }

    /**
     * Returns the end_timestamp by a given start_timestamp and interval.
     *
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return int $end_timestamp
     */
    public static function get_end_timestamp( $start_timestamp, $interval = 'week' ){
        if ( $interval === 'week' ) {
            $end_timestamp = strtotime( '-7 days', $start_timestamp );
        } else if ( $interval === '2-weeks' ) {
            $end_timestamp = strtotime( '-14 days', $start_timestamp );
        } else if ( $interval === 'month' ) {
            $end_timestamp = strtotime( '-1 month', $start_timestamp );
        } else {
            // $interval === 'day'
            $end_timestamp = strtotime( 'today', $start_timestamp );
        }
        return $end_timestamp;
    }

    /**
     * Returns the order- and group-by statement for a given interval.
     *
     * @param string $interval
     *
     * @return string $order_by
     */
    public static function get_order_and_group_by( $interval ){
        if ( $interval === 'day' ) {
            return 'hour';
        }
        return 'day';
    }


    /**
     * Building the sparkline by given wpdb-result with end- and start-timestamp
     *
     * @param array $items
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return array $sparkline
     */
    public static function build_sparkline( $items, $start_timestamp, $interval = 'week' ) {
        $sparkline = array();

        if( $interval === 'day' ){
            $items_by_hour  = LaterPay_Helper_Dashboard::sort_items_by_hour( $items );
            $items          = LaterPay_Helper_Dashboard::fill_empty_hours( $items_by_hour, $start_timestamp );
        }
        else {
            $items_by_day   = LaterPay_Helper_Dashboard::sort_items_by_date( $items );
            $days           = LaterPay_Helper_Dashboard::get_days_as_array( $start_timestamp, $interval );
            $items          = LaterPay_Helper_Dashboard::fill_empty_days( $items_by_day, $days );
        }

        foreach ( $items as $item ) {
            $sparkline[] = $item->quantity;
        }

        return array_reverse( $sparkline );
    }

    /**
     * Helper Function to convert a wpdb-result to diagram data
     *
     * @param array $items array(
     *                       stdClass Object (
     *                          [quantity]  => 3
     *                          [day_name]  => Monday
     *                          [day]       => 27
     *                      ),
     *                      ..
     *                  )
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return array $data array(
     *                          'x' => [{key}, day-of-week-1]
     *                          'y' => [{key}, kpi-value-1]
     *                      );
     */
    public static function convert_history_result_to_diagram_data( $items, $start_timestamp, $interval = 'week' ) {
        $data = array(
            'x' => array(),
            'y' => array(),
        );

        if( $interval === 'day' ){
            $items_by_hour  = LaterPay_Helper_Dashboard::sort_items_by_hour( $items );
            $items          = LaterPay_Helper_Dashboard::fill_empty_hours( $items_by_hour, $start_timestamp );
        }
        else {
            $items_by_day   = LaterPay_Helper_Dashboard::sort_items_by_date( $items );
            $days           = LaterPay_Helper_Dashboard::get_days_as_array( $start_timestamp, $interval );
            $items          = LaterPay_Helper_Dashboard::fill_empty_days( $items_by_day, $days );
        }


        $key = 1;
        foreach ( $items as $item ) {

            if ( $interval === 'day' ) {
                $data[ 'x' ][] = array(
                    $key,
                    $item->hour,
                );

                $data[ 'y' ][] = array(
                    $key,
                    $item->hour,
                );
            } else {
                $data[ 'x' ][] = array(
                    $key,
                    $item->day_name,
                );

                $data[ 'y' ][] = array(
                    $key,
                    $item->quantity,
                );
            }


            $key = $key + 1;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input' => $items,
                'result'=> $data,
            )
        );

        return $data;
    }

    /**
     * Sort all given items of a wpdb-result by date.
     *
     * @param array $items array(
     *                       stdClass Object (
     *                          [quantity]  => 3
     *                          [day_name]  => Monday
     *                          [day]       => 27
     *                          [date]      => 2014-10-27
     *                          [hour]      => 1
     *                      ),
     *                      ..
     *                  )
     *
     * @return array $items_by_date
     */
    public static function sort_items_by_date( $items ) {
        // sort all items by date
        $items_by_date = array();
        foreach ( $items as $item ){
            $items_by_date[ $item->date ] = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input' => $items,
                'output'=> $items_by_date,
            )
        );

        return $items_by_date;
    }

    /**
     * Sort all given items of a wpdb-result by hour.
     *
     * @param array $items array(
     *                       stdClass Object (
     *                          [quantity]  => 3
     *                          [day_name]  => Monday
     *                          [day]       => 27
     *                          [date]      => 2014-10-27
     *                          [hour]      => 1
     *                      ),
     *                      ..
     *                  )
     *
     * @return array $items_by_hour
     */
    public static function sort_items_by_hour( $items ) {
        $items_by_hour = array();
        foreach ( $items as $item ) {
            $items_by_hour[ $item->hour ] = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input' => $items,
                'output'=> $items_by_hour,
            )
        );

        return $items_by_hour;
    }

    /**
     * Return an array with all days within the given start and end timestamp.
     *
     * @param int $start_timestamp
     * @param int $interval
     *
     * @return array $last_days
     */
    public static function get_days_as_array( $start_timestamp, $interval ) {
        $last_days = array();

        if ( $interval === 'week' ) {
            $days = 7;
        } else if ( $interval === '2-weeks' ) {
            $days = 14;
        } else {
            $days = 30;
        }

        for ( $i = 0; $i < $days; $i++ ) {
            $time_stamp     = strtotime( '-' . $i . ' days', $start_timestamp );

            $item           = new stdClass();
            $item->date     = gmdate( 'Y-m-d', $time_stamp );
            $item->day_name = gmdate( 'l', $time_stamp );

            $last_days[]    = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'end_timestamp' => $start_timestamp,
                'formatted_end_timestamp' => date( 'Y-m-d', $start_timestamp ),
                'interval'      => $interval,
                'last_days'     => $last_days
            )
        );

        return $last_days;
    }

    /**
     * Helper function to fill a wpdb result sorted by day with quantity=0, if the day is missing.
     *
     * @param array $items
     * @param array $last_days
     *
     * @return array
     */
    public static function fill_empty_days( $items, $last_days ) {
        foreach( $last_days as $day_item ) {
            $date       = $day_item->date;
            $day_name   = $day_item->day_name;
            if ( ! array_key_exists( $date, $items ) ) {
                $item           = new stdClass();
                $item->day_name = $day_name;
                $item->quantity = 0;

                $items[ $date ] = $item;
            }
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'items'     => $items,
                'last_days' => $last_days,
            )
        );

        return $items;
    }


    /**
     * Helper function to fill a wpdb result sorted by hours with quantity=0, if the hour is missing.
     *
     * @param array $items
     * @param int $start_timestamp
     *
     * @return array
     */
    public static function fill_empty_hours( $items, $start_timestamp ) {

        $filled_items = array();

        $day = gmdate( 'd', $start_timestamp );
        $date= gmdate( 'Y-m-d', $start_timestamp );

        for ( $hour = 0; $hour < 24; $hour++ ) {
            if ( ! array_key_exists( $hour, $items ) ) {
                $item           = new stdClass();
                $item->hour     = $hour;
                $item->day      = $day;
                $item->date     = $date;
                $item->quantity = 0;
            } else {
                $item = $items[ $hour ];
            }
            $filled_items[ $hour ] = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input'     => $items,
                'output'    => $filled_items,
            )
        );

        return $filled_items;
    }


}
