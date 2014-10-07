<?php

class LaterPay_Core_Logger_Processor_Introspection
{

    private $level;

    private $skip_classes_partials;

    public function __construct( $level = LaterPay_Core_Logger::DEBUG, array $skip_classes_partials = array() ) {
        $this->level = $level;
        $this->skip_classes_partials = $skip_classes_partials;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record) {

        // return, if the level is not high enough
        if ( $record['level'] < $this->level ) {
            return $record;
        }

        $trace = debug_backtrace();

        // skip first since it's always the current method
        array_shift( $trace );
        // the call_user_func call is also skipped
        array_shift( $trace );

        $i = 0;

        while ( isset( $trace[$i]['class'] ) ) {
            foreach ( $this->skip_classes_partials as $part ) {
                if ( strpos( $trace[$i]['class'], $part ) !== false ) {
                    $i++;
                    continue 2;
                }
            }
            break;
        }

        // we should have the call source now
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'file'      => isset( $trace[$i-1]['file'] )    ? $trace[$i-1]['file']      : null,
                'line'      => isset( $trace[$i-1]['line'] )    ? $trace[$i-1]['line']      : null,
                'class'     => isset( $trace[$i]['class'] )     ? $trace[$i]['class']       : null,
                'function'  => isset( $trace[$i]['function'] )  ? $trace[$i]['function']    : null,
            )
        );

        return $record;
    }
}
