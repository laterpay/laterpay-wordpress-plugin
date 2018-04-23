<?php


/**
 * LaterPay logger line formatter.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Logger_Formatter_Line extends LaterPay_Core_Logger_Formatter_Normalizer
{
    const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    protected $format;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     */
    public function __construct( $format = null, $dateFormat = null )
    {
        $this->format = $format ? $format : static::SIMPLE_FORMAT;
        parent::__construct( $dateFormat );
    }

    /**
     * {@inheritdoc}
     */
    public function format( array $record )
    {
        $vars = parent::format( $record );

        $output = $this->format;

        foreach ( $vars['extra'] as $var => $val ) {
            if ( false !== strpos( $output, '%extra.' . $var . '%' ) ) {
                $output = str_replace( '%extra.' . $var . '%', $this->convert_to_string( $val ), $output );
                unset( $vars['extra'][ $var ] );
            }
        }

        if ( empty( $vars['context'] ) ) {
            unset( $vars['context'] );
            $output = str_replace( '%context%', '', $output );
        }

        if ( empty( $vars['extra'] ) ) {
            unset( $vars['extra'] );
            $output = str_replace( '%extra%', '', $output );
        }

        if ( is_array( $vars ) ) {
            foreach ( $vars as $var => $val ) {
                if ( false !== strpos( $output, '%' . $var . '%' ) ) {
                    $output = str_replace( '%' . $var . '%', $this->convert_to_string( $val ), $output );
                }
            }
        }

        return $output;
    }

    public function format_batch( array $records )
    {
        $message = '';
        foreach ( $records as $record ) {
            $message .= $this->format( $record );
        }

        return $message;
    }

    protected function convert_to_string( $data )
    {
        if ( null === $data ) {
            return "";
        }

        if( is_bool( $data ) ) {
            return $data ? "true" : "";
        }

        if ( is_scalar( $data ) ) {
            return (string) $data;
        }

        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
            return $this->to_json( $data );
        }

        return str_replace( '\\/', '/', wp_json_encode( $data ) );
    }
}
