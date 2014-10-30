<?php

class LaterPay_Helper_Dashboard {

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
     * @param int $days
     * @return array $data array(
     *                          'x' => [{key}, day-of-week-1]
     *                          'y' => [{key}, kpi-value-1]
     *                      );
     */
    public static function convert_history_result_to_diagram_data( $items, $days = 8 ){

        $data = array(
            'x' => array(),
            'y' => array(),
        );

        $last_days      = LaterPay_Helper_Dashboard::get_last_days( $days );
        $items_by_day   = LaterPay_Helper_Dashboard::sort_items_by_date( $items );
        $items          = LaterPay_Helper_Dashboard::fill_empty_days( $items_by_day, $last_days );

        $i = 1;
        foreach ( $items as $key => $item ) {
            $data[ 'x' ][] = array(
                $i,
                $item[ 'day_name' ]
            );

            $data[ 'y' ][] = array(
                $i,
                $item[ 'quantity' ]
            );

            $i++;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input' => $items,
                'result'=> $data
            )
        );

        return $data;
    }

    /**
     * Sorts all given items of a wpdb-result by date.
     *
     * @param array $items array(
     *                       stdClass Object (
     *                          [quantity]  => 3
     *                          [day_name]  => Monday
     *                          [day]       => 27
     *                      ),
     *                      ..
     *                  )
     *
     * @return array $items_by_date
     */
    public static function sort_items_by_date( $items ){
        // sort all items by date
        $items_by_date = array();
        foreach( $items as $item ){
            $items_by_date[ $item->date ] = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input' => $items,
                'output'=> $items_by_date
            )
        );

        return $items_by_date;
    }

    /**
     * Returns an array with {n} days back.
     *
     * @param int $days_back
     * @return array $last_days
     */
    public static function get_last_days( $days_back ){
        $last_days = array();
        for ( $i = 0; $i < $days_back; $i++ ) {
            $time_stamp     = strtotime( '-' . $i . ' days' );
            $last_days[]    = array(
                'date'      => gmdate( 'Y-m-d', $time_stamp ),
                'day_name'  => gmdate( 'l', $time_stamp )
            ) ;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'days_back' => $days_back,
                'last_days' => $last_days
            )
        );

        return $last_days;
    }

    /**
     * Helper function to fill a wpdb-result sorted by day with quantity=0 if the day is missing.
     *
     * @param array $items
     * @param array $last_days
     *
     * @return array
     */
    public static function fill_empty_days( $items, $last_days ) {
        foreach( $last_days as $day_item ) {
            $date       = $day_item[ 'date' ];
            $day_name   = $day_item[ 'day_name' ];
            if ( !array_key_exists( $date, $items ) ) {
                $items[ $date ] = array(
                    'day_name'   => $day_name,
                    'quantity'   => 0
                );
            }
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'items'     => $items,
                'last_days' => $last_days
            )
        );

        return $items;
    }

}
