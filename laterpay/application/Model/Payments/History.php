<?php

class LaterPay_Model_Payments_History extends LaterPay_Helper_Query
{

    /**
     * {@inheritdoc}
     */
    protected $field_types = array(
        'id'            => '%d',
        'mode'          => '%s',
        'post_id'       => '%s',
        'currency_id'   => '%d',
        'date'          => 'date',
        'ip'            => '%s',
        'hash'          => '%s',
    );

    /**
     * Name of payments history table.
     *
     * @var string
     */
    public $table;

    /**
     * Constructor for class LaterPay_Payments_History_Model, load table names.
     */
    function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'laterpay_payment_history';

        add_filter( 'date_query_valid_columns', array( $this, 'add_date_query_column' ) );
    }

    /**
     * Add the 'date' column to the allowed columns.
     *
     * @wp-hook date_query_valid_columns
     *
     * @param array $columns
     *
     * @return array $columns
     */
    public function add_date_query_column( $columns ) {
        $columns[] = 'date';
        $columns[] = $this->table . '.' . 'date';

        return $columns;
    }

    /**
     * Save payment to payment history.
     *
     * @param array $data payment data
     *
     * @return void
     */
    public function set_payment_history( $data ) {
        global $wpdb;

        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $payment = $this->get_payment_by_hash( $mode, $data['hash'] );
        if ( empty( $payment ) ) {
            $wpdb->insert(
                    $this->table,
                    array(
                        'post_id'       => $data['post_id'],
                        'mode'          => $mode,
                        'currency_id'   => $data['id_currency'],
                        'price'         => $data['price'],
                        'date'          => date( 'Y-m-d H:i:s', $data['date'] ),
                        'ip'            => $data['ip'],
                        'hash'          => $data['hash'],
                    ),
                    array(
                        '%d',
                        '%s',
                        '%d',
                        '%f',
                        '%s',
                        '%d',
                        '%s',
                    )
            );
        }
    }

    /**
     * Gets the history with default 8 days back.
     *
     * @param array $args
     * @return array $results
     */
    public function get_history( $args = array() ) {
        $default_args = array(
            'where' => array(
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( 8 )
                    )
                )
            ),
            'order'     => 'ASC',
            'fields'    => array(
                'COUNT(*)       AS quantity',
                'DATE(date)     AS date',
                'DAY(date)      AS day',
                'DAYNAME(date)  AS day_name'
            )
        );

        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Gets the revenue history with default 8 days back.
     *
     * @param array $args
     * @return array $results
     */
    public function get_revenue_history( $args = array() ) {
        $default_args = array(
            'where' => array(
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( 8 )
                    )
                )
            ),
            'group_by'  => 'currency_id',
            'order'     => 'ASC',
            'fields'    => array(
                'currency_id',
                'SUM(price)     AS amount',
                'COUNT(*)       AS quantity',
                'DATE(date)     AS date',
                'DAY(date)      AS day',
                'DAYNAME(date)  AS day_name'
            )
        );

        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Get total history by post id.
     *
     * @param int $post_id
     *
     * @return array history
     */
    public function get_total_history_by_post_id( $post_id ) {
        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $args = array(
            'fields' => array(
                'currency_id',
                'SUM(price) AS sum',
                'COUNT(id) AS quantity'
            ),
            'where' => array(
                'mode'      => (string) $mode,
                'post_id'   => (int) $post_id
            ),
            'group_by' => 'currency_id'
        );

        return $this->get_results( $args );
    }

    /**
     * Get today's history by post id.
     *
     * @param int $post_id
     *
     * @return array history
     */
    public function get_todays_history_by_post_id( $post_id ) {
        global $wpdb;

        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $args = array(
            'where' => array(
                'post_id'   => (int) $post_id,
                'mode'      => $mode,
                'date'      => array(
                    array(
                        'before'=> LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( 0 ) // start of today
                    )
                )
            ),
            'group_by'  => 'currency_id',
            'fields'    => array(
                'currency_id',
                'SUM(price) AS sum',
                'COUNT(id) as quantity'
            ),
        );

        return $this->get_results( $args );
    }

    /**
     * Get posts that generated the least revenue x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_least_revenue_generating_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'mode'      => 'live',
                'date'      => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'group_by'  => 'currency_id',
            'order_by'  => 'amount',
            'order'     => 'ASC',
            'fields'    => array(
                'currency_id',
                'post_id',
                'SUM(price) AS amount',
                'COUNT(id) as quantity'
            ),
            'limit' => (int) $count
        );

        $results = $this->get_results( $args );

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline      = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline= implode( ',', $sparkline );

            $data->amount = round( $data->amount, 2 );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get posts that generated the most revenue x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_most_revenue_generating_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'mode' => 'live',
                'date' => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'group_by'  => 'currency_id',
            'order_by'  => 'amount',
            'order'     => 'DESC',
            'fields'    => array(
                'currency_id',
                'post_id',
                'SUM(price) AS amount',
                'COUNT(id) as quantity'
            ),
            'limit' => (int) $count
        );

        $results = $this->get_results( $args );

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline    = implode( ',', $sparkline );

            $data->amount = round( $data->amount, 2 );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get last 30 days' history by post id.
     *
     * @param int $post_id id post
     *
     * @return array history
     */
    public function get_last_30_days_history_by_post_id( $post_id ) {
        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $args = array(
            'fields' => array(
                'currency_id',
                'DATE(date) AS date',
                'SUM(price) AS sum',
                'COUNT(id) AS quantity'
            ),
            'where' => array(
                'mode'      => (string) $mode,
                'post_id'   => (int) $post_id,
                'date'      => array(
                    'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ),
                    'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( 30 ),
                )
            ),
            'group_by'  => 'currency_id, DATE(date)',
            'order_by'  => 'currency_id, DATE(date)'
        );

        return $this->get_results( $args );
    }

    /**
     * Get payment by hash.
     *
     * @param string $mode mode (live or test)
     * @param string $hash hash for date payment
     *
     * @return array payment
     */
    public function get_payment_by_hash( $mode, $hash ) {
        $args = array(
            'fields'    => array( 'id' ),
            'where'     => array(
                'mode' => $mode,
                'hash' => $hash
            )
        );
        return $this->get_results( $args );
    }

    /**
     * Get number of purchased items.
     *
     * @return array $result
     */
    public function get_total_items_sold() {
        $args = array( 'fields' => array( 'COUNT(id) AS quantity' ) );
        return $this->get_row( $args );
    }


    /**
     * Get sum of prices of purchased items.
     *
     * @return array $result
     */
    public function get_total_revenue_items() {
        $args = array( 'fields' => array( 'sum(price) AS amount' ) );
        return $this->get_row( $args );
    }

    /**
     * Get most sold posts x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_best_selling_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'date' => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'fields'    => array( 'post_id', 'COUNT(*) AS amount' ),
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'DESC',
            'limit'     => (int) $count
        );
        $results = $this->get_results( $args );

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline      = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline= implode( ',', $sparkline );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get least sold posts x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_least_selling_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'date' => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'fields'    => array( 'post_id', 'COUNT(*) AS amount' ),
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'ASC',
            'limit'     => (int) $count
        );
        $results = $this->get_results( $args );

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline      = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline= implode( ',', $sparkline );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get sparkline data for the given $post_id for x days back.
     *
     * @param int $post_id
     * @param int $days
     *
     * @return array $sparkline
     */
    public function get_sparkline( $post_id, $days ) {
        $sparkline = array();

        for ($i = 1; $i <= (int) $days; $i ++) {
            $args = array(
                'fields' => array( 'COUNT(*) AS quantity' ),
                'where' => array(
                    'date' => array(
                        array(
                            'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $i ),
                            'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $i ),
                        )
                    ),
                    'post_id' => (int) $post_id
                )
            );

            $day_post_views = $this->get_results( $args );

            if ( empty( $day_post_views ) ) {
                $sparkline[] = 0;
            } else {
                $sparkline[] = $day_post_views[0]->quantity;
            }
        }

        // reverse the order of $sparkline, to start today - $days days
        $sparkline = array_reverse( $sparkline );

        return $sparkline;
    }


}
