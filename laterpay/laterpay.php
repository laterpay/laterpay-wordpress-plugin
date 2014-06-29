<?php

/*
  Plugin Name: LaterPay
  Plugin URI: https://laterpay.net/developers/plugins-and-libraries
  Description: This plugin integrates LaterPay into your blog. LaterPay is a new payment method for selling digital content with ease. Your users simply agree to pay later, get instant access to your content and pay once their invoice reaches 5 Euro. The set-up is fast and painless. You can set global, category, or individual prices and use extended pricing to change prices automatically over time to boost your sales. The plugin protects the files in your paid posts, provides sales statistics for your content, and is designed to work smoothly with social media plugins and crawlers in order to not reduce your blog's reach.
  Author: LaterPay GmbH and Mihail Turalenka
  Version: 0.9.4.2
  Author URI: https://laterpay.net/
 */

$laterpay_version = '0.9.4.2';

ini_set('display_errors', 0);

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

AutoLoader::registerDirectory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'controllers');
AutoLoader::registerDirectory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'core');
AutoLoader::registerDirectory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'helpers');
AutoLoader::registerDirectory(LATERPAY_GLOBAL_PATH . 'application' . DIRECTORY_SEPARATOR . 'models');
AutoLoader::registerDirectory(LATERPAY_GLOBAL_PATH . 'vendor');

$laterpay_config = require_once(LATERPAY_GLOBAL_PATH . 'laterpay-config.php');
foreach ( $laterpay_config as $option => $value ) {
    if ( !defined($option) ) {
        define($option, $value);
    }
}

$laterpay = new LaterPay(__FILE__);
$laterpay->run();
