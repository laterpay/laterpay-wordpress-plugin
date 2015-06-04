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
            laterpay_get_logger()->critical( $msg );

            return false;
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

        $this->register_cache_helper();
        $this->register_ajax_actions();

        $this->register_upgrade_checks();
        $this->register_admin_actions_step1();

        // check, if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        $this->register_custom_actions();
        $this->register_event_subscribers();

        // backend actions part 2
        if ( is_admin() ) {
            $this->register_admin_actions_step2();
        }

        $this->register_shortcodes();
        $this->register_global_actions();

        // late load event
        //add_action( 'wp_loaded', array( $this, 'late_load' ), 0 );
    }

    /**
     * Internal function to register global actions for frontend and backend.
     *
     * @return void
     */
    private function register_global_actions() {
        $post_controller = self::get_controller( 'Frontend_Post' );
        laterpay_event_dispatcher()->add_subscriber( $post_controller );
        add_filter( 'the_posts',                    'LaterPay_Helper_Post::hide_paid_posts', 1 );

        // prevent direct access to the attachments
        add_filter( 'wp_get_attachment_image_attributes', array( $post_controller, 'encrypt_image_source' ), 10, 3 );
        add_filter( 'wp_get_attachment_url',              array( $post_controller, 'encrypt_attachment_url' ), 10, 2 );
        add_filter( 'prepend_attachment',                 array( $post_controller, 'prepend_attachment' ) );

        // add custom action to render the LaterPay invoice indicator
        //$invoice_controller = self::get_controller( 'Frontend_Invoice' );
        //add_action( 'wp_enqueue_scripts',           array( $invoice_controller, 'add_frontend_scripts' ) );

        // add account links action
        //$account_controller = self::get_controller( 'Frontend_Account' );
        //add_action( 'wp_enqueue_scripts',           array( $account_controller, 'add_frontend_scripts' ) );

        // set up unique visitors tracking
        $statistics_controller = self::get_controller( 'Frontend_Statistic' );
        add_action( 'template_redirect',            array( $statistics_controller, 'add_unique_visitors_tracking' ) );
        add_action( 'wp_footer',                    array( $statistics_controller, 'modify_footer' ) );
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
     * Internal function to register the admin actions step 1.
     *
     * @return void
     */
    private function register_admin_actions_step1() {
        // add the admin panel
        $admin_controller = self::get_controller( 'Admin' );
        laterpay_event_dispatcher()->add_subscriber( $admin_controller );

        $settings_controller = self::get_controller( 'Admin_Settings' );
        laterpay_event_dispatcher()->add_subscriber( $settings_controller );
    }

    /**
     * Internal function to register the admin actions step 2 after the 'plugin_is_working' check.
     *
     * @return void
     */
    private function register_admin_actions_step2() {
        // register callbacks for adding meta_boxes
        $post_metabox_controller    = self::get_controller( 'Admin_Post_Metabox' );
        $column_controller          = self::get_controller( 'Admin_Post_Column' );
        laterpay_event_dispatcher()->add_subscriber( $post_metabox_controller );
        laterpay_event_dispatcher()->add_subscriber( $column_controller );
    }

    /**
     * Internal function to register custom actions for LaterPay.
     *
     * @return void
     */
    private function register_custom_actions() {
        // custom action to refresh the dashboard
        $dashboard_controller = self::get_controller( 'Admin_Dashboard' );
        laterpay_event_dispatcher()->add_subscriber( $dashboard_controller );

        // add custom filter to check if current user has access to the post content
        LaterPay_Hooks::add_wp_filter( 'laterpay_check_user_access', 'laterpay_check_user_access' );

        // add custom action to echo the LaterPay invoice indicator
        $invoice_controller = self::get_controller( 'Frontend_Invoice' );
        laterpay_event_dispatcher()->add_subscriber( $invoice_controller );
        // add account links action
        $account_controller = self::get_controller( 'Frontend_Account' );
        laterpay_event_dispatcher()->add_subscriber( $account_controller );
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
     * Internal function to register all Ajax requests.
     *
     * @return void
     */
    private function register_ajax_actions() {
        // plugin backend
        $controller = self::get_controller( 'Admin_Pricing' );
        add_action( 'wp_ajax_laterpay_pricing',                             array( $controller, 'process_ajax_requests' ) );
        add_action( 'wp_ajax_laterpay_get_category_prices',                 array( $controller, 'process_ajax_requests' ) );

        $controller = self::get_controller( 'Admin_Appearance' );
        add_action( 'wp_ajax_laterpay_appearance',                          array( $controller, 'process_ajax_requests' ) );

        $controller = self::get_controller( 'Admin_Account' );
        add_action( 'wp_ajax_laterpay_account',                             array( $controller, 'process_ajax_requests' ) );

        $controller = self::get_controller( 'Admin_Dashboard' );
        add_action( 'wp_ajax_laterpay_get_dashboard_data',                  array( $controller, 'ajax_get_dashboard_data' ) );

        // settings page
        $controller = self::get_controller( 'Admin_Settings' );
        add_action( 'wp_ajax_laterpay_backend_options',                     array( $controller, 'process_ajax_requests' ) );

        // edit post
        $controller = self::get_controller( 'Admin_Post_Metabox' );
        add_action( 'wp_ajax_laterpay_reset_post_publication_date',         array( $controller, 'reset_post_publication_date' ) );
        add_action( 'wp_ajax_laterpay_get_dynamic_pricing_data',            array( $controller, 'get_dynamic_pricing_data' ) );
        add_action( 'wp_ajax_laterpay_remove_post_dynamic_pricing',         array( $controller, 'remove_dynamic_pricing_data' ) );

        // view post
        $controller = self::get_controller( 'Frontend_Post' );
        add_action( 'wp_ajax_laterpay_post_load_purchased_content',         array( $controller, 'ajax_load_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_load_purchased_content',  array( $controller, 'ajax_load_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rate_purchased_content',         array( $controller, 'ajax_rate_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rate_purchased_content',  array( $controller, 'ajax_rate_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rating_summary',                 array( $controller, 'ajax_load_rating_summary' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rating_summary',          array( $controller, 'ajax_load_rating_summary' ) );

        add_action( 'wp_ajax_laterpay_redeem_voucher_code',                 array( $controller, 'ajax_redeem_voucher_code' ) );
        add_action( 'wp_ajax_nopriv_laterpay_redeem_voucher_code',          array( $controller, 'ajax_redeem_voucher_code' ) );

        // post statistics
        $controller = self::get_controller( 'Frontend_Statistic' );
        // post statistics are irrelevant, if only time pass purchases are allowed, but we still need to have the
        // option to switch the preview mode for the given post, so we only render that switch in this case
        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) === true ) {
            add_action( 'wp_ajax_laterpay_post_statistic_render',           array( $controller, 'ajax_render_tab_without_statistics' ) );
        } else {
            add_action( 'wp_ajax_laterpay_post_statistic_render',           array( $controller, 'ajax_render_tab' ) );
        }

        add_action( 'wp_ajax_laterpay_post_statistic_visibility',           array( $controller, 'ajax_toggle_visibility' ) );
        add_action( 'wp_ajax_laterpay_post_statistic_toggle_preview',       array( $controller, 'ajax_toggle_preview' ) );
        add_action( 'wp_ajax_laterpay_post_track_views',                    array( $controller, 'ajax_track_views' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_track_views',             array( $controller, 'ajax_track_views' ) );

        // protected files within posts
        $file_helper = new LaterPay_Helper_File();
        add_action( 'wp_ajax_laterpay_load_files',                          array( $file_helper, 'load_file' ) );
        add_action( 'wp_ajax_nopriv_laterpay_load_files',                   array( $file_helper, 'load_file' ) );

        // time passes
        $controller = self::get_controller( 'Admin_TimePass' );
        add_action( 'wp_ajax_laterpay_get_time_passes_data',                array( $controller, 'ajax_get_time_passes_data' ) );

        // gift cards
        $controller = self::get_controller( 'Frontend_Shortcode' );
        add_action( 'wp_ajax_laterpay_get_gift_card_actions',               array( $controller, 'ajax_load_gift_action' ) );
        add_action( 'wp_ajax_nopriv_laterpay_get_gift_card_actions',        array( $controller, 'ajax_load_gift_action' ) );

        // premium content links
        add_action( 'wp_ajax_laterpay_get_premium_shortcode_link',          array( $controller, 'ajax_get_premium_shortcode_link' ) );
        add_action( 'wp_ajax_nopriv_laterpay_get_premium_shortcode_link',   array( $controller, 'ajax_get_premium_shortcode_link' ) );
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
    private function register_event_subscribers() {
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Purchase() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Appearance() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_TimePasses() );
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
