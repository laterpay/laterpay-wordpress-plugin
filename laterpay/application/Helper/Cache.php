<?php

/**
 * LaterPay cache helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Cache
{

    /**
     * Check, if a known page caching plugin is active.
     *
     * @return bool
     */
    public static function site_uses_page_caching() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $caching_plugin_is_active = false;

        $caching_plugins = array(
            'wp-super-cache/wp-cache.php',          // WP Super Cache
            'w3-total-cache/w3-total-cache.php',    // W3 Total Cache
            'quick-cache/quick-cache.php',          // Quick Cache
            'wp-fastest-cache/wpFastestCache.php',  // WP Fastest Cache
            'cachify/cachify.php',                  // Cachify
            'wp-cachecom/wp-cache-com.php',         // WP-Cache.com
            'wordfence/wordfence.php',              // Wordfence
            'wp-rocket/wp-rocket.php',              // WP Rocket
            'aio-cache/aio.php',                    // AIO Cache & Performance
            'hyper-cache/plugin.php',               // Hyper Cache
            'hyper-cache-extended/plugin.php',      // Hyper Cache Extended
            'really-static/main.php',               // Really Static
            'wp-ffpc/wp-ffpc.php',                  // WP-FFPC
        );

        foreach ( $caching_plugins as $plugin ) {
            if ( is_plugin_active( $plugin ) ) {
                $caching_plugin_is_active = true;
                break;
            }
        }

        return $caching_plugin_is_active;
    }

    /**
     * Purge the cache, if a known cache plugin is active.
     *
     * @return void
     */
    public static function purge_cache() {
        /**
         * custom action for other cache plugins to purge their cache
         */
        do_action( 'laterpay_purge_cache' );

        // W3 Total Cache
        if ( function_exists( 'w3tc_pgcache_flush' ) ) {
            w3tc_pgcache_flush();
        } elseif ( function_exists( 'wp_cache_clean_cache' ) ) { // WP Super Cache
            global $file_prefix;
            wp_cache_clean_cache( $file_prefix );
        } elseif ( class_exists( 'quick_cache' ) && method_exists( 'quick_cache', 'clear' ) ) { // Quick Cache
            quick_cache::clear();
        } elseif ( class_exists( 'Cachify' ) && method_exists( 'Cachify', 'flush_total_cache' ) ) { // Cachify
            Cachify::flush_total_cache();
        }
    }

}
