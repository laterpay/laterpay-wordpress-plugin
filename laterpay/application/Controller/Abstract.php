<?php

/**
 * LaterPay abstract controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Abstract
{

    /**
     * Variables for substitution in templates.
     *
     * @var array
     */
    public $variables = array();

    /**
     * Contains all settings for the plugin.
     *
     * @var LaterPay_Model_Config
     */
    protected $config;

    /**
     * Contains the logger instance.
     *
     * @var LaterPay_Core_Logger
     */
    protected $logger;
    /**
     * @param LaterPay_Model_Config $config
     *
     * @return LaterPay_Controller_Abstract
     */
    public function __construct( LaterPay_Model_Config $config ) {
        $this->config = $config;
        $this->logger = laterpay_get_logger();

        // assign the config to the views
        $this->assign( 'config', $this->config );

        $this->initialize();

    }

    /**
     * Function which will be called on constructor and can be overwritten by child class.
     * @return void
     */
    protected function initialize() {}

    /**
     * Load all assets on boot-up.
     *
     * @return void
     */
    public function load_assets() {}

    /**
     * Render HTML file.
     *
     * @param string $file file to get HTML string
     *
     * @return void
     */
    public function render( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        $view_file = $this->config->get( 'view_dir' ) . $file . '.php';
        if ( ! file_exists( $view_file ) ) {
            $msg = sprintf(
                __( '%s : <code>%s</code> not found', 'laterpay' ),
                __METHOD__,
                __FILE__
            );

            $this->logger->error(
                __METHOD__ . ' - ' . $msg,
                array( 'view_file' => $view_file )
            );

            return;
        }

        $this->logger->info(
            __METHOD__ . ' - ' . $file,
            $this->variables
        );

        include_once( $view_file );
    }

    /**
     * Assign variable for substitution in templates.
     *
     * @param string $variable name variable to assign
     * @param mixed  $value    value variable for assign
     *
     * @return void
     */
    public function assign( $variable, $value ) {
        $this->variables[$variable] = $value;
    }

    /**
     * Get HTML from file.
     *
     * @param string $file file to get HTML string
     *
     * @return string $html html output as string
     */
    public function get_text_view( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        $view_file = $this->config->get( 'view_dir' ) . $file . '.php';
        if ( ! file_exists( $view_file ) ) {
            $msg = sprintf(
                __( '%s : <code>%s</code> not found', 'laterpay' ),
                __METHOD__,
                $file
            );

            $this->logger->error(
                __METHOD__ . ' - ' . $msg,
                array( 'view_file' => $view_file )
            );

            return '';
        }

        $this->logger->info(
            __METHOD__ . ' - ' . $file,
            $this->variables
        );

        ob_start();
        include( $view_file );
        $thread = ob_get_contents();
        ob_end_clean();
        $html = $thread;

        return $html;
    }

    /**
     * Render the navigation for the plugin backend.
     *
     * @param string $file
     *
     * @return string $html
     */
    public function get_menu( $file = null ) {
        if ( empty( $file ) ) {
            $file = 'backend/partials/navigation';
        }

        $current_page   = isset( $_GET['page'] ) ? $_GET['page'] : LaterPay_Helper_View::$pluginPage;
        $menu           = LaterPay_Helper_View::get_admin_menu();
        $plugin_page    = LaterPay_Helper_View::$pluginPage;

        $view_args      = array(
            'menu'         => $menu,
            'current_page' => $current_page,
            'plugin_page'  => $plugin_page,
        );

        $this->assign( 'laterpay', $view_args );

        $this->logger->info(
            __METHOD__ . ' - ' . $file,
            $view_args
        );

        return $this->get_text_view( $file );
    }
}
