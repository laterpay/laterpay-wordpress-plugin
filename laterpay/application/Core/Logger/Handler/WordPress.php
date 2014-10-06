<?php

class LaterPay_Core_Logger_Handler_WordPress extends LaterPay_Core_Logger_Handler_Abstract {

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
    public function __construct($level = LaterPay_Core_Logger::DEBUG) {
        parent::__construct($level, FALSE);

        $this->config = laterpay_get_plugin_config();

        add_action('wp_footer', array($this, 'render_records'), 99999);
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record) {
        if ($record['level'] < $this->level){
            return FALSE;
        }
        $this->records[] = $record;
        return TRUE;
    }

    /**
     * Callback to Render all Records to footer
     *
     * @wp-hook wp_footer
     * @return void
     */
    public function render_records() {
        ?>
        <section id="lp_debugger">
            <div class="lp_debugger_inner">
                <h1 data-icon="a" class="lp_debugger_headline"><?php _e(' Debugger', 'laterpay'); ?></h1>
                <?php
                $id = 'lp_debugger_tab';

                echo '<div id="'. $id . '_logger" class="lp_debugger_tab">';
                echo '<h2 class="lp_debugger_tab_headline"><a class="lp_debugger_tab_link" href="#' . $id . '_logger">' . __('Logger', 'laterpay') . '</a></h2>';
                echo '<div class="lp_debugger_tab_content">' . $this->get_formatter()->format_batch( $this->records ) . '</div>';
                echo '</div>';

                $tabs = $this->get_tabs();
                foreach( $tabs as $key => $tab ){
                    if ( empty( $tab[ 'content' ] ) ) {
                        continue;
                    }

                    echo '<div id="' . $id . '_' . $key .'" class="lp_debugger_tab">';
                    echo '<h2 class="lp_debugger_tab_headline"><a class="lp_debugger_tab_link" href="#' . $id . '_' . $key .'">' . $tab[ 'name' ] . '</a></h2>';

                    echo '<div class="lp_debugger_tab_content">';
                    echo '<table>';
                    foreach ( $tab[ 'content' ] as $k => $value) { ?>
                        <tr>
                            <th><?php echo $k; ?></th>
                            <td><pre><?php echo print_r( $value, true ); ?></pre></td>
                        </tr>
                    <?php }
                    echo '</table>';
                    echo '</div>';
                    echo '</div>';
                }

                ?>
            </div>
        </section>
        <?php
    }

    protected function get_tabs(){
        return array(
            array(
                'name'      => 'Request',
                'content'   => $_REQUEST,
            ),
            array(
                'name'      => 'Server',
                'content'   => $_SERVER,
            ),
            array(
                'name'      => 'Session',
                'content'   => isset($_SESSION) ? $_SESSION :  array(),
            ),
            array(
                'name'      => 'Cookies',
                'content'   => $_COOKIE,
            ),
        );
    }

    /**
     * Registering our laterpay-scripts
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function register_scripts() {

        wp_register_style(
            'laterpay-debugger', $this->config->get('css_url') . 'laterpay-debugger.css', array(), FALSE, FALSE
        );

        if ($this->config->get('debug_mode')){
            wp_enqueue_style('laterpay-debugger');
        }
    }

}
