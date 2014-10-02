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
        $tabs            = array();
        $tabs['logger']  = $this->get_logger_tab();
        ?>
        <section id="lp_debugger">
            <div class="lp_debugger_inner">
                <h1 data-icon="a" class="lp_debugger_headline"><?php _e(' Debugger', 'laterpay'); ?></h1>
                <?php
                foreach ($tabs as $key => $tab) {
                    if (empty($tab['content'])){
                        continue;
                    }
                    $id = 'lp_debugger_tab_' . $key;
                    echo '<div id="' . $id . '" class="lp_debugger_tab">';
                    //echo '<h2 class="lp_debugger_tab_headline"><a class="lp_debugger_tab_link" href="#' . $id . '">' . $tab['name'] . '</a></h2>';
                    echo '<div class="lp_debugger_tab_content">' . $this->get_formatter()->format_batch( $tab[ 'content' ] ) . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
        <?php
    }

    /**
     *
     * @return array $tab
     */
    protected function get_logger_tab() {
        return array(
            'name' => __('Logger', 'laterpay'),
            'content' => $this->records
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
