<?php

class LaterPay_Helper_File
{

    /**
     * Regex to detect URLs.
     *
     * @var string
     */
    const URL_REGEX_PATTERN = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

    /**
     * Path to script file.
     *
     * @var string
     */
    const SCRIPT_PATH = 'admin-ajax.php?action=laterpay_load_files';

    /**
     * File types protected against direct download from paid posts without purchasing.
     *
     * @var string
     */
    protected static $protected_file_types = '3gpp|aac|avi|divx|doc|docx|epup|flv|gif|jpeg|jpg|mobi|mov|mp3|mp4|mp4|mpg|ogg|pdf|png|ppt|pptx|rar|rtf|tif|tiff|txt|wav|wmv|xls|xlsx|zip';

    /**
     * Generate an encrypted URL for a file within a paid post that has a protected file type.
     *
     * @param int      $post_id
     * @param string   $url
     * @param boolean  $use_auth
     *
     * @return string $url
     */
    public static function get_encrypted_resource_url( $post_id, $url, $use_auth ) {
        $new_url            = admin_url( self::SCRIPT_PATH );
        $blog_url_parts     = parse_url( get_bloginfo('wpurl') );
        $resource_url_parts = parse_url( $url );
        if ( $blog_url_parts['host'] != $resource_url_parts['host'] ) {
            return $url;
        }
        $uri = $resource_url_parts['path'];
        if ( ! preg_match( '/.*\.(' . self::$protected_file_types . ')/i', $uri ) ) {
            return $url;
        }
        $cipher = new Crypt_AES();
        $cipher->setKey( SECURE_AUTH_SALT );
        $file = base64_encode( $cipher->encrypt( $uri ) );

        $request = new LaterPay_Core_Request();
        $path = $request->getServer('DOCUMENT_ROOT') . $uri;
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client( 
                $client_options['cp_key'], 
                $client_options['api_key'], 
                $client_options['api_root'], 
                $client_options['web_root'], 
                $client_options['token_name'] 
        );
        $params = array(
            'aid'   => $post_id,
            'file'  => $file,
            'ext'   => '.' . $ext,
        );
        if ( $use_auth ) {
            $client_options = LaterPay_Helper_Config::get_php_client_options();
            $client = new LaterPay_Client( 
                    $client_options['cp_key'], 
                    $client_options['api_key'], 
                    $client_options['api_root'], 
                    $client_options['web_root'], 
                    $client_options['token_name'] 
            );
            $tokenInstance  = new LaterPay_Core_Auth_Hmac( $client->get_api_key() );
            $params['auth'] = $tokenInstance->sign( $client->get_laterpay_token() );
        }

        return $new_url . '&' . $client->sign_and_encode( $params, $new_url );
    }

    /**
     * Ajax callback to load a file through a script to prevent direct access.
     *
     * @return  void
     */
    public function load_file() {
        unset( $_GET['action'] );
        // register libraries
        $request    = new LaterPay_Core_Request();
        $response   = new LaterPay_Core_Response();
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client( 
                $client_options['cp_key'], 
                $client_options['api_key'], 
                $client_options['api_root'], 
                $client_options['web_root'], 
                $client_options['token_name'] 
        );

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
        if ( empty( $file ) || empty( $aid ) ) {
            LaterPay_Core_Logger::error( 'RESOURCE:: empty $file or $aid' );
            $response->set_http_response_code( 400 );
            $response->send_response();
            exit();
        }

        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            LaterPay_Core_Logger::debug( 'RESOURCE:: plugin is not available. Sending file ...' );
            $this->send_response( $file );
            exit();
        }

        if ( ! empty( $hmac ) && ! empty( $ts ) ) {
            if ( ! LaterPay_Client_Signing::verify( $hmac, $client->get_api_key(), $request->get_data( 'get' ), admin_url( LaterPay_Helper_File::SCRIPT_PATH ), $_SERVER['REQUEST_METHOD'] ) ) {
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
        if ( ! empty( $lptoken ) ) {
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
            $new_url  = admin_url( LaterPay_Helper_File::SCRIPT_PATH );
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

        if ( ! empty( $auth ) ) {
            LaterPay_Core_Logger::debug( 'RESOURCE:: Auth param exists. Checking ...' );
            $tokenInstance = new LaterPay_Core_Auth_Hmac( $api_key );
            if ( $tokenInstance->validate_token( $client->get_laterpay_token(), time(), $auth ) ) {
                LaterPay_Core_Logger::error( 'RESOURCE:: Auth param is valid. Sending file.' );
                $this->send_response( $file, $mt );
                exit();
            }
            LaterPay_Core_Logger::debug( 'RESOURCE:: Auth param is not valid.' );
        }

        // check access
        if ( ! empty( $aid ) ) {
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
            $this->send_response( $file, $mt );
        } else {
            LaterPay_Core_Logger::error( 'RESOURCE:: Doesn\'t have access. Finish.' );
            $response->set_http_response_code( 403 );
            $response->send_response();
            exit();
        }

        exit;
    }

    /**
     * Get the file name of a file with encrypted filename.
     *
     * @param string $file
     *
     * @return string
     */
    protected function get_decrypted_file_name( $file ) {
        $request    = new LaterPay_Core_Request();
        $response   = new LaterPay_Core_Response();

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

    /**
     * Send a secured file to the user.
     *
     * @param string $file
     *
     * @return void
     */
    protected function send_response( $file ) {
        $response = new LaterPay_Core_Response();

        $file = $this->get_decrypted_file_name( $file );
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

    /**
     * Get the content of a paid post with encrypted links to contained files.
     *
     * @param int    $post_id
     * @param string $content
     * @param string $use_auth
     *
     * @return string $content
     */
    public static function get_encrypted_content( $post_id, $content, $use_auth ) {
        // encrypt links to the resources
        $urls       = array();
        $matches    = array();
        preg_match_all( self::URL_REGEX_PATTERN, $content, $matches );
        if ( isset( $matches[0] ) ) {
            $urls = $matches[0];
        }
        $search     = array();
        $replace    = array();

        foreach ( $urls as $resource_url ) {
            $new_url = self::get_encrypted_resource_url( $post_id, $resource_url, $use_auth );
            if ( $new_url != $resource_url ) {
                $search[]   = $resource_url;
                $replace[]  = $new_url;
            }
        }
        $content = str_replace( $search, $replace, $content );

        return $content;
    }

}
