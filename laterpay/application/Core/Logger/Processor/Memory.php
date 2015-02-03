<?php

class LaterPay_Core_Logger_Processor_Memory
{

    /**
     * @var boolean If true, get the real size of memory allocated from system. Else, only the memory used by emalloc() is reported.
     */
    protected $real_usage;

    /**
     * @var boolean If true, then format memory size to human readable string (MB, KB, B depending on size).
     */
    protected $use_formatting;

    /**
     * @param boolean $real_usage     Set this to true to get the real size of memory allocated from system
     * @param boolean $use_formatting If true, then format memory size to human readable string (MB, KB, B depending on size)
     */
    public function __construct( $real_usage = true, $use_formatting = true ) {
        $this->real_usage       = (boolean) $real_usage;
        $this->use_formatting   = (boolean) $use_formatting;
    }

    /**
     * Formats bytes into a human readable string if $this->use_formatting is true, otherwise return $bytes as is.
     *
     * @param int $bytes
     *
     * @return string|int Formatted string if $this->use_formatting is true, otherwise return $bytes as is
     */
    protected function format_bytes( $bytes ) {
        $bytes = (int) $bytes;

        if ( ! $this->use_formatting ) {
            return $bytes;
        }

        if ( $bytes > 1024 * 1024 ) {
            return round( $bytes / 1024 / 1024, 2 ) . ' MB';
        } elseif ( $bytes > 1024 ) {
            return round( $bytes / 1024, 2 ) . ' KB';
        }

        return $bytes . ' B';
    }

}
