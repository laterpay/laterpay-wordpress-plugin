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

ini_set( 'display_errors', PHP_DISPLAY_ERRORS );
ini_set( 'error_log', PHP_LOG_FILENAME );

AutoLoader::register_directory( APP_ROOT . '/vendor' );

// register libraries
$request    = new LaterPayRequest();
$response   = new LaterPayResponse();
$client     = new LaterPayClient();

// functions
function get_decrypted_file_name( $file ) {
    global $response, $request;

    $file = base64_decode( $file );
    if ( empty( $file ) ) {

        LaterPayLogger::error( 'RESOURCE:: cannot decode $file - empty result' );

        $response->set_http_response_code( 500 );
        $response->send_response();
        exit();
    }
    $cipher = new Crypt_AES();
    $cipher->setKey( LATERPAY_RESOURCE_ENCRYPTION_KEY );
    $file = $request->getServer( 'DOCUMENT_ROOT' ) . $cipher->decrypt( $file );

    return $file;
}

function send_response( $file ) {
    global $response;
    $file = get_decrypted_file_name( $file );
    if ( ! file_exists( $file ) ) {

        LaterPayLogger::error( 'RESOURCE:: file not found', array( 'file' => $file ) );

        $response->set_http_response_code( 404 );
        $response->send_response();
        exit();
    }
    $type = LaterPayFileHelper::get_file_mime_type( $file );

    $response->set_header( 'Content-Type', $type );
    $data = file_get_contents( $file );
    $response->setBody( $data );
    $response->set_http_response_code( 200 );
    $response->send_response();

    LaterPayLogger::debug( 'RESOURCE:: file sent. done.', array( 'file' => $file ) );

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

LaterPayLogger::debug(
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

    LaterPayLogger::error( 'RESOURCE:: empty $file or $aid' );

    $response->set_http_response_code( 400 );
    $response->send_response();
    exit();
}

if ( ! LaterPayViewHelper::plugin_is_working() ) {

    LaterPayLogger::debug( 'RESOURCE:: plugin is not available. Sending file ...' );

    send_response( $file );
    exit();
}

if ( ! empty( $hmac ) && ! empty( $ts ) ) {
    if ( ! LaterPayClient_Signing::verify( $hmac, $client->get_api_key(), $request->get_data( 'get' ), plugins_url( LaterPayFileHelper::SCRIPT_PATH ), $_SERVER['REQUEST_METHOD'] ) ) {

        LaterPayLogger::error( 'RESOURCE:: invalid $hmac or $ts has expired' );

        $response->set_http_response_code( 401 );
        $response->send_response();
        exit();
    }

    LaterPayLogger::debug( 'RESOURCE:: $hmac and $ts are valid' );

} else {

    LaterPayLogger::error( 'RESOURCE:: empty $hmac or $ts' );

    $response->set_http_response_code( 401 );
    $response->send_response();
    exit();
}

// check token
if ( ! empty($lptoken) ) {

    LaterPayLogger::debug( 'RESOURCE:: set token and make redirect' );

    // change URL
    $client->set_token( $lptoken );
    $params = array(
        'aid'   => $aid,
        'file'  => $file,
    );
    if ( ! empty( $auth ) ) {
        $tokenInstance  = new LaterPayAuth_Hmac( $client->get_api_key() );
        $params['auth'] = $tokenInstance->sign( $client->get_laterpay_token() );
    }
    $new_url  = plugins_url( LaterPayFileHelper::SCRIPT_PATH );
    $new_url .= '?' . $client->sign_and_encode( $params, $new_url );

    $response->set_header( 'Location', $new_url );
    $response->set_http_response_code( 302 );
    $response->send_response();
    exit();
}

if ( ! $client->has_token() ) {

    LaterPayLogger::debug( 'RESOURCE:: No token found. Acquiring token' );

    $client->acquire_token();
}

if ( ! empty($auth) ) {

    LaterPayLogger::debug( 'RESOURCE:: Auth param exists. Checking ...' );

    $tokenInstance = new LaterPayAuth_Hmac( $api_key );

    if ( $tokenInstance->validate_token( $client->get_laterpay_token(), time(), $auth ) ) {

        LaterPayLogger::error( 'RESOURCE:: Auth param is valid. Sending file.' );

        send_response( $file, $mt );
        exit();
    }

    LaterPayLogger::debug( 'RESOURCE:: Auth param is not valid.' );
}

// check access
if ( ! empty($aid) ) {

    LaterPayLogger::debug( 'RESOURCE:: Checking access in API ...' );

    $result = $client->get_access( $aid );

    if ( ! empty( $result ) && isset( $result['articles'][$aid] ) ) {
        $access = $result['articles'][$aid]['access'];
    }

    LaterPayLogger::debug( 'RESOURCE:: Checked access', array( 'access' => $access ) );
}

// send file
if ( $access ) {

    LaterPayLogger::debug( 'RESOURCE:: Has access - sending file.' );

    send_response( $file, $mt );
} else {

    LaterPayLogger::error( 'RESOURCE:: Doesn\'t have access. Finish.' );

    $response->set_http_response_code( 403 );
    $response->send_response();
    exit();
}
