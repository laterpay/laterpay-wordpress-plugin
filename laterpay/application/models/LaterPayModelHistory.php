<?php

class LaterPayModelHistory {
    /**
     * Name of payments history table
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Name of currency table
     *
     * @var string
     *
     * @access public
     */
    public $table_currency;

    /**
     * Constructor for class LaterPayModelHistory, load table names
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_payment_history';
        $this->table_currency = $wpdb->prefix . 'laterpay_currency';
    }

    /**
     * Save payment to payment history
     *
     * @param array $data payment data
     *
     * @access public
     */
    public function setPaymentHistory( $data ) {
        global $wpdb;

        if ( get_option('laterpay_plugin_is_in_live_mode') ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $payment = $this->getPaymentByHash($mode, $data['hash']);
        if ( empty($payment) ) {
            $wpdb->insert(
                    $this->table,
                    array(
                        'post_id'       => $data['post_id'],
                        'mode'          => $mode,
                        'currency_id'   => $data['id_currency'],
                        'price'         => $data['price'],
                        'date'          => date('Y-m-d H:i:s', $data['date']),
                        'ip'            => $data['ip'],
                        'hash'          => $data['hash']
                    ),
                    array(
                        '%d',
                        '%s',
                        '%d',
                        '%f',
                        '%s',
                        '%d',
                        '%s'
                    )
            );
        }
    }

    /**
     * Get total history by post id
     *
     * @param int $post_id id post
     *
     * @access public
     *
     * @return array history
     */
    public function getTotalHistoryByPostId( $post_id ) {
        global $wpdb;

        if ( get_option('laterpay_plugin_is_in_live_mode') ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $sql = "
            SELECT
                wlc.short_name AS currency,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity
            FROM
                {$this->table} AS wlph
                LEFT JOIN
                    {$this->table_currency} AS wlc
                ON
                    wlph.currency_id = wlc.id
            WHERE
                wlph.mode = '" . $mode . "'
                AND wlph.post_id = " . (int)$post_id . "
            GROUP BY
                wlph.currency_id
            ;
        ";
        $history = $wpdb->get_results($sql);

        return $history;
    }

    /**
     * Get today's history by post id
     *
     * @param int $post_id id post
     *
     * @access public
     *
     * @return array history
     */
    public function getTodayHistoryByPostId( $post_id ) {
        global $wpdb;

        if ( get_option('laterpay_plugin_is_in_live_mode') ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $sql = "
            SELECT
                wlc.short_name AS currency,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity
            FROM
                {$this->table} AS wlph
                LEFT JOIN
                    {$this->table_currency} AS wlc
                ON
                    wlph.currency_id = wlc.id
            WHERE
                wlph.mode = '" . $mode . "'
                AND wlph.post_id = " . (int)$post_id . "
                AND wlph.date
                    BETWEEN '" . date('Y-m-d 00:00:00') . "'
                    AND '" . date('Y-m-d 23:59:59') . "'
            GROUP BY
                wlph.currency_id
            ;
        ";
        $history = $wpdb->get_results($sql);

        return $history;
    }

    /**
     * Get last 30 days history by post id
     *
     * @param int $post_id id post
     *
     * @access public
     *
     * @return array history
     */
    public function getLast30DaysHistoryByPostId( $post_id ) {
        global $wpdb;

        if ( get_option('laterpay_plugin_is_in_live_mode') ) {
            $mode = 'live';
        } else {
            $mode = 'test';
        }

        $sql = "
            SELECT
                DATE(wlph.date) AS date,
                SUM(wlph.price) AS sum,
                COUNT(wlph.id) AS quantity,
                wlc.short_name AS currency
            FROM
                {$this->table} AS wlph
                LEFT JOIN
                    {$this->table_currency} AS wlc
                ON
                    wlph.currency_id = wlc.id
            WHERE
                wlph.mode = '" . $mode . "'
                AND wlph.post_id = " . (int)$post_id . "
                AND wlph.date
                    BETWEEN DATE(SUBDATE('" . date('Y-m-d 00:00:00') . "', INTERVAL 30 DAY))
                    AND '" . date('Y-m-d 23:59:59') . "'
            GROUP BY
                wlph.currency_id,
                DATE(wlph.date)
            ORDER BY
                wlph.currency_id,
                DATE(wlph.date)
            ;
        ";
        $history = $wpdb->get_results($sql);

        return $history;
    }

    /**
     * Get payment by hash
     *
     * @param string $mode mode (live or test)
     * @param string $hash hash for date payment
     *
     * @access public
     *
     * @return array payment
     */
    public function getPaymentByHash( $mode, $hash ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->table}
            WHERE
                mode = '$mode'
                AND hash = '$hash'
            ;
        ";
        $payment = $wpdb->get_results($sql);

        return $payment;
    }

}
