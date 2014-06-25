<?php

// initialize application
define('APP_ROOT', realpath(dirname(__FILE__) . '/..'));

// set up WordPress environment
if ( !defined('ABSPATH') ) {
    require_once(APP_ROOT . '/../../../wp-load.php');
}

if ( file_exists(APP_ROOT . '/config.php') ) {
    require_once(APP_ROOT . '/config.php');
} else {
    exit();
}
require_once(APP_ROOT . '/loader.php');

ini_set('display_errors', PHP_DISPLAY_ERRORS);
ini_set('error_log', PHP_LOG_FILENAME);

AutoLoader::registerDirectory(APP_ROOT . '/vendor');

// register libraries
$request    = new Request();
$response   = new Response();
$client     = new LaterPayClient();

// functions
function getDecryptedFileName( $file ) {
    global $response, $request;

    $file = base64_decode($file);
    if ( empty($file) ) {

        Logger::error('RESOURCE:: cannot decode $file - empty result');

        $response->setHttpResponseCode( 500 );
        $response->sendResponse();
        exit();
    }
    $cipher = new Crypt_AES();
    $cipher->setKey( LATERPAY_RESOURCE_ENCRYPTION_KEY );
    $file = $request->getServer('DOCUMENT_ROOT') . $cipher->decrypt($file);

    return $file;
}

function sendResponse( $file, $mt = null ) {
    global $response;
    $file = getDecryptedFileName($file);
    if ( !file_exists($file) ) {

        Logger::error('RESOURCE:: file not found', array('file' => $file));

        $response->setHttpResponseCode(404);
        $response->sendResponse();
        exit();
    }
    $type = FileHelper::getFileMimeType($file);

    $response->setHeader('Content-Type', $type);
    $data = file_get_contents($file);
    $response->setBody($data);
    $response->setHttpResponseCode(200);
    $response->sendResponse();

    Logger::debug('RESOURCE:: file sent. done.', array('file' => $file));

    exit();
}

// request parameters
$file       = $request->getParam('file');     // required, relative file path
$aid        = $request->getParam('aid');      // required, article id
$mt         = $request->getParam('mt');       // optional, need to convert file to requested type
$lptoken    = $request->getParam('lptoken');  // optional, to update token
$hmac       = $request->getParam('hmac');     // required, token to validate request
$ts         = $request->getParam('ts');       // required, timestamp
$auth       = $request->getParam('auth');     // required, need to bypass API::getAccess calls

Logger::debug(
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
if ( get_option('laterpay_plugin_mode_is_live') ) {
    $api_key = get_option('laterpay_live_api_key');
} else {
    $api_key = get_option('laterpay_sandbox_api_key');
}

// processing
if ( empty($file) || empty($aid) ) {

    Logger::error('RESOURCE:: empty $file or $aid');

    $response->setHttpResponseCode(400);
    $response->sendResponse();
    exit();
}

if ( !ViewHelper::isPluginAvailable() ) {

    Logger::debug('RESOURCE:: plugin is not available. Sending file...');

    sendResponse($file, $mt);
    exit();
}

if ( !empty($hmac) && !empty($ts) ) {
    if ( !LaterPayClient_Signing::verify($hmac, $client->getApiKey(), $request->getData( 'get' ), plugins_url( FileHelper::SCRIPT_PATH ), $_SERVER['REQUEST_METHOD']) ) {

        Logger::error('RESOURCE:: invalid $hmac or $ts has expired');

        $response->setHttpResponseCode(401);
        $response->sendResponse();
        exit();
    }

    Logger::debug('RESOURCE:: $hmac and $ts are valid');

} else {

    Logger::error('RESOURCE:: empty $hmac or $ts');

    $response->setHttpResponseCode(401);
    $response->sendResponse();
    exit();
}

// check token
if ( !empty($lptoken) ) {

    Logger::debug('RESOURCE:: set token and make redirect');

    // change URL
    $client->setToken($lptoken);
    $params = array(
        'aid'   => $aid,
        'file'  => $file,
    );
    if ( !empty($auth) ) {
        $tokenInstance  = new Auth_Hmac($client->getApiKey());
        $params['auth'] = $tokenInstance->sign($client->getLpToken());
    }
    $new_url  = plugins_url(FileHelper::SCRIPT_PATH);
    $new_url .= '?' . $client->signAndEncode($params, $new_url);

    $response->setHeader('Location', $new_url);
    $response->setHttpResponseCode(302);
    $response->sendResponse();
    exit();
}

if ( !$client->hasToken() ) {

    Logger::debug('RESOURCE:: No token found. Acquiring token');

    $client->acquireToken();
}

if ( !empty($auth) ) {

    Logger::debug('RESOURCE:: Auth param exists. Checking...');

    $tokenInstance = new Auth_Hmac($api_key);

    if ( $tokenInstance->validateToken($client->getLpToken(), time(), $auth) ) {

        Logger::error('RESOURCE:: Auth param is valid. Sending file.');

        sendResponse($file, $mt);
        exit();
    }

    Logger::debug( 'RESOURCE:: Auth param is not valid.' );
}

// check access
if ( !empty($aid) ) {

    Logger::debug('RESOURCE:: Checking access in API...');

    $result = $client->getAccess($aid);

    if ( !empty($result) && isset($result['articles'][$aid]) ) {
        $access = $result['articles'][$aid]['access'];
    }

    Logger::debug('RESOURCE:: Checked access', array('access' => $access));
}

// send file
if ( $access ) {

    Logger::debug('RESOURCE:: Has access - sending file.');

    sendResponse($file, $mt);
} else {

    Logger::error('RESOURCE:: Doesn\'t have access. Finish.');

    $response->setHttpResponseCode(403);
    $response->sendResponse();
    exit();
}
