<?php

class LaterPayFileHelper {

    const URL_REGEX_PATTERN     = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
    const SCRIPT_PATH           = 'laterpay/scripts/lp-get.php';

    /**
    * @param null|string $file
    */
    public static function getFileMimeType( $file ) {
        $type = '';
        if ( function_exists('finfo_file') ) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif ( function_exists('mime_content_type') ) {
            $type = mime_content_type($file);
        }

        return $type;
    }

    public static function getEncryptedResourceUrl( $post_id, $url, $use_auth ) {
        $new_url            = plugins_url(self::SCRIPT_PATH);
        $blog_url_parts     = parse_url(get_bloginfo('wpurl'));
        $resource_url_parts = parse_url($url);
        if ( $blog_url_parts['host'] != $resource_url_parts['host'] ) {
            return $url;
        }
        $uri = $resource_url_parts['path'];
        if ( !preg_match('/.*\.(' . LATERPAY_PROTECTED_FILE_TYPES . ')/i', $uri) ) {
            return $url;
        }
        $cipher = new Crypt_AES();
        $cipher->setKey(LATERPAY_RESOURCE_ENCRYPTION_KEY);
        $file = base64_encode($cipher->encrypt($uri));

        $client = new LaterPayClient();
        $params = array(
            'aid'   => $post_id,
            'file'  => $file,
        );
        if ( $use_auth ) {
            $client         = new LaterPayClient();
            $tokenInstance  = new LaterPayAuth_Hmac($client->getApiKey());
            $params['auth'] = $tokenInstance->sign($client->getLpToken());
        }

        return $new_url . '?' . $client->signAndEncode($params, $new_url);
    }

    public static function getEncryptedContent( $post_id, $content, $use_auth ) {
        // encrypt links to the resources
        $urls       = array();
        $matches    = array();
        preg_match_all(self::URL_REGEX_PATTERN, $content, $matches);
        if ( isset($matches[0]) ) {
            $urls = $matches[0];
        }
        $search     = array();
        $replace    = array();

        foreach ( $urls as $resource_url ) {
            $new_url = self::getEncryptedResourceUrl($post_id, $resource_url, $use_auth);
            if ( $new_url != $resource_url ) {
                $search[] = $resource_url;
                $replace[] = $new_url;
            }
        }
        $content = str_replace($search, $replace, $content);

        return $content;
    }

}
