<?php

abstract class LaterPay_Core_Logger_Handler_AbstractProcessing extends LaterPay_Core_Logger_Handler_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function handle( array $record )
    {
        if ( ! $this->is_handling( $record ) ) {
            return false;
        }

        $record = $this->processRecord( $record );
        $record['formatted'] = $this->get_formatter()->format( $record );
        $this->write( $record );

        return true;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    abstract protected function write( array $record );

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ( $this->processors ) {
            foreach ( $this->processors as $processor ) {
                $record = call_user_func( $processor, $record );
            }
        }

        return $record;
    }
}
