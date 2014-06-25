<?php

/**
 * Do nothing with log data
 */
class Logger_Handler_Null extends Logger_Abstract {
    /**
     *
     *
     * @param integer $level The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = Logger::DEBUG ) {
        parent::__construct($level, false);
    }

    /**
     * {@inheritdoc}
     */
    public function handle( array $record ) {
        if ( $record['level'] < $this->level ) {
            return false;
        }

        return true;
    }

    protected function write( array $record ) {
        // do nothing
    }
}
