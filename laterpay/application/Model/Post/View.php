<?php

/**
 * LaterPay post views model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Model_Post_View extends LaterPay_Helper_Query
{

    /**
     * Contains the join args to get the post_title.
     * @var array
     */
    protected $post_join = array();

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
        'id'         => '%d',
        'post_id'    => '%d',
        'mode'       => '%s',
        'date'       => 'date',
        'user_id'    => '%s',
        'ip'         => '%s',
        'has_access' => '%d',
    );

    /**
     * Constructor for class LaterPay_Post_Views_Model, load table name.
     */
    function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'laterpay_post_views';

        $this->post_join = array(
            array(
                'type'      => 'INNER',
                'fields'    => array( 'post_title' ),
                'table'     => $wpdb->posts,
                'on'        => array(
                    'field'         => 'ID',
                    'join_field'    => 'post_id',
                    'compare'       => '=',
                )
            ),
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
     * Get post views.
     *
     * @param int $post_id
     *
     * @return array views
     */
    public function get_post_view_data( $post_id ) {
        $mode  = LaterPay_Helper_View::get_plugin_mode();
        $where = array( 'post_id' => (int) $post_id, 'mode' => $mode );

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

        $mode = LaterPay_Helper_View::get_plugin_mode();
        $sql  = "
            INSERT INTO
                {$this->table} (post_id, mode, user_id, date, ip, has_access)
            VALUES
                ('%d', '%s', '%s', '%s', '%s', '%d')
            ;
        ";
        $sql = $wpdb->prepare(
            $sql,
            (int) $data['post_id'],
            $mode,
            $data['user_id'],
            date( 'Y-m-d H:i:s', $data['date'] ),
            $data['ip'],
            $data['access']
        );

        return $wpdb->get_results( $sql );
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
     * Get last 30 days' history by post id.
     *
     * @param int $post_id
     *
     * @return array $results
     */
    public function get_last_30_days_history( $post_id ) {
        $today     = strtotime( 'today GMT' );
        $month_ago = strtotime( '-1 month' );
        $mode      = LaterPay_Helper_View::get_plugin_mode();

        $args = array(
            'where' => array(
                'post_id'   => (int) $post_id,
                'mode'      => $mode,
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $today ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $month_ago )
                    ),
                ),
            ),
            'order_by'  => 'DATE(date)',
            'order'     => 'ASC',
            'group_by'  => 'DATE(date)',
            'fields'    => array(
                                 'DATE(date) AS date',
                                 'COUNT(*) as quantity',
                            )
        );

        return $this->get_results( $args );
    }

    /**
     * Get number of page views of posts that are purchasable.
     *
     * @param array $args
     *
     * @return array $result
     */
    public function get_total_post_impression( $args = array() ) {
        $default_args = array( 'fields' => array( 'COUNT(*) AS quantity' ) );
        $args = wp_parse_args( $args, $default_args );

        return $this->get_row( $args );
    }

    /**
     * Get post views data.
     *
     * @param array     $args
     *
     * @return array $results
     */
    public function get_posts_views_data( $args = array() ) {
        $default_args = array(
            'fields'    => array(
                                 'post_id',
                                 'post_title',
                                 'COUNT(*) AS quantity',
                            ),
            'group_by'  => 'post_id',
            'join'      => $this->post_join,
        );

        $args = wp_parse_args( $args, $default_args );

        return $this->get_results( $args );
    }

    /**
     * Get today's history by post id.
     *
     * @param int $post_id id post
     *
     * @return array history
     */
    public function get_todays_history( $post_id ) {
        $today  = strtotime( 'today GMT' );
        $mode   = LaterPay_Helper_View::get_plugin_mode();
        $args   = array(
            'fields' => array( 'COUNT(*) AS quantity' ),
            'where'  => array(
                'post_id'   => (int) $post_id,
                'mode'      => $mode,
                'date'      => array(
                    array(
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $today ), // end of today
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $today ), // start of today
                    ),
                ),
            ),
            'join'  => $this->post_join,
        );

        return $this->get_results( $args );
    }

    /**
     * Get sparkline data for the given $post_id for x days back.
     *
     * @param int       $post_id
     * @param int       $start_timestamp
     * @param string    $interval
     * @param bool|int  $has_access
     *
     * @return array $sparkline
     */
    public function get_sparkline( $post_id, $start_timestamp, $interval = 'week', $has_access = null ) {
        $end_timestamp = LaterPay_Helper_Dashboard::get_end_timestamp( $start_timestamp, $interval );
        $mode          = LaterPay_Helper_View::get_plugin_mode();

        $args = array(
            'fields' => array(
                'DAY(date)      AS day',
                'MONTH(date)    AS month',
                'DATE(date)     AS date',
                'HOUR(date)     AS hour',
                'COUNT(*)       AS quantity',
            ),
            'where' => array(
                'date' => array(
                    array(
                        'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $end_timestamp ),
                        'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $start_timestamp ),
                    ),
                ),
                'post_id' => (int) $post_id,
                'mode'    => $mode,
            ),
            'group_by' => 'DAY(date)',
            'order_by' => 'DATE(date)',
        );

        if ( $has_access !== null ) {
            $args['where']['has_access'] = $has_access;
        }

        if ( $interval === 'day' ) {
            $args['group_by'] = 'HOUR(date)';
            $args['order_by'] = 'HOUR(date)';
        } else if ( $interval === 'month' ) {
            $args['group_by'] = 'WEEK(date)';
            $args['order_by'] = 'WEEK(date)';
        }

        $results = $this->get_results( $args );

        return LaterPay_Helper_Dashboard::build_sparkline( $results, $start_timestamp, $interval );
    }

    /**
     * Delete old data from table.
     *
     * @param string $modifier
     *
     * @return bool  $success
     */
    public function delete_old_data( $modifier ) {
        global $wpdb;

        $sql = "
            DELETE FROM
                {$this->table}
            WHERE
                date < DATE_SUB( CURDATE(), INTERVAL {$modifier} );";

        $success = $wpdb->get_results( $sql );
        return (bool) $success;
    }
}
