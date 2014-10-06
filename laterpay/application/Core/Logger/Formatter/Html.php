<?php

class LaterPay_Core_Logger_Formatter_Html extends LaterPay_Core_Logger_Formatter_Normalizer
{

    /**
     * Translates log levels to html color priorities.
     */
    private $log_levels = array(
        LaterPay_Core_Logger::DEBUG     => '#cccccc',
        LaterPay_Core_Logger::INFO      => '#468847',
        LaterPay_Core_Logger::NOTICE    => '#3a87ad',
        LaterPay_Core_Logger::WARNING   => '#c09853',
        LaterPay_Core_Logger::ERROR     => '#f0ad4e',
        LaterPay_Core_Logger::CRITICAL  => '#FF7708',
        LaterPay_Core_Logger::ALERT     => '#C12A19',
        LaterPay_Core_Logger::EMERGENCY => '#000000',
    );

    /**
     * Creates an HTML table row.
     *
     * @param  string $th       Row header content
     * @param  string $td       Row standard cell content
     * @param  bool   $escapeTd false if td content must not be html escaped
     *
     * @return string
     */
    private function add_row( $th, $td = ' ', $escapeTd = true ) {
        $th = htmlspecialchars( $th, ENT_NOQUOTES, 'UTF-8' );
        if ( $escapeTd ) {
            $td = '<pre>' . htmlspecialchars( $td, ENT_NOQUOTES, 'UTF-8' ) . '</pre>';
        }

        return "<tr style=\"padding: 4px;spacing: 0;text-align: left;\">\n<th style=\"background: #ccc\" width=\"100px\">$th:</th>\n<td style=\"padding: 4px;spacing: 0;text-align: left;background: #eeeeee\">" . $td . "</td>\n</tr>";
    }

    /**
     * Create a HTML h1 tag.
     *
     * @param  string  $title Text to be in the h1
     * @param  integer $level Error level
     *
     * @return string
     */
    private function add_title( $title, $level ) {
        $title = htmlspecialchars( $title, ENT_NOQUOTES, 'UTF-8' );

        return '<h1 style="background: ' . $this->log_levels[$level] . ';color:#fff; padding:5px;">' . $title . '</h1>';
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format( array $record ) {
        $output = $this->add_title( $record['level_name'], $record['level'] );
        $output .= '<table cellspacing="1" width="100%">';

        $output .= $this->add_row( 'Message', (string) $record['message'] );
        $output .= $this->add_row( 'Time', $record['datetime']->format( $this->date_format ) );
        $output .= $this->add_row( 'Channel', $record['channel'] );
        if ( $record[ 'context' ] ) {
            $embedded_table = '<table cellspacing="1" width="100%">';
            foreach ( $record['context'] as $key => $value ) {
                $embedded_table .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
            $embedded_table .= '</table>';
            $output .= $this->add_row( 'Context', $embedded_table, false );
        }
        if ( $record['extra'] ) {
            $embedded_table = '<table cellspacing="1" width="100%">';
            foreach ( $record['extra'] as $key => $value ) {
                $embedded_table .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
            $embedded_table .= '</table>';
            $output .= $this->add_row( 'Extra', $embedded_table, false );
        }

        return $output . '</table>';
    }

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function format_batch( array $records ) {
        $message = '';
        foreach ( $records as $record ) {
            $message .= $this->format( $record );
        }

        return $message;
    }

    protected function convert_to_string( $data ) {
        if ( null === $data || is_scalar( $data ) ) {
            return (string) $data;
        }

        $data = $this->normalize( $data );
        if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
            return json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }

        return str_replace( '\\/', '/', json_encode( $data ) );
    }
}
