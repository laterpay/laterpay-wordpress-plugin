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

		// requirements-check
		// TODO: Should be an own controller
		add_action( 'admin_notices', array( $this, 'check_requirements' ) );

		// checking for plugin updates
		// TODO: move this to an own controller - see also method LaterPay_Core::check_requirements
		add_action( 'admin_notices', array( $this, 'check_for_updates' ) );

		// loading the textdomain
		$textdomain_path = dirname( plugin_basename( $this->config->plugin_file_path ) ) . $this->config->text_domain_path;
		load_plugin_textdomain(
			'laterpay',
			false,
			$textdomain_path
		);

		if( is_admin() ){

			// adding the admin panel
			$admin_controller = new LaterPay_Controller_Admin( $this->config );
			add_action( 'admin_menu',                   array( $admin_controller, 'add_to_admin_panel' ) );
			add_action( 'admin_print_footer_scripts',   array( $admin_controller, 'modify_footer' ) );

			// admin assets
			add_action( 'admin_enqueue_scripts', array( $this, 'add_plugin_admin_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_pointers_script' ) );

			// checking for upgrades
			$github_updater = new LaterPay_Core_Updater_GitHub();
			$github_updater->init(
				$this->config->plugin_file_path,
				LATERPAY_GITHUB_USER_NAME,
				LATERPAY_GITHUB_PROJECT_NAME,
				LATERPAY_GITHUB_TOKEN
			);
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
	        // TODO: Moving the callbacks to a controller
	        add_shortcode( 'laterpay_premium_download', array( $this, 'render_premium_download_box' ) );
	        add_shortcode( 'laterpay_box_wrapper',      array( $this, 'render_premium_download_box_wrapper' ) );

			// setup custom columns in post_table
	        $column_controller = new LaterPay_Controller_Post_Column( $this->config );
	        add_filter( 'manage_post_posts_columns',         array( $column_controller, 'add_columns_to_posts_table' ) );
	        add_action( 'manage_post_posts_custom_column',   array( $column_controller, 'add_data_to_posts_table' ), 10, 2 );

			// setup unique visitors tracking
	        // TODO: Moving the callback to a controller
	        add_action( 'init', array( $this, 'add_unique_visitors_tracking' ) );

			if( !is_admin() ) {

				// registering our frontend scripts
		        add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_stylesheets' ) );
		        add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
			}

        }

		add_action( 'plugin_action_links_' . $this->config->plugin_base_name, array( $this, 'add_plugin_settings_link' ) );
    }

	/**
	 * Callback to generate the user settings.
	 *
	 * @param   Array $settings
     *
	 * @return  Array
	 */
    private function _generate_user_settings( $settings ) {
        $config = str_replace(
            array(
                '{salt}',
                '{resource_encryption_key}',
                "'{SITE_USES_PAGE_CACHING}'",
            ),
            array(
                md5( uniqid( 'salt' ) ),
                md5( uniqid( 'key' ) ),
                LaterPay_Helper_Cache::site_uses_page_caching() ? 'true' : 'false',
            ),
            $settings
        );

        return $config;
    }

	/**
     * Write settings.php file based on configurations.
	 *
	 * @return void
	 */
	protected function create_configuration_file() {
        try {
            if ( ! file_exists( LATERPAY_GLOBAL_PATH . 'settings.php' ) ) {
                $config = file_get_contents( LATERPAY_GLOBAL_PATH . 'settings.sample.php' );
                $config = $this->_generate_user_settings( $config );
                file_put_contents( LATERPAY_GLOBAL_PATH . 'settings.php', $config );
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

	/**
     * Update an existing settings.php file based on configurations.
     *
	 * @return void
	 */
	protected function update_configuration_file() {
        if ( ! file_exists( LATERPAY_GLOBAL_PATH . 'settings.php' ) && ! file_exists( LATERPAY_GLOBAL_PATH . 'config.php' ) ) {
            $this->create_configuration_file();
            return;
        }

        try {
            $default_config = require( LATERPAY_GLOBAL_PATH . 'settings.sample.php' );
            $updated_config = array();

            // backwards compatibility: get configuration from old formated file
            if ( file_exists( LATERPAY_GLOBAL_PATH . 'config.php' )) {
                require_once( LATERPAY_GLOBAL_PATH . 'config.php' );
                $config = array();
                foreach ( $default_config as $option => $value ) {
                    if ( defined( $option ) ) {
                        $config[$option] = constant( $option );
                    }
                }
                @unlink( LATERPAY_GLOBAL_PATH . 'config.php' );
            } else {
                $config = require( LATERPAY_GLOBAL_PATH . 'settings.php' );
            }
            $changed = false;

            foreach ( $config as $option => $value ) {
                // use manually updated option instead of default
                if ( in_array( $option, $default_config ) && $default_config[$option] != $value ) {
                    $updated_config[$option] = $value;
                    $changed = true;
                }
            }

            if ( $changed ) {
                $config_file = file_get_contents( LATERPAY_GLOBAL_PATH . 'settings.sample.php' );

                foreach ( $updated_config as $option => $value ) {
                    if ( is_string( $value ) ) {
                        $value = "'$value'";
                    } elseif ( is_bool( $value ) ) {
                        $value = $value ? 'true' : 'false';
                    }
                    $config_file = preg_replace(
                                        '#(.*)' . $option . '(.*)(\s*=>\s*)(.*)(,?)#i',
                                        '${1}' . $option . '${2}${3}' . $value . ',',
                                        $config_file
                                    );
                }
                $config_file = $this->_generate_user_settings($config_file);
                file_put_contents( LATERPAY_GLOBAL_PATH . 'settings.php', $config_file );
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    /**
     * Install callback to create custom database tables.
     *
     * @wp-hook register_activiation_hook
     *
     * @return  void
     */
    public function activate() {
        global $wpdb;

        $this->update_configuration_file();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_currency     = $wpdb->prefix . 'laterpay_currency';
        $table_terms_price  = $wpdb->prefix . 'laterpay_terms_price';
        $table_history      = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views   = $wpdb->prefix . 'laterpay_post_views';

        $sql = "
            CREATE TABLE $table_currency (
                id            INT(10)         NOT NULL AUTO_INCREMENT,
                short_name    VARCHAR(3)      NOT NULL,
                full_name     VARCHAR(64)     NOT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE $table_terms_price (
                id                INT(11)         NOT NULL AUTO_INCREMENT,
                term_id           INT(11)         NOT NULL,
                price             DOUBLE          NOT NULL DEFAULT '0',
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE $table_history (
                id                INT(11)         NOT NULL AUTO_INCREMENT,
                mode              ENUM('test', 'live') NOT NULL DEFAULT 'test',
                post_id           INT(11)         NOT NULL,
                currency_id       INT(11)         NOT NULL,
                price             FLOAT           NOT NULL,
                date              DATETIME        NOT NULL,
                ip                INT             NOT NULL,
                hash              VARCHAR(32)     NOT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE $table_post_views (
                post_id           INT(11)         NOT NULL,
                date              DATETIME        NOT NULL,
                user_id           VARCHAR(32)     NOT NULL,
                count             BIGINT UNSIGNED NOT NULL DEFAULT 1,
                ip                VARBINARY(16)   NOT NULL,
                UNIQUE KEY  (post_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta( $sql );

        // seed currency table
        $wpdb->replace(
            $table_currency,
            array(
                'id'            => 1,
                'short_name'    => 'USD',
                'full_name'     => 'U.S. dollar',
            )
        );
        $wpdb->replace(
            $table_currency,
            array(
                'id'            => 2,
                'short_name'    => 'EUR',
                'full_name'     => 'Euro',
            )
        );

        add_option( 'laterpay_plugin_is_activated',      '' );
        add_option( 'laterpay_teaser_content_only',      '1' );
        add_option( 'laterpay_plugin_is_in_live_mode',   '0' );
        add_option( 'laterpay_sandbox_merchant_id',      '' );
        add_option( 'laterpay_sandbox_api_key',          '' );
        add_option( 'laterpay_live_merchant_id',         '' );
        add_option( 'laterpay_live_api_key',             '' );
        add_option( 'laterpay_global_price',             LATERPAY_GLOBAL_PRICE_DEFAULT );
        add_option( 'laterpay_currency',                 LATERPAY_CURRENCY_DEFAULT );
        update_option( 'laterpay_version', $this->config->version );

        // clear opcode cache
        LaterPay_Helper_Cache::reset_opcode_cache();

        // activate plugin
        $activated = get_option( 'laterpay_plugin_is_activated', '' );
        if ( $activated !== '' ) { // never activated before
            update_option( 'laterpay_plugin_is_activated', '1' );
        }
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
     * Render a teaser box for selling additional (downloadable) content from the shortcode [laterpay_premium_download].
     *
     * The shortcode [laterpay_premium_download] accepts various parameters:
     * - target_page_title (required): the title of the page that contains the paid content
     * - heading_text: the text that should be displayed as heading in the teaser box;
     *   restricted to one line
     * - description_text: text that provides additional information on the paid content;
     *   restricted to a maximum of three lines
     * - content_type: choose between 'text', 'music', 'video', 'gallery', or 'file',
     *   to display the corresponding default teaser image provided by the plugin;
     *   can be overridden with a custom teaser image using the teaser_image_path attribute
     * - teaser_image_path: path to an image that should be used instead of the default LaterPay teaser image
     *
     * Basic example:
     * [laterpay_premium_download target_page_title="Event video footage"]
     *
     * Advanced example:
     * [laterpay_premium_download target_page_title="Event video footage" heading_text="Video footage of concert"
     * description_text="Full HD video of the entire concept, including behind the scenes action."
     * teaser_image_path="/uploads/images/concert-video-still.jpg"]
     *
     * @param   array $atts
     *
     * @return  string $html
     */
    public function render_premium_download_box( $atts ) {
        $a = shortcode_atts(array(
               'target_page_title'  => '',
               'heading_text'       => __( 'Additional Premium Content', 'laterpay' ),
               'description_text'   => '',
               'content_type'       => '',
               'teaser_image_path'  => '',
             ), $atts);

        if ( $a['target_page_title'] == '' ) {
            die;
        } else {
            $target_page    = get_page_by_title( $a['target_page_title'], OBJECT, array( 'post', 'page', 'attachment' ) );
            $page_id        = $target_page->ID;
            $page_url       = get_permalink( $page_id );
            $price          = LaterPay_Helper_View::format_number( LaterPay_Controller_Post_Content::get_post_price( $page_id ), 2 );
            $currency       = get_option( 'laterpay_currency' );
            $price_tag      = sprintf( __( '%s<small>%s</small>', 'laterpay' ), $price, $currency );
        }

        $content_type = $a['content_type'];

        if ( $content_type == '' ) {
            // determine $content_type from MIME Type of files attached to post
            $page_mime_type = get_post_mime_type( $page_id );

            switch ( $page_mime_type ) {
                case 'application/zip':
                case 'application/x-rar-compressed':
                case 'application/pdf':
                    $content_type = 'file';
                    break;

                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $content_type = 'gallery';
                    break;

                case 'audio/vnd.wav':
                case 'audio/mpeg':
                case 'audio/mp4':
                case 'audio/ogg':
                case 'audio/aac':
                case 'audio/aacp':
                    $content_type = 'audio';
                    break;

                case 'video/mpeg':
                case 'video/mp4':
                case 'video/quicktime':
                    $content_type = 'video';
                    break;

                default:
                    $content_type = 'text';
            }
        }

        // build the HTML for the teaser box
        if ( $a['teaser_image_path'] != '' ) {
            $html = "<div class=\"laterpay-premium-file-link\" style=\"background-image:url({$a['teaser_image_path']})\">";
        } else {
            $html = "<div class=\"laterpay-premium-file-link {$content_type}\">";
        }
        $html .= "    <a href=\"{$page_url}\" class=\"laterpay-premium-file-button\" data-icon=\"b\">{$price_tag}</a>";
        $html .= '    <div class=\"details\">';
        $html .= "        <h3>{$a['heading_text']}</h3>";
        if ( $a['description_text'] != '' ) {
            $html .= "    <p>{$a['description_text']}</p>";
        }
        $html .= '    </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Align multiple teaser boxes in a row when enclosing them in shortcode [laterpay_box_wrapper].
     *
     * Important: Avoid line breaks between the shortcodes as WordPress will replace them with <br> tags
     *
     * Example:
     * [laterpay_box_wrapper][laterpay_premium_download target_page_title="Vocabulary list"][laterpay_premium_download target_page_title="Excercises"][/laterpay_box_wrapper]
     *
     * @param   array $atts
     * @param   string $content
     *
     * @return  string
     */
    function render_premium_download_box_wrapper( $atts, $content = null ) {
        return '<div class="laterpay-premium-file-link-wrapper">' . do_shortcode( $content ) . '</div>';
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
     * Track unique visitors.
     * TODO: remove this to an own controller
     * @return  void
     */
    public function add_unique_visitors_tracking() {
        if ( ! LATERPAY_ACCESS_LOGGING_ENABLED || is_admin() ) {
            return;
        }
        $url    = LaterPay_Helper_Statistics::get_full_url( $_SERVER );
        $postid = url_to_postid($url);
        LaterPay_Helper_Statistics::track($postid);
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
        $client         = new LaterPay_Core_Client();
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
     * Check plugin requirements.
     *
     * Deactivates plugin and renders admin notices if requirements are not fulfilled.
     *
     * @wp-hook admin_notices
     *
     * @return  void
     */
    public function check_requirements() {
        global $wp_version;

        $installed_php_version          = phpversion();
        $installed_wp_version           = $wp_version;
        $required_php_version           = '5.2.4';
        $required_wp_version            = '3.3';
        $installed_php_is_compatible    = version_compare( $installed_php_version, $required_php_version, '>=' );
        $installed_wp_is_compatible     = version_compare( $installed_wp_version, $required_wp_version, '>=' );

        $notices = array();
        $template = __( '<p>LaterPay: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'laterpay' );

        // check PHP compatibility
        if ( ! $installed_php_is_compatible ) {
            $notices[] = sprintf( $template, 'PHP', $required_php_version, 'PHP', $installed_php_version );
        }

        // check WordPress compatibility
        if ( ! $installed_wp_is_compatible ) {
            $notices[] = sprintf( $template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version );
        }

        // check file / folder permissions
        $template = __( '<p>LaterPay: Directory %s <strong>is not writable</strong>.</p>', 'laterpay' );
        $file = dirname( $this->config->plugin_file_path );
        if ( ! is_writable( $file ) ) {
            $notices[] = sprintf( $template, $file );
        }
        $file = dirname( $this->config->plugin_file_path ) . DIRECTORY_SEPARATOR . 'cache';
        if ( ! is_writable( $file ) ) {
            $notices[] = sprintf( $template, $file );
        }

        // deactivate plugin and render error messages if requirements are not fulfilled
        if ( count( $notices ) > 0 ) {
            deactivate_plugins( $this->config->plugin_base_name );

            $notices[] = __( 'The LaterPay plugin could not be installed. Please fix the reported issues and try again.', 'laterpay' );
            $out = join( "\n", $notices );
            echo '<div class="error">' . $out . '</div>';
        }
    }

    /**
     * Install settings and tables if update is required.
     *
     * @wp-hook plugins_loaded
     * @return  void
     */
    public function check_for_updates() {
	    // todo: using version_compare!
        if ( get_option('laterpay_version') != $this->config->version ) {
            $this->activate();
            $_capabilities = new LaterPay_Core_Capabilities();
            $_capabilities->populate_roles();
        }
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