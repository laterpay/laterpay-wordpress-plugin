<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay
{

    /**
     * @var LaterPayPostPricingController
     */
    private $_laterPayPostPricingController;
    /**
     * @var LaterPayAdminController
     */
    private $_laterPayAdminController;
    /**
     * @var LaterPayPostContentController
     */
    private $_laterPayPostContentController;
    /**
     * @var LaterPayGitHubPluginUpdater
     */
    private $_gitHubPluginUpdater;

    private $_pluginFile;

    public function __construct( $file ) {
        $this->_pluginFile = $file;
    }

    protected function get_laterpay_post_pricing_controller() {
        if ( empty($this->_laterPayPostPricingController) ) {
            $this->_laterPayPostPricingController = new LaterPayPostPricingController();
        }

        return $this->_laterPayPostPricingController;
    }

    protected function get_laterpay_admin_controller() {
        if ( empty($this->_laterPayAdminController) ) {
            $this->_laterPayAdminController = new LaterPayAdminController();
        }

        return $this->_laterPayAdminController;
    }

    protected function get_laterpay_post_content_controller() {
        if ( empty($this->_laterPayPostContentController) ) {
            $this->_laterPayPostContentController = new LaterPayPostContentController();
        }

        return $this->_laterPayPostContentController;
    }

    protected function get_github_plugin_updater() {
        if ( empty($this->_gitHubPluginUpdater) ) {
            $this->_gitHubPluginUpdater = new LaterPayGitHubPluginUpdater();
            $this->_gitHubPluginUpdater->init(
                $this->_pluginFile,
                LATERPAY_GITHUB_USER_NAME,
                LATERPAY_GITHUB_PROJECT_NAME,
                LATERPAY_GITHUB_TOKEN
            );
        }

        return $this->_gitHubPluginUpdater;
    }

    public function run() {
        $this->setup_requirements_checking();

        $this->setup_plugin_translations();
        $this->setup_admin_panel();
        $this->setup_admin_routes();

        $this->setup_plugin_admin_assets();
        $this->setup_admin_pointers_script();

        if ( LaterPayViewHelper::plugin_is_working() ) {
            $this->setup_purchasing();
            $this->setup_teaser_content_box();
            $this->setup_post_pricing_form();
            $this->setup_shortcodes();

            $this->setup_custom_data_in_posts_table();

            $this->setup_unique_visitors_tracking();
            $this->setup_post_content_filters();
            $this->setup_plugin_frontend_assets();
        }

        $this->setup_registration();
        $this->setup_checking_for_updates();

        if ( is_admin() ) {
            $this->setup_checking_for_upgrades();
        }

        $this->setup_plugin_settings_link();
    }

    /**
     *
     * @param string $settings
     */
    private function _generate_user_settings( $settings ) {
        $config = str_replace(
            array(
                '{salt}',
                '{resource_encryption_key}',
                "'{SITE_USES_PAGE_CACHING}'"
            ),
            array(
                md5(uniqid('salt')),
                md5(uniqid('key')),
                LaterPayCacheHelper::site_uses_page_caching() ? 'true' : 'false'
            ),
            $settings
        );

        return $config;
    }

    protected function create_configuration_file() {
        try {
            if ( ! file_exists(LATERPAY_GLOBAL_PATH . 'settings.php') ) {
                $config = file_get_contents(LATERPAY_GLOBAL_PATH . 'settings.sample.php');
                $config = $this->_generate_user_settings($config);
                file_put_contents(LATERPAY_GLOBAL_PATH . 'settings.php', $config);
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    protected function update_configuration_file() {
        if ( ! file_exists(LATERPAY_GLOBAL_PATH . 'settings.php') && ! file_exists(LATERPAY_GLOBAL_PATH . 'config.php') ) {
            $this->create_configuration_file();
            return;
        }

        try {
            $default_config = require(LATERPAY_GLOBAL_PATH . 'settings.sample.php');
            $updated_config = array();

            // backwards compatibility: get configuration from old formated file
            if ( file_exists(LATERPAY_GLOBAL_PATH . 'config.php')) {
                require_once(LATERPAY_GLOBAL_PATH . 'config.php');
                $config = array();
                foreach ( $default_config as $option => $value ) {
                    if ( defined($option) ) {
                        $config[$option] = constant($option);
                    }
                }
                @unlink( LATERPAY_GLOBAL_PATH . 'config.php' );
            } else {
                $config = require(LATERPAY_GLOBAL_PATH . 'settings.php');
            }
            $changed = false;

            foreach ( $config as $option => $value ) {
                // use manually updated option instead of default
                if ( in_array($option, $default_config) && $default_config[$option] ! = $value ) {
                    $updated_config[$option] = $value;
                    $changed = true;
                }
            }

            if ( $changed ) {
                $config_file = file_get_contents(LATERPAY_GLOBAL_PATH . 'settings.sample.php');

                foreach ( $updated_config as $option => $value ) {
                    if ( is_string($value) ) {
                        $value = "'$value'";
                    } elseif ( is_bool($value) ) {
                        $value = $value ? 'true' : 'false';
                    }
                    $config_file = preg_replace(
                                        "#(.*)" . $option . "(.*)(\s*=>\s*)(.*)(,?)#i",
                                        '${1}' . $option . '${2}${3}' . $value . ',',
                                        $config_file
                                    );
                }
                $config_file = $this->_generate_user_settings($config_file);
                file_put_contents(LATERPAY_GLOBAL_PATH . 'settings.php', $config_file);
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    /**
     * Activate plugin
     *
     * @global object $wpdb
     * @global string $laterpay_version
     */
    public function activate() {
        global $wpdb,
        $laterpay_version;

        $this->update_configuration_file();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

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
        dbDelta($sql);

        $sql = "
            CREATE TABLE $table_terms_price (
                id                INT(11)         NOT NULL AUTO_INCREMENT,
                term_id           INT(11)         NOT NULL,
                price             DOUBLE          NOT NULL DEFAULT '0',
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);

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
        dbDelta($sql);

        $sql = "
            CREATE TABLE $table_post_views (
                post_id           INT(11)         NOT NULL,
                date              DATETIME        NOT NULL,
                user_id           VARCHAR(32)     NOT NULL,
                count             BIGINT UNSIGNED NOT NULL DEFAULT 1,
                ip                VARBINARY(16)   NOT NULL,
                UNIQUE KEY  (post_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta($sql);

        // seed currency table
        $wpdb->replace(
            $table_currency,
            array(
                'id'            => 1,
                'short_name'    => 'USD',
                'full_name'     => 'U.S. dollar'
            )
        );
        $wpdb->replace(
            $table_currency,
            array(
                'id'            => 2,
                'short_name'    => 'EUR',
                'full_name'     => 'Euro'
            )
        );

        add_option('laterpay_plugin_is_activated',      '');
        add_option('laterpay_teaser_content_only',      '1');
        add_option('laterpay_plugin_is_in_live_mode',   '0');
        add_option('laterpay_sandbox_merchant_id',      '');
        add_option('laterpay_sandbox_api_key',          '');
        add_option('laterpay_live_merchant_id',         '');
        add_option('laterpay_live_api_key',             '');
        add_option('laterpay_global_price',             LATERPAY_GLOBAL_PRICE_DEFAULT);
        add_option('laterpay_currency',                 LATERPAY_CURRENCY_DEFAULT);
        add_option('laterpay_version',                  $laterpay_version) || update_option('laterpay_version', $laterpay_version);

        // clear opcode cache
        LaterPayCacheHelper::reset_opcode_cache();

        // activate plugin
        $activated = get_option( 'laterpay_plugin_is_activated', '' );
        if ( $activated ! == '' ) { // never activated before
            update_option( 'laterpay_plugin_is_activated', '1' );
        }
    }

    /**
     * Deactivate plugin
     *
     */
    public function deactivate() {
        update_option( 'laterpay_plugin_is_activated', '0' );
    }

    /**
     * Add plugin to administrator panel
     */
    public function add_to_admin_panel() {
        $plugin_page = LaterPayViewHelper::$pluginPage;
        add_menu_page(
            __('LaterPay Plugin Settings', 'laterpay'),
            'LaterPay',
            'laterpay_read_plugin_pages',
            $plugin_page,
            array( $this->get_laterpay_admin_controller(), 'run' ),
            'dashicons-laterpay-logo',
            81
        );

        $activated = get_option( 'laterpay_plugin_is_activated', '' );
        if ( $activated === '' ) { // never activated before
            return;
        }
        $page_number = 0;
        foreach ( LaterPayViewHelper::$adminMenu as $name => $page ) {
            if ( $activated && $name == 'get_started' ) {
                continue;
            }
            $slug = ! $page_number ? $plugin_page : $page['url'];

            $page = add_submenu_page(
                $plugin_page,
                __($page['title'], 'laterpay') . ' | ' . __('LaterPay Plugin Settings', 'laterpay'),
                __($page['title'], 'laterpay'),
                'laterpay_read_plugin_pages',
                $slug,
                array( $this->get_laterpay_admin_controller(), 'run_' . $name )
            );
            add_action( 'load-' . $page, array( $this->get_laterpay_admin_controller(), 'help_' . $name ) );
            $page_number++;
        }

        add_action( 'load-post.php', array( $this->get_laterpay_admin_controller(), 'help_wp_edit_post' ) );
        add_action( 'load-post-new.php', array( $this->get_laterpay_admin_controller(), 'help_wp_add_post' ) );
    }

    protected function setup_admin_panel() {
        add_action( 'admin_menu', array( $this, 'add_to_admin_panel' ) );
    }

    /**
     *  Add Ajax hooks for tabs in plugin backend
     */
    protected function setup_admin_routes() {
        if ( class_exists( 'LaterPayGetStartedController' ) ) {
            add_action( 'wp_ajax_getstarted',    'LaterPayGetStartedController::process_ajax_requests' );
        }
        if ( class_exists( 'LaterPayPricingController' ) ) {
            add_action( 'wp_ajax_pricing',       'LaterPayPricingController::process_ajax_requests' );
        }
        if ( class_exists( 'LaterPayAppearanceController' ) ) {
            add_action( 'wp_ajax_appearance',    'LaterPayAppearanceController::process_ajax_requests' );
        }
        if ( class_exists( 'LaterPayAccountController' ) ) {
            add_action( 'wp_ajax_account',       'LaterPayAccountController::process_ajax_requests' );
        }
        if ( class_exists( 'LaterPayAdminController' ) ) {
            add_action( 'wp_ajax_admin',         'LaterPayAdminController::process_ajax_requests' );
        }
        if ( class_exists( 'LaterPayPostPricingController' ) ) {
            add_action( 'wp_ajax_post_pricing',  'LaterPayPostPricingController::process_ajax_requests' );
        }
    }

    /**
     * Add teaser content editor to add / edit post page
     */
    public function add_teaser_content_box() {
        add_meta_box( 'laterpay_teaser_content',
            __('Teaser Content', 'laterpay'),
            array( $this->get_laterpay_post_pricing_controller(), 'render_teaser_content_box' ),
            'post',
            'normal',
            'high'
        );
    }

    protected function setup_teaser_content_box() {
        add_action( 'save_post',    array( $this->get_laterpay_post_pricing_controller(), 'save_teaser_content_box' ) );
        add_action( 'admin_menu',   array( $this, 'add_teaser_content_box' ) );
    }

    /**
     * Add pricing form to add / edit post page
     */
    public function add_post_pricing_form() {
        add_meta_box( 'laterpay_pricing_post_content',
            __('Pricing for this Post', 'laterpay'),
            array( $this->get_laterpay_post_pricing_controller(), 'render_post_pricing_form' ),
            'post',
            'side',
            'high'
        );
    }

    protected function setup_post_pricing_form() {
        add_action( 'save_post',    array( $this->get_laterpay_post_pricing_controller(), 'save_post_pricing_form') );
        add_action( 'admin_menu',   array( $this, 'add_post_pricing_form') );

        // Ajax actions for pricing box
        add_action( 'wp_ajax_get_category_prices', 'PostPricingController::getCategoryPrices' );
    }

    protected function setup_shortcodes() {
        add_shortcode( 'laterpay_premium_download', array( $this, 'render_premium_download_box' ) );
        add_shortcode( 'laterpay_box_wrapper',      array( $this, 'render_premium_download_box_wrapper' ) );
    }

    /**
     * Renders a teaser box for selling additional (downloadable) content from the shortcode [laterpay_premium_download]
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
     */
    public function render_premium_download_box( $atts ) {
        $a = shortcode_atts(array(
               'target_page_title'  => '',
               'heading_text'       => __('Additional Premium Content', 'laterpay'),
               'description_text'   => '',
               'content_type'       => '',
               'teaser_image_path'  => ''
             ), $atts);

        if ( $a['target_page_title'] == '' ) {
            die;
        } else {
            $target_page    = get_page_by_title( $a['target_page_title'], OBJECT, array( 'post', 'page', 'attachment' ) );
            $page_id        = $target_page->ID;
            $page_url       = get_permalink( $page_id );
            $price          = LaterPayPostContentController::get_post_price( $page_id );
            $currency       = get_option( 'laterpay_currency' );
            $price_tag      = sprintf( __('%s<small>%s</small>', 'laterpay'), $price, $currency );
        }

        $content_type = $a['content_type'];

        if ( $content_type == '' ) {
            // determine $content_type from MIME Type of files attached to post
            $page_mime_type = get_post_mime_type( $page_id );

            switch ($page_mime_type) {
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
        if ( $a['teaser_image_path'] ! = '' ) {
            $html = "<div class=\"laterpay-premium-file-link\" style=\"background-image:url({$a['teaser_image_path']})\">";
        } else {
            $html = "<div class=\"laterpay-premium-file-link {$content_type}\">";
        }
        $html .= "    <a href=\"{$page_url}\" class=\"laterpay-premium-file-button\" data-icon=\"b\">{$price_tag}</a>";
        $html .= "    <div class=\"details\">";
        $html .= "        <h3>{$a['heading_text']}</h3>";
        if ( $a['description_text'] ! = '' ) {
            $html .= "    <p>{$a['description_text']}</p>";
        }
        $html .= "    </div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Aligns multiple teaser boxes in a row when enclosing them in shortcode [laterpay_box_wrapper]
     *
     * Important: Avoid line breaks between the shortcodes as WordPress will replace them with <br> tags
     *
     * Example:
     * [laterpay_box_wrapper][laterpay_premium_download target_page_title="Vocabulary list"][laterpay_premium_download target_page_title="Excercises"][/laterpay_box_wrapper]
     */
    function render_premium_download_box_wrapper( $atts, $content = null ) {
        return '<div class="laterpay-premium-file-link-wrapper">' . do_shortcode( $content ) . '</div>';
    }

    /**
     * Load LaterPay stylesheet with LaterPay vector icon on all pages where the admin menu is visible
     *
     * @global string $laterpay_version
     * @param string  $page
     */
    public function add_plugin_admin_assets( $page ) {
        global $laterpay_version;
        wp_register_style(
            'laterpay-admin',
            LATERPAY_ASSETS_PATH . '/css/laterpay-admin.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style( 'laterpay-admin' );

        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );

        if ( $page == 'post.php' || $page == 'post-new.php' ) {
            $this->get_laterpay_post_pricing_controller()->load_assets();
        }
    }

    protected function setup_plugin_admin_assets() {
        add_action( 'admin_enqueue_scripts', array( $this, 'add_plugin_admin_assets' ) );
    }

    /**
     * Add custom columns to posts table
     */
    public function add_columns_to_posts_table( $columns ) {
        $extended_columns   = array();
        $insert_after       = 'title';

        foreach ( $columns as $key => $val ) {
            $extended_columns[$key] = $val;
            if ( $key == $insert_after ) {
                $extended_columns['post_price']         = __('Price', 'laterpay');
                $extended_columns['post_price_type']    = __('Price Type', 'laterpay');
            }
        }

        return $extended_columns;
    }

    public function add_data_to_posts_table( $column_name, $post_id ) {
        if ( $column_name == 'post_price' ) {
            $price      = number_format( (float) LaterPayPostContentController::get_post_price( $post_id ), 2 );
            $currency   = get_option( 'laterpay_currency' );

            if ( $price > 0 ) {
                echo "<strong>$price</strong> <span>$currency</span>";
            } else {
                echo '&mdash;';
            }

        } else if ( $column_name == 'post_price_type' ) {
            $post_price_type = get_post_meta( $post_id, 'Pricing Post Type', true );

            if ( $post_price_type ! == '' ) {
                echo __($post_price_type, 'laterpay');
            } else {
                echo '&mdash;';
            }

        }
    }

    protected function setup_custom_data_in_posts_table() {
        add_filter( 'manage_post_posts_columns',         array( $this, 'add_columns_to_posts_table' ) );
        add_action( 'manage_post_posts_custom_column',   array( $this, 'add_data_to_posts_table' ), 10, 2 );
    }

    /**
     * Hint at the newly installed plugin using WP pointers
     */
    public function add_admin_pointers_script() {
        add_action( 'admin_print_footer_scripts', array( $this->get_laterpay_admin_controller(), 'modify_footer' ) );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }

    protected function setup_admin_pointers_script() {
        add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_pointers_script' ) );
    }

    /**
     * Track unique visitors
     */
    public function add_unique_visitors_tracking() {
        if ( ! LATERPAY_ACCESS_LOGGING_ENABLED || is_admin() ) {
            return;
        }
        $url    = LaterPayStatisticsHelper::get_full_url( $_SERVER );
        $postid = url_to_postid($url);
        LaterPayStatisticsHelper::track($postid);
    }

    protected function setup_unique_visitors_tracking() {
        add_action( 'init', array( $this, 'add_unique_visitors_tracking' ) );
    }

    protected function setup_purchasing() {
        add_action( 'init', 'LaterPayPostContentController::token_hook' );
        add_action( 'init', 'LaterPayPostContentController::buy_post' );
    }

    /**
     * Add filters to override post content
     */
    protected function setup_post_content_filters() {
        add_filter( 'the_title',                array( $this->get_laterpay_post_content_controller(), 'modify_post_title' ) );
        add_filter( 'the_content',              array( $this->get_laterpay_post_content_controller(), 'view' ) );
        add_filter( 'wp_footer',                array( $this->get_laterpay_post_content_controller(), 'modify_footer' ) );

        add_action( 'save_post',                array( $this->get_laterpay_post_content_controller(), 'init_teaser_content' ), 10, 2 );
        add_action( 'edit_form_after_editor',   array( $this->get_laterpay_post_content_controller(), 'init_teaser_content' ), 10, 2 );
    }

    /**
     * Load LaterPay stylesheets on all post pages
     *
     * @global string $laterpay_version
     */
    public function add_plugin_frontend_stylesheets() {
        global $laterpay_version;

        wp_register_style(
            'laterpay-post-view',
            LATERPAY_ASSETS_PATH . '/css/laterpay-post-view.css',
            array(),
            $laterpay_version
        );
        wp_register_style(
            'laterpay-dialogs',
            'https://static.sandbox.laterpaytest.net/webshell_static/client/1.0.0/laterpay-dialog/css/dialog.css'
        );
        wp_enqueue_style( 'laterpay-post-view' );
        wp_enqueue_style( 'laterpay-dialogs' );
    }

    /**
     * Load LaterPay JS libraries on all post pages
     *
     * @global string $laterpay_version
     */
    public function add_plugin_frontend_scripts() {
        global $laterpay_version;

        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );
        wp_register_script(
            'laterpay-yui',
            'https://static.laterpay.net/yui/3.13.0/build/yui/yui-min.js',
            array(),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-config',
            'https://static.laterpay.net/client/1.0.0/config.js',
            array( 'laterpay-yui' ),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-peity',
            LATERPAY_ASSETS_PATH . '/js/vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-post-view',
            LATERPAY_ASSETS_PATH . '/js/laterpay-post-view.js',
            array( 'jquery', 'laterpay-peity' ),
            $laterpay_version,
            false
        );
        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-config' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-post-view' );

        // pass localized strings and variables to script
        $client         = new LaterPayClient();
        $balance_url    = $client->get_controls_balance_url();
        wp_localize_script(
            'laterpay-post-view',
            'lpVars',
            array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'lpBalanceUrl'  => $balance_url,
                'getArticleUrl' => plugins_url( 'laterpay/scripts/lp-article.php' ),
                'getFooterUrl'  => plugins_url( 'laterpay/scripts/lp-footer.php' ),
                'getTitleUrl'   => plugins_url( 'laterpay/scripts/lp-title.php' ),
                'i18nAlert'     => __('In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay'),
            )
        );
    }

    protected function setup_plugin_frontend_assets() {
        add_action( 'wp_enqueue_scripts', array( $this, 'add_plugin_frontend_stylesheets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'add_plugin_frontend_scripts' ) );
    }

    /**
     * Load translations
     */
    public function add_plugin_translations() {
        load_plugin_textdomain( 'laterpay', false, dirname( plugin_basename( $this->_pluginFile ) ) . '/languages/' );
    }

    protected function setup_plugin_translations() {
        add_action( 'plugins_loaded', array( $this, 'add_plugin_translations' ) );
    }

    /**
     * Add installation and deactivation hooks for plugin.
     *
     * Uninstallation is handled by uninstall.php
     */
    protected function setup_registration() {
        register_activation_hook( $this->_pluginFile, array( $this, 'activate' ) );
        register_deactivation_hook( $this->_pluginFile, array( $this, 'deactivate' ) );
    }

    /**
     * Check plugin requirements.
     * Deactivates plugin and renders admin notices if requirements are not fulfilled.
     *
     * @global string $wp_version
     */
    public function add_requirements_checking() {
        global $wp_version;

        $installed_php_version          = phpversion();
        $installed_wp_version           = $wp_version;
        $required_php_version           = '5.2.4';
        $required_wp_version            = '3.3';
        $installed_php_is_compatible    = version_compare( $installed_php_version, $required_php_version, '>=' );
        $installed_wp_is_compatible     = version_compare( $installed_wp_version, $required_wp_version, '>=' );

        $notices = array();
        $template = __('<p>LaterPay: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'laterpay');

        // check PHP compatibility
        if ( ! $installed_php_is_compatible ) {
            $notices[] = sprintf( $template, 'PHP', $required_php_version, 'PHP', $installed_php_version );
        }

        // check WordPress compatibility
        if ( ! $installed_wp_is_compatible ) {
            $notices[] = sprintf( $template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version );
        }

        // check file / folder permissions
        $template = __('<p>LaterPay: Directory %s <strong>is not writable</strong>.</p>', 'laterpay');
        $file = dirname( $this->_pluginFile );
        if ( ! is_writable( $file ) ) {
            $notices[] = sprintf( $template, $file );
        }
        $file = dirname( $this->_pluginFile ) . DIRECTORY_SEPARATOR . 'cache';
        if ( ! is_writable( $file ) ) {
            $notices[] = sprintf( $template, $file );
        }

        // deactivate plugin and render error messages if requirements are not fulfilled
        if ( count( $notices ) > 0 ) {
            deactivate_plugins( plugin_basename( $this->_pluginFile ) );

            $notices[] = __('The LaterPay plugin could not be installed. Please fix the reported issues and try again.', 'laterpay');
            $out = join( "\n", $notices );
            echo '<div class="error">' . $out . '</div>';
        }
    }

    protected function setup_requirements_checking() {
        add_action( 'admin_notices', array( $this, 'add_requirements_checking' ) );
    }

    /**
     * Install settings and tables if update is required
     *
     * @global string $laterpay_version
     */
    public function add_checking_for_updates() {
        global $laterpay_version;

        if ( get_option('laterpay_version') ! = $laterpay_version ) {
            $this->activate();
            $_capabilities = new LaterPayCapabilities();
            $_capabilities->populate_roles();
        }
    }

    protected function setup_checking_for_updates() {
        add_action( 'plugins_loaded', array( $this, 'add_checking_for_updates' ) );
    }

    protected function setup_checking_for_upgrades() {
        add_filter( 'pre_set_site_transient_update_plugins',    array( $this->get_github_plugin_updater(), 'set_transient' ) );
        add_filter( 'plugins_api',                              array( $this->get_github_plugin_updater(), 'set_plugin_info' ), 10, 3 );
        add_filter( 'upgrader_pre_install',                     array( $this->get_github_plugin_updater(), 'pre_install' ), 10, 2 );
        add_filter( 'upgrader_post_install',                    array( $this->get_github_plugin_updater(), 'post_install' ), 10, 3 );
    }

    /**
     * Add settings link to plugins table
     *
     * @param type $links
     *
     * @return array
     */
    public function add_plugin_settings_link( $links ) {
        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'plugin-editor.php?file=laterpay%2Fsettings.php' ) . '">' . __('Settings', 'laterpay') . '</a>'
            ),
            $links
        );
    }

    protected function setup_plugin_settings_link() {
        add_action( 'plugin_action_links_' . plugin_basename( $this->_pluginFile ), array( $this, 'add_plugin_settings_link' ) );
    }

}
