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
require_once( APP_ROOT . '/laterpay-load.php' );

ini_set( 'display_errors', PHP_DISPLAY_ERRORS );
ini_set( 'error_log', PHP_LOG_FILENAME );

LaterPay_AutoLoader::register_directory( LATERPAY_GLOBAL_PATH . 'library' . DIRECTORY_SEPARATOR . 'laterpay' );
LaterPay_AutoLoader::register_directory( LATERPAY_GLOBAL_PATH . 'library' . DIRECTORY_SEPARATOR . 'vendor' );

// register libraries
$request    = new LaterPay_Core_Request();
$response   = new LaterPay_Core_Response();
$config     = laterpay_get_plugin_config();
$client     = new LaterPay_Core_Client( $config );

// functions
function get_decrypted_file_name( $file ) {
    global $response, $request;

    $file = base64_decode( $file );
    if ( empty( $file ) ) {

        LaterPay_Core_Logger::error( 'RESOURCE:: cannot decode $file - empty result' );

        $response->set_http_response_code( 500 );
        $response->send_response();
        exit();
    }
    $cipher = new Crypt_AES();
    $cipher->setKey( SECURE_AUTH_SALT );
    $file = $request->getServer( 'DOCUMENT_ROOT' ) . $cipher->decrypt( $file );

    return $file;
}

function send_response( $file ) {
    global $response;

    $file = get_decrypted_file_name( $file );
    if ( ! file_exists( $file ) ) {

        LaterPay_Core_Logger::error( 'RESOURCE:: file not found', array( 'file' => $file ) );

        $response->set_http_response_code( 404 );
        $response->send_response();
        exit();
    }
    $filetype = wp_check_filetype( $file );
    $fsize    = filesize( $file );
    $data     = file_get_contents( $file );
    $filename = basename( $file );

    $response->set_header( 'Content-Type', $filetype['type'] );
    $response->set_header( 'Content-Disposition', 'inline; filename="' . $filename .'"' );
    $response->set_header( 'Content-Length', $fsize );
    $response->setBody( $data );
    $response->set_http_response_code( 200 );
    $response->send_response();

    LaterPay_Core_Logger::debug( 'RESOURCE:: file sent. done.', array( 'file' => $file ) );

    exit();
}

// request parameters
$file       = $request->get_param( 'file' );     // required, relative file path
$aid        = $request->get_param( 'aid' );      // required, article id
$mt         = $request->get_param( 'mt' );       // optional, need to convert file to requested type
$lptoken    = $request->get_param( 'lptoken' );  // optional, to update token
$hmac       = $request->get_param( 'hmac' );     // required, token to validate request
$ts         = $request->get_param( 'ts' );       // required, timestamp
$auth       = $request->get_param( 'auth' );     // required, need to bypass API::get_access calls

LaterPay_Core_Logger::debug(
    'RESOURCE::incoming parameters',
    array(
        'file'      => $file,
        'aid'       => $aid,
        'mt'        => $mt,
        'lptoken'   => $lptoken,
        'hmac'      => $hmac,
        'ts'        => $ts,
        'auth'      => $auth
    )
);

// variables
$access     = false;
$upload_dir = wp_upload_dir();
$basedir    = $upload_dir['basedir'];
if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
    $api_key = get_option( 'laterpay_live_api_key' );
} else {
    $api_key = get_option( 'laterpay_sandbox_api_key' );
}

// processing
if ( empty($file) || empty($aid) ) {

    LaterPay_Core_Logger::error( 'RESOURCE:: empty $file or $aid' );

    $response->set_http_response_code( 400 );
    $response->send_response();
    exit();
}

if ( ! LaterPay_Helper_View::plugin_is_working() ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: plugin is not available. Sending file ...' );

    send_response( $file );
    exit();
}

if ( ! empty( $hmac ) && ! empty( $ts ) ) {
    if ( ! LaterPay_Core_Client_Signing::verify( $hmac, $client->get_api_key(), $request->get_data( 'get' ), plugins_url( LaterPay_Helper_File::SCRIPT_PATH ), $_SERVER['REQUEST_METHOD'] ) ) {

        LaterPay_Core_Logger::error( 'RESOURCE:: invalid $hmac or $ts has expired' );

        $response->set_http_response_code( 401 );
        $response->send_response();
        exit();
    }

    LaterPay_Core_Logger::debug( 'RESOURCE:: $hmac and $ts are valid' );

} else {

    LaterPay_Core_Logger::error( 'RESOURCE:: empty $hmac or $ts' );

    $response->set_http_response_code( 401 );
    $response->send_response();
    exit();
}

// check token
if ( ! empty($lptoken) ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: set token and make redirect' );

    // change URL
    $client->set_token( $lptoken );
    $params = array(
        'aid'   => $aid,
        'file'  => $file,
    );
    if ( ! empty( $auth ) ) {
        $tokenInstance  = new LaterPay_Core_Auth_Hmac( $client->get_api_key() );
        $params['auth'] = $tokenInstance->sign( $client->get_laterpay_token() );
    }
    $new_url  = plugins_url( LaterPay_Helper_File::SCRIPT_PATH );
    $new_url .= '?' . $client->sign_and_encode( $params, $new_url );

    $response->set_header( 'Location', $new_url );
    $response->set_http_response_code( 302 );
    $response->send_response();
    exit();
}

if ( ! $client->has_token() ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: No token found. Acquiring token' );

    $client->acquire_token();
}

if ( ! empty($auth) ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: Auth param exists. Checking ...' );

    $tokenInstance = new LaterPay_Core_Auth_Hmac( $api_key );

    if ( $tokenInstance->validate_token( $client->get_laterpay_token(), time(), $auth ) ) {

        LaterPay_Core_Logger::error( 'RESOURCE:: Auth param is valid. Sending file.' );

        send_response( $file, $mt );
        exit();
    }

    LaterPay_Core_Logger::debug( 'RESOURCE:: Auth param is not valid.' );
}

// check access
if ( ! empty($aid) ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: Checking access in API ...' );

    $result = $client->get_access( $aid );

    if ( ! empty( $result ) && isset( $result['articles'][$aid] ) ) {
        $access = $result['articles'][$aid]['access'];
    }

    LaterPay_Core_Logger::debug( 'RESOURCE:: Checked access', array( 'access' => $access ) );
}

// send file
if ( $access ) {

    LaterPay_Core_Logger::debug( 'RESOURCE:: Has access - sending file.' );

    send_response( $file, $mt );
} else {

    LaterPay_Core_Logger::error( 'RESOURCE:: Doesn\'t have access. Finish.' );

    $response->set_http_response_code( 403 );
    $response->send_response();
    exit();
}
