<?php

/**
 * LaterPay logger formatter normalizer.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Logger_Formatter_Normalizer implements LaterPay_Core_Logger_Formatter_Interface
{

    /**
     * @const string default date format
     */
    const SIMPLE_DATE = 'H:i:s j.m.Y';

    /**
     * @var string date format
     */
    protected $date_format;

    /**
     * @param string $date_format The format of the timestamp: one supported by DateTime::format
     */
    public function __construct( $date_format = null ) {
        $this->date_format = ( $date_format === null ) ? self::SIMPLE_DATE : $date_format;
    }

    /**
     * Equile to normalize method
     *
     * @param array Record data
     *
     * @return void
     */
    public function format( array $record ) {
        return $this->normalize( $record );
    }

    /**
     * @param array array of records data to normalize
     *
     * @return void
     */
    public function format_batch( array $records ) {
        foreach ( $records as $key => $record ) {
            $records[$key] = $this->format( $record );
        }

        return $records;
    }

    /**
     * Transform record into normalized form.
     *
     * @param mixed $data - incoming variable for normalizing
     *
     * @return string
     */
    protected function normalize( $data ) {
        if ( null === $data || is_scalar( $data ) ) {
            return $data;
        }

        if ( is_array( $data ) || $data instanceof \Traversable ) {
            $normalized = array();

            $count = 1;
            foreach ( $data as $key => $value ) {
                if ( $count++ >= 1000 ) {
                    $normalized['...'] = 'Over 1000 items, aborting normalization';
                    break;
                }
                $normalized[$key] = $this->normalize( $value );
            }

            return $normalized;
        }

        if ( $data instanceof \DateTime ) {
            return $data->format( $this->date_format );
        }

        if ( is_object( $data ) ) {
            if ( $data instanceof Exception ) {
                return $this->normalize_exception( $data );
            }

            return sprintf( '[object] (%s: %s)', get_class( $data ), $this->to_json( $data, true ) );
        }

        if ( is_resource( $data ) ) {
            return '[resource]';
        }

        return '[unknown(' . gettype( $data ) . ')]';
    }

    /**
     * Special method for normalizing exception.
     *
     * @param Exception $e
     *
     * @return string
     */
    protected function normalize_exception( Exception $e ) {
        $data = array(
            'class'     => get_class( $e ),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile() . ':' . $e->getLine(),
        );

        $trace = $e->getTrace();
        foreach ( $trace as $frame ) {
            if ( isset( $frame['file'] ) ) {
                $data['trace'][] = $frame['file'] . ':' . $frame['line'];
            } else {
                $data['trace'][] = json_encode( $frame );
            }
        }

        if ( $previous = $e->getPrevious() ) {
            $data['previous'] = $this->normalize_exception( $previous );
        }

        return $data;
    }

    /**
     * Convert variable into JSON.
     *
     * @param variable  $data
     * @param bool      $ignoreErrors - ignore errors or not
     *
     * @return string
     */
    protected function to_json( $data, $ignoreErrors = false ) {
        // suppress json_encode errors since it's twitchy with some inputs
        if ( $ignoreErrors ) {
            if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
                return @json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
            }

            return @json_encode( $data );
        }

        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
            return json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }

        return json_encode( $data );
    }
}
