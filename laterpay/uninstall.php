<?php

// exit, if uninstall was not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_currency     = $wpdb->prefix . 'laterpay_currency';
$table_terms_price  = $wpdb->prefix . 'laterpay_terms_price';
$table_history      = $wpdb->prefix . 'laterpay_payment_history';
$table_post_views   = $wpdb->prefix . 'laterpay_post_views';
$table_postmeta     = $wpdb->prefix . 'postmeta';
$table_usermeta     = $wpdb->prefix . 'usermeta';

// remove custom tables
$sql = "DROP TABLE IF EXISTS
            $table_currency,
            $table_terms_price,
            $table_history,
            $table_post_views;
        ";
$wpdb->query( $sql );

// remove custom data from WP core tables
$sql = "DELETE FROM
            $table_postmeta
        WHERE
            meta_key IN (
                'Teaser content',
                'Pricing Post',
                'Pricing Post Type',
                'laterpay_start_price',
                'laterpay_end_price',
                'laterpay_change_start_price_after_days',
                'laterpay_transitional_period_end_after_days',
                'laterpay_reach_end_price_after_days'
            )
        ;
        ";
$wpdb->query( $sql );
$sql = "DELETE FROM
            $table_usermeta
        WHERE
            meta_key IN (
                'laterpay_preview_post_as_visitor',
                'laterpay_hide_statistics_pane'
            )
        ;
        ";
$wpdb->query( $sql );

// delete cached config
wp_cache_delete( 'laterpay', 'config' );
