<?php

// exit, if uninstall was not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_terms_price  = $wpdb->prefix . 'laterpay_terms_price';
$table_history      = $wpdb->prefix . 'laterpay_payment_history';
$table_post_views   = $wpdb->prefix . 'laterpay_post_views';
$table_time_passes  = $wpdb->prefix . 'laterpay_passes';
$table_postmeta     = $wpdb->postmeta;
$table_usermeta     = $wpdb->usermeta;

// remove custom tables
$sql = "DROP TABLE IF EXISTS
            $table_terms_price,
            $table_history,
            $table_post_views,
            $table_time_passes;
        ";
$wpdb->query( $sql );

// remove pricing and voting data from wp_postmeta table
delete_post_meta_by_key( 'laterpay_post_prices' );
delete_post_meta_by_key( 'laterpay_post_teaser' );
delete_post_meta_by_key( 'laterpay_rating' );
delete_post_meta_by_key( 'laterpay_users_voted' );

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
delete_option( 'laterpay_teaser_content_only' );
delete_option( 'laterpay_plugin_is_in_live_mode' );
delete_option( 'laterpay_sandbox_merchant_id' );
delete_option( 'laterpay_sandbox_api_key' );
delete_option( 'laterpay_live_merchant_id' );
delete_option( 'laterpay_live_api_key' );
delete_option( 'laterpay_global_price' );
delete_option( 'laterpay_currency' );
delete_option( 'laterpay_version' );
delete_option( 'laterpay_enabled_post_types' );
delete_option( 'laterpay_ratings' );
delete_option( 'laterpay_voucher_codes' );
delete_option( 'laterpay_voucher_statistic' );
delete_option( 'laterpay_bulk_operations' );


// remove custom capabilities
foreach ( array( 'administrator', 'editor' ) as $role ) {
    $role = get_role( $role );
    if ( empty( $role ) ) {
        continue;
    }
    $role->remove_cap( 'laterpay_read_post_statistics' );
    $role->remove_cap( 'laterpay_edit_individual_price' );
    $role->remove_cap( 'laterpay_edit_teaser_content' );
}

foreach ( array( 'author', 'contributor' ) as $role ) {
    $role = get_role( $role );
    if ( empty( $role ) ) {
        continue;
    }
    $role->remove_cap( 'laterpay_read_post_statistics' );
    $role->remove_cap( 'laterpay_edit_teaser_content' );
}

$role = get_role( 'author' );
if ( ! empty( $role ) ) {
    $role->remove_cap( 'laterpay_edit_individual_price' );
}

// remove all dismissed LaterPay pointers

// register LaterPay autoloader
$dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

if ( ! class_exists( 'LaterPay_Autoloader' ) ) {
    require_once( $dir . 'laterpay_load.php' );
}

LaterPay_AutoLoader::register_namespace( $dir . 'application', 'LaterPay' );

// delete_user_meta can't remove these pointers without damaging other data,
// also we need to use prefix ',' before pointer names to remove them properly from string
$pointers = LaterPay_Controller_Admin::get_all_pointers();

if ( ! empty( $pointers ) && is_array( $pointers ) ) {
    $replace_string = 'meta_value';

    foreach ( $pointers as $pointer ) {
        $replace_string = "REPLACE($replace_string, ',$pointer', '')";
    }

    $sql = "
        UPDATE
            $table_usermeta
        SET
            meta_value = $replace_string
        WHERE
            meta_key = 'dismissed_wp_pointers'
        ;
    ";

    $wpdb->query( $sql );
}
