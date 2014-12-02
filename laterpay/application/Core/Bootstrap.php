<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay_Core_Bootstrap
{

    /**
     * Contains all settings for the plugin.
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
    }

    /**
     * Start the plugin on plugins_loaded hook.
     *
     * @return void
     */
    public function run() {
        // load the textdomain
        $textdomain_path = dirname( $this->config->plugin_base_name ) . $this->config->text_domain_path;
        load_plugin_textdomain(
            'laterpay',
            false,
            $textdomain_path
        );

        $install_controller = new LaterPay_Controller_Install( $this->config );

        // backend actions part 1
        if ( is_admin() ) {
            // perform requirements check on plugins.php page only
            if ( ! empty ( $GLOBALS[ 'pagenow' ] ) && $GLOBALS[ 'pagenow' ] === 'plugins.php' ) {
                $install_controller->check_requirements();
                add_action( 'admin_notices',                        array( $install_controller, 'render_requirements_notices' ) );
                add_action( 'admin_notices',                        array( $install_controller, 'check_for_updates' ) );
                add_action( 'admin_notices',                        array( $install_controller, 'maybe_update_meta_keys' ) );
                add_action( 'admin_notices',                        array( $install_controller, 'maybe_update_terms_price_table' ) );
                add_action( 'admin_notices',                        array( $install_controller, 'maybe_update_currency_to_euro' ) );
            }

            // add the plugin, if it is active and all checks are ok
            if ( is_plugin_active( $this->config->plugin_base_name ) ) {
                // add the admin panel
                $admin_controller = new LaterPay_Controller_Admin( $this->config );

                add_action( 'admin_head',                           array( $admin_controller, 'add_html5shiv_to_admin_head' ) );
                add_action( 'admin_menu',                           array( $admin_controller, 'add_to_admin_panel' ) );
                add_action( 'admin_print_footer_scripts',           array( $admin_controller, 'modify_footer' ) );
                add_action( 'load-post.php',                        array( $admin_controller, 'help_wp_edit_post' ) );
                add_action( 'load-post-new.php',                    array( $admin_controller, 'help_wp_add_post' ) );
                add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_plugin_admin_assets' ) );
                add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_admin_pointers_script' ) );

                $admin_pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
                add_action( 'wp_ajax_laterpay_pricing',                  array( $admin_pricing_controller, 'process_ajax_requests' ) );
                add_action( 'wp_ajax_laterpay_get_category_prices',      array( $admin_pricing_controller, 'process_ajax_requests' ) );

                $admin_appearance_controller = new LaterPay_Controller_Admin_Appearance( $this->config );
                add_action( 'wp_ajax_laterpay_appearance',          array( $admin_appearance_controller, 'process_ajax_requests' ) );

                $admin_account_controller = new LaterPay_Controller_Admin_Account( $this->config );
                add_action( 'wp_ajax_laterpay_account',             array( $admin_account_controller, 'process_ajax_requests' ) );

            }
        }

        // migrate multiple pricing postmeta from older plugin versions to an array
        add_filter( 'get_post_metadata', array( $install_controller, 'migrate_pricing_post_meta' ), 10, 4 );

        // add the shortcodes
        $shortcode_controller = new LaterPay_Controller_Shortcode( $this->config );
        add_shortcode( 'laterpay_premium_download',             array( $shortcode_controller, 'render_premium_download_box' ) );
        add_shortcode( 'laterpay_box_wrapper',                  array( $shortcode_controller, 'render_premium_download_box_wrapper' ) );
        // add shortcode 'laterpay' as alias for shortcode 'laterpay_premium_download':
        add_shortcode( 'laterpay',                              array( $shortcode_controller, 'render_premium_download_box' ) );

        // check if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        // backend actions part 2
        if ( is_admin() ) {
            // register callbacks for adding meta_boxes
            $post_metabox_controller = new LaterPay_Controller_Admin_Post_Metabox( $this->config );
            // add the metaboxes
            add_action( 'add_meta_boxes',                   array( $post_metabox_controller, 'add_meta_boxes' ) );

            // save LaterPay post data
            add_action( 'save_post',                        array( $post_metabox_controller, 'save_laterpay_post_data' ) );
            add_action( 'edit_attachment',                  array( $post_metabox_controller, 'save_laterpay_post_data' ) );
            add_action( 'transition_post_status',           array( $post_metabox_controller, 'update_post_publication_date' ), 10, 3 );

            // load scripts for the admin pages
            add_action( 'admin_print_styles-post.php',      array( $post_metabox_controller, 'load_assets' ) );
            add_action( 'admin_print_styles-post-new.php',  array( $post_metabox_controller, 'load_assets' ) );

            // Ajax hooks for edit post page
            add_action( 'wp_ajax_laterpay_reset_post_publication_date', array( $post_metabox_controller, 'reset_post_publication_date' ) );
            add_action( 'wp_ajax_laterpay_get_dynamic_pricing_data',    array( $post_metabox_controller, 'get_dynamic_pricing_data' ) );
            add_action( 'wp_ajax_laterpay_remove_post_dynamic_pricing', array( $post_metabox_controller, 'remove_dynamic_pricing_data' ) );

            // setup custom columns for each allowed post_type
            $column_controller = new LaterPay_Controller_Admin_Post_Column( $this->config );
            foreach ( $this->config->get( 'content.enabled_post_types' ) as $post_type ) {
                add_filter( 'manage_' . $post_type . '_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
                add_action( 'manage_' . $post_type . '_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );
            }
        }

        $dashboard_controller = new LaterPay_Controller_Admin_Dashboard( $this->config );
        add_action( 'laterpay_refresh_dashboard_data',          array( $dashboard_controller, 'refresh_dashboard_data' ), 10, 3 );
        add_action( 'wp_ajax_laterpay_get_dashboard_data',      array( $dashboard_controller, 'ajax_get_dashboard_data' ) );

        $post_controller = new LaterPay_Controller_Post( $this->config );
        // Ajax hooks for frontend
        add_action( 'wp_ajax_laterpay_post_load_purchased_content',          array( $post_controller, 'ajax_load_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_load_purchased_content',   array( $post_controller, 'ajax_load_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rate_purchased_content',          array( $post_controller, 'ajax_rate_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rate_purchased_content',   array( $post_controller, 'ajax_rate_purchased_content' ) );

        add_action( 'wp_ajax_laterpay_post_rating_summary',                  array( $post_controller, 'ajax_load_rating_summary' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_rating_summary',           array( $post_controller, 'ajax_load_rating_summary' ) );

        add_action( 'wp_ajax_laterpay_redeem_voucher_code',                  array( $post_controller, 'ajax_redeem_voucher_code' ) );
        add_action( 'wp_ajax_nopriv_laterpay_redeem_voucher_code',           array( $post_controller, 'ajax_redeem_voucher_code' ) );

        // Ajax hooks for post resources
        $file_helper = new LaterPay_Helper_File();
        add_action( 'wp_ajax_laterpay_load_files',              array( $file_helper, 'load_file' ) );
        add_action( 'wp_ajax_nopriv_laterpay_load_files',       array( $file_helper, 'load_file' ) );

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

        /**
         * ->   add filters to override post content
         * ->   we're using these filters in Ajax requests, so they have to stay outside the is_admin() check
         * ->   the priority has to be 1 (first filter triggered)
         *      to fetch and manipulate content first and before other filters are triggered (wp_embed, wpautop, external plugins / themes, ...)
         */
        add_filter( 'the_content',                                      array( $post_controller, 'modify_post_content' ), 1 );
        add_filter( 'wp_footer',                                        array( $post_controller, 'modify_footer' ) );

        $statistics_controller = new LaterPay_Controller_Statistics( $this->config );
        add_action( 'wp_ajax_laterpay_post_statistic_render',           array( $statistics_controller, 'ajax_render_tab' ) );
        add_action( 'wp_ajax_laterpay_post_statistic_visibility',       array( $statistics_controller, 'ajax_toggle_visibility' ) );
        add_action( 'wp_ajax_laterpay_post_statistic_toggle_preview',   array( $statistics_controller, 'ajax_toggle_preview' ) );
        add_action( 'wp_ajax_laterpay_post_track_views',                array( $statistics_controller, 'ajax_track_views' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_track_views',         array( $statistics_controller, 'ajax_track_views' ) );

        // frontend actions
        if ( ! is_admin() ) {

            $invoice_controller = new LaterPay_Controller_Invoice( $this->config );
            add_action( 'laterpay_invoice_indicator',   array( $invoice_controller, 'the_invoice_indicator' ) );
            add_action( 'wp_enqueue_scripts',           array( $invoice_controller, 'add_frontend_scripts' ) );

            add_action( 'template_redirect',            array( $post_controller, 'buy_post' ) );
            add_action( 'template_redirect',            array( $post_controller, 'buy_time_pass' ) );
            add_action( 'template_redirect',            array( $post_controller, 'create_token' ) );

            // add custom action to echo the LaterPay purchase button
            add_action( 'laterpay_purchase_button',     array( $post_controller, 'the_purchase_button' ) );

            // add custom action to echo the LaterPay time passes
            add_action( 'laterpay_time_passes',         array( $post_controller, 'the_time_passes_widget'), 10, 3 );

            // prefetch the post_access for loops
            add_filter( 'the_posts',                    array( $post_controller, 'prefetch_post_access' ) );

            // register the frontend scripts
            add_action( 'wp_enqueue_scripts',           array( $post_controller, 'add_frontend_stylesheets' ) );
            add_action( 'wp_enqueue_scripts',           array( $post_controller, 'add_frontend_scripts' ) );

            // setup unique visitors tracking
            add_action( 'template_redirect',            array( $statistics_controller, 'add_unique_visitors_tracking' ) );
            add_action( 'wp_footer',                    array( $statistics_controller, 'modify_footer' ) );

        }
    }

    /**
     * Install callback to create custom database tables.
     *
     * @wp-hook register_activation_hook
     *
     * @return void
     */
    public function activate() {
        $install_controller = new LaterPay_Controller_Install( $this->config );
        $install_controller->install();


        // registering the dashboard refresh cron-job
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

        // un-registering the dashboard cron-job
        wp_clear_scheduled_hook( 'laterpay_refresh_dashboard_data' );

    }

}
