<?php

/**
 * LaterPay date helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Date
{

    /**
     * Get a 'before' search, starting at 23:59:59.
     *
     * @param int $timestamp
     *
     * @return array $after
     */
    public static function get_date_query_before_end_of_day( $timestamp ) {
        return array(
            'day'       => date( 'd', $timestamp ),
            'month'     => date( 'm', $timestamp ),
            'year'      => date( 'Y', $timestamp ),
            'hour'      => 23,
            'minute'    => 59,
            'second'    => 59,
        );
    }

    /**
     * Get an 'after' search, starting at 00:00:00.
     *
     * @param int $timestamp
     *
     * @return array $after
     */
    public static function get_date_query_after_start_of_day( $timestamp ) {
        return array(
            'day'       => date( 'd', $timestamp ),
            'month'     => date( 'm', $timestamp ),
            'year'      => date( 'Y', $timestamp ),
            'hour'      => 0,
            'minute'    => 0,
            'second'    => 0,
        );
    }

}
