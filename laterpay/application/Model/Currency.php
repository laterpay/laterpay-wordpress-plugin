<?php

class LaterPay_Model_Currency
{

    /**
     * Contains all currencies.
     *
     * @var array
     */
    protected $currencies = array(
        array(
            'id'            => 2,
            'short_name'    => 'EUR',
            'full_name'     => 'Euro',
        ),
    );

    /**
     * Constructor for class LaterPay_Currency_Model.
     *
     * @return LaterPay_Model_Currency
     */
    function __construct() { }

    /**
     * Get currencies.
     *
     * @return array currencies
     */
    public function get_currencies() {
        return $this->currencies;
    }

    /**
     * Get short name by currency_id.
     *
     * @param integer $currency_id
     *
     * @return string $short_name
     */
    public function get_short_name_by_currency_id( $currency_id ) {
        $short_name = null;

        foreach ( $this->currencies as $currency ) {
            if ( (int) $currency[ 'id' ] === (int) $currency_id ) {
                $short_name = $currency[ 'short_name' ];
                break;
            }
        }

        return $short_name;
    }

    /**
     * Get currency id by ISO 4217 currency code.
     *
     * @param string $name ISO 4217 currency code
     *
     * @return int|null $currency_id
     */
    public function get_currency_id_by_iso4217_code( $name ) {
        $currency_id = null;

        foreach ( $this->currencies as $currency ) {
            if ( $currency[ 'short_name' ] === $name ) {
                $currency_id = $currency[ 'id' ];
                break;
            }
        }

        return $currency_id;
    }

    /**
     * Get full name of currency by ISO 4217 currency code.
     *
     * @param string $name ISO 4217 currency code
     *
     * @return string $full_name
     */
    public function get_currency_name_by_iso4217_code( $name ) {
        $full_name = '';

        foreach ( $this->currencies as $currency ) {
            if ( $currency[ 'short_name' ] === $name ) {
                $full_name = $currency[ 'full_name' ];
                break;
            }
        }

        return $full_name;
    }

}
