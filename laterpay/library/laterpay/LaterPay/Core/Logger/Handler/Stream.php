<?php

/**
 * Store to any stream resource
 */
class LaterPay_Core_Logger_Handler_Stream extends LaterPay_Core_Logger_Abstract
{

    protected $stream;
    protected $url;
    protected static $errorMessage;

    /**
     * @param string  $stream
     * @param integer $level  The minimum logging level at which this handler will be triggered
     */
    public function __construct( $stream, $level = LaterPay_Core_Logger::DEBUG ) {
        parent::__construct($level);
        if ( is_resource($stream) ) {
            $this->stream = $stream;
        } else {
            $this->url = $stream;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        if ( is_resource($this->stream) ) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    public static function error_handler( $code, $msg ) {
        self::$errorMessage = preg_replace('{^fopen\(.*?\): }', '', $msg);
    }

    /**
     * {@inheritdoc}
     */
    protected function write( array $record ) {
        if ( null === $this->stream ) {
            if ( ! $this->url ) {
                throw new LogicException('Missing stream URL, the stream can not be opened. This may be caused by a premature call to close().');
            }
            self::$errorMessage = null;
            set_error_handler(array('LaterPay_Core_Logger_Handler_Stream', 'error_handler'));
            $this->stream = fopen($this->url, 'a');
            restore_error_handler();
            if ( ! is_resource($this->stream) ) {
                $this->stream = null;
                throw new UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: ' . self::$errorMessage, $this->url));
            }
        }
        fwrite($this->stream, (string) $record['formatted']);

        return true;
    }
}
