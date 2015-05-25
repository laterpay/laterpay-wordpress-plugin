<?php

/**
 * LaterPay core logger processor memory peak usage.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Logger_Processor_MemoryPeakUsage extends LaterPay_Core_Logger_Processor_Memory implements LaterPay_Core_Logger_Processor_Interface
{

    /**
     * Record processor
     *
     * @param array record data
     *
     * @return array processed record
     */
    public function process( array $record ) {
        $bytes      = memory_get_peak_usage( $this->real_usage );
        $formatted  = $this->format_bytes( $bytes );

        $record['extra'] = array_merge(
            $record['extra'],
            array( 'memory_peak_usage' => $formatted, )
        );

        return $record;
    }
}
