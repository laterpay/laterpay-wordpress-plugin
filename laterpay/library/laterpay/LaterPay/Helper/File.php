<?php

class LaterPay_Helper_File
{

	/**
	 * Regex to detect urls
     *
	 * @var string
	 */
	const URL_REGEX_PATTERN = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

	/**
	 * Path to script file
     *
	 * @var string
	 */
	const SCRIPT_PATH = 'laterpay/scripts/laterpay-get-script.php';

	/**
	 * File types protected against direct download from paid posts without purchasing
	 * @var string
	 */
	protected static $protected_file_types = '3gpp|aac|avi|divx|doc|docx|epup|flv|gif|jpeg|jpg|mobi|mov|mp3|mp4|mp4|mpg|ogg|pdf|png|ppt|pptx|rar|rtf|tif|tiff|txt|wav|wmv|xls|xlsx|zip';

	/**
	 *
	 * @param   int    $post_id
	 * @param   string $url
	 * @param   bool   $use_auth
	 *
	 * @return  string $url
	 */
	public static function get_encrypted_resource_url( $post_id, $url, $use_auth ) {
        $new_url            = plugins_url( self::SCRIPT_PATH );
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

		$config = laterpay_get_plugin_config();
        $client = new LaterPay_Core_Client( $config );
        $params = array(
            'aid'   => $post_id,
            'file'  => $file,
            'ext'   => '.' . $ext,
        );
        if ( $use_auth ) {
            $client         = new LaterPay_Core_Client( $config );
            $tokenInstance  = new LaterPay_Core_Auth_Hmac( $client->get_api_key() );
            $params['auth'] = $tokenInstance->sign( $client->get_laterpay_token() );
        }

        return $new_url . '?' . $client->sign_and_encode( $params, $new_url );
    }

	/**
	 * getting the encrypted content by a given post_id
	 *
	 * @param   int $post_id
	 * @param   string $content
	 * @param   string $use_auth
	 *
	 * @return  string $content
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
