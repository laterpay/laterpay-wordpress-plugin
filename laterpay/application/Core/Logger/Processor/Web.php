<?php

/**
 * LaterPay core logger processor web.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Logger_Processor_Web implements LaterPay_Core_Logger_Processor_Interface
{

    /**
     * @var array|\ArrayAccess
     */
    protected $server_data;

    /**
     * @var array
     */
    protected $extra_fields = array(
        'url'         => 'REQUEST_URI',
        'ip'          => 'REMOTE_ADDR',
        'http_method' => 'REQUEST_METHOD',
        'server'      => 'SERVER_NAME',
        'referrer'    => 'HTTP_REFERER',
    );

    /**
     * @param array|\ArrayAccess $server_data  Array or object w/ ArrayAccess that provides access to the $_SERVER data
     * @param array|null         extra_fields Extra field names to be added (all available by default)
     */
    public function __construct( $server_data = null, array $extra_fields = null ) {
        if ( $server_data === null ) {
            $this->server_data =& $_SERVER;
        } elseif ( is_array( $server_data ) || $server_data instanceof \ArrayAccess ) {
            $this->server_data = $server_data;
        } else {
            throw new \UnexpectedValueException( '$server_data must be an array or object implementing ArrayAccess.' );
        }

        if ( $extra_fields !== null ) {
            foreach ( array_keys( $this->extra_fields ) as $fieldName ) {
                if ( ! in_array( $fieldName, $extra_fields ) ) {
                    unset( $this->extra_fields[$fieldName] );
                }
            }
        }
    }

    /**
     * Record processor
     *
     * @param array record data
     *
     * @return array processed record
     */
    public function process( array $record ) {
        // skip processing if for some reason request data is not present (CLI or wonky SAPIs)
        if ( ! isset( $this->server_data['REQUEST_URI'] ) ) {
            return $record;
        }

        $record['extra'] = $this->append_extra_fields( $record['extra'] );

        return $record;
    }

    /**
     * @param string $extraName
     * @param string $serverName
     *
     * @return $this
     */
    public function add_extra_field( $extraName, $serverName ) {
        $this->extra_fields[$extraName] = $serverName;

        return $this;
    }

    /**
     * @param array $extra
     *
     * @return array
     */
    private function append_extra_fields( array $extra ) {
        foreach ( $this->extra_fields as $extraName => $serverName ) {
            $extra[$extraName] = isset( $this->server_data[$serverName] ) ? $this->server_data[$serverName] : null;
        }

        if ( isset( $this->server_data['UNIQUE_ID'] ) ) {
            $extra['unique_id'] = $this->server_data['UNIQUE_ID'];
        }

        return $extra;
    }

}
