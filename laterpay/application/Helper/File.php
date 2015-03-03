<?php

/**
 * LaterPay file helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
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
     * Default file disposition.
     *
     * @var string
     */
    const DEFAULT_FILE_DISPOSITION = 'inline';

    /**
     * Cache protected urls.
     *
     * @var null, array
     */
    private static $protected_urls = null;

    /**
     * Decide, if an URI should be encrypted.
     *
     * @param array $resource_url_parts
     *
     * @return boolean
     */
    public static function check_url_encrypt( $resource_url_parts ) {
        // get path of resource
        $blog_url_parts = parse_url( get_bloginfo( 'wpurl' ) );
        if ( ! $blog_url_parts ) {
            return false;
        }

        if ( $blog_url_parts['host'] != $resource_url_parts['host'] ) {
            // don't encrypt, because resource is not located at current host
            return false;
        }
        $uri = $resource_url_parts['path'];

        if ( ! isset( self::$protected_urls ) ) {
            self::$protected_urls = array();
            // add path of wp-uploads folder to $protected_urls
            $upload_dir = wp_upload_dir();
            $upload_url = parse_url( $upload_dir['baseurl'] );
            $upload_url = $upload_url['path'];
            $upload_url = ltrim( $upload_url, '/' );
            self::$protected_urls['upload_url'] = $upload_url;

            // add path of wp-content folder to $protected_urls
            $content_url = content_url();
            $content_url = parse_url( $content_url );
            $content_url = $content_url['path'];
            $content_url = ltrim( $content_url, '/' );
            self::$protected_urls['content_url'] = $content_url;

            // add path of wp-includes folder to $protected_urls
            $includes_url = includes_url();
            $includes_url = parse_url( $includes_url );
            $includes_url = $includes_url['path'];
            $includes_url = ltrim( $includes_url, '/' );
            self::$protected_urls['includes_url'] = $includes_url;
        }

        // check, if resource is located inside one of the protected folders
        foreach ( self::$protected_urls as $protected_name => $protected_url ) {
            if ( strstr( $uri, $protected_url ) ) {
                // encrypt, because URI is among the protected URIs
                return true;
            };
        };

        // don't encrypt, if we could not determine that it should be encrypted
        return false;
    }

    /**
     * Return an encrypted URL, if a file should be secured against direct access.
     *
     * @param int           $post_id
     * @param string        $url
     * @param boolean       $use_auth
     * @param string|null   $set_file_disposition
     *
     * @return string $url
     */
    public static function get_encrypted_resource_url( $post_id, $url, $use_auth, $set_file_disposition = null ) {
        $resource_url_parts = parse_url( $url );
        if ( ! self::check_url_encrypt( $resource_url_parts ) ) {
            // return unmodified URL, if file should not be encrypted
            return $url;
        }

        $new_url    = admin_url( self::SCRIPT_PATH );
        $uri        = $resource_url_parts['path'];

        $cipher     = new Crypt_AES();
        $cipher->setKey( SECURE_AUTH_SALT );
        $file       = base64_encode( $cipher->encrypt( $uri ) );
        $file       = strtr( $file, '+/', '-_' );

        $request    = new LaterPay_Core_Request();
        $path       = ABSPATH . $uri;
        $ext        = pathinfo( $path, PATHINFO_EXTENSION );

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
        if ( isset( $set_file_disposition ) ) {
            $params['file_disposition'] = $set_file_disposition;
        }
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
     * @return void
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
        $file               = $request->get_param( 'file' );                // required, relative file path
        $aid                = $request->get_param( 'aid' );                 // required, article id
        $mt                 = $request->get_param( 'mt' );                  // optional, need to convert file to requested type
        $lptoken            = $request->get_param( 'lptoken' );             // optional, to update token
        $hmac               = $request->get_param( 'hmac' );                // required, token to validate request
        $ts                 = $request->get_param( 'ts' );                  // required, timestamp
        $auth               = $request->get_param( 'auth' );                // required, need to bypass API::get_access calls
        $file_disposition   = $request->get_param( 'file_disposition' );    // optional, required for attachments

        laterpay_get_logger()->debug(
            'RESOURCE::incoming parameters',
            array(
                'file'      => $file,
                'aid'       => $aid,
                'mt'        => $mt,
                'lptoken'   => $lptoken,
                'hmac'      => $hmac,
                'ts'        => $ts,
                'auth'      => $auth,
            )
        );

        // variables
        $access     = false;
        $upload_dir = wp_upload_dir();
        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $api_key = get_option( 'laterpay_live_api_key' );
        } else {
            $api_key = get_option( 'laterpay_sandbox_api_key' );
        }

        // processing
        if ( empty( $file ) || empty( $aid ) ) {
            laterpay_get_logger()->error( 'RESOURCE:: empty $file or $aid' );
            $response->set_http_response_code( 400 );
            $response->send_response();
            // exit script after response was created
            exit();
        }

        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            laterpay_get_logger()->debug( 'RESOURCE:: plugin is not available. Sending file ...' );
            $this->send_response( $file );
            // exit script after response was created
            exit();
        }

        if ( ! empty( $hmac ) && ! empty( $ts ) ) {
            if ( ! LaterPay_Client_Signing::verify( $hmac, $client->get_api_key(), $request->get_data( 'get' ), admin_url( LaterPay_Helper_File::SCRIPT_PATH ), $_SERVER['REQUEST_METHOD'] ) ) {
                laterpay_get_logger()->error( 'RESOURCE:: invalid $hmac or $ts has expired' );
                $response->set_http_response_code( 401 );
                $response->send_response();
                // exit script after response was created
                exit();
            }
            laterpay_get_logger()->debug( 'RESOURCE:: $hmac and $ts are valid' );
        } else {
            laterpay_get_logger()->error( 'RESOURCE:: empty $hmac or $ts' );
            $response->set_http_response_code( 401 );
            $response->send_response();
            // exit script after response was created
            exit();
        }

        // check token
        if ( ! empty( $lptoken ) ) {
            laterpay_get_logger()->debug( 'RESOURCE:: set token and make redirect' );
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
            // exit script after response was created
            exit();
        }

        if ( ! $client->has_token() ) {
            laterpay_get_logger()->debug( 'RESOURCE:: No token found. Acquiring token' );
            $client->acquire_token();
        }

        if ( ! empty( $auth ) ) {
            laterpay_get_logger()->debug( 'RESOURCE:: Auth param exists. Checking ...' );
            $tokenInstance = new LaterPay_Core_Auth_Hmac( $api_key );
            if ( $tokenInstance->validate_token( $client->get_laterpay_token(), time(), $auth ) ) {
                laterpay_get_logger()->error( 'RESOURCE:: Auth param is valid. Sending file.' );
                $this->send_response( $file, $file_disposition );
                // exit script after response was created
                exit();
            }
            laterpay_get_logger()->debug( 'RESOURCE:: Auth param is not valid.' );
        }

        // check access
        if ( ! empty( $aid ) ) {
            laterpay_get_logger()->debug( 'RESOURCE:: Checking access in API ...' );
            $result = $client->get_access( $aid );
            if ( ! empty( $result ) && isset( $result['articles'][$aid] ) ) {
                $access = $result['articles'][$aid]['access'];
            }
            laterpay_get_logger()->debug( 'RESOURCE:: Checked access', array( 'access' => $access ) );
        }

        // send file
        if ( $access ) {
            laterpay_get_logger()->debug( 'RESOURCE:: Has access - sending file.' );
            $this->send_response( $file, $file_disposition );
            // exit script after response was created
            exit();
        }

        laterpay_get_logger()->error( 'RESOURCE:: Doesn\'t have access. Finish.' );
        $response->set_http_response_code( 403 );
        $response->send_response();
        // exit script after response was created
        exit();
    }

    /**
     * Get the file name of a file with encrypted filename.
     *
     * @param string $file
     *
     * @return string
     */
    protected function get_decrypted_file_name( $file ) {
        $response   = new LaterPay_Core_Response();
        // prepare file for further processing
        $file       = strtr( $file, '-_', '+/' );
        $file       = base64_decode( $file );

        if ( empty( $file ) ) {
            laterpay_get_logger()->error( 'RESOURCE:: cannot decode $file - empty result' );

            $response->set_http_response_code( 500 );
            $response->send_response();
            // exit script after response was created
            exit();
        }

        $cipher = new Crypt_AES();
        $cipher->setKey( SECURE_AUTH_SALT );
        $file   = ABSPATH . $cipher->decrypt( $file );

        return $file;
    }

    /**
     * Send a secured file to the user.
     *
     * @param string      $file
     * @param string|null $disposition
     *
     * @return void
     */
    protected function send_response( $file, $disposition = null ) {
        $response = new LaterPay_Core_Response();

        if ( empty( $disposition ) ) {
            $disposition = self::DEFAULT_FILE_DISPOSITION;
        }

        $file = $this->get_decrypted_file_name( $file );
        if ( ! file_exists( $file ) ) {
            laterpay_get_logger()->error( 'RESOURCE:: file not found', array( 'file' => $file ) );

            $response->set_http_response_code( 404 );
            $response->send_response();
            // exit script after response was created
            exit();
        }

        $filetype = wp_check_filetype( $file );
        $fsize    = filesize( $file );
        $data     = file_get_contents( $file );
        $filename = basename( $file );

        $response->set_header( 'Content-Type', $filetype['type'] );
        $response->set_header( 'Content-Disposition', $disposition . '; filename="' . $filename . '"' );
        $response->set_header( 'Content-Length', $fsize );
        $response->setBody( $data );
        $response->set_http_response_code( 200 );
        $response->send_response();

        laterpay_get_logger()->debug( 'RESOURCE:: file sent. done.', array( 'file' => $file ) );

        // exit script after response was created
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

    /**
     * Remove directory and all included files.
     *
     * @param $path
     */
    public static function delete_directory( $path ) {
        if ( ! @is_dir( $path ) ) {
            return;
        }

        if ( substr( $path, strlen( $path ) - 1, 1 ) != '/' ) {
            $path .= '/';
        }
        $files = glob( $path . '*', GLOB_MARK );
        foreach ( $files as $file ) {
            if ( @is_dir( $file ) ) {
                self::delete_directory( $file );
            } else {
                unlink( $file );
            }
        }
        @rmdir( $path );

        return;
    }
}
