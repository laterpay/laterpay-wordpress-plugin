<?php

/**
 * LaterPay bootstrap class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Bootstrap
{

    /**
     * Contains all controller instances.
     * @var array
     */
    private static $controllers = array();

    /**
     * Contains all settings for the plugin.
     *
     * @var LaterPay_Model_Config
     */
    private $config;

    /**
     * @param LaterPay_Model_Config $config
     *
     * @return LaterPay_Core_Bootstrap
     */
    public function __construct( LaterPay_Model_Config $config ) {
        $this->config = $config;

        // load the textdomain for 'plugins_loaded', 'register_activation_hook', and 'register_deactivation_hook'
        $textdomain_dir     = dirname( $this->config->get( 'plugin_base_name' ) );
        $textdomain_path    = $textdomain_dir . $this->config->get( 'text_domain_path' );
        load_plugin_textdomain(
            'laterpay',
            false,
            $textdomain_path
        );
    }

    /**
     * Internal function to create and get controllers.
     *
     * @param string $name name of the controller without prefix.
     *
     * @return bool|LaterPay_Controller_Base $controller instance of the given controller name
     */
    public static function get_controller( $name ) {
        $class = 'LaterPay_Controller_' . (string) $name;

        if ( ! class_exists( $class ) ) {
            $msg = __( '%s: <code>%s</code> not found', 'laterpay' );
            $msg = sprintf( $msg, __METHOD__, $class );
            throw new LaterPay_Core_Exception( $msg );
        }

        if ( ! array_key_exists( $class, self::$controllers ) ) {
            self::$controllers[ $class ] = new $class( laterpay_get_plugin_config() );
        }

        return self::$controllers[ $class ];
    }

    /**
     * Start the plugin on plugins_loaded hook.
     *
     * @wp-hook plugins_loaded
     *
     * @return void
     */
    public function run() {
        $this->register_wordpress_hooks();
        $this->register_modules();

        $this->register_cache_helper();
        $this->register_upgrade_checks();

        $this->register_admin_actions();
        $this->register_frontend_actions();
        $this->register_shortcodes();

        // LaterPay loaded finished. Triggering event for other plugins
        LaterPay_Hooks::get_instance()->laterpay_ready();
        laterpay_event_dispatcher()->dispatch( 'laterpay_init_finished' );
    }

    /**
     * Internal function to register global actions for frontend and backend.
     *
     * @return void
     */
    private function register_frontend_actions() {
        $post_controller = self::get_controller( 'Frontend_Post' );
        laterpay_event_dispatcher()->add_subscriber( $post_controller );

        // set up unique visitors tracking
        $statistics_controller = self::get_controller( 'Frontend_Statistic' );
        laterpay_event_dispatcher()->add_subscriber( $statistics_controller );

        // add custom action to echo the LaterPay invoice indicator
        $invoice_controller = self::get_controller( 'Frontend_Invoice' );
        laterpay_event_dispatcher()->add_subscriber( $invoice_controller );
        // add account links action
        $account_controller = self::get_controller( 'Frontend_Account' );
        laterpay_event_dispatcher()->add_subscriber( $account_controller );
    }

    /**
     * Internal function to register all shortcodes.
     *
     * @return void
     */
    private function register_shortcodes() {
        $shortcode_controller = self::get_controller( 'Frontend_Shortcode' );
        // add 'free to read' shortcodes
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_premium_download', 'laterpay_shortcode_premium_download' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_box_wrapper', 'laterpay_shortcode_box_wrapper' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay', 'laterpay_shortcode_laterpay' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_time_passes', 'laterpay_shortcode_time_passes' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_gift_card', 'laterpay_shortcode_gift_card' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_redeem_voucher', 'laterpay_shortcode_redeem_voucher' );
        LaterPay_Hooks::add_wp_shortcode( 'laterpay_account_links', 'laterpay_shortcode_account_links' );

        laterpay_event_dispatcher()->add_subscriber( $shortcode_controller );
    }

    /**
     * Internal function to register the admin actions step 2 after the 'plugin_is_working' check.
     *
     * @return void
     */
    private function register_admin_actions() {
        // add the admin panel
        $admin_controller = self::get_controller( 'Admin' );
        laterpay_event_dispatcher()->add_subscriber( $admin_controller );

        $settings_controller = self::get_controller( 'Admin_Settings' );
        laterpay_event_dispatcher()->add_subscriber( $settings_controller );

        // plugin backend
        $controller = self::get_controller( 'Admin_Pricing' );
        laterpay_event_dispatcher()->add_subscriber( $controller );

        $controller = self::get_controller( 'Admin_Appearance' );
        laterpay_event_dispatcher()->add_subscriber( $controller );

        $controller = self::get_controller( 'Admin_Account' );
        laterpay_event_dispatcher()->add_subscriber( $controller );

        // time passes
        $controller = self::get_controller( 'Admin_TimePass' );
        laterpay_event_dispatcher()->add_subscriber( $controller );

        // custom action to refresh the dashboard
        $dashboard_controller = self::get_controller( 'Admin_Dashboard' );
        laterpay_event_dispatcher()->add_subscriber( $dashboard_controller );

        // register callbacks for adding meta_boxes
        $post_metabox_controller    = self::get_controller( 'Admin_Post_Metabox' );
        laterpay_event_dispatcher()->add_subscriber( $post_metabox_controller );

        $column_controller          = self::get_controller( 'Admin_Post_Column' );
        laterpay_event_dispatcher()->add_subscriber( $column_controller );
    }

    /**
     * Internal function to register the cache helper for {update_option_} hooks.
     *
     * @return void
     */
    private function register_cache_helper() {
        // cache helper to purge the cache on update_option()
        $cache_helper = new LaterPay_Helper_Cache();

        laterpay_event_dispatcher()->add_listener( 'laterpay_option_update', array( $cache_helper, 'purge_cache' ) );
    }

    /**
     * Internal function to register all upgrade checks.
     *
     * @return void
     */
    private function register_upgrade_checks() {
        laterpay_event_dispatcher()->add_subscriber( self::get_controller( 'Install' ) );
    }

    /**
     * Late load event for other plugins to remove / add own actions to the LaterPay plugin.
     *
     * @return void
     */
    public function late_load() {
        /**
         * Late loading event for LaterPay.
         *
         * @param LaterPay_Core_Bootstrap $this
         */
        do_action( 'laterpay_and_wp_loaded', $this );
    }

    /**
     * Install callback to create custom database tables.
     *
     * @wp-hook register_activation_hook
     *
     * @return void
     */
    public function activate() {
        $install_controller = self::get_controller( 'Install' );
        $install_controller->install();

        // register the 'refresh dashboard' cron job
        wp_schedule_event( time(), 'hourly', 'laterpay_refresh_dashboard_data' );
        // register the 'delete old post views' cron job
        wp_schedule_event( time(), 'daily', 'laterpay_delete_old_post_views', array( '3 month' ) );
    }

    /**
     * Callback to deactivate the plugin.
     *
     * @wp-hook register_deactivation_hook
     *
     * @return void
     */
    public function deactivate() {
        // de-register the 'refresh dashboard' cron job
        wp_clear_scheduled_hook( 'laterpay_refresh_dashboard_data' );
        // de-register the 'delete old post views' cron job
        wp_clear_scheduled_hook( 'laterpay_delete_old_post_views', array( '3 month' ) );
    }

    /**
     * Internal function to register event subscribers.
     *
     * @return void
     */
    private function register_modules() {
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Appearance() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Purchase() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_TimePasses() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Rates() );
    }

    /**
     * Internal function to register event subscribers.
     *
     * @return void
     */
    private function register_wordpress_hooks() {
        LaterPay_Hooks::get_instance()->init();
    }
}
