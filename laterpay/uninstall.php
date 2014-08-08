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

// remove pricing data from wp_postmeta table
delete_post_meta_by_key( 'laterpay_post_prices' );
delete_post_meta_by_key( 'laterpay_post_teaser' );

// remove user settings from wp_usermeta table
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

// remove global settings from wp_options table
delete_option( 'laterpay_plugin_is_activated' );
delete_option( 'laterpay_teaser_content_only' );
delete_option( 'laterpay_plugin_is_in_live_mode' );
delete_option( 'laterpay_sandbox_merchant_id' );
delete_option( 'laterpay_sandbox_api_key' );
delete_option( 'laterpay_live_merchant_id' );
delete_option( 'laterpay_live_api_key' );
delete_option( 'laterpay_global_price' );
delete_option( 'laterpay_currency' );
delete_option( 'laterpay_version' );
