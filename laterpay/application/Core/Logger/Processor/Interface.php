<?php

interface LaterPay_Core_Logger_Processor_Interface
{

    /**
    * @param  array $record
    *
    * @return array $record
    */
    public function process( array $record );

}
