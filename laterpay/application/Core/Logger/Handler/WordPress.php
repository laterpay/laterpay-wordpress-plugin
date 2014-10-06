<?php

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

        add_action( 'wp_footer',             array( $this, 'render_records' ), 1000 );
        add_action( 'admin_footer',          array( $this, 'render_records' ), 1000 );
        add_action( 'wp_enqueue_scripts',    array( $this, 'register_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
        add_action( 'admin_bar_menu',        array( &$this, 'admin_bar_menu' ), 1000 );
    }

    public function admin_bar_menu() {
        global $wp_admin_bar;

        $args = array(
            'id'        => 'lp_js_debugger-admin-bar-menu',
            'parent'    => 'top-secondary',
            'title'     => __( 'LaterPay Debugger', 'laterpay' )
        );

        $wp_admin_bar->add_menu( $args );
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

    /**
     * Callback to render all records to footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function render_records() {
        ?>
            <div class="lp_debugger lp_is_hidden">
                <header>
                    <a class="lp_js_close-debugger lp_close-link lp_fl-right lp_b-r3" data-icon="e"></a>
                    <h2 data-icon="a"><?php _e( 'Debugger', 'laterpay' ); ?></h2>
                </header>

                <ul class="lp_debugger-tabs lp_fl-clearfix">
                    <li class="lp_is_selected">
                        <a href="#"><?php _e( 'Messages', 'laterpay' ); ?></a>
                    </li>
                    <?php
                        foreach ( $this->get_tabs() as $key => $tab ) {
                            if ( empty( $tab[ 'content' ] ) ) {
                                continue;
                            }
                    ?>
                        <li>
                            <a href="#"><?php _e( $tab[ 'name' ], 'laterpay' ); ?></a>
                        </li>
                    <?php } ?>
                </ul>

                <ul class="lp_debugger-content-list">
                    <li class="lp_debugger-content">
                        <ul>
                            <?php echo $this->get_formatter()->format_batch( $this->records ); ?>
                        </ul>
                    </li>
                    <?php
                        foreach ( $this->get_tabs() as $key => $tab ) {
                            if ( empty( $tab[ 'content' ] ) ) {
                                continue;
                            }
                    ?>
                        <li class="lp_debugger-content lp_is_hidden">
                            <table>
                                <?php foreach ( $tab[ 'content' ] as $key => $value  ) { ?>
                                    <tr>
                                        <th><?php echo $key; ?></th>
                                        <td><?php print_r( $value ); ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php
    }

    /**
     * @return array $tabs
     */
    protected function get_tabs() {
        return array(
            array(
                'name'      => 'Get Data',
                'content'   => $_GET,
            ),
            array(
                'name'      => 'Post Data',
                'content'   => $_POST
            ),
            array(
                'name'      => 'Server',
                'content'   => $_SERVER,
            ),
            array(
                'name'      => 'Session',
                'content'   => isset( $_SESSION ) ? $_SESSION : array(),
            ),
            array(
                'name'      => 'Cookies',
                'content'   => $_COOKIE,
            ),
        );
    }

    /**
     * Load stylesheets for debug window
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function register_scripts() {
        wp_register_style(
            'laterpay-debugger',
            $this->config->get( 'css_url' ) . 'laterpay-debugger.css',
            array(),
            $this->config->version
        );

        wp_register_script(
            'laterpay-debugger',
            $this->config->get( 'js_url' ) . 'laterpay-debugger.js',
            array( 'jquery' ),
            $this->config->version
        );

        if ( $this->config->get( 'debug_mode' ) ) {
            wp_enqueue_style( 'laterpay-debugger' );
            wp_enqueue_script( 'laterpay-debugger' );
        }
    }

}
