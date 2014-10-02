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

        add_filter( 'wp_footer',            array( $this, 'render_records' ), 99999 );
        add_action( 'wp_enqueue_scripts',   array( $this, 'add_frontend_scripts' ) );
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

        $dir    = $this->config->get( 'log_dir' ) . date( 'Y/m/d/' );
        $file   = time() . '.php';
        wp_mkdir_p( $dir );

        // store the records to a log file for later use
        $content = 'return ' . var_export( $this->records, true ) . ';';
        file_put_contents( $dir . $file, $content );

        ?>
        <section id="lp_debugger">
            <h1 data-icon="a" class="lp_debugger_headline"><?php _e( ' Debugger', 'laterpay' ); ?></h1>
            <?php
            foreach( $this->records as $record ){
                echo '<div class="lp_debugger_row">';
                echo '<div class="lp_debugger_row_inner">';
                echo '<h2 class="lp_debugger_row_headline">' . $record[ 'message' ] . '</h1>';
                echo '<table class="lp_debugger_detail_table">';
                foreach( $record as $key => $value ){
                    $this->render_row( $key, $value );
                }
                echo '</table>';
                echo '</div>';
                echo '</div>';
            }
            ?>

        </section>
        <?php
    }

    /**
     *
     * @param   string $key
     * @param   mixed $value
     *
     * @return void
     */
    protected function render_row( $key, $value ){
        echo '<tr>';
        echo '<th>' . $key . '</th>';
        echo '<td>';
        switch ( $key ) {
            case 'datetime':
                echo $value->format( 'Y-m-d H:i:s' );
                break;
            case 'context':
                echo "<pre>";
                print_r( $value );
                echo "</pre>";
                break;
            default:
                echo $value;

        }
        echo '</td>';
        echo '</tr>';
    }

    public function add_frontend_scripts(){

        wp_register_style(
            'laterpay-debugger',
            $this->config->get( 'css_url' ) . 'laterpay-debugger.css',
            array(),
            false,
            false
        );

        if( $this->config->get( 'debug_mode' ) ){
            wp_enqueue_style( 'laterpay-debugger' );
        }

    }

}
