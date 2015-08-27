<?php
/*
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Description: Sell digital content with LaterPay. It allows super easy and fast payments from as little as 5 cent up to 149.99 Euro at a 15% fee and no fixed costs.
 * Author: LaterPay GmbH and Mihail Turalenka
 * Version: 0.9.12
 * Author URI: https://laterpay.net/
 * Textdomain: laterpay
 * Domain Path: /languages
 */


// Kick-off
add_action( 'plugins_loaded', 'laterpay_init' );

register_activation_hook( __FILE__, 'laterpay_activate' );
register_deactivation_hook( __FILE__, 'laterpay_deactivate' );

/**
 * Callback for starting the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function laterpay_init() {
    laterpay_before_start();

    $config   = laterpay_get_plugin_config();
    $laterpay = new LaterPay_Core_Bootstrap( $config );

    try {
        $laterpay->run();
    } catch ( Exception $e ) {
        $context = array(
            'message' => $e->getMessage(),
            'trace'   => $e->getTrace(),
        );
        laterpay_get_logger()->critical( __( 'Unexpected error during plugin init', 'laterpay' ), $context );
    }
}

/**
 * Callback for activating the plugin.
 *
 * @wp-hook register_activation_hook
 *
 * @return void
 */
function laterpay_activate() {
    laterpay_before_start();
    $config     = laterpay_get_plugin_config();
    $laterpay   = new LaterPay_Core_Bootstrap( $config );

    laterpay_event_dispatcher()->dispatch( 'laterpay_activate_before' );
    $laterpay->activate();
    laterpay_event_dispatcher()->dispatch( 'laterpay_activate_after' );
}

/**
 * Callback for deactivating the plugin.
 *
 * @wp-hook register_deactivation_hook
 *
 * @return void
 */
function laterpay_deactivate() {
    laterpay_before_start();
    $config     = laterpay_get_plugin_config();
    $laterpay   = new LaterPay_Core_Bootstrap( $config );

    laterpay_event_dispatcher()->dispatch( 'laterpay_deactivate_before' );
    $laterpay->deactivate();
    laterpay_event_dispatcher()->dispatch( 'laterpay_deactivate_after' );
}

/**
 * Get the plugin settings.
 *
 * @return LaterPay_Model_Config
 */
