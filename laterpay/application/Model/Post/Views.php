<?php

class LaterPay_Model_Post_Views
{

    /**
     * Name of PostViews table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Constructor for class LaterPay_Post_Views_Model, load table name.
     */
    function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'laterpay_post_views';
    }

    /**
     * Get post views.
     *
     * @access public
     *
     * @return array views
     */
    public function get_post_view_data( $post_id ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table}
            WHERE
                post_id = %d
            ;
        ";
        $views = $wpdb->get_results( $wpdb->prepare( $sql, (int) $post_id ) );

        return $views;
    }

    /**
     * Save payment to payment history.
     *
     * @param array $data payment data
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
	    $sql =  $wpdb->prepare(
		    $sql,
		    (int) $data['post_id'],
		    (int) $data['user_id'],
		    date( 'Y-m-d H:i:s', $data['date'] ),
		    $data['ip']
	    );

	    return $wpdb->get_results( $sql );

    }

    /**
     * Get last 30 days' history by post id.
     *
     * @param int $post_id id post
     *
     * @return array history
     */
    public function get_last_30_days_history( $post_id ) {
        global $wpdb;

        $sql = "
            SELECT
                DATE(wlpv.date) AS date,
                COUNT(*) AS quantity
            FROM
                {$this->table} AS wlpv
            WHERE
                wlpv.post_id = %d
                AND wlpv.date
                    BETWEEN DATE(SUBDATE('%s', INTERVAL 30 DAY))
                    AND '%s'
            GROUP BY
                DATE(wlpv.date)
            ORDER BY
                DATE(wlpv.date) ASC
            ;
        ";

        $history = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                (int) $post_id,
                date( 'Y-m-d 00:00:00' ),
                date( 'Y-m-d 23:59:59' )
            )
        );

        return $history;
    }

    /**
     * Get today's history by post id.
     *
     * @param int $post_id id post
     *
     * @return array history
     */
    public function get_todays_history( $post_id ) {
        global $wpdb;

        $sql = "
            SELECT
                COUNT(*) AS quantity
            FROM
                {$this->table} AS wlpv
            WHERE
                wlpv.post_id = %d
                AND wlpv.date
                    BETWEEN '%s'
                    AND '%s'
            ;
        ";

        $history = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                (int) $post_id,
                date( 'Y-m-d 00:00:00' ),
                date( 'Y-m-d 23:59:59' )
            )
        );

        return $history;
    }

}
