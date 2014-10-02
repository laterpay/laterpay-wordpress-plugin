<?php

/**
 * Do nothing with log data.
 */
class LaterPay_Core_Logger_Handler_WordPress extends LaterPay_Core_Logger_Handler_Abstract
{

    /**
     *
     * @var array
     */
    protected $records = array();

    /**
     * @var LaterPay_Model_Config
     */
    protected $config;

    /**
     * @param integer $level The minimum logging level at which this handler will be triggered
     */
    public function __construct( $level = LaterPay_Core_Logger::DEBUG ) {
        parent::__construct( $level, false );

        $this->config = laterpay_get_plugin_config();

        add_action( 'wp_footer',            array( $this, 'render_records' ), 99999 );
        add_action( 'wp_enqueue_scripts',   array( $this, 'register_scripts' ) );

    }

    /**
     * {@inheritdoc}
     */
    public function handle( array $record ) {
        if ( $record['level'] < $this->level ) {
            return false;
        }
        $this->records[] = $record;
        return true;
    }

    protected function write( array $record ) {
        // do nothing
    }

    /**
     * Callback to Render all Records to footer
     *
     * @wp-hook wp_footer
     * @return void
     */
    public function render_records(){

        // log file and dir
        $log_dir    = $this->config->get( 'log_dir' ) . date( 'Y/m/d/' );
        $log_file   = time() . '.php';

        $tabs = array();
        $tabs[ 'logger' ] = $this->get_logger_tab();
        $tabs[ 'request' ] = $this->get_request_tab();
        $tabs[ 'server' ] = $this->get_server_tab();
        $tabs[ 'session' ] = $this->get_session_tab();

        ?>
        <section id="lp_debugger">
            <h1 data-icon="a" class="lp_debugger_headline"><?php _e( ' Debugger', 'laterpay' ); ?></h1>
            <p>
                <?php printf(
                    __( 'Logfile: <code>%s</code>', 'laterpay' ),
                    $log_file
                );
                ?>
            </p>
                <?php
                foreach( $tabs as $key => $tab ){
                    if ( empty( $tab[ 'content' ] ) ){
                        continue;
                    }
                    $id = 'lp_debugger_tab_' . $key;
                    echo '<div id="'. $id . '" class="lp_debugger_tab">';
                    echo '<h2 class="lp_debugger_tab_headline"><a class="lp_debugger_tab_link" href="#' . $id . '">' . $tab[ 'name' ] . '</a></h2>';
                    echo '<div class="lp_debugger_tab_content">' . $tab[ 'content' ] . '</div>';
                    echo '</div>';
                }
                ?>
            <script async>
                (function($) {
                    $( '.lp_debugger_tab_link' ).on( 'click', function(e){
                        var target = $(this ).attr('href');
                        console.log(target);
                        $(target).children('.lp_debugger_tab_content').toggle();
                    });
                } )(jQuery);
            </script>
            </div>
        </section>
        <?php

        // creating the log-file
        wp_mkdir_p( $log_dir );

        // store the records to a log file for later use
        $content = "<?php  \n return " . var_export( $tabs, true ) . ";";
        file_put_contents( $log_dir . $log_file, $content );


    }

    /**
     *
     * @return array $tab
     */
    protected function get_logger_tab(){
        $output = '';

        foreach( $this->records as $record ){
            $output .= '<table class="lp_debugger_detail_table">';
            $output .= $this->get_table_row( __( 'Pid', 'laterpay' ) , $record[ 'pid' ] );
            $output .= $this->get_table_row( __( 'Message', 'laterpay' ) , $record[ 'message' ] );
            $output .= $this->get_table_row( __( 'Level Name', 'laterpay' ) , $record[ 'level_name' ] );
            $output .= $this->get_table_row( __( 'Date', 'laterpay' ) , $record[ 'datetime' ]->format( 'd.m.Y H:i:s') );
            $output .= $this->get_table_row( __( 'Message', 'laterpay' ) , $record[ 'message' ] );

            if ( array_key_exists( 'context', $record ) ) {
                $inner = '<pre>' . print_r( $record[ 'context' ], true ) . '</pre>';
                $output .= $this->get_table_row( __( 'Context', 'laterpay' ), $inner, false);
            }
            $output .= '</table>';
        }

        return array(
            'name'      => __( 'Logger', 'laterpay' ),
            'content'   => $output
        );
    }

    /**
     *
     * @return array $tab
     */
     protected function get_request_tab(){
        $output = '';

        if( ! empty( $_REQUEST  ) ){
            $output .= '<table class="lp_debugger_detail_table">';
            foreach ( $_REQUEST as $key => $value ) {
                $output .= $this->get_table_row( $key, $value );
            }
            $output .= '</table>';
        }

        return array(
            'name'     => __( 'Request', 'laterpay' ),
            'content'  => $output
        );

    }

    /**
     *
     * @return array $tab
     */
    protected function get_server_tab(){
        $output = '';

        if ( ! empty( $_SERVER ) ) {
            $output .= '<table class="lp_debugger_detail_table">';
            foreach ( $_SERVER as $key => $value ) {
                $output .= $this->get_table_row( $key, $value );
            }
            $output .= '</table>';
        }

        return array(
            'name'     => __( 'Server', 'laterpay' ),
            'content'  => $output
        );
    }


    /**
     *
     * @return array $tab
     */
    protected function get_session_tab(){
        $output = '';

        if( ! empty( $_SESSION ) ){
            $output .= '<table class="lp_debugger_detail_table">';
            foreach ( $_SESSION as $key => $value ) {
                $output .= $this->get_table_row( $key, $value );
            }
            $output .= '</table>';
        }

        return array(
            'name'     => __( 'Session', 'laterpay' ),
            'content'  => $output
        );
    }

    /**
     *
     * @param string $th
     * @param string $td
     * @param bool $escape
     *
     * @return string $output
     */
    protected function get_table_row( $th, $td, $escape = true ){

        $th = htmlspecialchars( $th, ENT_NOQUOTES, 'UTF-8' );

        if( $escape ){
            $td = htmlspecialchars( $td, ENT_NOQUOTES, 'UTF-8');
        }

        $output = '';
        $output .= '<tr>';
        $output .= '<th>' . $th . '</th>';
        $output .= '<td>' . $td . '</td>';
        $output .= '</tr>';

        return $output;
    }

    /**
     * Registering our laterpay-scripts
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function register_scripts(){

        wp_register_style(
            'laterpay-debugger',
            $this->config->get( 'css_url' ) . 'laterpay-debugger.css',
            array(),
            false,
            false
        );

        if ( $this->config->get( 'debug_mode' ) ){
            wp_enqueue_style( 'laterpay-debugger' );
        }
    }

}
