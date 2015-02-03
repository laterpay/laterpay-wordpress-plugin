<?php

abstract class LaterPay_Core_Logger_Handler_Abstract implements LaterPay_Core_Logger_Handler_Interface
{

    /**
     * @var FormatterInterface
     */
    protected $formatter;
    protected $processors = array();

    protected $level = LaterPay_Core_Logger::DEBUG;

    /**
    * @param integer $level
    */
    public function __construct( $level = LaterPay_Core_Logger::DEBUG ) {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function handle_batch( array $records ) {
        foreach ( $records as $record ) {
            $this->handle( $record );
        }
    }

    protected function get_formatted( array $record ) {
        $output = "%datetime%:%pid%.%channel%.%level_name%: %message% %context%\n";
        foreach ( $record as $var => $val ) {
            $output = str_replace( '%' . $var . '%', $this->convert_to_string( $val ), $output );
        }

        return $output;
    }

    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed.
     */
    public function close() {}

    public function __destruct() {
        try {
            $this->close();
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    protected function convert_to_string( $data ) {
        if ( null === $data || is_scalar( $data ) ) {
            return ( string ) $data;
        }

        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && defined( 'JSON_UNESCAPED_SLASHES' ) && defined( 'JSON_UNESCAPED_UNICODE' ) ) {
            return json_encode( $this->normalize( $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }

        return str_replace( '\\/', '/', json_encode( $this->normalize( $data ) ) );
    }

    protected function normalize( $data ) {
        if ( is_bool( $data ) || is_null( $data ) ) {
            return var_export( $data, true );
        }

        if ( null === $data || is_scalar( $data ) ) {
            return $data;
        }

        if ( is_array( $data ) || $data instanceof Traversable ) {
            $normalized = array();

            foreach ( $data as $key => $value ) {
                $normalized[$key] = $this->normalize( $value );
            }

            return $normalized;
        }

        if ( $data instanceof DateTime ) {
            return $data->format( 'Y-m-d H:i:s.u' );
        }

        if ( is_object( $data ) ) {
            return sprintf( '[object] (%s: %s)', get_class( $data ), json_encode( $data ) );
        }

        if ( is_resource( $data ) ) {
            return '[resource]';
        }

        return '[unknown(' . gettype( $data ) . ')]';
    }

    /**
     * {@inheritdoc}
     */
    public function is_handling( array $record ) {
        return $record[ 'level' ] >= $this->level;
    }


    /**
     * {@inheritdoc}
     */
    public function push_processor( $callback ) {
        if ( ! is_callable( $callback ) ) {
            throw new \InvalidArgumentException( 'Processors must be valid callables (callback or object with an __invoke method), ' . var_export( $callback, true ) . ' given' );
        }
        array_unshift( $this->processors, $callback );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function pop_processor() {
        if ( ! $this->processors ) {
            throw new \LogicException( 'You tried to pop from an empty processor stack.' );
        }

        return array_shift( $this->processors );
    }

    /**
     * {@inheritdoc}
     */
    public function set_formatter( LaterPay_Core_Logger_Formatter_Interface $formatter ) {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get_formatter() {
        if ( ! $this->formatter ) {
            $this->formatter = $this->get_default_formatter();
        }

        return $this->formatter;
    }

    /**
     * Sets minimum logging level at which this handler will be triggered.
     *
     * @param  integer $level
     * @return self
     */
    public function set_level( $level ) {
        $this->level = $level;
        return $this;
    }

    /**
     * Gets minimum logging level at which this handler will be triggered.
     *
     * @return integer
     */
    public function get_level() {
        return $this->level;
    }

    /**
     * Gets the default formatter.
     *
     * @return LaterPay_Core_Logger_Formatter_Interface
     */
    protected function get_default_formatter() {
        return new LaterPay_Core_Logger_Formatter_Normalizer();
    }

}
