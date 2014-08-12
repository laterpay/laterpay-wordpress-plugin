<?php

class LaterPay_AutoLoader
{

    static private $paths = array();

    /**
     * Store the filename (without extension) and full path of all '.php' files found.
     */
    public static function register_directory( $dirName ) {
        LaterPay_AutoLoader::$paths[] = $dirName;
        set_include_path(
            implode(
                PATH_SEPARATOR,
                array(
                    realpath( $dirName ),
                    get_include_path()
                )
            )
        );
    }

    public static function load_class( $class ) {
        $class = str_replace( '..', '', $class );
        if ( strpos( $class, '_' ) !== false ) {
            $class = str_replace( '_', DIRECTORY_SEPARATOR, $class );
        } else {
            $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        }

        foreach ( LaterPay_AutoLoader::$paths as $path ) {
            $file = $path . DIRECTORY_SEPARATOR . $class . '.php';
            if ( file_exists( $file ) ) {
                require_once( $file );
                break;
            }
        }
    }
}

spl_autoload_register( array( 'LaterPay_AutoLoader', 'load_class' ), false );
