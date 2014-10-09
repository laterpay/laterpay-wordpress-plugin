<?php

class LaterPay_Model_Payments_History
{

    /**
     * Name of payments history table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Constructor for class LaterPay_Payments_History_Model, load table names.
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_payment_history';
    }

    /**
     * Save payment to payment history.
     *
     * @param array $data payment data
     *
     * @access public
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
     * Get total history by post id.
     *
     * @param int $post_id
     *
     * @access public
     *
     * @return array history
     */
    public function get_total_history_by_post_id( $post_id ) {
        global $wpdb;

        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $sql = "
            SELECT
                wlph.currency_id,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity
            FROM
                {$this->table} AS wlph
            WHERE
                wlph.mode = %s
                AND wlph.post_id = %d
            GROUP BY
                wlph.currency_id
            ;
        ";
        $history = $wpdb->get_results( $wpdb->prepare( $sql, $mode, (int) $post_id ) );

        return $history;
    }

    /**
     * Get today's history by post id.
     *
     * @param int $post_id
     *
     * @access public
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

        $sql = "
            SELECT
                wlph.currency_id,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity
            FROM
                {$this->table} AS wlph
            WHERE
                wlph.mode = %s
                AND wlph.post_id = %d
                AND wlph.date
                    BETWEEN '" . date( 'Y-m-d 00:00:00' ) . "'
                    AND '" . date( 'Y-m-d 23:59:59' ) . "'
            GROUP BY
                wlph.currency_id
            ;
        ";
        $history = $wpdb->get_results( $wpdb->prepare( $sql, $mode, (int) $post_id ) );

        return $history;
    }

    /**
     * Get last 30 days' history by post id.
     *
     * @param int $post_id id post
     *
     * @access public
     *
     * @return array history
     */
    public function get_last_30_days_history_by_post_id( $post_id ) {
        global $wpdb;

        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $sql = "
            SELECT
                wlph.currency_id,
                DATE(wlph.date) AS date,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity
            FROM
                {$this->table} AS wlph
            WHERE
                wlph.mode = %s
                AND wlph.post_id = %d
                AND wlph.date
                    BETWEEN DATE(SUBDATE('" . date( 'Y-m-d 00:00:00' ) . "', INTERVAL 30 DAY))
                    AND '" . date( 'Y-m-d 23:59:59' ) . "'
            GROUP BY
                wlph.currency_id,
                DATE(wlph.date)
            ORDER BY
                wlph.currency_id,
                DATE(wlph.date)
            ;
        ";
        $history = $wpdb->get_results( $wpdb->prepare( $sql, $mode, (int) $post_id ) );

        return $history;
    }

    /**
     * Get payment by hash.
     *
     * @param string $mode mode (live or test)
     * @param string $hash hash for date payment
     *
     * @access public
     *
     * @return array payment
     */
    public function get_payment_by_hash( $mode, $hash ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->table}
            WHERE
                mode = %s
                AND hash = %s
            ;
        ";
        $payment = $wpdb->get_results( $wpdb->prepare( $sql, $mode, $hash ) );

        return $payment;
    }

}
