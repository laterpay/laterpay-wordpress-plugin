<?php
/**
 * Store to any stream resource
 */
class LaterPay_Core_Logger_Handler_Stream extends LaterPay_Core_Logger_Handler_AbstractProcessing
{
    /**
     * @var resource
     */
    protected $stream;

    protected $url;
    protected $useLocking;

    private   $errorMessage;

    /**
     * @param resource|string $stream
     * @param integer         $level          The minimum logging level at which this handler will be triggered
     * @param bool            $useLocking     Try to lock log file before doing any writes
     */
    public function __construct( $stream, $level = LaterPay_Core_Logger::DEBUG, $useLocking = false )
    {
        parent::__construct( $level );

        // try to create log directory if it not exists
        $dir = $this->config->get( 'log_dir' );
        if ( null !== $dir && ! is_dir( $dir ) ) {
            $this->errorMessage = null;
            set_error_handler( array( $this, 'error_handler' ) );
            $status = mkdir( $dir, 0777 );
            restore_error_handler();
            if ( false === $status ) {
                throw new UnexpectedValueException( sprintf( 'There is no existing directory at "%s" and its not buildable: ' . $this->errorMessage, $dir ) );
            }
        }

        if ( is_resource( $stream ) ) {
            $this->stream = $stream;
        } elseif ( is_string( $stream ) ) {
            $this->url = $stream;
        } else {
            throw new InvalidArgumentException( 'A stream must either be a resource or a string.' );
        }

        $this->useLocking = $useLocking;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        if ( is_resource( $this->stream ) ) {
            fclose( $this->stream );
        }
        $this->stream = null;
    }

    /**
     * @param $code
     * @param $msg
     */
    private function error_handler( $code, $msg ) {
        $this->errorMessage = preg_replace( '{^fopen\(.*?\): }', '', $msg );
    }

    /**
     * {@inheritdoc}
     */
    protected function write( array $record ) {
        if ( ! is_resource( $this->stream ) ) {
            if ( ! $this->url ) {
                throw new LogicException( 'Missing stream URL, the stream can not be opened. This may be caused by a premature call to close().' );
            }
            $this->errorMessage = null;
            set_error_handler( array( $this, 'error_handler' ) );
            $this->stream = fopen( $this->url, 'a' );
            restore_error_handler();
            if ( ! is_resource( $this->stream ) ) {
                $this->stream = null;
                throw new UnexpectedValueException( sprintf( 'The stream or file "%s" could not be opened: ' . $this->errorMessage, $this->url ) );
            }
        }

        if ( $this->useLocking ) {
            // ignoring errors here, there's not much we can do about them
            flock( $this->stream, LOCK_EX );
        }

        fwrite( $this->stream, (string) $record['formatted'] );

        if ( $this->useLocking ) {
            flock( $this->stream, LOCK_UN );
        }
    }
}
