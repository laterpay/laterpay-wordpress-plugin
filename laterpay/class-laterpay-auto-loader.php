<?php

class LaterPay_Auto_Loader
{

    static private $paths = array();

    /**
     * Store the filename (without extension) and full path of all '.php' files found
     */
    public static function register_directory( $dirName ) {
        LaterPay_Auto_Loader::$paths[] = $dirName;
    }

    public static function load_class( $class ) {
        $class = str_replace( '..', '', $class );
        if ( strpos( $class, '_' ) !== false ) {
            $class = str_replace( '_', DIRECTORY_SEPARATOR, $class );
        } else {
            $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        }

        foreach ( LaterPay_Auto_Loader::$paths as $path ) {
            $file = $path . DIRECTORY_SEPARATOR . 'class-' . strtolower( $class ) . '.php';
            if ( file_exists( $file ) ) {
                require_once( $file );
                break;
            }
        }
    }
}

spl_autoload_register( array( 'LaterPay_Auto_Loader', 'load_class' ), false );
