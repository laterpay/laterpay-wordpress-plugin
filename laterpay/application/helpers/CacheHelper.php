<?php

class CacheHelper {

    public static function resetOpcodeCache() {
        $reset = false;

        if ( function_exists('opcache_reset') ) {
            $reset = opcache_reset();
        }
        if ( function_exists('apc_clear_cache') ) {
            $reset = apc_clear_cache();
        }
        if ( function_exists('eaccelerator_clean') ) {
            $reset = eaccelerator_clean();
        }
        if ( function_exists('xcache_clear_cache') ) {
            $reset = xcache_clear_cache();
        }

        Logger::debug('CacheHelper::resetOpcodeCache', array($reset));

        return $reset;
    }

}
