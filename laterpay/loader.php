<?php

class AutoLoader {
    static private $paths = array();

    /**
     * Store the filename (without extension) and full path of all '.php' files found
     */
    public static function registerDirectory( $dirName ) {
        AutoLoader::$paths[] = $dirName;
    }

    public static function loadClass( $class ) {
        $class = str_replace('..', '', $class);
        if ( strpos($class, '_') !== false ) {
            $class = str_replace('_', DIRECTORY_SEPARATOR, $class);
        } else {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        }

        foreach ( AutoLoader::$paths as $path ) {
            $file = $path . DIRECTORY_SEPARATOR . $class . '.php';
            if ( file_exists($file) ) {
                require_once($file);
                break;
            }
        }
    }
}

spl_autoload_register(array('AutoLoader', 'loadClass'), false);
