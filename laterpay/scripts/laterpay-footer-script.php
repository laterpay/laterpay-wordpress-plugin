<?php

// initialize application
define( 'APP_ROOT', realpath( dirname( __FILE__ ) . '/..' ) );

// set up WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    require_once( APP_ROOT . '/../../../wp-load.php' );
}

if ( file_exists( APP_ROOT . '/laterpay-config.php' ) ) {
    require_once( APP_ROOT . '/laterpay-config.php' );
} else {
    exit();
}
require_once( APP_ROOT . '/loader.php' );

AutoLoader::register_directory( APP_ROOT . '/vendor' );

// register libraries
$request    = new LaterPay_Request();
$response   = new LaterPay_Response();

// request parameters
$post_id    = $request->get_param( 'id' ); // required, relative file path

$response->set_header( 'Content-Type', 'text/html' );

if ( LaterPay_Request_Helper::is_ajax() && ! empty( $post_id ) ) {
    $controller = new LaterPay_Post_Content_Controller();

    ob_start();
    $controller->modify_footer();
    $html = ob_get_contents();
    ob_end_clean();

    $response->setBody( $html );
} else {
    $response->setBody( '' );
}

$response->send_response();
