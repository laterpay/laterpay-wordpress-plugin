<?php

class LaterPay_Model_Currency
{

    /**
     * Name of currency table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Constructor for class LaterPay_Currency_Model, load table name.
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_currency';
    }

    /**
     * Get currencies.
     *
     * @return array currencies
     */
    public function get_currencies() {
        global $wpdb;

        $currencies = $wpdb->get_results( "SELECT * FROM {$this->table}" );

        return $currencies;
    }

    /**
     * Get currency id by ISO 4217 currency code.
     *
     * @param string $name ISO 4217 currency code
     *
     * @return array currencies
     */
    public function get_currency_id_by_iso4217_code( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->table}
            WHERE
                short_name = %s
            ;
        ";
        $currency = $wpdb->get_row( $wpdb->prepare( $sql, $name ) );

        return $currency->id;
    }

    /**
     * Get full name of currency by ISO 4217 currency code.
     *
     * @param string $name ISO 4217 currency code
     *
     * @return array currencies
     */
    public function get_currency_name_by_iso4217_code( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                full_name
            FROM
                {$this->table}
            WHERE
                short_name = %s
            ;
        ";
        $currency = $wpdb->get_row( $wpdb->prepare( $sql, $name ) );

        return $currency->full_name;
    }

}
