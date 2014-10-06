<?php

class LaterPay_Core_Logger_Formatter_Html extends LaterPay_Core_Logger_Formatter_Normalizer
{

    /**
     * Create an HTML table row.
     *
     * @param  string $th       Row header content
     * @param  string $td       Row standard cell content
     * @param  bool   $escapeTd false if td content must not be HTML escaped
     *
     * @return string
     */
    private function add_row( $th, $td = ' ', $escapeTd = true ) {
        $th = htmlspecialchars( $th, ENT_NOQUOTES, 'UTF-8' );

        if ( $escapeTd ) {
            $td = htmlspecialchars( $td, ENT_NOQUOTES, 'UTF-8' );
        }

        $html = "<tr>
                    <th>$th</th>
                    <td>$td</td>
                </tr>";

        return $html;
    }

    /**
     * Format a log record.
     *
     * @param  array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format( array $record ) {
        $output = '<table class="lp_log-entry-table lp_log-level-' . $record['level'] . '">';

        $output .= $this->add_row( $record['level_name'], (string) $record['message'] );
        $output .= $this->add_row( 'Time',      $record['datetime']->format( $this->date_format ) );
        $output .= $this->add_row( 'Channel',   $record['channel'] );

        if ( $record[ 'context' ] ) {
            $embedded_table = '<table>';
            foreach ( $record['context'] as $key => $value ) {
                $embedded_table .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
            $embedded_table .= '</table>';

            $output .= $this->add_row( 'Context', $embedded_table, false );
        }

        if ( $record['extra'] ) {
            $embedded_table = '<table>';
            foreach ( $record['extra'] as $key => $value ) {
                $embedded_table .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
            $embedded_table .= '</table>';

            $output .= $this->add_row( 'Extra', $embedded_table, false );
        }

        $output .= '</table>';

        return $output;
    }

    /**
     * Format a set of log records.
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
