<?php

class LaterPayClient
{

    /**
     * API key
     */
    protected $api_key;
    protected $api_root;
    protected $web_root;
    protected $cp_key;
    protected $lptoken = null;
    protected $token_name;

    /**
     * Constructor for class LaterPayAPICore
     */
    public function __construct( $_args = array() ) {
        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $this->cp_key   = get_option( 'laterpay_live_merchant_id' );
            $this->api_key  = get_option( 'laterpay_live_api_key' );
            $this->api_root = LATERPAY_LIVE_API_URL;
            $this->web_root = LATERPAY_LIVE_WEB_URL;
        } else {
            $this->cp_key   = get_option( 'laterpay_sandbox_merchant_id' );
            $this->api_key  = get_option( 'laterpay_sandbox_api_key' );
            $this->api_root = LATERPAY_SANDBOX_API_URL;
            $this->web_root = LATERPAY_SANDBOX_WEB_URL;
        }

        $this->token_name = LATERPAY_COOKIE_TOKEN_NAME;
        if ( isset( $_COOKIE[$this->token_name] ) ) {
            $this->lptoken = $_COOKIE[$this->token_name];
        }
        foreach ( $_args as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                $this->{$key} = $value;
            }
        }

        LaterPayLogger::debug('LaterPayClient::constructor', array(
                            'api_key'       => $this->api_key,
                            'cp_key'        => $this->cp_key,
                            'lptoken'       => $this->lptoken,
                            'token_name'    => $this->token_name,
                            'api_root'      => $this->api_root,
                            'web_root'      => $this->web_root,
                        )
                    );
    }

    public function get_laterpay_token() {
        return $this->lptoken;
    }

    /**
     * Get API key
     *
     * @return string|null
     */
    public function get_api_key() {
        return $this->api_key;
    }

    /**
     * Get access URL
     *
     * @return string
     */
    private function _get_access_url() {
        return $this->api_root . '/access';
    }

    /**
     * Get add URL
     *
     * @return string
     */
    private function _get_add_url() {
        return $this->api_root . '/add';
    }

    /**
     * Get identify URL
     *
     * @return string
     */
    private function _get_identify_url() {
        $url = $this->api_root . '/identify';

        return $url;
    }

    /**
     * Get token URL
     *
     * @return string
     */
    private function _get_token_url() {
        return $this->api_root . '/gettoken';
    }

    /**
     * Get token redirect URL
     *
     * @param string $return_to URL
     *
     * @return string URL
     */
    public function _get_token_redirect_url( $return_to ) {
        $url    = $this->_get_token_url();
        $params = $this->sign_and_encode(
                        array(
                            'redir' => $return_to,
                            'cp' => $this->cp_key
                        ),
                        $url,
                        LaterPayRequest::GET
                    );
        $url   .= '?' . $params;

        LaterPayLogger::debug('LaterPayClient::_get_token_redirect_url',
                        array(
                            'url'       => $url,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        return $url;
    }

    /**
     * Get identify URL
     *
     * @param string $identify_callback
     *
     * @return string
     */
    public function get_identify_url( $identify_callback = null ) {
        $url = $this->_get_identify_url();

        $data = array( 'cp' => $this->cp_key );
        if ( ! empty( $identify_callback ) ) {
            $data['callback_url'] = $identify_callback;
        }
        $params = $this->sign_and_encode( $data, $url, LaterPayRequest::GET );
        $url .= '?' . $params;

        LaterPayLogger::debug('LaterPayClient::get_identify_url', array(
                            'url'       => $url,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        return $url;
    }

    /**
     * Get iframe API URL
     *
     * @param string  $next_url
     * @param string  $css_url
     * @param string  $forcelang
     * @param boolean $show_greeting
     * @param boolean $show_long_greeting
     * @param boolean $show_login
     * @param boolean $show_signup
     * @param boolean $show_long_signup
     * @param boolean $use_jsevents
     *
     * @return string URL
     */
    public function get_iframe_api_url( $next_url, $css_url = null, $forcelang = null, $show_greeting = false, $show_long_greeting = false, $show_login = false, $show_signup = false, $show_long_signup = false, $use_jsevents = false ) {
        $data = array( 'next' => $next_url );
        $data['cp'] = $this->cp_key;
        if ( ! empty( $forcelang ) ) {
            $data['forcelang'] = $forcelang;
        }
        if ( ! empty( $css_url ) ) {
            $data['css'] = $css_url;
        }
        if ( $use_jsevents ) {
            $data['jsevents'] = '1';
        }
        if ( $show_long_greeting ) {
            if ( ! isset( $data['show'] ) ) {
                $data['show'] = 'gg';
            }
        } elseif ( $show_greeting ) {
            if ( ! isset( $data['show'] ) ) {
                $data['show'] = 'g';
            }
        }
        if ( $show_login ) {
            if ( ! isset( $data['show'] ) ) {
                $data['show'] = 'l';
            }
        }
        if ( $show_long_signup ) {
            if ( ! isset( $data['show'] ) ) {
                $data['show'] = 'ss';
            }
        } elseif ( $show_signup ) {
            if ( ! isset( $data['show'] ) ) {
                $data['show'] = 's';
            }
        }
        $data['xdmprefix'] = substr( uniqid( '', true ), 0, 10 );

        $url    = $this->web_root . '/iframeapi/links';
        $params = $this->sign_and_encode( $data, $url, LaterPayRequest::GET );

        return join( '?', array( $url, $params ) );
    }

    /**
     * Get iframe API balance URL
     *
     * @param string|null $forcelang
     * @deprecated since version 0.9.5
     *
     * @return string url
     */
    public function get_iframe_api_balance_url( $forcelang = null ) {
        $data = array('cp' => $this->cp_key);

        if ( ! empty( $forcelang ) ) {
            $data['forcelang'] = $forcelang;
        }

        $data['xdmprefix'] = substr( uniqid( '', true ), 0, 10 );
        $base_url   = $this->web_root . '/iframeapi/balance';
        $params     = $this->sign_and_encode( $data, $base_url );
        $url        = $base_url . '?' . $params;

        return $url;
    }

    /**
     * Get iframe API balance URL
     *
     * @param string|null $forcelang
     * @deprecated since version 0.9.5
     *
     * @return string url
     */
    public function get_controls_balance_url( $forcelang = null ) {
        $data = array( 'cp' => $this->cp_key );

        if ( ! empty( $forcelang ) ) {
            $data['forcelang'] = $forcelang;
        }

        $data['xdmprefix'] = substr( uniqid( '', true ), 0, 10 );
        $base_url   = $this->web_root . '/controls/balance';
        $params     = $this->sign_and_encode( $data, $base_url );
        $url        = $base_url . '?' . $params;

        return $url;
    }

    protected function get_dialog_api_url( $url ) {
        return $this->web_root . '/dialog-api?url=' . urlencode( $url );
    }

    public function get_login_dialog_url( $next_url, $use_jsevents = false ) {
        if ( $use_jsevents ) {
            $aux = '"&jsevents=1';
        } else {
            $aux = '';
        }
        $url = $this->web_root . '/dialog/login?next=' . urlencode( $next_url ) . $aux . '&cp=' . $this->cp_key;

        return $this->get_dialog_api_url( $url );
    }

    public function get_signup_dialog_url( $next_url, $use_jsevents = false ) {
        if ( $use_jsevents ) {
            $aux = '"&jsevents=1';
        } else {
            $aux = '';
        }
        $url = $this->web_root . '/dialog/login?next=' . urlencode( $next_url ) . $aux . '&cp=' . $this->cp_key;

        return $this->get_dialog_api_url( $url );
    }

    public function get_logout_dialog_url( $next_url, $use_jsevents = false ) {
        if ( $use_jsevents ) {
            $aux = '"&jsevents=1';
        } else {
            $aux = '';
        }
        $url = $this->web_root . '/dialog/logout?next=' . urlencode( $next_url ) . $aux . '&cp=' . $this->cp_key;

        return $this->get_dialog_api_url( $url );
    }

    /**
    * @param string $page_type
    */
    protected function get_web_url( $data, $page_type, $product_key = null, $dialog = true, $use_jsevents = false, $skip_add_to_invoice = false, $transaction_reference = null ) {
        if ( $use_jsevents ) {
            $data['jsevents'] = 1;
        }
        if ( $transaction_reference ) {
            if ( strlen( $transaction_reference ) < 6 ) {
                // throw new Exception('Transaction reference is not unique enough');
            }
            $data['tref'] = $transaction_reference;
        }
        if ( $skip_add_to_invoice ) {
            $data['skip_add_to_invoice'] = 1;
        }

        if ( $dialog ) {
            $prefix = $this->web_root . '/dialog';
        } else {
            $prefix = $this->web_root;
        }
        if ( ! empty($product_key) ) {
            $base_url = join( '/', array( $prefix, $product_key, $page_type ) );
        } else {
            $base_url = join( '/', array( $prefix, $page_type ) );
        }
        $params = $this->sign_and_encode( $data, $base_url, LaterPayRequest::GET );
        $url    = $base_url . '?' . $params;

        LaterPayLogger::debug('LaterPayClient::get_web_url', array(
                            'url'       => $this->get_dialog_api_url( $url ),
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        return $this->get_dialog_api_url( $url );
    }

    public function get_buy_url( $data, $product_key = null, $dialog = true, $use_jsevents = false, $skip_add_to_invoice = false, $transaction_reference = null ) {
        return $this->get_web_url(
            $data,
            'buy',
            $product_key,
            $dialog,
            $use_jsevents,
            $skip_add_to_invoice,
            $transaction_reference
        );
    }

    public function get_add_url( $data, $product_key = null, $dialog = true, $use_jsevents = false, $skip_add_to_invoice = false, $transaction_reference = null ) {
        $data['cp'] = $this->cp_key;

        return $this->get_web_url(
            $data,
            'add',
            $product_key,
            $dialog,
            $use_jsevents,
            $skip_add_to_invoice,
            $transaction_reference
        );
    }

    public function has_token() {
        return ! empty($this->lptoken);
    }

    public function add_metered_access( $article_id, $threshold = 5, $product_key = null ) {
        $params = array(
            'lptoken'    => $this->lptoken,
            'cp'         => $this->cp_key,
            'threshold'  => $threshold,
            'feature'    => 'metered',
            'period'     => 'monthly',
            'article_id' => $article_id,
        );

        if ( ! empty( $product_key ) ) {
            $params['product'] = $product_key;
        }

        $data = $this->make_request( $this->_get_add_url(), $params, LaterPayRequest::POST );

        if ( isset( $data['status'] ) && $data['status'] == 'invalid_token' ) {
            $this->acquire_token();
        }
    }

    public function get_metered_access( $article_ids, $threshold = 5, $product_key = null ) {
        if ( ! is_array( $article_ids ) ) {
            $article_ids = array( $article_ids );
        }

        $params = array(
            'lptoken'    => $this->lptoken,
            'cp'         => $this->cp_key,
            'article_id' => $article_ids,
            'feature'    => 'metered',
            'threshold'  => $threshold,
            'period'     => 'monthly',
        );

        if ( ! empty( $product_key ) ) {
            $params['product'] = $product_key;
        }

        $data = $this->make_request( $this->_get_access_url(), $params );
        if ( isset( $data['subs'] ) ) {
            $subs = $data['subs'];
        } else {
            $subs = array();
        }

        if ( isset( $data['status'] ) && $data['status'] == 'invalid_token' ) {
            $this->acquire_token();

            return array();
        }
        if ( isset( $data['status'] ) && $data['status'] != 'ok' ) {
            return array();
        }
        if ( isset( $data['exceeded'] ) ) {
            $exceeded = $data['exceeded'];
        } else {
            $exceeded = false;
        }

        return array( $data['articles'], $exceeded, $subs );
    }

    /**
     * Preprocess parameters
     *
     * @param array  $params array params
     * @param string $url
     * @param string $method http method
     *
     * @return string query params
     */
    public function sign_and_encode( $params, $url, $method = LaterPayRequest::GET ) {
        LaterPayLogger::debug('LaterPayClient::sign_and_encode', array($params, $url, $method));

        return LaterPayClient_Signing::sign_and_encode( $this->api_key, $params, $url, $method );
    }

    /**
     * Get response from access for post
     *
     * @param array  $article_ids array with posts ids
     * @param string $product_key array with posts ids
     *
     * @return string json string response
     */
    public function get_access( $article_ids, $product_key = null ) {
        LaterPayLogger::debug('LaterPayClient::get_access', array('checking access', $article_ids));

        if ( ! is_array( $article_ids ) ) {
            $article_ids = array( $article_ids );
        }

        $params = array(
            'lptoken'    => $this->lptoken,
            'cp'         => $this->cp_key,
            'article_id' => $article_ids
        );
        if ( ! empty( $product_key ) ) {
            $params['product'] = $product_key;
        }
        $data = $this->make_request( $this->_get_access_url(), $params );
        $allowed_statuses = array( 'ok', 'invalid_token', 'connection_error' );

        if ( ! in_array( $data['status'], $allowed_statuses ) ) {
            LaterPayLogger::error( 'get_access::invalid status', array( 'result' => $data ) );
        }

        LaterPayLogger::debug( 'LaterPayClient::get_access', array(
                            'params'    => $params,
                            'result'    => $data,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        return $data;
    }

    /**
     * Update token
     */
    public function acquire_token() {
        $link = $this->_get_token_redirect_url( LaterPayRequestHelper::get_current_url() );

        LaterPayLogger::debug( 'LaterPayClient::acquire_token', array(
                            'link'      => $link,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        header( 'Location: ' . $link );
        exit();
    }

    /**
     * Set token to cookie
     *
     * @param string $token    token key
     * @param bool   $redirect redirect after set token
     */
    public function set_token( $token, $redirect = false ) {
        LaterPayLogger::debug( 'LaterPayClient::set_token', array(
                            'token'     => $token,
                            'redirect'  => $redirect,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        $this->lptoken = $token;
        setcookie( $this->token_name, $token, strtotime( '+1 day' ), '/' );
        if ( $redirect ) {
            header( 'Location: ' . LaterPayRequestHelper::get_current_url() );
            die;
        }
    }

    public function delete_token() {
        LaterPayLogger::debug( 'LaterPayClient::delete_token', array(
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        setcookie( $this->token_name, '', time() - 100000, '/' );
        unset( $_COOKIE[$this->token_name] );
        $this->token = null;
    }

    /**
     * Send request to $url
     *
     * @param string $url    URL to send request to
     * @param array  $params
     * @param string $method
     *
     * @return object data response
     */
    protected function make_request( $url, $params = array(), $method = LaterPayRequest::GET ) {
        LaterPayLogger::debug( 'LaterPayClient::make_request', array(
                            'url'       => $url,
                            'params'    => $params,
                            'post'      => $post,
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        // build the request
        $params = $this->sign_and_encode( $params, $url, $method );
        $headers = array(
            'X-LP-APIVersion' => 2,
            'User-Agent'      => 'LaterPay Client - PHP - v0.2',
        );
        try {
            if ( $method == LaterPayRequest::POST ) {
                $requestsResponse = wp_remote_retrieve_body(
                                        wp_remote_post(
                                            $url,
                                            array(
                                                'headers'   => $headers,
                                                'body'      => $params,
                                                'timeout'   => 30,
                                            )
                                        )
                                    );
            } else {
                $url .= '?' . $params;
                $requestsResponse = wp_remote_retrieve_body(
                                        wp_remote_get(
                                            $url,
                                            array(
                                                'headers' => $headers,
                                                'timeout' => 30,
                                            )
                                        )
                                    );
            }

            LaterPayLogger::debug( 'LaterPayClient::make_request', array( $requestsResponse ) );

            $response = Zend_Json::decode( $requestsResponse, Zend_Json::TYPE_ARRAY );
            if ( $response['status'] == 'invalid_token' ) {
                $this->delete_token();
            }
            if ( array_key_exists( 'new_token', $response ) ) {
                $this->set_token( $response['new_token'] );
            }
        } catch ( Zend_Json_Exception $e ) {
            LaterPayLogger::error( 'LaterPayClient::make_request', array(
                                'message'   => $e->getMessage(),
                                'url'       => $url,
                                'params'    => $params,
                                'post'      => $post,
                            )
                        );

            $response = array( 'status' => 'unexpected_error' );
        } catch ( Exception $e ) {
            LaterPayLogger::error( 'LaterPayClient::make_request', array(
                                'message'   => $e->getMessage(),
                                'url'       => $url,
                                'params'    => $params,
                                'post'      => $post
                            )
                        );

            $response = array( 'status' => 'connection_error' );
        }

        LaterPayLogger::debug( 'LaterPayClient::make_request', array( 'response' => $response ) );

        return $response;
    }

}
