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

// Kick-Off
add_action( 'plugins_loaded', 'laterpay_init', 0 );

register_activation_hook( __FILE__, 'laterpay_activate' );
register_deactivation_hook( __FILE__, 'laterpay_deactivate' );

// TODO: can be removed, we've in controllers $this->config->version with references to Version in Plugin-Header (Line 7)
$laterpay_version = '0.9.6';

define( 'LATERPAY_GLOBAL_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
define( 'LATERPAY_BASE_NAME',   plugin_basename( dirname( __FILE__ ) ) );

if( ! class_exists( 'LaterPay_Autoloader' ) ) {
	require_once( LATERPAY_GLOBAL_PATH . 'laterpay-load.php' );
}

LaterPay_AutoLoader::register_directory( LATERPAY_GLOBAL_PATH . 'library' . DIRECTORY_SEPARATOR . 'laterpay' );
LaterPay_AutoLoader::register_directory( LATERPAY_GLOBAL_PATH . 'library' . DIRECTORY_SEPARATOR . 'vendor' );

$laterpay_config = require_once( LATERPAY_GLOBAL_PATH . 'laterpay-config.php' );
foreach ( $laterpay_config as $option => $value ) {
	if ( ! defined( $option ) ) {
		define( $option, $value );
	}
}


/**
 * Callback to start our plugin
 *
 * @wp-hook plugins_loaded
 * @return  void
 */
function laterpay_init() {
	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->run();


}

/**
 * Callback for activating the plugin
 * @wp-hook register_deactivation_hook
 * @return  void
 */
function laterpay_active() {
	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->activate();

}

/**
 * Callback for deactivating the plugin
 * @wp-hook register_deactivation_hook
 * @return  void
 */
function laterpay_deactive() {
	$config     = laterpay_get_plugin_config();
	$laterpay   = new LaterPay_Core_Bootstrap( $config );
	$laterpay->deactivate();

}

/**
 * getting the plugin settings
 * @return  stdClass
 */
function laterpay_get_plugin_config() {

	$data = new LaterPay_Model_Config();
	$data->plugin_dir_path  = plugin_dir_path( __FILE__ );
	$data->plugin_file_path = __FILE__;
	$data->plugin_base_name = plugin_basename( __FILE__ );
	$data->plugin_url       = plugins_url( '/', __FILE__ );
	$data->css_url          = $data->plugin_url . 'assets/css/';
	$data->js_url           = $data->plugin_url . 'assets/js/';
	$data->image_url        = $data->plugin_url . 'assets/images/';

	$headers = get_file_data(
		__FILE__,
		array (
			'text_domain_path' => 'Domain Path',
			'plugin_uri'       => 'Plugin URI',
			'plugin_name'      => 'Plugin Name',
			'version'          => 'Version'
		)
	);

	foreach ( $headers as $name => $value ) {
		$data->$name = $value;
	}


	return $data;
}