function laterpay_get_plugin_config() {
    // check, if the config is in cache -> don't load it again.
    $config = wp_cache_get( 'config', 'laterpay' );
    if ( is_a( $config, 'LaterPay_Model_Config' ) ) {
        return $config;
    }

    $config = new LaterPay_Model_Config();

    // plugin default settings for paths and directories
    $config->set( 'plugin_dir_path',    plugin_dir_path( __FILE__ ) );
    $config->set( 'plugin_file_path',   __FILE__ );
    $config->set( 'plugin_base_name',   plugin_basename( __FILE__ ) );
    $config->set( 'plugin_url',         plugins_url( '/', __FILE__ ) );
    $config->set( 'view_dir',           plugin_dir_path( __FILE__ ) . 'views/' );
    $config->set( 'cache_dir',          plugin_dir_path( __FILE__ ) . 'cache/' );

    $upload_dir = wp_upload_dir();
    $config->set( 'log_dir',            $upload_dir['basedir'] . '/laterpay_log/' );
    $config->set( 'log_url',            $upload_dir['baseurl'] . '/laterpay_log/' );

    $plugin_url = $config->get( 'plugin_url' );
    $config->set( 'css_url',            $plugin_url . 'built_assets/css/' );
    $config->set( 'js_url',             $plugin_url . 'built_assets/js/' );
    $config->set( 'image_url',          $plugin_url . 'built_assets/img/' );

    // plugin modes
    $config->set( 'is_in_live_mode',    (bool) get_option( 'laterpay_plugin_is_in_live_mode', false ) );
    $config->set( 'ratings_enabled',    (bool) get_option( 'laterpay_ratings', false ) );

    $client_address         = isset( $_SERVER['REMOTE_ADDR'] ) ? laterpay_sanitized( $_SERVER['REMOTE_ADDR'] ) : null;
    $debug_mode_enabled     = (bool) get_option( 'laterpay_debugger_enabled', false );
    $debug_mode_addresses   = (string) get_option( 'laterpay_debugger_addresses', '' );
    $debug_mode_addresses   = explode( ',', $debug_mode_addresses );
    $debug_mode_addresses   = array_map( 'trim', $debug_mode_addresses );

    $config->set( 'debug_mode',         $debug_mode_enabled && ! empty( $debug_mode_addresses ) && in_array( $client_address, $debug_mode_addresses ) );
    $config->set( 'script_debug_mode',  defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );

    if ( $config->get( 'is_in_live_mode' ) ) {
        $laterpay_dialog_library_src = 'https://lpstatic.net/combo?yui/3.17.2/build/yui/yui-min.js&client/1.0.0/config.js';
    } elseif ( $config->get( 'script_debug_mode' ) ) {
        $laterpay_dialog_library_src = 'https://sandbox.lpstatic.net/combo?yui/3.17.2/build/yui/yui.js&client/1.0.0/config-sandbox.js';
    } else {
        $laterpay_dialog_library_src = 'https://sandbox.lpstatic.net/combo?yui/3.17.2/build/yui/yui-min.js&client/1.0.0/config-sandbox.js';
    }
    $config->set( 'laterpay_yui_js', $laterpay_dialog_library_src );

    // plugin headers
    $plugin_headers = get_file_data(
        __FILE__,
        array(
            'plugin_name'       => 'Plugin Name',
            'plugin_uri'        => 'Plugin URI',
            'description'       => 'Description',
            'author'            => 'Author',
            'version'           => 'Version',
            'author_uri'        => 'Author URI',
            'textdomain'        => 'Textdomain',
            'text_domain_path'  => 'Domain Path',
        )
    );
    $config->import( $plugin_headers );

    // make sure all API variables are set
    if ( ! get_option( 'laterpay_sandbox_backend_api_url' ) ) {
        update_option( 'laterpay_sandbox_backend_api_url', 'https://api.sandbox.laterpaytest.net' );
    }
    if ( ! get_option( 'laterpay_sandbox_dialog_api_url' ) ) {
        update_option( 'laterpay_sandbox_dialog_api_url', 'https://web.sandbox.laterpaytest.net' );
    }
    if ( ! get_option( 'laterpay_live_backend_api_url' ) ) {
        update_option( 'laterpay_live_backend_api_url', 'https://api.laterpay.net' );
    }
    if ( ! get_option( 'laterpay_live_dialog_api_url' ) ) {
        update_option( 'laterpay_live_dialog_api_url', 'https://web.laterpay.net' );
    }
    if ( ! get_option( 'laterpay_api_merchant_backend_url' ) ) {
        update_option( 'laterpay_api_merchant_backend_url', 'https://merchant.laterpay.net/' );
    }

    /**
     * LaterPay API endpoints and API default settings.
     *
     * @var array
     */
    $api_settings = array(
        'api.sandbox_backend_api_url'   => get_option( 'laterpay_sandbox_backend_api_url' ),
        'api.sandbox_dialog_api_url'    => get_option( 'laterpay_sandbox_dialog_api_url' ),
        'api.live_backend_api_url'      => get_option( 'laterpay_live_backend_api_url' ),
        'api.live_dialog_api_url'       => get_option( 'laterpay_live_dialog_api_url' ),
        'api.merchant_backend_url'      => get_option( 'laterpay_api_merchant_backend_url' ),
    );

    // non-editable settings for the LaterPay API
    $api_settings['api.token_name']           = 'token';
    $api_settings['api.sandbox_merchant_id']  = 'LaterPay-WordPressDemo';
    $api_settings['api.sandbox_api_key']      = 'decafbaddecafbaddecafbaddecafbad';

    $config->import( $api_settings );

    // default settings for currency and VAT
    $currency_settings = array(
        'currency.default'          => 'EUR',
        'currency.default_price'    => 0.29,
    );
    $config->import( $currency_settings );

    /**
     * Use page caching compatible mode.
     *
     * Set this to true, if you are using a caching solution like WP Super Cache that caches entire HTML pages;
     * In compatibility mode the plugin renders paid posts without the actual content so they can be cached as static
     * files and then uses an Ajax request to load either the preview content or the full content,
     * depending on the current visitor
     *
     * @var boolean $caching_compatible_mode
     *
     * @return boolean $caching_compatible_mode
     */
    $config->set( 'caching.compatible_mode', get_option( 'laterpay_caching_compatibility' ) );

    $enabled_post_types = get_option( 'laterpay_enabled_post_types' );

    // content preview settings
    $content_settings = array(
        'content.auto_generated_teaser_content_word_count'  => get_option( 'laterpay_teaser_content_word_count' ),
        'content.preview_percentage_of_content'             => get_option( 'laterpay_preview_excerpt_percentage_of_content' ),
        'content.preview_word_count_min'                    => get_option( 'laterpay_preview_excerpt_word_count_min' ),
        'content.preview_word_count_max'                    => get_option( 'laterpay_preview_excerpt_word_count_max' ),
        'content.enabled_post_types'                        => $enabled_post_types ? $enabled_post_types : array(),
    );
    $config->import( $content_settings );

    /**
     * Access logging for generating sales statistics within the plugin;
     * Sets a cookie and logs all requests from visitors to your blog, if enabled
     *
     * @var boolean$access_logging_enabled
     *
     * @return boolean$access_logging_enabled
     */
    $config->set( 'logging.access_logging_enabled', get_option( 'laterpay_access_logging_enabled' ) );

    // Browscap browser detection library
    $browscap_settings = array(
        // Auto-update browscap library
        // The plugin requires browscap to ensure search engine bots, social media sites, etc. don't crash when visiting a paid post
        // When set to true, the plugin will automatically fetch updates of this library from browscap.org
        'browscap.autoupdate'               => false,
        'browscap.silent'                   => true,
        // If you can't or don't want to enable automatic updates, you can provide the full path to a browscap.ini file
        // on your server that you update manually from http://browscap.org/stream?q=PHP_BrowsCapINI
        'browscap.manually_updated_copy'    => null,
    );
    $config->import( $browscap_settings );

    // cache the config
    wp_cache_set( 'config', $config, 'laterpay' );

    return $config;
}

