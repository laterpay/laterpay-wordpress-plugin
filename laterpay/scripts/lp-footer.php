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

// request parameters
$post_id    = $request->getParam('id');     // required, relative file path

$response->setHeader('Content-Type', 'text/html');

if ( LaterPayRequestHelper::isAjax() && !empty($post_id) ) {
    $controller = new LaterPayPostContentController();

    ob_start();
    $controller->modifyFooter();
    $html = ob_get_contents();
    ob_end_clean();

    $response->setBody($html);
} else {
    $response->setBody('');
}

$response->sendResponse();
