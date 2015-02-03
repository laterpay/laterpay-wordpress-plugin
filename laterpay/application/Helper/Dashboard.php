<?php

class LaterPay_Helper_Dashboard
{

    /**
     * Helper function to load the cached data by a given file path.
     *
     * @param array $options
     *
     * @return array $cache_data array with cached data or empty array on failure
     */
    public static function get_cache_data( $options ) {
        $file_path = $options[ 'cache_file_path' ];

        if ( ! file_exists( $file_path ) ) {
            laterpay_get_logger()->error(
                __METHOD__ . ' - cache-file not found',
                array( 'file_path' => $file_path )
            );

            return array();
        }

        $cache_data = file_get_contents( $file_path );
        $cache_data = maybe_unserialize( $cache_data );

        if ( ! is_array( $cache_data ) ) {
            laterpay_get_logger()->error(
                __METHOD__ . ' - invalid cache data',
                array(
                    'file_path'     => $file_path,
                    'cache_data'    => $cache_data,
                )
            );

            return array();
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'file_path'     => $file_path,
                'cache_data'    => $cache_data,
            )
        );

        return $cache_data;
    }

    /**
     * Callback for wp-ajax and wp-cron to refresh today's dashboard data.
     * The Cron job provides two params for {x} days back and {n} count of items to
     * register your own cron with custom params to cache data.
     *
     * @wp-hook laterpay_refresh_dashboard_data
     *
     * @param array $options
     * @param array $data
     *
     * @return void
     */
    public static function refresh_cache_data( $options, $data ) {
        $timestamp = strtotime( 'now GMT' );
        $data[ 'last_update' ] = array(
            'date'      => date( 'd.m.Y H.i:s', $timestamp ),
            'timestamp' => $timestamp,
        );

        $cache_dir      = $options[ 'cache_dir' ];
        $cache_filename = $options[ 'cache_filename' ];

        // create the cache dir, if it doesn't exist
        wp_mkdir_p( $cache_dir );

        $context = $options;
        $context[ 'data' ] = $data;
        laterpay_get_logger()->info(
            __METHOD__,
            $context
        );

        // write the data to the cache dir
        file_put_contents(
            $cache_dir . $cache_filename,
            serialize( $data )
        );
    }

    /**
     * Return the cache dir by a given strottime() timestamp.
     *
     * @param int|null $timestamp default null will be set to strototime( 'today GMT' );
     *
     * @return  string $cache_dir
     */
    public static function get_cache_dir( $timestamp = null ) {
        if ( $timestamp === null ) {
            $timestamp = strtotime( 'today GMT' );
        }

        $cache_dir = laterpay_get_plugin_config()->get( 'cache_dir' ) . 'cron/' . gmdate( 'Y/m/d', $timestamp ) . '/';
        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'timestamp' => $timestamp,
                'cache_dir' => $cache_dir,
            )
        );

        return $cache_dir;
    }

    /**
     * Return the cache file name for the given days and item count.
     *
     * @param array $options
     *
     * @return string $cache_filename
     */
    public static function get_cache_filename( $options ) {
        unset( $options[ 'start_timestamp' ] );
        $array_values   = array_values( $options );
        $cache_filename = implode( '-', $array_values ) . '.cache';

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'options'           => $options,
                'cache_filename'    => $cache_filename,
            )
        );

        return $cache_filename;
    }

    /**
     * Check and sanitize a given interval.
     *
     * @param string $interval    day|week|2-weeks|month
     *
     * @return string $interval
     */
    public static function get_interval( $interval ) {
        $allowed_intervals  = array( 'day', 'week', '2-weeks', 'month' );
        $interval           = sanitize_text_field( (string) $interval );

        if ( ! in_array( $interval, $allowed_intervals ) ) {
           $interval = 'week';
        }

        return $interval;
    }

    /**
     * Return the end_timestamp by a given start_timestamp and interval.
     *
     * @param int       $start_timestamp
     * @param string    $interval
     *
     * @return int $end_timestamp
     */
    public static function get_end_timestamp( $start_timestamp, $interval = 'week' ) {
        if ( $interval === 'week' ) {
            $end_timestamp = strtotime( '-7 days', $start_timestamp );
        } else if ( $interval === '2-weeks' ) {
            $end_timestamp = strtotime( '-14 days', $start_timestamp );
        } else if ( $interval === 'month' ) {
            $end_timestamp = strtotime( '-30 days', $start_timestamp );
        } else {
            // $interval === 'day'
            $end_timestamp = strtotime( 'today', $start_timestamp );
        }

        return $end_timestamp;
    }

    /**
     * Helper function to format the amount in most-/least-items.
     *
     * @param array     $items
     * @param int       $decimal
     *
     * @return array    $items
     */
    public static function format_amount_value_most_least_data( $items, $decimal = 2 ) {
        foreach ( $items as $key => $item ) {
            $item->amount = number_format_i18n( $item->amount, $decimal );
            $items[ $key ] = $item;
        }

        return $items;
    }

    /**
     * Return the GROUP BY statement for a given interval.
     *
     * @param string $interval
     *
     * @return string $order_by
     */
    public static function get_group_by( $interval ) {
        if ( $interval === 'day' ) {
            return 'hour';
        } else if ( $interval === 'month' ) {
            return 'month';
        }

        return 'day';
    }

    /**
     * Return the ORDER BY statement for a given interval.
     *
     * @param string $interval
     *
     * @return string $order_by
     */
    public static function get_order_by( $interval ) {
        if ( $interval === 'day' ) {
            return 'hour';
        } else if ( $interval === 'month' ) {
            return 'month';
        }

        return 'date';
    }

    /**
     * Build the sparkline by given wpdb result with end and start timestamp.
     *
     * @param array     $items
     * @param int       $start_timestamp
     * @param string    $interval
     *
     * @return array $sparkline
     */
    public static function build_sparkline( $items, $start_timestamp, $interval = 'week' ) {
        $sparkline = array();

        if ( $interval === 'day' ) {
            $items_by_hour  = LaterPay_Helper_Dashboard::sort_items_by_hour( $items );
            $items          = LaterPay_Helper_Dashboard::fill_empty_hours( $items_by_hour, $start_timestamp );
        } else {
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
     * Helper Function to convert a wpdb-result to diagram data.
     *
     * @param array     $items array(
     *                      stdClass Object (
     *                          [quantity]  => 3
     *                          [day_name]  => Mon
     *                          [day]       => 27
     *                      ),
     *                      ..
     *                  )
     * @param int       $start_timestamp
     * @param string    $interval
     *
     * @return array $data array(
     *                          'x' => [{key}, day-of-week-1],
     *                          'y' => [{key}, kpi-value-1]
     *                      );
     */
    public static function convert_history_result_to_diagram_data( $items, $start_timestamp, $interval = 'week' ) {
        $data = array(
            'x' => array(),
            'y' => array(),
        );

        if ( $interval === 'day' ) {
            $items_by_hour  = LaterPay_Helper_Dashboard::sort_items_by_hour( $items );
            $items          = LaterPay_Helper_Dashboard::fill_empty_hours( $items_by_hour, $start_timestamp );
        } else {
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
                    $item->quantity,
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
                'input'     => $items,
                'result'    => $data,
            )
        );

        return $data;
    }

    /**
     * Sort all given items of a wpdb result by date.
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
        if ( empty( $items ) ) {
            laterpay_get_logger()->warning( __METHOD__ . ' - empty items-array' );
            return array();
        }

        // sort all items by date
        $items_by_date = array();
        foreach ( $items as $item ) {
            $items_by_date[ $item->date ] = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input'     => $items,
                'output'    => $items_by_date,
            )
        );

        return $items_by_date;
    }

    /**
     * Sort all given items of a wpdb result by hour.
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
        if ( empty( $items ) ) {
            laterpay_get_logger()->warning( __METHOD__ . ' - empty items-array' );
            return array();
        }
        $items_by_hour = array();
        foreach ( $items as $item ) {
            $items_by_hour[ $item->hour ] = $item;
        }
        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'input'     => $items,
                'output'    => $items_by_hour,
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
            $days = 8;
        } else if ( $interval === '2-weeks' ) {
            $days = 15;
        } else {
            $days = 31;
        }

        for ( $i = 0; $i < $days; $i++ ) {
            $time_stamp     = strtotime( '-' . $i . ' days', $start_timestamp );

            $item           = new stdClass();
            $item->date     = gmdate( 'Y-m-d', $time_stamp );
            $item->day_name = gmdate( 'D', $time_stamp );

            $last_days[]    = $item;
        }

        laterpay_get_logger()->info(
            __METHOD__,
            array(
                'end_timestamp'             => $start_timestamp,
                'formatted_end_timestamp'   => date( 'Y-m-d', $start_timestamp ),
                'interval'                  => $interval,
                'last_days'                 => $last_days,
            )
        );

        return $last_days;
    }

    /**
     * Helper function to fill a wpdb result sorted by day with quantity = 0, if the day is missing.
     *
     * @param array $items
     * @param array $last_days
     *
     * @return array
     */
    public static function fill_empty_days( $items, $last_days ) {
        foreach ( $last_days as $day_item ) {
            $date       = $day_item->date;
            $day_name   = $day_item->day_name;
            if ( ! array_key_exists( $date, $items ) ) {
                $item           = new stdClass();
                $item->day_name = $day_name;
                $item->quantity = 0;
                $item->date     = $date;

                $items[ $date ] = $item;
            }
        }

        ksort( $items );

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
     * Helper function to fill a wpdb result sorted by hour with quantity = 0, if the hour is missing.
     *
     * @param array $items
     * @param int   $start_timestamp
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
