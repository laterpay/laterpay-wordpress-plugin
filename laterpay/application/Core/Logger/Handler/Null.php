<?php

/**
 * Do nothing with log data.
 */
class LaterPay_Core_Logger_Handler_Null extends LaterPay_Core_Logger_Abstract
{

    /**
     * @param integer $level The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = LaterPay_Core_Logger::DEBUG ) {
        parent::__construct( $level, false );
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
