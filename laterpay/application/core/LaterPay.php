<?php

/**
 *  LaterPay bootstrap class
 *
 */
class LaterPay {

    /**
     *
     *
     * @var PricingPostController
     */
    private $_pricingPostController;
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

    protected function getPricingPostController() {
        if ( empty($this->_pricingPostController) ) {
            $this->_pricingPostController = new PricingPostController();
        }

        return $this->_pricingPostController;
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
            $this->_gitHubPluginUpdater->init(  $this->_pluginFile,
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
            $this->setupTeaserContentBox();
            $this->setupPricingPostContentBox();

            $this->setupPurchases();

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

    protected function createConfigurationFile() {
        try {
            if ( !file_exists(LATERPAY_GLOBAL_PATH . 'config.php') ) {
                $config = file_get_contents(LATERPAY_GLOBAL_PATH . 'config.sample.php');
                $config = str_replace(
                    array('{LATERPAY_SALT}', '{LATERPAY_RESOURCE_ENCRYPTION_KEY}'),
                    array(md5(uniqid('salt')), md5(uniqid('key'))), $config
                );
                file_put_contents(LATERPAY_GLOBAL_PATH . 'config.php', $config);
                require_once(LATERPAY_GLOBAL_PATH . 'config.php');
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }
    
    protected function updateConfigurationFile() {
        if ( !file_exists( LATERPAY_GLOBAL_PATH . 'config.php' ) ) {
            $this->createConfigurationFile();
            return;
        }
        try {
            $config = require(LATERPAY_GLOBAL_PATH . 'config.php');
            $default_config = require(LATERPAY_GLOBAL_PATH . 'config.sample.php');
            $updated_config = array();
            if ( !is_array( $config ) ) { // get config settings from old format file
                $config = array();
                foreach ( $default_config as $option => $value ) {
                    if ( defined( $option ) ) {
                        $config[$option] = constant( $option );
                    }
                }
            }
            $changed = false;
            foreach ( $config as $option => $value ) {
                if ( in_array( $option, $default_config ) && $default_config[$option] != $value ) {
                    $updated_config[$option] = $value; // use manully updated option, instead of default
                    $changed = true;
                }
            }
            if ( $changed ) {
                $config_file = file_get_contents( LATERPAY_GLOBAL_PATH . 'config.sample.php' );
                foreach ( $updated_config as $option => $value ) {
                    if ( is_string( $value )){
                        $value = "'$value'";
                    }elseif (  is_bool( $value )){
                        $value = $value ? 'true' : 'false';
                    }
                    
                    $config_file = preg_replace( "#(.*)" . $option . "(.*)(\s*=>\s*)(.*)(,?)#i", '${1}' . $option . '${2}${3}' . $value . ',', $config_file );
                }
                file_put_contents( LATERPAY_GLOBAL_PATH . 'config.php', $config_file );
            }
        } catch ( Exception $e ) {
            // do nothing
        }
    }

    /**
     * Plugin activation hook
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
                     CREATE TABLE `$table_currency` (
                       `id` int(10) NOT NULL AUTO_INCREMENT,
                       `short_name` varchar(64) NOT NULL,
                       `full_name` varchar(64) NOT NULL,
                       PRIMARY KEY (`id`)
                     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                 ";
        dbDelta($sql);

        $sql = "
                     CREATE TABLE `$table_terms_price` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `term_id` int(11) NOT NULL,
                     `price` double NOT NULL DEFAULT '0',
                     PRIMARY KEY (`id`)
                     ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
                 ";
        dbDelta($sql);

        $sql = "
                     INSERT INTO `$table_currency` (`id`, `short_name`, `full_name`) VALUES
                     (1, 'USD', 'U.S. dollar'),
                     (2, 'EUR', 'Euro');
                 ";
        dbDelta($sql);

        $sql = "
                     CREATE TABLE `$table_history` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                         `mode` enum('test','live') NOT NULL DEFAULT 'test',
                         `post_id` int(11) NOT NULL,
                         `currency_id` int(11) NOT NULL,
                         `price` float NOT NULL,
                         `date` datetime NOT NULL,
                         `ip` int NOT NULL,
                         `hash` varchar(32) NOT NULL,
                         PRIMARY KEY (`id`)
                     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
                 ";
        dbDelta($sql);

        $sql = "
                 CREATE TABLE `$table_post_views`  (
                     `post_id` int(11) NOT NULL,
                     `date` datetime NOT NULL,
                     `user_id` VARCHAR(32) NOT NULL,
                     `count` BIGINT UNSIGNED NOT NULL DEFAULT 1,
                     `ip` VARBINARY(16) NOT NULL,
                     UNIQUE KEY `uk_view` (`post_id` ASC, `user_id` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        dbDelta($sql);

        add_option('laterpay_activate',             '0');
        add_option('laterpay_teaser_content_only',  '');
        add_option('laterpay_plugin_mode_is_live',  '');
        add_option('laterpay_sandbox_merchant_id',  '');
        add_option('laterpay_sandbox_api_key',      '');
        add_option('laterpay_live_merchant_id',     '');
        add_option('laterpay_live_api_key',         '');
        add_option('laterpay_global_price',         LATERPAY_GLOBAL_PRICE_DEFAULT);
        add_option('laterpay_currency',             LATERPAY_CURRENCY_DEFAULT);
        add_option('laterpay_version',              $laterpay_version ) || update_option( 'laterpay_version', $laterpay_version);

        // clear opcode cache
        CacheHelper::resetOpcodeCache();
    }

    /**
     * Plugin deactivation hook
     *
     * @global object $wpdb
     */
    public function deactivate() {
        global $wpdb;
        $table_currency     = $wpdb->prefix . 'laterpay_currency';
        $table_terms_price  = $wpdb->prefix . 'laterpay_terms_price';
        $table_history      = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views   = $wpdb->prefix . 'laterpay_post_views';

        $sql = "DROP TABLE `" . $table_currency . "`;";
        $wpdb->query($sql);
        $sql = "DROP TABLE `" . $table_terms_price . "`;";
        $wpdb->query($sql);
        $sql = "DROP TABLE `" . $table_history . "`;";
        $wpdb->query($sql);
        $sql = "DROP TABLE `" . $table_post_views . "`;";
        $wpdb->query($sql);

        delete_option('laterpay_activate');
        delete_option('laterpay_teaser_content_only');
        delete_option('laterpay_plugin_mode_is_live');
        delete_option('laterpay_sandbox_merchant_id');
        delete_option('laterpay_sandbox_api_key');
        delete_option('laterpay_live_merchant_id');
        delete_option('laterpay_live_api_key');
        delete_option('laterpay_global_price');
        delete_option('laterpay_currency');
        delete_option('laterpay_version');
    }

    /**
     * Add plugin to administrator panel
     */
    public function addAdminPanel() {
        add_menu_page(
            __('LaterPay Plugin Settings', 'laterpay'),
            'LaterPay',
            'manage_options',
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
            add_action('wp_ajax_getstarted',        'GetStartedController::pageAjax');
            add_action('wp_ajax_nopriv_getstarted', 'GetStartedController::pageAjax');
        }
        if ( class_exists('PricingController') ) {
            add_action('wp_ajax_pricing',           'PricingController::pageAjax');
            add_action('wp_ajax_nopriv_pricing',    'PricingController::pageAjax');
        }
        if ( class_exists('AppearanceController') ) {
            add_action('wp_ajax_appearance',        'AppearanceController::pageAjax');
            add_action('wp_ajax_nopriv_appearance', 'AppearanceController::pageAjax');
        }
        if ( class_exists('AccountController') ) {
            add_action('wp_ajax_account',           'AccountController::pageAjax');
            add_action('wp_ajax_nopriv_account',    'AccountController::pageAjax');
        }
    }

    /**
     * Add teaser content editor to add / edit post page
     */
    public function addTeaserContentBox() {
        add_meta_box('laterpay_teaser_content',
            __('Teaser Content', 'laterpay'),
            array($this->getPricingPostController(), 'teaserContentBox'),
            'post',
            'normal',
            'high'
        );
    }

    protected function setupTeaserContentBox() {
        add_action('save_post', array ($this->getPricingPostController(), 'saveTeaserContentBox'));
        add_action('admin_menu', array ($this, 'addTeaserContentBox'));
    }

    /**
     * Add pricing form to add / edit post page
     */
    public function addPricingPostContentBox() {
        add_meta_box('laterpay_pricing_post_content',
            __('Pricing for this Post', 'laterpay'),
            array($this->getPricingPostController(), 'pricingPostContentBox'),
            'post',
            'side',
            'high'  // show as high as possible in sidebar (priority 'high')
        );
    }

    protected function setupPricingPostContentBox() {
        add_action('save_post', array($this->getPricingPostController(), 'savePricingPostContentBox'));
        add_action('admin_menu', array($this, 'addPricingPostContentBox'));
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
            LATERPAY_ASSET_PATH . '/css/laterpay-admin.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style('laterpay-admin');

        wp_register_script(
            'jquery',
            '//code.jquery.com/jquery-1.11.0.min.js'
        );

        if ( $page == 'post.php' || $page == 'post-new.php' ) {
            $this->getPricingPostController()->loadAssets();
        }
    }

    protected function setupPluginAdminResources() {
        add_action('admin_enqueue_scripts', array($this, 'addPluginAdminResources'));
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
        add_action('admin_enqueue_scripts', array($this,'addAdminPointersScript'));
    }

    /**
     * Track unique visitors
     */
    public function addUniqueVisitorsTracking() {
        if ( !LATERPAY_ACCESS_LOGGING_ENABLED || is_admin() ) {
            return;
        }
        $url = StatisticHelper::getFullUrl($_SERVER);
        $postid = url_to_postid($url);
        StatisticHelper::track($postid);
    }

    protected function setupUniqueVisitorsTracking() {
        add_action('init', array($this, 'addUniqueVisitorsTracking'));
    }

    protected function setupPurchases() {
        // add token hook
        add_action('init', 'PostContentController::tokenHook');
        // add purchase hook
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
     * Load LaterPay stylesheets on all pages where a post title is visible
     *
     * @global string $laterpay_version
     */
    public function addPluginFrontendStylesheets() {
        global $laterpay_version;
        wp_register_style(
            'laterpay-post-view',
            LATERPAY_ASSET_PATH . '/css/laterpay-post-view.css',
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
            'https://static.laterpay.net/yui/3.13.0/build/yui/yui.js',
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
            LATERPAY_ASSET_PATH . '/js/vendor/jquery.peity.min.js',
            array('jquery'),
            $laterpay_version,
            false
        );
        wp_register_script(
            'laterpay-post-view',
            LATERPAY_ASSET_PATH . '/js/laterpay-post-view.js',
            array('jquery', 'laterpay-peity'),
            $laterpay_version,
            false
        );
        wp_enqueue_script('laterpay-yui');
        wp_enqueue_script('laterpay-config');
        wp_enqueue_script('laterpay-peity');
        wp_enqueue_script('laterpay-post-view');
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
     * Add install / uninstall hook for plugin
     */
    protected function setupRegistration() {
        register_activation_hook($this->_pluginFile,   array($this, 'activate'));
        register_deactivation_hook($this->_pluginFile, array($this, 'deactivate'));
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
                'settings' => '<a href="' . admin_url('plugin-editor.php?file=laterpay%2Fconfig.php') . '">' . __('Settings', 'laterpay') . '</a>'
            ),
            $links
        );
    }

    protected function setupPluginSettingsLink() {
        add_action('plugin_action_links_' . plugin_basename($this->_pluginFile), array($this, 'addPluginSettingsLink'));
    }

}
