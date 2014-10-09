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

                add_action('admin_head',                            array( $admin_controller, 'add_html5shiv_to_admin_head' ) );
                add_action( 'admin_menu',                           array( $admin_controller, 'add_to_admin_panel' ) );
                add_action( 'admin_print_footer_scripts',           array( $admin_controller, 'modify_footer' ) );
                add_action( 'load-post.php',                        array( $admin_controller, 'help_wp_edit_post' ) );
                add_action( 'load-post-new.php',                    array( $admin_controller, 'help_wp_add_post' ) );
                add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_plugin_admin_assets' ) );
                add_action( 'admin_enqueue_scripts',                array( $admin_controller, 'add_admin_pointers_script' ) );

                $admin_pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
                add_action( 'wp_ajax_laterpay_pricing',             array( $admin_pricing_controller, 'process_ajax_requests' ) );
                add_action( 'wp_ajax_laterpay_get_category_prices', array( $admin_pricing_controller, 'process_ajax_requests' ) );

                $admin_appearance_controller = new LaterPay_Controller_Admin_Appearance( $this->config );
                add_action( 'wp_ajax_laterpay_appearance',          array( $admin_appearance_controller, 'process_ajax_requests' ) );

                $admin_account_controller = new LaterPay_Controller_Admin_Account( $this->config );
                add_action( 'wp_ajax_laterpay_account',             array( $admin_account_controller, 'process_ajax_requests' ) );

            }
        }

        // migrate multiple pricing postmeta from older plugin versions to an array
        add_filter( 'get_post_metadata', array( $install_controller, 'migrate_pricing_post_meta' ), 10, 4 );

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

            // save the teaser
            add_action( 'save_post',                        array( $post_metabox_controller, 'save_teaser_content_box' ) );
            add_action( 'edit_attachment',                  array( $post_metabox_controller, 'save_teaser_content_box' ) );

            // save the pricing
            add_action( 'save_post',                        array( $post_metabox_controller, 'save_post_pricing_form') );
            add_action( 'edit_attachment',                  array( $post_metabox_controller, 'save_post_pricing_form') );

            // load scripts for the admin pages
            add_action( 'admin_print_styles-post.php',      array( $post_metabox_controller, 'load_assets' ) );
            add_action( 'admin_print_styles-post-new.php',  array( $post_metabox_controller, 'load_assets' ) );

            // setup custom columns for each allowed post_type
            $column_controller = new LaterPay_Controller_Admin_Post_Column( $this->config );
            foreach ( $this->config->get( 'content.enabled_post_types' ) as $post_type ) {
                add_filter( 'manage_' . $post_type . '_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
                add_action( 'manage_' . $post_type . '_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );
            }
        }

        // add the shortcodes
        $shortcode_controller = new LaterPay_Controller_Shortcode( $this->config );
        add_shortcode( 'laterpay_premium_download',             array( $shortcode_controller, 'render_premium_download_box' ) );
        add_shortcode( 'laterpay_box_wrapper',                  array( $shortcode_controller, 'render_premium_download_box_wrapper' ) );
        // add shortcode 'laterpay' as alias for shortcode 'laterpay_premium_download':
        add_shortcode( 'laterpay',                              array( $shortcode_controller, 'render_premium_download_box' ) );

        $post_controller = new LaterPay_Controller_Post( $this->config );
        // add Ajax hooks for frontend
        add_action( 'wp_ajax_laterpay_post_load_purchased_content',          array( $post_controller, 'ajax_load_purchased_content' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_load_purchased_content',   array( $post_controller, 'ajax_load_purchased_content' ) );

        // Ajax hooks for post resources
        $file_helper = new LaterPay_Helper_File();
        add_action( 'wp_ajax_laterpay_load_files',              array( $file_helper, 'load_file' ) );
        add_action( 'wp_ajax_nopriv_laterpay_load_files',       array( $file_helper, 'load_file' ) );

        // add filters to override post content
        // we're using the filters in Ajax requests, so they have to stay outside the is_admin()-check
        add_filter( 'the_content',                              array( $post_controller, 'modify_post_content' ) );
        add_filter( 'wp_footer',                                array( $post_controller, 'modify_footer' ) );

        $statistics_controller = new LaterPay_Controller_Statistics( $this->config );
        add_action( 'wp_ajax_laterpay_post_statistic_render',           array( $statistics_controller, 'ajax_render_tab' ) );
        add_action( 'wp_ajax_laterpay_post_statistic_visibility',       array( $statistics_controller, 'ajax_toggle_visibility' ) );
        add_action( 'wp_ajax_laterpay_post_statistic_toggle_preview',   array( $statistics_controller, 'ajax_toggle_preview' ) );
        add_action( 'wp_ajax_laterpay_post_track_views',                array( $statistics_controller, 'ajax_track_views' ) );
        add_action( 'wp_ajax_nopriv_laterpay_post_track_views',         array( $statistics_controller, 'ajax_track_views' ) );

        // frontend actions
        if ( ! is_admin() ) {

            $invoice_controller = new LaterPay_Controller_Invoice( $this->config );
            add_action( 'laterpay_invoice_indicator',           array( $invoice_controller, 'the_invoice_indicator' ) );
            add_action( 'wp_enqueue_scripts',                   array( $invoice_controller, 'add_frontend_scripts' ) );

            add_action( 'template_redirect',                    array( $post_controller, 'buy_post' ) );
            add_action( 'template_redirect',                    array( $post_controller, 'create_token' ) );

            // add custom action to echo the LaterPay purchase button
            add_action( 'laterpay_purchase_button',             array( $post_controller, 'the_purchase_button' ) );

            // prefetch the post_access for loops
            add_filter( 'the_posts',                            array( $post_controller, 'prefetch_post_access' ) );

            // register the frontend scripts
            add_action( 'wp_enqueue_scripts',       array( $post_controller, 'add_frontend_stylesheets' ) );
            add_action( 'wp_enqueue_scripts',       array( $post_controller, 'add_frontend_scripts' ) );

            // setup unique visitors tracking
            add_action( 'template_redirect',                    array( $statistics_controller, 'add_unique_visitors_tracking' ) );
            add_action( 'wp_footer',                            array( $statistics_controller, 'modify_footer' ) );

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
    }

    /**
     * Callback to deactivate the plugin.
     *
     * @wp-hook register_deactivation_hook
     *
     * @return void
     */
    public function deactivate() {
    }

}
