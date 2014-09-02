<?php

class LaterPay_Helper_Cache
{

    /**
     * Reset cache, if it exists.
     *
     * @return bool|void
     */
    public static function reset_opcode_cache() {
        $reset = false;

        if ( function_exists( 'opcache_reset' ) ) {
            $reset = opcache_reset();
        }
        if ( function_exists( 'apc_clear_cache' ) ) {
            $reset = apc_clear_cache();
        }
        if ( function_exists( 'eaccelerator_clean' ) ) {
            $reset = eaccelerator_clean();
        }
        if ( function_exists( 'xcache_clear_cache' ) ) {
            $reset = xcache_clear_cache();
        }

        LaterPay_Core_Logger::debug( 'LaterPay_Cache_Helper::reset_opcode_cache', array( $reset ) );

        return $reset;
    }


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
        );

        foreach ( $caching_plugins as $plugin ) {
            if ( is_plugin_active( $plugin ) ) {
                $caching_plugin_is_active = true;
                break;
            }
        }

        return $caching_plugin_is_active;
    }

}
