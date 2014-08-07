<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay_Core_Bootstrap
{

    /**
     * Contains all settings for the plugin
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
        $textdomain_path = dirname( plugin_basename( $this->config->plugin_file_path ) ) . $this->config->text_domain_path;
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
                add_action( 'admin_notices', array( $install_controller, 'render_requirements_notices' ) );
                add_action( 'admin_notices', array( $install_controller, 'check_for_updates' ) );
                add_action( 'admin_notices', array( $install_controller, 'maybe_update_meta_keys' ) );
            }

            // add the plugin, if it is active and all checks are ok
            if ( is_plugin_active( $this->config->plugin_base_name ) ) {
                // add the admin panel
                $admin_controller = new LaterPay_Controller_Admin( $this->config );
                add_action( 'admin_menu',                   array( $admin_controller, 'add_to_admin_panel' ) );
                add_action( 'admin_print_footer_scripts',   array( $admin_controller, 'modify_footer' ) );
                add_action( 'load-post.php',                array( $admin_controller, 'help_wp_edit_post' ) );
                add_action( 'load-post-new.php',            array( $admin_controller, 'help_wp_add_post' ) );

                // load the admin assets
                add_action( 'admin_enqueue_scripts', array( $this, 'add_plugin_admin_assets' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_pointers_script' ) );

                // check for upgrades
                $github_updater = new LaterPay_Controller_Admin_GitHubUpdater( $this->config );
                add_filter( 'pre_set_site_transient_update_plugins',    array( $github_updater, 'set_transient' ) );
                add_filter( 'plugins_api',                              array( $github_updater, 'set_plugin_info' ), 10, 3 );
                add_filter( 'upgrader_pre_install',                     array( $github_updater, 'pre_install' ), 10, 2 );
                add_filter( 'upgrader_post_install',                    array( $github_updater, 'post_install' ), 10, 3 );

                // add Ajax hooks for tabs in plugin backend
                $admin_get_started_controller = new LaterPay_Controller_Admin_GetStarted( $this->config );
                add_action( 'wp_ajax_laterpay_getstarted', array( $admin_get_started_controller, 'process_ajax_requests' ) );

                $admin_pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
                add_action( 'wp_ajax_laterpay_pricing',             array( $admin_pricing_controller, 'process_ajax_requests' ) );
                add_action( 'wp_ajax_laterpay_post_pricing',        array( $admin_pricing_controller, 'process_category_ajax_requests' ) );
                add_action( 'wp_ajax_laterpay_get_category_prices', array( $admin_pricing_controller, 'get_category_prices' ) );

                $admin_appearance_controller = new LaterPay_Controller_Admin_Appearance( $this->config );
                add_action( 'wp_ajax_laterpay_appearance', array( $admin_appearance_controller, 'process_ajax_requests' ) );

                $admin_account_controller = new LaterPay_Controller_Admin_Account( $this->config );
                add_action( 'wp_ajax_laterpay_account', array( $admin_account_controller, 'process_ajax_requests' ) );

                $admin_controller = new LaterPay_Controller_Admin( $this->config );
                add_action( 'wp_ajax_laterpay_admin', array( $admin_controller, 'process_ajax_requests' ) );
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
            add_action( 'add_meta_boxes',   array( $post_metabox_controller, 'add_meta_boxes' ) );

            // saving the teaser
            add_action( 'save_post',        array( $post_metabox_controller, 'save_teaser_content_box' ) );
            // saving the pricing
            add_action( 'save_post',        array( $post_metabox_controller, 'save_post_pricing_form') );

            // load scripts for the admin pages
            add_action( 'admin_print_styles-post.php',      array( $post_metabox_controller, 'load_assets' ) );
            add_action( 'admin_print_styles-post-new.php',  array( $post_metabox_controller, 'load_assets' ) );

            // setup custom columns for each allowed post_type
            $column_controller = new LaterPay_Controller_Admin_Post_Column( $this->config );
            foreach( $this->config->get( 'content.allowed_post_types' ) as $post_type ){
                add_filter( 'manage_' . $post_type . '_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
                add_action( 'manage_' . $post_type . '_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );
            }

        }

        // add the shortcodes
        $shortcode_controller = new LaterPay_Controller_Shortcode( $this->config );
        add_shortcode( 'laterpay_premium_download', array( $shortcode_controller, 'render_premium_download_box' ) );
        add_shortcode( 'laterpay_box_wrapper',      array( $shortcode_controller, 'render_premium_download_box_wrapper' ) );
        // add shortcode 'laterpay' as alias for shortcode 'laterpay_premium_download':
        add_shortcode( 'laterpay',                  array( $shortcode_controller, 'render_premium_download_box' ) );

        $post_controller = new LaterPay_Controller_Post_Content( $this->config );
        // add Ajax hooks for frontend
        add_action( 'wp_ajax_laterpay_article_script',          array( $post_controller, 'get_cached_post' ) );
        add_action( 'wp_ajax_nopriv_laterpay_article_script',   array( $post_controller, 'get_cached_post' ) );
        add_action( 'wp_ajax_laterpay_footer_script',           array( $post_controller, 'get_modified_footer' ) );
        add_action( 'wp_ajax_nopriv_laterpay_footer_script',    array( $post_controller, 'get_modified_footer' ) );
        
        // ajax hooks for post resources
        $file_helper = new LaterPay_Helper_File();
        add_action( 'wp_ajax_laterpay_load_files',              array( $file_helper, 'load_file' ) );
        add_action( 'wp_ajax_nopriv_laterpay_load_files',       array( $file_helper, 'load_file' ) );

        // frontend actions
//        if ( ! is_admin() ) 
        {
            add_action( 'init',                     array( $post_controller, 'create_token' ) );
            add_action( 'init',                     array( $post_controller, 'buy_post' ) );

            // add filters to override post content
            add_filter( 'the_content',              array( $post_controller, 'modify_post_content' ) );
            add_filter( 'wp_footer',                array( $post_controller, 'modify_footer' ) );

            // setup unique visitors tracking
            $tracking_controller = new LaterPay_Controller_Tracking( $this->config );
            add_action( 'init',         array( $tracking_controller, 'add_unique_visitors_tracking' ) );

            // register the frontend scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_stylesheets' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
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
     * Callback to deactivate plugin.
     * Sets option 'laterpay_plugin_is_activated' to false, if the installation was successfully activated at that time.
     *
     * @wp-hook register_deactivation_hook
     *
     * @return void
     */
    public function deactivate() {
        $activated = get_option( 'laterpay_plugin_is_activated', '' );
        if ( $activated == '1' ) {
            update_option( 'laterpay_plugin_is_activated', '0' );
        }
    }

    /**
     * Register custom menu in admin panel.
     *
     * @return void
     */
    protected function setup_admin_panel() {
        add_action( 'admin_menu', array( $this, 'add_to_admin_panel' ) );
    }

    /**
     * Load LaterPay stylesheet with LaterPay vector icon on all pages where the admin menu is visible.
     *
     * @return void
     */
    public function add_plugin_admin_assets( ) {
        wp_register_style(
            'laterpay-admin',
            $this->config->css_url . 'laterpay-admin.css',
            array(),
            $this->config->version
        );
        wp_enqueue_style( 'laterpay-admin' );

        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );
    }

    /**
     * Hint at the newly installed plugin using WordPress pointers.
     *
     * @return void
     */
    public function add_admin_pointers_script() {
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }

    /**
     * Load LaterPay stylesheets on all post pages.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_stylesheets() {
        wp_register_style(
            'laterpay-post-view',
            $this->config->css_url . 'laterpay-post-view.css',
            array(),
            $this->config->version
        );
        wp_register_style(
            'laterpay-dialogs',
            'https://static.sandbox.laterpaytest.net/webshell_static/client/1.0.0/laterpay-dialog/css/dialog.css'
        );
        wp_enqueue_style( 'laterpay-post-view' );
        wp_enqueue_style( 'laterpay-dialogs' );
    }

    /**
     * Load LaterPay JS libraries on all post pages.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_scripts() {
        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );
        wp_register_script(
            'laterpay-yui',
            'https://static.laterpay.net/yui/3.13.0/build/yui/yui-min.js',
            array(),
            $this->config->version,
            false
        );
        wp_register_script(
            'laterpay-config',
            'https://static.laterpay.net/client/1.0.0/config.js',
            array( 'laterpay-yui' ),
            $this->config->version,
            false
        );
        wp_register_script(
            'laterpay-peity',
            $this->config->js_url . '/vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->version,
            false
        );
        wp_register_script(
            'laterpay-post-view',
            $this->config->js_url . '/laterpay-post-view.js',
            array( 'jquery', 'laterpay-peity' ),
            $this->config->version,
            false
        );
        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-config' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-post-view' );

        // pass localized strings and variables to script
        $client         = new LaterPay_Core_Client( $this->config );
        $balance_url    = $client->get_controls_balance_url();
        wp_localize_script(
            'laterpay-post-view',
            'lpVars',
            array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'lpBalanceUrl'  => $balance_url,
                'i18nAlert'     => __( 'In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay' ),
                'i18nOutsideAllowedPriceRange' => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' )
            )
        );
    }

}
