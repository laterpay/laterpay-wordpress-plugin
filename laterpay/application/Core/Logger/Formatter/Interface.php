<?php

interface LaterPay_Core_Logger_Formatter_Interface
{

    /**
    * Formats a log record.
    *
    * @param  array $record A record to format
    *
    * @return mixed The formatted record
    */
    public function format( array $record );

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function format_batch( array $records );
}
