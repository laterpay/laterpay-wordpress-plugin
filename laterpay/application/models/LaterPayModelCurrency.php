<?php

class LaterPayModelCurrency {
    /**
     * Name of currency table
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Constructor for class LaterPayModelCurrency, load table name
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_currency';
    }

    /**
     * Get currencies
     *
     * @access public
     *
     * @return array currencies
     */
    public function getCurrencies() {
        global $wpdb;

        $currencies = $wpdb->get_results("SELECT * FROM {$this->table}");

        return $currencies;
    }

    /**
     * Get currency id by currency code
     *
     * @param string $name short name currency
     *
     * @access public
     *
     * @return array currencies
     */
    public function getCurrencyIdByShortName( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->table}
            WHERE
                short_name = '$name'
            ;
        ";
        $currency = $wpdb->get_row($sql);

        return $currency->id;
    }

    /**
     * Get full name of currency by currency code
     *
     * @param string $name short name currency
     *
     * @access public
     *
     * @return array currencies
     */
    public function getCurrencyFullNameByShortName( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                full_name
            FROM
                {$this->table}
            WHERE
                short_name = '$name'
            ;
        ";
        $currency = $wpdb->get_row($sql);

        return $currency->full_name;
    }

}
