<?php

class LaterPay_Core_Logger
{

    const DEBUG     = 100;
    const INFO      = 200;
    const NOTICE    = 250;
    const WARNING   = 300;
    const ERROR     = 400;
    const CRITICAL  = 500;
    const ALERT     = 550;
    const EMERGENCY = 600;

    /**
     * contains all debugging levels.
     *
     * @var array
     */
    protected static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    /**
     * @var LaterPay_Core_Logger
     */
    protected static $handler;

    /**
     * @var null|string
     */
    protected static $unique_id = null;

    /**
     * @var array
     */
    protected static $_options = array();

    /**
     * @var string
     */
    protected static $_name = 'default';

    public static function init( $name, array $params ) {
        self::$_name = $name;
        if ( isset( $params[$name] ) ) {
            self::$_options = $params[$name];
        } else {
            self::$_options = array();
        }
    }

    /**
     *
     * @return LaterPay_Core_Logger_Handler_Abstract
     */
    public static function get_handler() {
        if ( empty( self::$handler ) ) {
            try {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    // log to stream, if WordPress debug mode is activated (WP_DEBUG == true)
                    self::$handler = new LaterPay_Core_Logger_Handler_WordPress();
                } else {
                    // do nothing with log data otherwise
                    self::$handler = new LaterPay_Core_Logger_Handler_Null();
                }
            } catch ( Exception $e ) {
                self::$handler = new LaterPay_Core_Logger_Handler_Null();
            }
        }

        return self::$handler;
    }

    /**
     * Add a log record at the DEBUG level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     *
     * @return boolean Whether the record has been processed
     */
    public static function debug( $message, array $context = array() ) {
        return self::log( self::DEBUG, $message, $context );
    }

    /**
     * Add a log record at the ERROR level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     *
     * @return boolean Whether the record has been processed
     */
    public static function error( $message, array $context = array() ) {
        return self::log( self::ERROR, $message, $context );
    }

    /**
     * Add a record to the log.
     *
     * @param integer $level
     * @param string  $message
     * @param array   $context
     *
     * @return boolean
     */
    public static function log( $level, $message, array $context = array() ) {
        if ( ! self::$unique_id ) {
            self::$unique_id = uniqid( getmypid() . '_' );
        }
        $date = new DateTime();
        $record = array(
            'message'       => (string) $message,
            'pid'           => self::$unique_id,
            'context'       => $context,
            'level'         => $level,
            'level_name'    => self::get_level_name( $level ),
            'channel'       => self::$_name,
            'datetime'      => $date,
        );
        try {
            $result = self::get_handler()->handle( $record );
        } catch ( Exception $e ) {
            return false;
        }

        return $result;
    }

    /**
     * Get the name of the logging level.
     *
     * @param integer $level
     *
     * @return string $level_name
     */
    public static function get_level_name( $level ) {
        return self::$levels[$level];
    }

}
