<?php

/**
 * LaterPay core logger handler interface.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
interface LaterPay_Core_Logger_Handler_Interface
{

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * This is mostly done for performance reasons, to avoid calling processors for nothing.
     *
     * Handlers should still check the record levels within handle(), returning false in isHandling()
     * is no guarantee that handle() will not be called, and isHandling() might not be called
     * for a given record.
     *
     * @param array $record
     *
     * @return Boolean
     */
    public function is_handling( array $record );

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param array $record The record to handle
     *
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle( array $record );

    /**
     * Handles a set of records at once.
     *
     * @param array $records The records to handle (an array of record arrays)
     */
    public function handle_batch( array $records );

    /**
     * Adds a processor in the stack.
     *
     * @param callable $callback
     *
     * @return self
     */
    public function push_processor( $callback );

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function pop_processor();

    /**
     * Sets the formatter.
     *
     * @param LaterPay_Core_Logger_Formatter_Interface $formatter
     *
     * @return self
     */
    public function set_formatter( LaterPay_Core_Logger_Formatter_Interface $formatter );

    /**
     * Gets the formatter.
     *
     * @return LaterPay_Core_Logger_Formatter_Interface
     */
    public function get_formatter();

}
