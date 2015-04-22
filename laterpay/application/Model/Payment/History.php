<?php

/**
 * LaterPay payment history model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Model_Payment_History extends LaterPay_Helper_Query
{

    /**
     * Contains the join-args to get the post_title
     * @var array
     */
    protected $post_join = array();

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
        'revenue_model' => '%s',
        'pass_id'       => '%d',
        'code'          => '%s',
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

        $this->post_join = array(
            array(
                'type'      => 'INNER',
                'fields'    => array( 'post_title' ),
                'table'     => $wpdb->posts,
                'on'        => array(
                    'field'         => 'ID',
                    'join_field'    => 'post_id',
                    'compare'       => '='
                )
            )
        );

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

        $mode    = LaterPay_Helper_View::get_plugin_mode();
        $payment = $this->get_payment_by_hash( $mode, $data['hash'] );
        if ( empty( $payment ) ) {
            $wpdb->insert(
                    $this->table,
                    array(
                        'mode'          => $mode,
                        'post_id'       => $data['post_id'],
                        'currency_id'   => $data['id_currency'],
                        'price'         => $data['price'],
                        'date'          => date( 'Y-m-d H:i:s', $data['date'] ),
                        'ip'            => $data['ip'],
                        'hash'          => $data['hash'],
                        'revenue_model' => $data['revenue_model'],
                        'pass_id'       => $data['pass_id'],
                        'code'          => $data['code'],
                    ),
                    array(
                        '%s',
                        '%d',
                        '%d',
                        '%f',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                    )
            );
        }
    }

    /**
     * Get the user statistics.
     *
     * @param array $args
     *
     * @return array $results
     */
    public function get_user_stats( $args = array() ) {
        $default_args = array(
            'order_by'  => 'quantity',
            'order'     => 'DESC',
            'group_by'  => 'ip',
            'fields'    => array(
                                'post_id',
                                'ip',
                                'COUNT(ip)  AS quantity',
                                'SUM(price) AS amount',
                            ),
        );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Get the history.
     *
     * @param array $args
     *
     * @return array $results
     */
    public function get_history( $args = array() ) {
        $default_args = array(
            'order'     => 'ASC',
            'fields'    => array(
                'COUNT(*)       AS quantity',
                'DATE(date)     AS date',
                'DAY(date)      AS day',
                'MONTH(date)    AS month',
                'HOUR(date)     AS hour',
            ),
        );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Get the revenue history.
     *
     * @param array $args
     *
     * @return array $results
     */
    public function get_revenue_history( $args = array() ) {
        $default_args = array(
            'group_by'  => 'currency_id',
            'order'     => 'ASC',
            'fields'    => array(
                'currency_id',
                'SUM(price)     AS amount',
                'COUNT(*)       AS quantity',
                'DATE(date)     AS date',
                'DAY(date)      AS day',
                'MONTH(date)    AS month',
                'HOUR(date)     AS hour',
            ),
        );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Get the total history by post id.
     *
     * @param int $post_id
     *
     * @return array history
     */
    public function get_total_history_by_post_id( $post_id ) {
        $mode = LaterPay_Helper_View::get_plugin_mode();
        $args = array(
            'fields' => array(
                'currency_id',
                'SUM(price) AS sum',
                'COUNT(id)  AS quantity',
            ),
            'where' => array(
                'mode'      => $mode,
                'post_id'   => (int) $post_id,
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
        $mode  = LaterPay_Helper_View::get_plugin_mode();
        $today = strtotime( 'today GMT' );

        $args = array(
            'where'     => array(
                                'post_id'   => (int) $post_id,
                                'mode'      => $mode,
                                'date'      => array(
                                    array(
                                        'before'=> LaterPay_Helper_Date::get_date_query_before_end_of_day( $today ), // end of today
                                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $today ), // start of today
                                    )
                                ),
                            ),
            'group_by'  => 'currency_id',
            'fields'    => array(
                $this->table . '.currency_id',
                'SUM(' . $this->table . '.price) AS sum',
                'COUNT(' . $this->table . '.id)  AS quantity',
            ),
            'join'  => $this->post_join
        );

        return $this->get_results( $args );
    }

    /**
     * Get the posts that generated the least revenue.
     *
     * @param array $args
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return array $results
     */
    public function get_least_revenue_generating_posts( $args = array(), $start_timestamp = null, $interval = 'week' ) {
        $default_args = array(
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'ASC',
            'fields'    => array(
                'post_id',
                'post_title',
                'SUM(price) AS amount',
            ),
            'limit' => 10,
            'join'  => $this->post_join,
        );
        $args = wp_parse_args( $args, $default_args );

        $results = $this->get_results( $args );

        if ( $start_timestamp === null ) {
            return $results;
        }

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $start_timestamp, $interval );
            $data->sparkline    = implode( ',', $sparkline );
            $data->amount       = round( $data->amount, 2 );
            $results[$key]      = $data;
        }

        return $results;
    }

    /**
     * Get the posts that generated the most revenue x days back.
     * Leave end and start timestamp empty to fetch the results without sparkline.
     *
     * @param array $args
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return array $results
     */
    public function get_most_revenue_generating_posts( $args = array(), $start_timestamp = null, $interval = 'week' ) {
        $default_args = array(
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'DESC',
            'fields'    => array(
                'post_id',
                'post_title',
                'SUM(price) AS amount',
            ),
            'limit' => 10,
            'join'  => $this->post_join,
        );

        $args = wp_parse_args( $args, $default_args );

        $results = $this->get_results( $args );

        if ( $start_timestamp === null ) {
            return $results;
        }

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $start_timestamp, $interval );
            $data->sparkline    = implode( ',', $sparkline );
            $data->amount       = round( $data->amount, 2 );
            $results[$key]      = $data;
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
        $today      = strtotime( 'today GMT' );
        $month_ago  = strtotime( '-1 month' );
        $mode       = LaterPay_Helper_View::get_plugin_mode();

        $args = array(
            'fields' => array(
                'currency_id',
                'DATE(date) AS date',
                'SUM(price) AS sum',
                'COUNT(id)  AS quantity',
            ),
            'where' => array(
                'mode'      => $mode,
                'post_id'   => (int) $post_id,
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $today ),
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $month_ago ),
                    )
                )
            ),
            'group_by'  => 'currency_id, DATE(date)',
            'order_by'  => 'currency_id, DATE(date)',
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
                'hash' => $hash,
            )
        );

        return $this->get_results( $args );
    }

    /**
     * Get number of purchased items.
     *
     * @param array $args
     *
     * @return array $result
     */
    public function get_total_items_sold( $args = array() ) {
        $default_args = array(
            'fields' => array( 'COUNT(id) AS quantity', ),
        );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_row( $args );
    }


    /**
     * Get the sum of the prices of the purchased items.
     *
     * @param array $args
     *
     * @return array $result
     */
    public function get_total_revenue_items( $args = array() ) {
        $default_args = array(
            'fields' => array( 'SUM(price) AS amount', ),
        );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_row( $args );
    }

    /**
     * Get the most sold posts x days back. By default with max. 10 posts.
     * Leave end- and start timestamp empty to fetch the results without sparkline.
     *
     * @param array $args
     * @param int   $start_timestamp
     * @param string $interval
     *
     * @return array $results
     */
    public function get_best_selling_posts( $args = array(), $start_timestamp = null, $interval = 'week' ) {
        $default_args = array(
            'fields'    => array(
                                'post_id',
                                'post_title',
                                'COUNT(*) AS amount',
                             ),
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'DESC',
            'limit'     => 10,
            'join'      => $this->post_join,
        );
        $args = wp_parse_args( $args, $default_args );

        $results = $this->get_results( $args );

        if ( $start_timestamp === null ) {
            return $results;
        }

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $start_timestamp, $interval );
            $data->sparkline    = implode( ',', $sparkline );
            $results[$key]    = $data;
        }

        return $results;
    }

    /**
     * Get the least sold posts x days back. By default with max. 10 posts.
     * Leave end- and start timestamp empty to fetch the results without sparkline.
     *
     * @param array $args
     * @param int   $start_timestamp
     * @param string $interval
     *
     * @return array $results
     */
    public function get_least_selling_posts( $args = array(), $start_timestamp = null, $interval = 'week' ) {
        $default_args = array(
            'fields'    => array(
                                'post_id',
                                'post_title',
                                'COUNT(*)   AS amount',
                            ),
            'group_by'  => 'post_id',
            'order_by'  => 'amount',
            'order'     => 'ASC',
            'limit'     => 10,
            'join'      => $this->post_join,
        );
        $args = wp_parse_args( $args, $default_args );

        $results = $this->get_results( $args );

        if ( $start_timestamp === null ) {
            return $results;
        }

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $start_timestamp, $interval );
            $data->sparkline    = implode( ',', $sparkline );
            $results[$key]    = $data;
        }

        return $results;
    }

    /**
     * Get sparkline data for the given $post_id for x days back.
     *
     * @param int $post_id
     * @param int $start_timestamp
     * @param string $interval
     *
     * @return array $sparkline
     */
    public function get_sparkline( $post_id, $start_timestamp, $interval ) {
        $end_timestamp = LaterPay_Helper_Dashboard::get_end_timestamp( $start_timestamp, $interval );
        $mode          = LaterPay_Helper_View::get_plugin_mode();

        $args = array(
            'fields' => array(
                'DAY(date)  AS day',
                'MONTH(date) AS month',
                'DATE(date) AS date',
                'HOUR(date) AS hour',
                'COUNT(*)   AS quantity',
            ),
            'where' => array(
                'date' => array(
                    array(
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $end_timestamp ),
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $start_timestamp ),
                    )
                ),
                'post_id' => (int) $post_id,
                'mode'    => $mode,
            ),
            'group_by' => 'DAY(date)',
            'order_by' => 'DATE(date)',
        );

        if ( $interval === 'day' ) {
            $args['group_by'] = 'HOUR(date)';
            $args['order_by'] = 'HOUR(date)';
        }

        $results = $this->get_results( $args );
        return LaterPay_Helper_Dashboard::build_sparkline( $results, $start_timestamp, $interval );
    }

    public function get_time_pass_history( $pass_id = null ) {
        global $wpdb;

        $mode = LaterPay_Helper_View::get_plugin_mode();

        $sql = "
            SELECT
                pass_id,
                price,
                date,
                code
            FROM
                {$this->table}
            WHERE
                mode = '$mode'";

        if ( $pass_id ) {
            $sql .= "
                AND pass_id = $pass_id
            ";
        } else {
            $sql .= "
                AND pass_id <> 0
            ";
        }

        $sql .= "
            ORDER BY
                date ASC
        ";

        return $wpdb->get_results( $sql );
    }

}
