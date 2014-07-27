<?php
/*
 * Plugin Name: LaterPay
 * Plugin URI: https://laterpay.net/developers/plugins-and-libraries
 * Description: Sell digital content with LaterPay. It allows super easy and fast payments from as little as 5 cent up to 149.99 Euro at a 15% fee and no fixed costs.
 * Author: LaterPay GmbH and Mihail Turalenka
 * Version: 0.9.6
 * Author URI: https://laterpay.net/
 * Textdomain: laterpay
 */

$laterpay_version = '0.9.6';

define('LATERPAY_GLOBAL_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('LATERPAY_BASE_NAME', plugin_basename(dirname(__FILE__)));

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(LATERPAY_GLOBAL_PATH . 'vendor/'),
            get_include_path()
        )
    )
);

require_once(LATERPAY_GLOBAL_PATH . 'loader.php');

AutoLoader::register_directory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'controllers');
AutoLoader::register_directory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'core');
AutoLoader::register_directory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'helpers');
AutoLoader::register_directory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'models');
AutoLoader::register_directory(LATERPAY_GLOBAL_PATH . 'vendor');

$laterpay_config = require_once(LATERPAY_GLOBAL_PATH . 'laterpay-config.php');
foreach ( $laterpay_config as $option => $value ) {
    if ( ! defined($option) ) {
        define($option, $value);
    }
}

$laterpay = new LaterPay(__FILE__);
$laterpay->run();