/**
 * Run before plugins_loaded, activate_laterpay, and deactivate_laterpay, to register our autoload paths.
 *
 * @return void
 */
function laterpay_before_start() {
    try {
        $dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

        if ( ! class_exists( 'LaterPay_Autoloader' ) ) {
            require_once( $dir . 'laterpay-load.php' );
        }

        LaterPay_AutoLoader::register_namespace( $dir . 'application', 'LaterPay' );
        LaterPay_AutoLoader::register_directory( $dir . 'vendor' . DIRECTORY_SEPARATOR . 'laterpay' . DIRECTORY_SEPARATOR . 'laterpay-client-php' );
        LaterPay_AutoLoader::register_directory( $dir . 'vendor' . DIRECTORY_SEPARATOR . 'laterpay' . DIRECTORY_SEPARATOR . 'laterpay-php-browscap-library' );
    } catch ( Exception $e ) {
        // deactivate laterpay plugin
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }

    // boot-up the logger on 'plugins_loaded', 'register_activation_hook', and 'register_deactivation_hook' event
    // to register the required script and style filters
    laterpay_get_logger();
}

/**
 * Get logger object.
 *
 * @return LaterPay_Core_Logger
 */
function laterpay_get_logger() {
    // check, if the config is cached -> don't load it again
    $logger = wp_cache_get( 'logger', 'laterpay' );
    if ( is_a( $logger, 'LaterPay_Core_Logger' ) ) {
        return $logger;
    }

    $config     = laterpay_get_plugin_config();
    $handlers   = array();

    if ( $config->get( 'debug_mode' ) ) {
        // LaterPay WordPress handler to render the debugger pane
        $wp_handler = new LaterPay_Core_Logger_Handler_WordPress( LaterPay_Core_Logger::WARNING );
        $wp_handler->set_formatter( new LaterPay_Core_Logger_Formatter_Html() );

        $handlers[] = $wp_handler;
    } else {
        $handlers[] = new LaterPay_Core_Logger_Handler_Null();
    }

    // add additional processors for more detailed log entries
    $processors = array(
        new LaterPay_Core_Logger_Processor_Web(),
        new LaterPay_Core_Logger_Processor_MemoryUsage(),
        new LaterPay_Core_Logger_Processor_MemoryPeakUsage(),
    );
    laterpay_event_dispatcher()->set_debug_enabled( true );
    $logger = new LaterPay_Core_Logger( 'laterpay', $handlers, $processors );

    // cache the config
    wp_cache_set( 'logger', $logger, 'laterpay' );

    return $logger;
}


/**
 * This function makes sure that only the allowed HTML element names,
 * attribute names and attribute values plus only sane HTML entities will occur in $string.
 * Function is registered as 'customSanitizingFunctions' for 'WordPress.XSS.EscapeOutput' rule.
 *
 * @param string $string
 *
 * @return string
 * @link     http://codex.wordpress.org/Data_Validation Data Validation on WordPress Codex
 */
function laterpay_sanitize_output( $string ) {
    return wp_kses( $string, 'post' );
}

/**
 * This function required to by pass phpcs checks for valid generated html.
 * So this functions do nothing, just returns input string.
 * Function is registered as 'customAutoEscapedFunctions' for 'WordPress.XSS.EscapeOutput' rule.
 *
 * @param string $string
 * @return string
 */
function laterpay_sanitized( $string ) {
    return $string;
}

/**
 * Alias for the LaterPay Event Dispatcher
 *
 * @return LaterPay_Core_Event_Dispatcher
 */
function laterpay_event_dispatcher() {
    return LaterPay_Core_Event_Dispatcher::get_dispatcher();
}

