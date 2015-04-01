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
    private $controllers = array();

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
     * @return bool|$controller instance of the given controller name
     */
    protected function get_controller( $name ) {
        $class = 'LaterPay_Controller_' . (string) $name;

        if ( ! class_exists( $class ) ) {
            $msg = __( '%s: <code>%s</code> not found', 'laterpay' );
            $msg = sprintf( $msg, __METHOD__, $class );
            laterpay_get_logger()->critical( $msg );

            return false;
        }

        if ( ! array_key_exists( $class, $this->controllers ) ) {
            $this->controllers[ $class ] = new $class( $this->config );
        }
        $controller = $this->controllers[ $class ];

        return $controller;
    }

    /**
     * Start the plugin on plugins_loaded hook.
     *
     * @wp-hook plugins_loaded
     *
     * @return void
     */
    public function run() {
        $this->register_custom_actions();
        $this->register_cache_helper();
        $this->register_ajax_actions();

        if ( is_admin() ) {
            $this->register_upgrade_checks();
            $this->register_admin_actions_step1();
        }

        // check, if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        // backend actions part 2
        if ( is_admin() ) {
            $this->register_admin_actions_step2();
        }

        $this->register_shortcodes();
        $this->register_global_actions();

        // late load event
        add_action( 'wp_loaded', array ( $this, 'late_load' ), 0 );
    }

    /**
     * Internal function to register global actions for frontend and backend.
     *
     * @return void
     */
    private function register_global_actions() {
        // migrate multiple pricing postmeta from older plugin versions to an array
        add_filter( 'get_post_metadata', array( $this->get_controller( 'Install' ), 'migrate_pricing_post_meta' ), 10, 4 );

        $post_controller = $this->get_controller( 'Post' );
        /**
         * ->   add filters to override post content
         * ->   we're using these filters in Ajax requests, so they have to stay outside the is_admin() check
         * ->   the priority has to be 1 (first filter triggered)
         *      to fetch and manipulate content first and before other filters are triggered (wp_embed, wpautop, external plugins / themes, ...)
         */
        add_filter( 'the_content',                  array( $post_controller, 'modify_post_content' ), 1 );
        add_filter( 'wp_footer',                    array( $post_controller, 'modify_footer' ) );

        add_action( 'template_redirect',            array( $post_controller, 'buy_post' ) );
        add_action( 'template_redirect',            array( $post_controller, 'buy_time_pass' ) );
        add_action( 'template_redirect',            array( $post_controller, 'create_token' ) );

        // prefetch the post_access for loops
        add_filter( 'the_posts',                    array( $post_controller, 'prefetch_post_access' ) );

        // enqueue the frontend assets
        add_action( 'wp_enqueue_scripts',           array( $post_controller, 'add_frontend_stylesheets' ) );
        add_action( 'wp_enqueue_scripts',           array( $post_controller, 'add_frontend_scripts' ) );

        // add custom action to render the LaterPay invoice indicator
        $invoice_controller = $this->get_controller( 'Invoice' );
        add_action( 'wp_enqueue_scripts',           array( $invoice_controller, 'add_frontend_scripts' ) );

        // add account links action
        $account_controller = $this->get_controller( 'Account' );
        add_action( 'wp_enqueue_scripts',           array( $account_controller, 'add_frontend_scripts' ) );

        // set up unique visitors tracking
        $statistics_controller = $this->get_controller( 'Statistic' );
        add_action( 'template_redirect',            array( $statistics_controller, 'add_unique_visitors_tracking' ) );
        add_action( 'wp_footer',                    array( $statistics_controller, 'modify_footer' ) );
    }

    /**
     * Internal function to register all shortcodes.
     *
     * @return void
     */
    private function register_shortcodes() {
        $shortcode_controller = $this->get_controller( 'Shortcode' );
        // add 'free to read' shortcodes
        add_shortcode( 'laterpay_premium_download', array( $shortcode_controller, 'render_premium_download_box' ) );
        add_shortcode( 'laterpay_box_wrapper',      array( $shortcode_controller, 'render_premium_download_box_wrapper' ) );
        // add shortcode 'laterpay' as alias for shortcode 'laterpay_premium_download':
        add_shortcode( 'laterpay',                  array( $shortcode_controller, 'render_premium_download_box' ) );

        // add time passes shortcode (as alternative to action 'laterpay_time_passes')
        add_shortcode( 'laterpay_time_passes',      array( $shortcode_controller, 'render_time_passes_widget' ) );

        // add gift cards shortcodes
        add_shortcode( 'laterpay_gift_card',        array( $shortcode_controller, 'render_gift_card' ) );
        add_shortcode( 'laterpay_redeem_voucher',   array( $shortcode_controller, 'render_redeem_gift_code' ) );

        // add account links shortcode
        add_shortcode( 'laterpay_account_links',    array( $shortcode_controller, 'render_account_links' ) );
    }

    /**
     * Internal function to register the admin actions step 1.
     *
     * @return void
     */
    private function register_admin_actions_step1() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        // add the plugin, if it is active and all checks are OK
        if ( is_plugin_active( $this->config->get( 'plugin_base_name' ) ) ) {
            // add the admin panel
            $admin_controller = $this->get_controller( 'Admin' );

            add_action( 'admin_head',                           array( $admin_controller, 'add_html5shiv_to_admin_head' ) );
            add_action( 'admin_menu',                           array( $admin_controller, 'add_to_admin_panel' ) );
            add_action( 'admin_print_footer_scripts',           array( $admin_controller, 'modify_footer' ) );
            add_action( 'load-post.php',                        array( $admin_controller, 'help_wp_edit_post' ) );
            add_action( 'load-post-new.php',                    array( $admin_controller, 'help_wp_add_post' ) );
            add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_plugin_admin_assets' ) );
            add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_admin_pointers_script' ) );
            add_action( 'delete_term_taxonomy',                 array( $admin_controller, 'update_post_prices_after_category_delete' ) );

            $settings_controller = $this->get_controller( 'Setting' );
            add_action( 'admin_menu',                           array( $settings_controller, 'add_laterpay_advanced_settings_page' ) );
            add_action( 'admin_init',                           array( $settings_controller, 'init_laterpay_advanced_settings' ) );
        }
    }

    /**
     * Internal function to register the admin actions step 2 after the 'plugin_is_working' check.
     *
     * @return void
     */
    private function register_admin_actions_step2() {
        // register callbacks for adding meta_boxes
        $post_metabox_controller = $this->get_controller( 'Admin_Post_Metabox' );
        // add the metaboxes
        add_action( 'add_meta_boxes',                   array( $post_metabox_controller, 'add_meta_boxes' ) );

        // save LaterPay post data. If only time pass purchases are allowed, then pricing information need not be saved.
        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) ) {
            add_action( 'save_post',                    array( $post_metabox_controller, 'save_laterpay_post_data_without_pricing' ) );
            add_action( 'edit_attachment',              array( $post_metabox_controller, 'save_laterpay_post_data_without_pricing' ) );
        } else {
            add_action( 'save_post',                    array( $post_metabox_controller, 'save_laterpay_post_data' ) );
            add_action( 'edit_attachment',              array( $post_metabox_controller, 'save_laterpay_post_data' ) );
        }

        add_action( 'transition_post_status',           array( $post_metabox_controller, 'update_post_publication_date' ), 10, 3 );

        // load scripts for the admin pages
        add_action( 'admin_print_styles-post.php',      array( $post_metabox_controller, 'load_assets' ) );
        add_action( 'admin_print_styles-post-new.php',  array( $post_metabox_controller, 'load_assets' ) );

        // setup custom columns for each allowed post_type, if allowed purchases aren't restricted to time passes
        if ( ! get_option( 'laterpay_only_time_pass_purchases_allowed' ) ) {
            $column_controller = $this->get_controller( 'Admin_Post_Column' );
            foreach ( $this->config->get( 'content.enabled_post_types' ) as $post_type ) {
                add_filter( 'manage_' . $post_type . '_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
                add_action( 'manage_' . $post_type . '_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );
            }
        }
    }

    /**
     * Internal function to register custom actions for LaterPay.
     *
     * @return void
     */
    private function register_custom_actions() {
        // custom action to refresh the dashboard
        $dashboard_controller = $this->get_controller( 'Admin_Dashboard' );
        add_action( 'laterpay_refresh_dashboard_data',  array( $dashboard_controller, 'refresh_dashboard_data' ), 10, 3 );

        $post_controller = $this->get_controller( 'Post' );
        // add custom action to echo the LaterPay purchase button
        add_action( 'laterpay_purchase_button',         array( $post_controller, 'the_purchase_button' ) );

        // add custom action to echo the LaterPay time passes
        add_action( 'laterpay_time_passes',             array( $post_controller, 'the_time_passes_widget'), 10, 4 );

        // add custom action to echo the LaterPay invoice indicator
        $invoice_controller = $this->get_controller( 'Invoice' );
        add_action( 'laterpay_invoice_indicator',       array( $invoice_controller, 'the_invoice_indicator' ) );

        // add account links action
        $account_controller = $this->get_controller( 'Account' );
        add_action( 'laterpay_account_links',           array( $account_controller, 'render_account_links' ), 10, 4 );
    }

    /**
     * Internal function to register the cache helper for {update_option_} hooks.
     *
     * @return void
     */
    private function register_cache_helper() {
        // cache helper to purge the cache on update_option()
        $cache_helper = new LaterPay_Helper_Cache();
        $options = array(
            'laterpay_global_price',
            'laterpay_global_price_revenue_model',
            'laterpay_currency',
            'laterpay_enabled_post_types',
            'laterpay_teaser_content_only',
            'laterpay_plugin_is_in_live_mode',
        );
        foreach ( $options as $option_name ) {
            add_action( 'update_option_' . $option_name, array( $cache_helper, 'purge_cache' ) );
        }
    }

    /**
     * Internal function to register all upgrade checks.
     *
     * @return void
     */
    private function register_upgrade_checks() {
        if ( empty ( $GLOBALS['pagenow'] ) || $GLOBALS['pagenow'] !== 'plugins.php' ) {
            return;
        }

        /**
         * @var LaterPay_Controller_Install
         */
        $install_controller = $this->get_controller( 'Install' );
        $install_controller->check_requirements();
        add_action( 'admin_notices', array( $install_controller, 'render_requirements_notices' ) );
        add_action( 'admin_notices', array( $install_controller, 'check_for_updates' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_meta_keys' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_terms_price_table' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_currency_to_euro' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_time_passes_table' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_payment_history_add_revenue_model' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_add_only_time_pass_purchase_option' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_api_urls_options_names' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_add_only_time_pass_purchase_option' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_add_is_in_visible_test_mode_option' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_clean_api_key_options' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_unlimited_access' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_update_post_views' ) );
        add_action( 'admin_notices', array( $install_controller, 'maybe_clear_dashboard_cache' ) );
    }

    /**
     * Internal function to register all Ajax requests.
     *
     * @return void
     */
    private function register_ajax_actions() {
        // plugin backend
        $controller = $this->get_controller( 'Admin_Pricing' );
        add_action( 'wp_ajax_laterpay_pricing',                             array( $controller, 'process_ajax_requests' ) );
        add_action( 'wp_ajax_laterpay_get_category_prices',                 array( $controller, 'process_ajax_requests' ) );

        $controller = $this->get_controller( 'Admin_Appearance' );
        add_action( 'wp_ajax_laterpay_appearance',                          array( $controller, 'process_ajax_requests' ) );

        $controller = $this->get_controller( 'Admin_Account' );
        add_action( 'wp_ajax_laterpay_account',                             array( $controller, 'process_ajax_requests' ) );

        $controller = $this->get_controller( 'Admin_Dashboard' );
        add_action( 'wp_ajax_laterpay_get_dashboard_data',                  array( $controller, 'ajax_get_dashboard_data' ) );

        // edit post
        $controller = $this->get_controller( 'Admin_Post_Metabox' );
        add_action( 'wp_ajax_laterpay_reset_post_publication_date',         array( $controller, 'reset_post_publication_date' ) );
        add_action( 'wp_ajax_laterpay_get_dynamic_pricing_data',            array( $controller, 'get_dynamic_pricing_data' ) );
        add_action( 'wp_ajax_laterpay_remove_post_dynamic_pricing',         array( $controller, 'remove_dynamic_pricing_data' ) );

        // view post
        $controller = $this->get_controller( 'Post' );
        add_action( 'wp_ajax_laterpay_post_load_purchased_content',         array( $controller, 'ajax_load_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_load_purchased_content',  array( $controller, 'ajax_load_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rate_purchased_content',         array( $controller, 'ajax_rate_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rate_purchased_content',  array( $controller, 'ajax_rate_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rating_summary',                 array( $controller, 'ajax_load_rating_summary' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rating_summary',          array( $controller, 'ajax_load_rating_summary' ) );

        add_action( 'wp_ajax_laterpay_redeem_voucher_code',                 array( $controller, 'ajax_redeem_voucher_code' ) );
        add_action( 'wp_ajax_nopriv_laterpay_redeem_voucher_code',          array( $controller, 'ajax_redeem_voucher_code' ) );

        // post statistics
        $controller = $this->get_controller( 'Statistic' );
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
        $controller = $this->get_controller( 'Admin_TimePass' );
        add_action( 'wp_ajax_laterpay_get_time_passes_data',                array( $controller, 'ajax_get_time_passes_data' ) );

        // gift cards
        $controller = $this->get_controller( 'Shortcode' );
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
        $install_controller = $this->get_controller( 'Install' );
        $install_controller->install();

        // register the 'refresh dashboard' cron job
        wp_schedule_event( time(), 'hourly', 'laterpay_refresh_dashboard_data' );
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
    }
}
