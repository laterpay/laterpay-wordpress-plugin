<?php
/*
 * Plugin Name: LaterPay
 * Plugin URI: https://laterpay.net/developers/plugins-and-libraries
 * Description: Sell digital content with LaterPay. It allows super easy and fast payments from as little as 5 cent up to 149.99 Euro at a 15% fee and no fixed costs.
 * Author: LaterPay GmbH and Mihail Turalenka
 * Version: 0.9.6
 * Author URI: https://laterpay.net/
 * Textdomain: laterpay
 * Domain Path: /languages
 */

// Kick-off
add_action( 'plugins_loaded', 'laterpay_init', 0 );

register_activation_hook( __FILE__, 'laterpay_activate' );
register_deactivation_hook( __FILE__, 'laterpay_deactivate' );

/**
 * Callback to start the plugin
 *
 * @wp-hook plugins_loaded
 * @return  void
 */
function laterpay_init() {

	laterpay_before_start();

	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->run();


}

/**
 * Callback for activating the plugin
 *
 * @wp-hook register_deactivation_hook
 * @return  void
 */
function laterpay_activate() {

	laterpay_before_start();

	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->activate();

}

/**
 * Callback for deactivating the plugin
 * @wp-hook register_deactivation_hook
 * @return  void
 */
function laterpay_deactivate() {

	laterpay_before_start();

	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->deactivate();

}

/**
 * getting the plugin settings
 * @return  LaterPay_Model_Config
 */
function laterpay_get_plugin_config() {

	// checking if the config is in cache -> don't load it again.
	$config = wp_cache_get( 'laterpay', 'config' );
	if( is_a( $config, 'LaterPay_Model_Config' ) ){
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

	$plugin_url = $config->get( 'plugin_url' );
	$config->set( 'css_url',    $plugin_url . 'assets/css/' );
	$config->set( 'js_url',     $plugin_url . 'assets/js/' );
	$config->set( 'image_url',  $plugin_url . 'assets/images/' );

	// plugin headers
	$plugin_headers = get_file_data(
		__FILE__,
		array (
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


	// GitHub settings
	$config->set( 'github.name', 'laterpay-wordpress-plugin' );
	$config->set( 'github.user', 'laterpay' );
	$config->set( 'github.token', '' );

	/**
	 * LaterPay API endpoints and API default settings
	 *
	 * @var array
	 */
	$api_settings = array(
		'api.sandbox_url'           => 'https://api.sandbox.laterpaytest.net',
		'api.sandbox_web_url'       => 'https://web.sandbox.laterpaytest.net',
		'api.live_url'              => 'https://api.laterpay.net',
		'api.live_web_url'          => 'https://web.laterpay.net',
		'api.merchant_backend_url'  => 'https://merchant.laterpay.net/',
	    'api.token_name'            => 'token',
	    'api.sandbox_merchant_id'   => 'LaterPay-WordPressDemo',
	    'api.sandbox_api_key'       => 'decafbaddecafbaddecafbaddecafbad',
	);

	/**
	 * Plugin filter for manipulating the API endpoint URLs
	 *
	 * @param   Array $api_settings
	 *
	 * @return  Array $api_settings
	 */
	$api_settings = apply_filters( 'laterpay_get_api_settings', $api_settings );
	$config->import( $api_settings );


	// default currency settings
	$currency_settings = array(
		'currency.default'          => 'EUR',
        'currency.default_price'    => 0.29,
        'currency.default_vat'      => 'DE19',
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
	 * @var     bool    $caching_compatible_mode
	 *
	 * @return  bool    $caching_compatible_mode
	 */
	$caching_compatible_mode = apply_filters(
		'laterpay_get_caching_compatible_mode',
		LaterPay_Helper_Cache::site_uses_page_caching()
	);
	$config->set( 'caching.compatible_mode', (bool) $caching_compatible_mode );

	// content settings
	$content_settings = array(
		'content.auto_generated_teaser_content_word_count'  => 60,
		'content.preview_percentage_of_content'             => 25,
		'content.preview_word_count_min'                    => 26,
		'content.preview_word_count_max'                    => 200,
        'content.allowed_post_types'                        => get_post_types( array( 'public' => true ) )
	);

	/**
	 * Content filter to change the settings for preview output
	 *
	 * @var     Array $content_settings
	 *
	 * @return  Array $content_settings array(
	 *                                     'content.auto_generated_teaser_content_word_count'   => Integer - Number of words used for automatically extracting teaser content for paid posts,
	 *                                     'content.preview_percentage_of_content'              => Integer - percentage of content to be extracted (values: 1-100); 20 means "extract 20% of the total number of words of the post",
	 *                                     'content.preview_word_count_min'                     => Integer - MINimum number of words; applied if number of words as percentage of the total number of words is less than this value,
	 *                                     'content.preview_word_count_max'                     => Integer - MAXimum number of words; applied if number of words as percentage of the total number of words exceeds this value,
     *                                     'content.allowed_post_types'                         => Array - allowed post_types which supports LaterPay-Payment
	 *                                  );
	 */
	$content_settings = apply_filters( 'laterpay_get_content_settings', $content_settings );
	$config->import( $content_settings );

	/**
	 * Access logging for generating sales statistics within the plugin;
	 * Sets a cookie and logs all requests from visitors to your blog, if enabled
	 *
	 * @var     bool $access_logging_enabled
	 *
	 * @return  bool $access_logging_enabled
	 */
	$access_logging_enabled = apply_filters(
		'later_pay_access_logging_enabled',
		true
	);
	$config->set( 'logging.access_logging_enabled', (bool) $access_logging_enabled );

	// browscap
	$browscap_settings = array(
		// Auto-update browscap library
		// The plugin requires browscap to ensure search engine bots, social media sites, etc. don't crash when visiting a paid post
		// When set to true, the plugin will automatically fetch updates of this library from browscap.org
		'browscap.autoupdate' => true,
		// If you can't or don't want to enable automatic updates, you can provide the full path to a browscap.ini file
		// on your server that you update manually from http://browscap.org/stream?q=PHP_BrowsCapINI
		'browscap.manually_updated_copy' => '',
	);

	/**
	 * @var     array $browscap_settings
	 *
	 * @return  array $browscap_settings
	 */
	$browscap_settings = apply_filters( 'laterpay_get_browscap_settings', $browscap_settings );
	$config->import( $browscap_settings );

	// cache the config
	wp_cache_set( 'laterpay', $config, 'config' );

	return $config;
}

/**
 * Run before plugins_loaded, activate_laterpay, and deactivate_laterpay, to register our autoloading paths.
 *
 * @return  Void
 */
function laterpay_before_start() {

	$dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

	if ( ! class_exists( 'LaterPay_Autoloader' ) ) {
		require_once( $dir . 'laterpay-load.php' );
	}

	LaterPay_AutoLoader::register_directory( $dir . 'library' . DIRECTORY_SEPARATOR . 'laterpay' );
	LaterPay_AutoLoader::register_directory( $dir . 'library' . DIRECTORY_SEPARATOR . 'vendor' );

}
