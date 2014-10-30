<?php

class LaterPay_Helper_Dashboard
{

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
     * @param array $days
     *
     * @return array $data array(
     *                          'x' => [{key}, day-of-week-1]
     *                          'y' => [{key}, kpi-value-1]
     *                      );
     */
    public static function convert_history_result_to_diagram_data( $items, $days ) {
        $data = array(
            'x' => array(),
            'y' => array(),
        );

        $items_by_day   = LaterPay_Helper_Dashboard::sort_items_by_date( $items );
        $items          = LaterPay_Helper_Dashboard::fill_empty_days( $items_by_day, $days );

        $key = 1;
        foreach ( $items as $item ) {
            $data[ 'x' ][] = array(
                $key,
                $item->day_name,
            );

            $data[ 'y' ][] = array(
                $key,
                $item->quantity,
            );

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
     * Return an array with all days within the given start and end timestamp.
     *
     * @param int $start_timestamp
     * @param int $interval
     *
     * @return array $last_days
     */
    public static function get_days_as_array( $start_timestamp, $interval ) {
        $last_days = array();
        for ( $i = 0; $i < $interval; $i++ ) {
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

}
