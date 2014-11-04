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
        add_action( 'wp_enqueue_scripts',    array( $this, 'load_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
        add_action( 'admin_bar_menu',        array( &$this, 'admin_bar_menu' ), 1000 );
    }

    public function admin_bar_menu() {
        global $wp_admin_bar;

        $args = array(
            'id'        => 'lp_js_toggleDebuggerVisibility',
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
     * Load CSS and JS for debug pane.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function load_assets() {
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

    /**
     * Callback to render all records to footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function render_records() {
        ?>
            <div class="lp_debugger lp_is-hidden">
                <header>
                    <a href="#" class="lp_js_closeDebugger lp_closeLink lp_u_right" data-icon="l"></a>
                    <div class="lp_u_right"><?php echo sprintf( __( '%s Memory Usage', 'laterpay' ), number_format( memory_get_peak_usage() / pow( 1024, 2 ), 1 ) . ' MB' ); ?></div>
                    <h2 data-icon="a"><?php _e( 'Debugger', 'laterpay' ); ?></h2>
                </header>

                <ul class="lp_debugger_tabs lp_u_clearfix">
                    <li class="lp_is-selected">
                        <a href="#"><?php echo sprintf( __( 'Messages<span>%s</span>', 'laterpay' ), count( $this->records ) ); ?></a>
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

                <ul class="lp_debugger_contentList">
                    <li class="lp_debugger_content">
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
                        <li class="lp_debugger_content lp_is-hidden">
                            <table>
                                <?php foreach ( $tab[ 'content' ] as $key => $value  ): ?>
                                    <tr>
                                        <th><?php echo $key; ?></th>
                                        <td><?php print_r( $value ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
                'name'      => __( 'Requests', 'laterpay' ),
                'content'   => array_merge( $_GET, $_POST ),
            ),
            array(
                'name'      => __( 'Session', 'laterpay' ),
                'content'   => isset( $_SESSION ) ? $_SESSION : array(),
            ),
            array(
                'name'      => sprintf( __( 'Cookies<span>%s</span>', 'laterpay' ), count( $_COOKIE ) ),
                'content'   => $_COOKIE,
            ),
            array(
                'name'      => __( 'System Config', 'laterpay' ),
                'content'   => $this->get_system_info(),
            ),
            array(
                'name'      => __( 'Plugin Config', 'laterpay' ),
                'content'   => $this->config->get_all(),
            ),
        );
    }

    public function get_system_info() {
        // get theme data
        $theme_data = wp_get_theme();
        $theme      = $theme_data->Name . ' ' . $theme_data->Version;

        // get active plugin data
        $installed_plugins  = get_plugins();
        $active_plugins     = get_option( 'active_plugins', array() );
        $plugins            = array();

        foreach ( $installed_plugins as $plugin_path => $plugin ) {
            if ( ! in_array( $plugin_path, $active_plugins ) ) {
                continue;
            }

            array_push( $plugins, $plugin['Name'] . ' ' . $plugin['Version'] );
        }

        // get active network plugin data
        if ( is_multisite() ) {
            $network_plugins        = wp_get_active_network_plugins();
            $active_network_plugins = get_site_option( 'active_sitewide_plugins', array() );

            foreach ( $plugins as $plugin_path ) {
                $plugin_base = plugin_basename( $plugin_path );
                if ( ! array_key_exists( $plugin_base, $active_network_plugins ) ) {
                    continue;
                }

                $network_plugin = get_plugin_data( $plugin_path );

                array_push( $network_plugins, $network_plugin['Name'] . ' ' . $network_plugin['Version'] );
            }
        }

        // collect system info
        $system_info = array(
            'WordPress version'         => get_bloginfo( 'version' ),
            'Multisite'                 => is_multisite() ? __( 'yes', 'laterpay' ) : __( 'no', 'laterpay' ),
            'WordPress memory limit'    => ( $this->let_to_num( WP_MEMORY_LIMIT ) / 1024 ) . ' MB',
            'Active plugins'            => implode( ', ', $plugins ),
            'Network active plugins'    => is_multisite() ? $network_plugins : __( 'none', 'laterpay' ),
            'Registered post types'     => implode( ', ', get_post_types( array( 'public' => true ) ) ),
            'Active theme'              => $theme,
            'PHP version'               => PHP_VERSION,
            'PHP memory limit'          => ini_get( 'memory_limit' ),
            'PHP modules'               => implode( ', ', get_loaded_extensions() ),
            'Web server info'           => $_SERVER['SERVER_SOFTWARE'],
        );

        return $system_info;
    }

    /**
     * Convert sizes.
     *
     * @param unknown $v
     *
     * @return int|string
     */
    static function let_to_num( $v ) {
        $l   = substr( $v, -1 );
        $ret = substr( $v, 0, -1 );

        switch ( strtoupper( $l ) ) {
            case 'P': // fall-through
            case 'T': // fall-through
            case 'G': // fall-through
            case 'M': // fall-through
            case 'K': // fall-through
                $ret *= 1024;
                break;
            default:
                break;
        }

        return $ret;
    }
}
