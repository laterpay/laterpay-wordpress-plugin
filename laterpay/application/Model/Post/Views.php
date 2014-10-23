<?php

class LaterPay_Model_Post_Views extends LaterPay_Helper_Query
{

    /**
     * Name of PostViews table.
     * @var string
     */
    protected $table;

    /**
     * {@inhertidoc}
     */
    protected $table_short = 'wplpv';

    /**
     * {@inheritdoc}
     */
    protected $field_types = array(
        'post_id'   => '%d',
        'date'      => 'date',
        'user_id'   => '%d',
        'count'     => '%d',
        'ip'        => '%s'
    );

    /**
     * Constructor for class LaterPay_Post_Views_Model, load table name.
     */
    function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'laterpay_post_views';

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
     * Get post views.
     *
     * @param int $post_id
     *
     * @return array views
     */
    public function get_post_view_data( $post_id ) {
        $where = array( 'post_id' => (int) $post_id );

        return $this->get_results( $where );
    }

    /**
     * Save payment to payment history.
     *
     * @param array $data payment data
     *
     * @return array post views
     */
    public function update_post_views( $data ) {
        global $wpdb;

        $sql = "
            INSERT INTO
                {$this->table} (post_id, user_id, date, ip)
            VALUES
                ('%d', '%s', '%s', '%s')
            ON DUPLICATE KEY UPDATE
                count = count + 1
            ;
        ";
        $sql = $wpdb->prepare(
            $sql,
            (int) $data['post_id'],
            $data['user_id'],
            date( 'Y-m-d H:i:s', $data['date'] ),
            $data['ip']
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Get last 30 days' history by post id.
     *
     * @param int $post_id
     *
     * @return array $results
     */
    public function get_last_30_days_history( $post_id ) {
        $args = array(
            'where' => array(
                'post_id'   => (int) $post_id,
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( 30 )
                    )
                )
            ),
            'order_by'  => 'DATE(date)',
            'order'     => 'ASC',
            'group_by'  => 'DATE(date)',
            'fields'    => array( 'DATE(date) AS date', 'COUNT(*) as quantity' )
        );

        return $this->get_results( $args );
    }

    /**
     * Get post views count.
     *
     * @return array $results
     */
    public function get_post_view_quantity() {
        $args = array( 'fields' => array( 'COUNT(*) AS quantity' ) );

        return $this->get_results( $args );
    }

    /**
     * Get most viewed posts x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_most_viewed_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'date' => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'fields'    => array( 'post_id', 'COUNT(*) AS quantity' ),
            'group_by'  => 'post_id',
            'order_by'  => 'quantity',
            'order'     => 'DESC',
            'limit'     => (int) $count
        );
        $results = $this->get_results( $args );

        // fetch the total count of post views
        $total_quantity = $this->get_post_view_quantity();
        $total_quantity = $total_quantity[0]->quantity;

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline    = implode( ',', $sparkline );

            // % amount
            $data->amount       = $data->quantity * 100 / $total_quantity;
            $data->amount       = number_format( $data->amount, 2 );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get least viewed posts x days back.
     *
     * @param int $days
     * @param int $count
     *
     * @return array $results
     */
    public function get_least_viewed_posts( $days = 8, $count = 10 ) {
        $args = array(
            'where' => array(
                'date' => array(
                    array(
                        'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                    )
                )
            ),
            'fields'    => array( 'post_id', 'COUNT(*) AS quantity' ),
            'group_by'  => 'post_id',
            'order_by'  => 'quantity',
            'order'     => 'ASC',
            'limit'     => (int) $count
        );
        $results = $this->get_results( $args );

        $total_quantity = $this->get_post_view_quantity();
        $total_quantity = $total_quantity[0]->quantity;

        foreach ( $results as $key => $data ) {
            // the sparkline for the last x days
            $sparkline          = $this->get_sparkline( $data->post_id, $days );
            $data->sparkline    = implode( ',', $sparkline );

            // % amount
            $data->amount       = $data->quantity * 100 / $total_quantity;
            $data->amount       = number_format( $data->amount, 2 );

            $results[ $key ] = $data;
        }

        return $results;
    }

    /**
     * Get today's history by post id.
     *
     * @param int $post_id id post
     *
     * @return array history
     */
    public function get_todays_history( $post_id ) {
        $args = array(
            'fields'=> array( 'COUNT(*) AS quantity' ),
            'where' => array(
                'post_id'   => (int) $post_id,
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( 0 ) // start of today
                    )
                )
            )
        );

        return $this->get_results( $args );
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
