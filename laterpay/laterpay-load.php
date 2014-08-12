<?php

class LaterPay_AutoLoader
{

    static private $paths = array();
    static private $namespaces = array();
    
    public static function register_namespace( $dirName, $namespace ) {
        LaterPay_AutoLoader::$namespaces[] = array( 'path' => $dirName, 'name' => $namespace );
    }
    
    protected static function get_class_relative_path( $class ) {
        $class = str_replace( '..', '', $class );
        if ( strpos( $class, '_' ) !== false ) {
            $class = str_replace( '_', DIRECTORY_SEPARATOR, $class );
        } else {
            $class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );
        }
        
        return $class;
    }

    public static function load_class_from_namespace( $class ) {
        $class = self::get_class_relative_path($class);
        
        foreach ( LaterPay_AutoLoader::$namespaces as $namespace ) {
            if ( strpos($class, $namespace['name']) !== false ) {
                $relative_path = str_replace($namespace['name'], '', $class);
                $relative_path = trim($relative_path, DIRECTORY_SEPARATOR);
                $file = $namespace['path'] . DIRECTORY_SEPARATOR . $relative_path . '.php';
                if ( file_exists($file) ) {
                    require_once( $file );
                    break;
                }
            }
        }
    }

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
        $class = self::get_class_relative_path($class);

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
spl_autoload_register( array( 'LaterPay_AutoLoader', 'load_class_from_namespace' ), false );
