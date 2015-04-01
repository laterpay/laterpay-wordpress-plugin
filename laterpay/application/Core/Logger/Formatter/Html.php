<?php

/**
 * LaterPay logger HTML formatter.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Logger_Formatter_Html extends LaterPay_Core_Logger_Formatter_Normalizer
{

    /**
     * Format a set of log records.
     *
     * @param array $records A set of records to format
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

    /**
     * Format a log record.
     *
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format( array $record ) {
        $output  = '<li class="lp_debugger-content-list__item">';
        $output .= '<table class="lp_js_debuggerContentTable lp_debugger-content__table lp_is-hidden">';

        // generate thead of log record
        $output .= $this->add_head_row( (string) $record['message'], $record['level'] );

        // generate tbody of log record with details
        $output .= '<tbody class="lp_js_logEntryDetails lp_debugger-content__table-body" style="display:none;">';
        $output .= '<tr><td class="lp_debugger-content__table-td" colspan="2"><table class="lp_debugger-content__table">';

        if ( $record['context'] ) {
            foreach ( $record['context'] as $key => $value ) {
                $output .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
        }

        if ( $record['extra'] ) {
            foreach ( $record['extra'] as $key => $value ) {
                $output .= $this->add_row( $key, $this->convert_to_string( $value ) );
            }
        }

        $output .= '</td></tr></table>';
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</li>';

        return $output;
    }

    /**
     * Create the header row for a log record.
     *
     * @param string   $message  log message
     * @param int      $level    log level
     *
     * @return string
     */
    private function add_head_row( $message = '', $level ) {
        $show_details_link = '<a href="#" class="lp_js_toggleLogDetails" data-icon="l">' . __( 'Details', 'laterpay' ) . '</a>';

        $html = '<thead class="lp_js_debuggerContentTableTitle lp_debugger-content__table-title">
            <tr>
                <td class="lp_debugger-content__table-td"><span class="lp_debugger__log-level lp_debugger__log-level--' . $level . ' lp_vectorIcon"></span>' . $message . '</td>
                <td class="lp_debugger-content__table-td">' . $show_details_link . '</td>
            </tr>
        </thead>';

        return $html;
    }

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
                    <th class='lp_debugger-content__table-th' title=\"$th\">$th</th>
                    <td class='lp_debugger-content__table-td'>$td</td>
                </tr>";

        return $html;
    }

    /**
     * Convert data into string
     *
     * @param mixed $data
     *
     * @return sting
     */
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
