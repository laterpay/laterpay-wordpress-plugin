<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay_Core_Bootstrap {

	/**
	 * Contains all settings for our plugin
	 * @var LaterPay_Model_Config
	 */
	private $config;

	/**
	 * @param   LaterPay_Model_Config $config
	 * @return  LaterPay_Core_Bootstrap
	 */
	public function __construct( LaterPay_Model_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Starting our plugin on plugins_loaded-Hook
	 * @return void
	 */
	public function run() {

		// loading the textdomain
		$textdomain_path = dirname( plugin_basename( $this->config->plugin_file_path ) ) . $this->config->text_domain_path;
		load_plugin_textdomain(
			'laterpay',
			false,
			$textdomain_path
		);

		// requirements-check
		$install_controller = new LaterPay_Controller_Install( $this->config );
		add_action( 'admin_notices', array( $install_controller, 'check_requirements' ) );
		add_action( 'admin_notices', array( $install_controller, 'check_for_updates' ) );

		// only in backend
		if( is_admin() ){

			// adding the admin panel
			$admin_controller = new LaterPay_Controller_Admin( $this->config );
			add_action( 'admin_menu',                   array( $admin_controller, 'add_to_admin_panel' ) );
			add_action( 'admin_print_footer_scripts',   array( $admin_controller, 'modify_footer' ) );

			// admin assets
			add_action( 'admin_enqueue_scripts', array( $this, 'add_plugin_admin_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_pointers_script' ) );

			// checking for upgrades
			$github_updater = new LaterPay_Controller_Admin_GitHubUpdater( $this->config );
			add_filter( 'pre_set_site_transient_update_plugins',    array( $github_updater, 'set_transient' ) );
			add_filter( 'plugins_api',                              array( $github_updater, 'set_plugin_info' ), 10, 3 );
			add_filter( 'upgrader_pre_install',                     array( $github_updater, 'pre_install' ), 10, 2 );
			add_filter( 'upgrader_post_install',                    array( $github_updater, 'post_install' ), 10, 3 );

		}

		// Add Ajax hooks for tabs in plugin backend.
		$admin_get_started_controller = new LaterPay_Controller_Admin_GetStarted( $this->config );
		add_action( 'wp_ajax_getstarted',    array( $admin_get_started_controller, 'process_ajax_requests' ) );

		$admin_pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
		add_action( 'wp_ajax_pricing',       array( $admin_pricing_controller, 'process_ajax_requests' ) );

		$admin_appearance_controller = new LaterPay_Controller_Admin_Appearance( $this->config );
		add_action( 'wp_ajax_appearance',    array( $admin_appearance_controller, 'process_ajax_requests' ) );

		$admin_account_controller = new LaterPay_Controller_Admin_Account( $this->config );
		add_action( 'wp_ajax_account',       array( $admin_account_controller, 'process_ajax_requests' ) );

		$admin_controller = new LaterPay_Controller_Admin( $this->config );
		add_action( 'wp_ajax_admin', array( $admin_controller, 'process_ajax_requests' ) );

		$admin_pricing_controller = new LaterPay_Controller_Post_Pricing( $this->config );
		add_action( 'wp_ajax_post_pricing',  array( $admin_pricing_controller, 'process_ajax_requests' ) );

		if ( LaterPay_Helper_View::plugin_is_working() ) {

			$post_controller = new LaterPay_Controller_Post_Content( $this->config );
			add_action( 'init',                     array( $post_controller, 'token_hook' ) );
			add_action( 'init',                     array( $post_controller, 'buy_post' ) );
			// Add filters to override post content.
			add_filter( 'the_title',                array( $post_controller, 'modify_post_title' ) );
			add_filter( 'the_content',              array( $post_controller, 'view' ) );
			add_filter( 'wp_footer',                array( $post_controller, 'modify_footer' ) );
			add_action( 'save_post',                array( $post_controller, 'init_teaser_content' ), 10, 2 );
			add_action( 'edit_form_after_editor',   array( $post_controller, 'init_teaser_content' ), 10, 2 );

			// Register callbacks for adding meta_boxes.
			$pricing_controller = new LaterPay_Controller_Post_Pricing( $this->config );
			add_action( 'save_post',    array( $pricing_controller, 'save_teaser_content_box' ) );
			add_action( 'admin_menu',   array( $pricing_controller, 'add_teaser_content_box' ) );

			add_action( 'save_post',    array( $pricing_controller, 'save_post_pricing_form') );
			add_action( 'admin_menu',   array( $pricing_controller, 'add_post_pricing_form') );

			// loading scripts for our admin pages
			add_action( 'admin_print_styles-post.php', array( $pricing_controller, 'load_assets' ) );
			add_action( 'admin_print_styles-post-new.php', array( $pricing_controller, 'load_assets' ) );

			// Ajax actions for pricing box
			add_action( 'wp_ajax_get_category_prices', array( $pricing_controller, 'get_category_prices' ) );

			// adding our shortcodes
			$shortcode_controller = new LaterPay_Controller_Shortcode( $this->config );
			add_shortcode( 'laterpay_premium_download', array( $shortcode_controller, 'render_premium_download_box' ) );
			add_shortcode( 'laterpay_box_wrapper',      array( $shortcode_controller, 'render_premium_download_box_wrapper' ) );

			// setup custom columns in post_table
			$column_controller = new LaterPay_Controller_Post_Column( $this->config );
			add_filter( 'manage_post_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
			add_action( 'manage_post_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );

			// setup unique visitors tracking
			// TODO: Moving the callback to a controller
			$tracking_controller = new LaterPay_Controller_Tracking( $this->config );
			add_action( 'init', array( $tracking_controller, 'add_unique_visitors_tracking' ) );

			if( !is_admin() ) {

				// registering our frontend scripts
				add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_stylesheets' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
			}

		}

		add_action( 'plugin_action_links_' . $this->config->plugin_base_name, array( $this, 'add_plugin_settings_link' ) );
	}

	/**
	 * Install callback to create custom database tables.
	 *
	 * @wp-hook register_activiation_hook
	 *
	 * @return  void
	 */
	public function activate() {
		$install_controller = new LaterPay_Controller_Install( $this->config );
		$install_controller->install();
	}

	/**
	 * Deactivate plugin.
	 *
	 * @wp-hook register_deactivation_hook
	 *
	 * @return  bool
	 */
	public function deactivate() {
		return update_option( 'laterpay_plugin_is_activated', '0' );
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
	 * @return  void
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
	 * @return  void
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
	 * @return  void
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
	 * @return  void
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
				'getArticleUrl' => plugins_url( 'laterpay/scripts/laterpay-article-script.php' ),
				'getFooterUrl'  => plugins_url( 'laterpay/scripts/laterpay-footer-script.php' ),
				'getTitleUrl'   => plugins_url( 'laterpay/scripts/laterpay-title-script.php' ),
				'i18nAlert'     => __( 'In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay' ),
			)
		);
	}

	/**
	 * Add settings link to plugins table.
	 *
	 * @wp-hook plugin_action_links_{plugin_basename}
	 *
	 * @param   array $links
	 *
	 * @return  array
	 */
	public function add_plugin_settings_link( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'plugin-editor.php?file=laterpay%2Fsettings.php' ) . '">' . __( 'Settings', 'laterpay' ) . '</a>'
			),
			$links
		);
	}

}