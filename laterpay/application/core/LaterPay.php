<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay {

    /**
     *
     *
     * @var PostPricingController
     */
    private $_postPricingController;
    /**
     *
     *
     * @var AdminController
     */
    private $_adminController;
    /**
     *
     *
     * @var PostContentController
     */
    private $_postContentController;
    /**
     *
     *
     * @var GitHubPluginUpdater
     */
    private $_gitHubPluginUpdater;

    private $_pluginFile;

    public function __construct( $file ) {
        $this->_pluginFile = $file;
    }

    protected function getPostPricingController() {
        if ( empty($this->_postPricingController) ) {
            $this->_postPricingController = new PostPricingController();
        }

        return $this->_postPricingController;
    }

    protected function getAdminController() {
        if ( empty($this->_adminController) ) {
            $this->_adminController = new AdminController();
        }

        return $this->_adminController;
    }

    protected function getPostContentController() {
        if ( empty($this->_postContentController) ) {
            $this->_postContentController = new PostContentController();
        }

        return $this->_postContentController;
    }

    protected function getGitHubPluginUpdater() {
        if ( empty($this->_gitHubPluginUpdater) ) {
            $this->_gitHubPluginUpdater = new GitHubPluginUpdater();
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
        $this->setupPluginTranslations();
        $this->setupAdminPanel();
        $this->setupAdminRoutes();

        $this->setupPluginAdminResources();
        $this->setupAdminPointersScript();

        if ( ViewHelper::isPluginAvailable() ) {
            $this->setupPurchases();
            $this->setupTeaserContentBox();
            $this->setupPricingPostContentBox();
            $this->setupPremiumDownloadsShortcode();

            $this->setupCustomDataInPostsTable();

            $this->setupUniqueVisitorsTracking();
            $this->setupPostContentFilter();
            $this->setupPluginFrontendResources();
        }

        $this->setupRegistration();
        $this->setupRequirementsChecking();
        $this->setupCheckingForUpdate();

        if ( is_admin() ) {
            $this->setupCheckingForUpgrade();
        }

        $this->setupPluginSettingsLink();
    }

    /**
     *
     * @param string $settings
     */
    private function _generateUserSettings( $settings ) {
        $config = str_replace(
            array(
                '{salt}',
                '{resource_encryption_key}',
                "'{SITE_USES_PAGE_CACHING}'"
            ),
            array(
                md5(uniqid('salt')),
                md5(uniqid('key')),
                CacheHelper::siteUsesPageCaching() ? 'true' : 'false'
            ),
            $settings
        );

        return $config;
    }

    protected function createConfigurationFile() {
        try {
            if ( !file_exists(LATERPAY_GLOBAL_PATH . 'settings.php') ) {
                $config = file_get_contents(LATERPAY_GLOBAL_PATH . 'settings.sample.php');
                $config = $this->_generateUserSettings($config);
                file_put_contents(LATERPAY_GLOBAL_PATH . 'settings.php', $config);
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    protected function updateConfigurationFile() {
        if ( !file_exists(LATERPAY_GLOBAL_PATH . 'settings.php') && !file_exists(LATERPAY_GLOBAL_PATH . 'config.php') ) {
            $this->createConfigurationFile();
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
                if ( in_array($option, $default_config) && $default_config[$option] != $value ) {
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
                $config_file = $this->_generateUserSettings($config_file);
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

        $this->updateConfigurationFile();
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

        add_option('laterpay_plugin_is_activated',      '0');
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
        CacheHelper::resetOpcodeCache();
    }

    /**
     * Deactivate plugin
     *
     */
    public function deactivate() {
        update_option('laterpay_plugin_is_activated', '0');
    }

    /**
     * Add plugin to administrator panel
     */
    public function addAdminPanel() {
        add_menu_page(
            __('LaterPay Plugin Settings', 'laterpay'),
            'LaterPay',
            'laterpay_read_plugin_pages',
            'laterpay/laterpay-admin.php',
            '',
            'dashicons-laterpay-logo',
            81
        );
    }

    protected function setupAdminPanel() {
        add_action('admin_menu', array($this, 'addAdminPanel'));
    }

    /**
     *  Add Ajax hooks for tabs in plugin backend
     */
    protected function setupAdminRoutes() {
        if ( class_exists('GetStartedController') ) {
            add_action('wp_ajax_getstarted',    'GetStartedController::pageAjax');
        }
        if ( class_exists('PricingController') ) {
            add_action('wp_ajax_pricing',       'PricingController::pageAjax');
        }
        if ( class_exists('AppearanceController') ) {
            add_action('wp_ajax_appearance',    'AppearanceController::pageAjax');
        }
        if ( class_exists('AccountController') ) {
            add_action('wp_ajax_account',       'AccountController::pageAjax');
        }
        if ( class_exists('AdminController') ) {
            add_action('wp_ajax_admin',         'AdminController::pageAjax');
        }
    }

    /**
     * Add teaser content editor to add / edit post page
     */
    public function addTeaserContentBox() {
        add_meta_box('laterpay_teaser_content',
            __('Teaser Content', 'laterpay'),
            array($this->getPostPricingController(), 'teaserContentBox'),
            'post',
            'normal',
            'high'
        );
    }

    protected function setupTeaserContentBox() {
        add_action('save_post', array($this->getPostPricingController(), 'saveTeaserContentBox'));
        add_action('admin_menu', array($this, 'addTeaserContentBox'));
    }

    /**
     * Add pricing form to add / edit post page
     */
    public function addPricingPostContentBox() {
        add_meta_box('laterpay_pricing_post_content',
            __('Pricing for this Post', 'laterpay'),
            array($this->getPostPricingController(), 'pricingPostContentBox'),
            'post',
            'side',
            'high'  // show as high as possible in sidebar (priority 'high')
        );
    }

    protected function setupPricingPostContentBox() {
        add_action('save_post', array($this->getPostPricingController(), 'savePricingPostContentBox'));
        add_action('admin_menu', array($this, 'addPricingPostContentBox'));
    }


    protected function setupPremiumDownloadsShortcode() {
        add_shortcode('laterpay_premium_download', array($this, 'renderPremiumDownloadLink'));
    }

    /**
     * Renders a teaser box for selling additional downloadable content from the shortcode [laterpay_premium_download]
     */
    public function renderPremiumDownloadLink( $atts ) {
        $a = shortcode_atts(array(
               'post'           => '',
               'title'          => __('Additional Premium Content', 'laterpay'),
               'description'    => '',
               'teaser_image'   => ''
             ), $atts);

        // TODO: add default teaser_image to assets/img
        // TODO: choose default teaser_image depending on mime type

        if ( $a['post'] != '' ) {
            $page       = get_page_by_title($a['post']);    // FIXME: this or the next line is failing
            $page_url   = get_permalink($page->ID);
        } else {
            $page_url   = '#';
        }
        $currency       = get_option('laterpay_currency');
        $price          = '2.99';   // TODO: get price from post
        $price_tag      = sprintf(__('%s<small>%s</small>', 'laterpay'), $price, $currency);

        $content_type = ''; // assign class depending on mime type

        if ( $a['teaser_image'] != '' ) {
            $link  = "<div class=\"premium-file-link\" style=\"background-image:url({$a['teaser_image']})\">";
        } else {
            $link  = "<div class=\"premium-file-link {$content_type}\">";
        }
        $link .= "    <a href=\"{$page_url}\" class=\"premium-file-button\" data-icon=\"b\">{$price_tag}</a>";
        $link .= "    <div class=\"details\">";
        $link .= "        <h3>{$a['title']}</h3>";
        if ( $a['description'] != '' ) {
            $link .= "    <p>{$a['description']}</p>";
        }
        $link .= "    </div>";
        $link .= "</div>";

        return $link;
    }

    /**
     * Load LaterPay stylesheet with LaterPay vector icon on all pages where the adminmenu is visible
     *
     * @global string $laterpay_version
     * @param string  $page
     */
    public function addPluginAdminResources( $page ) {
        global $laterpay_version;
        wp_register_style(
            'laterpay-admin',
            LATERPAY_ASSETS_PATH . '/css/laterpay-admin.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style('laterpay-admin');

        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );

        if ( $page == 'post.php' || $page == 'post-new.php' ) {
            $this->getPostPricingController()->loadAssets();
        }
    }

    protected function setupPluginAdminResources() {
        add_action('admin_enqueue_scripts', array($this, 'addPluginAdminResources'));
    }

    /**
     * Add custom columns to posts table
     */
    public function addColumnsToPostsTable( $columns ) {
        $insert_after = 'title';

        foreach ( $columns as $key => $val ) {
            $extended_columns[$key] = $val;
            if ($key == $insert_after) {
                $extended_columns['post_price'] = __('Price', 'laterpay');
            }
        }

        return $extended_columns;
    }

    public function addDataToPostsTable( $column_name, $post_id ) {
        if ($column_name == 'post_price') {
            $price      = number_format((float)PostContentController::getPostPrice($post_id), 2);
            $currency   = get_option('laterpay_currency');

            if ( $price > 0 ) {
                echo "<strong>$price</strong> <span>$currency</span>";
            } else {
                echo '&mdash;';
            }

        }
    }

    protected function setupCustomDataInPostsTable() {
        add_filter('manage_post_posts_columns',         array($this, 'addColumnsToPostsTable'));
        add_action('manage_post_posts_custom_column',   array($this, 'addDataToPostsTable'), 10, 2);
    }

    /**
     * Hint at the newly installed plugin using WP pointers
     */
    public function addAdminPointersScript() {
        add_action('admin_print_footer_scripts', array($this->getAdminController(), 'modifyFooter'));
        wp_enqueue_script('wp-pointer');
        wp_enqueue_style('wp-pointer');
    }

    protected function setupAdminPointersScript() {
        add_action('admin_enqueue_scripts', array($this, 'addAdminPointersScript'));
    }

    /**
     * Track unique visitors
     */
    public function addUniqueVisitorsTracking() {
        if ( !LATERPAY_ACCESS_LOGGING_ENABLED || is_admin() ) {
            return;
        }
        $url = StatisticsHelper::getFullUrl($_SERVER);
        $postid = url_to_postid($url);
        StatisticsHelper::track($postid);
    }

    protected function setupUniqueVisitorsTracking() {
        add_action('init', array($this, 'addUniqueVisitorsTracking'));
    }

    protected function setupPurchases() {
        add_action('init', 'PostContentController::tokenHook');
        add_action('init', 'PostContentController::buyPost');
    }

    /**
     * Add filters to override post content
     */
    protected function setupPostContentFilter() {
        add_filter('the_title',                array($this->getPostContentController(), 'modifyPostTitle'));
        add_filter('the_content',              array($this->getPostContentController(), 'view'));
        add_filter('wp_footer',                array($this->getPostContentController(), 'modifyFooter'));
        add_action('save_post',                array($this->getPostContentController(), 'initTeaserContent'), 10, 2);
        add_action('edit_form_after_editor',   array($this->getPostContentController(), 'initTeaserContent'), 10, 2);
    }

    /**
     * Load LaterPay stylesheets on all post pages
     *
     * @global string $laterpay_version
     */
    public function addPluginFrontendStylesheets() {
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
        wp_enqueue_style('laterpay-post-view');
        wp_enqueue_style('laterpay-dialogs');
    }

    /**
     * Load LaterPay JS libraries on all post pages
     *
     * @global string $laterpay_version
     */
    public function addPluginFrontendScripts() {
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
            array('laterpay-yui'),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-peity',
            LATERPAY_ASSETS_PATH . '/js/vendor/jquery.peity.min.js',
            array('jquery'),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-post-view',
            LATERPAY_ASSETS_PATH . '/js/laterpay-post-view.js',
            array('jquery', 'laterpay-peity'),
            $laterpay_version,
            false
        );
        wp_enqueue_script('laterpay-yui');
        wp_enqueue_script('laterpay-config');
        wp_enqueue_script('laterpay-peity');
        wp_enqueue_script('laterpay-post-view');

        // pass localized strings and variables to script
        $client = new LaterPayClient();
        $balance_url = $client->getControlsBalanceUrl();
        wp_localize_script(
            'laterpay-post-view',
            'lpVars',
            array(
                'ajaxUrl'       => admin_url('admin-ajax.php'),
                'lpBalanceUrl'  => $balance_url,
                'getArticleUrl' => plugins_url('laterpay/scripts/lp-article.php'),
                'getFooterUrl'  => plugins_url('laterpay/scripts/lp-footer.php'),
                'getTitleUrl'   => plugins_url('laterpay/scripts/lp-title.php'),
                'i18nAlert'     => __('In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay'),
            )
        );
    }

    protected function setupPluginFrontendResources() {
        add_action('wp_enqueue_scripts', array($this, 'addPluginFrontendStylesheets'));
        add_action('wp_enqueue_scripts', array($this, 'addPluginFrontendScripts'));
    }

    /**
     * Load translations
     */
    public function addPluginTranslations() {
        load_plugin_textdomain('laterpay', false, dirname(plugin_basename($this->_pluginFile)) . '/languages/');
    }

    protected function setupPluginTranslations() {
        add_action('plugins_loaded', array($this, 'addPluginTranslations'));
    }

    /**
     * Add installation and deactivatino hooks for plugin
     *
     * Uninstallation is handled by uninstall.php
     */
    protected function setupRegistration() {
        register_activation_hook($this->_pluginFile,    array($this, 'activate'));
        register_deactivation_hook($this->_pluginFile,  array($this, 'deactivate'));
    }

    /**
     * Add notices for requirements
     *
     * @global string $wp_version
     */
    public function addRequirementsChecking() {
        global $wp_version;

        $installed_php_version          = phpversion();
        $installed_wp_version           = $wp_version;
        $required_php_version           = '5.2.4';
        $required_wp_version            = '3.3';
        $installed_php_is_compatible    = version_compare($installed_php_version, $required_php_version, '>=');
        $installed_wp_is_compatible     = version_compare($installed_wp_version, $required_wp_version, '>=');

        $notices = array();
        $template = __('<p>LaterPay: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'laterpay');
        if ( !$installed_php_is_compatible ) {
            $notices[] = sprintf($template, 'PHP', $required_php_version, 'PHP', $installed_php_version);
        }
        if ( !$installed_wp_is_compatible ) {
            $notices[] = sprintf($template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version);
        }

        if ( count($notices) > 0 ) {
            $out = join('\n', $notices);
            echo '<div class="error">' . $out . '</div>';
        }
    }

    protected function setupRequirementsChecking() {
        add_action('admin_notices', array($this, 'addRequirementsChecking'));
    }

    /**
     * Install settings and tables if update is required
     *
     * @global string $laterpay_version
     */
    public function addCheckingForUpdate() {
        global $laterpay_version;

        if ( get_option('laterpay_version') != $laterpay_version ) {
            $this->activate();
            $_capabilities = new LaterPayCapabilities();
            $_capabilities->populateRoles();
        }
    }

    protected function setupCheckingForUpdate() {
        add_action('plugins_loaded', array($this, 'addCheckingForUpdate'));
    }

    protected function setupCheckingForUpgrade() {
        add_filter('pre_set_site_transient_update_plugins',    array($this->getGitHubPluginUpdater(), 'setTransient'));
        add_filter('plugins_api',                              array($this->getGitHubPluginUpdater(), 'setPluginInfo'), 10, 3);
        add_filter('upgrader_pre_install',                     array($this->getGitHubPluginUpdater(), 'preInstall'), 10, 2);
        add_filter('upgrader_post_install',                    array($this->getGitHubPluginUpdater(), 'postInstall'), 10, 3);
    }

    /**
     * Add settings link in plugins table
     *
     * @param type $links
     *
     * @return array
     */
    public function addPluginSettingsLink( $links ) {
        return array_merge(
            array(
                'settings' => '<a href="' . admin_url('plugin-editor.php?file=laterpay%2Fsettings.php') . '">' . __('Settings', 'laterpay') . '</a>'
            ),
            $links
        );
    }

    protected function setupPluginSettingsLink() {
        add_action('plugin_action_links_' . plugin_basename($this->_pluginFile), array($this, 'addPluginSettingsLink'));
    }

}
