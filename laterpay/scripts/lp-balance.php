<?php

// initialize application
define('APP_ROOT', realpath(dirname(__FILE__) . '/..'));

// set up WordPress environment
if ( !defined('ABSPATH') ) {
    require_once(APP_ROOT . '/../../../wp-load.php');
}

if ( file_exists(APP_ROOT . '/laterpay-config.php') ) {
    require_once(APP_ROOT . '/laterpay-config.php');
} else {
    exit();
}
require_once(APP_ROOT . '/loader.php');

AutoLoader::registerDirectory(APP_ROOT . '/vendor');

// register libraries
$request    = new Request();
$response   = new Response();
$client     = new LaterPayClient();

// request parameters
$hmac   = $request->getParam('hmac'); // required, token to validate request
$ts     = $request->getParam('ts');   // required, timestamp
$isLive = get_option('laterpay_plugin_is_in_live_mode');

if ( !function_exists('wp_get_current_user')) {
    include_once(ABSPATH . 'wp-includes/pluggable.php');
}

if ( $isLive || (!$isLive && current_user_can('manage_options')) ) {
    $response->setHeader('Location', $client->getIframeApiBalanceUrl());
    $response->setHttpResponseCode(302);
    $response->sendResponse();
} else {
    $response->sendResponse();
}
