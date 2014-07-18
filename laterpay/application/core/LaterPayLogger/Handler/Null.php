<?php

/**
 * Do nothing with log data
 */
class LaterPayLogger_Handler_Null extends LaterPayLogger_Abstract {
    /**
     *
     *
     * @param integer $level The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = LaterPayLogger::DEBUG ) {
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
