<?php

class LaterPay_Client
{

    /**
     * API key
     * @var string
     */
    protected $api_key;

	/**	 *
	 * @var string
	 */
	protected $api_root;

	/**
	 * @var string
	 */
	protected $web_root;

	/**
	 * @var string
	 */
	protected $cp_key;

	/**
	 * @var null|string
	 */
	protected $lptoken = null;

	/**
	 * @var string
	 */
	protected $token_name;

	/**
	 * @param   LaterPay_Model_Config $config
	 * @return  LaterPay_Client
	 */
    public function __construct( LaterPay_Model_Config $config ) {

	    $this->config = $config;

        if ( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $this->cp_key   = get_option( 'laterpay_live_merchant_id' );
            $this->api_key  = get_option( 'laterpay_live_api_key' );
            $this->api_root = $this->config->get( 'api.live_url' );
            $this->web_root = $this->config->get( 'api.live_web_url' );
        } else {
            $this->cp_key   = get_option( 'laterpay_sandbox_merchant_id' );
            $this->api_key  = get_option( 'laterpay_sandbox_api_key' );
            $this->api_root = $this->config->get( 'api.sandbox_url' );
            $this->web_root = $this->config->get( 'api.sandbox_web_url' );
        }

        $this->token_name = $this->config->get( 'api.token_name' );
        if ( isset( $_COOKIE[$this->token_name] ) ) {
            $this->lptoken = $_COOKIE[$this->token_name];
        }

        LaterPay_Core_Logger::debug( 'LaterPay_Client::constructor', array(
                            'api_key'       => $this->api_key,
                            'cp_key'        => $this->cp_key,
                            'lptoken'       => $this->lptoken,
                            'token_name'    => $this->token_name,
                            'api_root'      => $this->api_root,
                            'web_root'      => $this->web_root,
                        )
                    );
    }

	/**
	 *
	 * @return null|string
	 */
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
     * @param   string $return_to URL
     *
     * @return  string $url
     */
    public function _get_token_redirect_url( $return_to ) {
        $url    = $this->_get_token_url();
        $params = $this->sign_and_encode(
                        array(
                            'redir' => $return_to,
                            'cp'    => $this->cp_key,
                        ),
                        $url,
                        LaterPay_Core_Request::GET
                    );
        $url   .= '?' . $params;

        LaterPay_Core_Logger::debug( 'LaterPay_Client::_get_token_redirect_url',
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
     * @param   string $identify_callback
     *
     * @return  string
     */
    public function get_identify_url( $identify_callback = null ) {
        $url = $this->_get_identify_url();

        $data = array( 'cp' => $this->cp_key );
        if ( ! empty( $identify_callback ) ) {
            $data['callback_url'] = $identify_callback;
        }
        $params = $this->sign_and_encode( $data, $url, LaterPay_Core_Request::GET );
        $url .= '?' . $params;

        LaterPay_Core_Logger::debug('LaterPay_Client::get_identify_url', array(
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
     * TODO: 1 array as param ...
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
        $params = $this->sign_and_encode( $data, $url, LaterPay_Core_Request::GET );

        return join( '?', array( $url, $params ) );
    }

    /**
     * Get iframe API balance URL.
     *
     * @deprecated since version 0.9.5
     *
     * @param   string|null $forcelang
     *
     * @return  string $url
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
     * Get iframe API balance URL.
     *
     * @deprecated since version 0.9.5
     *
     * @param   string|null $forcelang
     *
     * @return  string $url
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

	/**
	 *
	 * @param   string $url
     *
	 * @return  string
	 */
	protected function get_dialog_api_url( $url ) {
        return $this->web_root . '/dialog-api?url=' . urlencode( $url );
    }

	/**
     * Get URL for the LaterPay login form.
     *
	 * @param   string $next_url
	 * @param   boolean$use_jsevents
     *
	 * @return  string $url
	 */
	public function get_login_dialog_url( $next_url, $use_jsevents = false ) {
        if ( $use_jsevents ) {
            $aux = '"&jsevents=1';
        } else {
            $aux = '';
        }
        $url = $this->web_root . '/dialog/login?next=' . urlencode( $next_url ) . $aux . '&cp=' . $this->cp_key;

        return $this->get_dialog_api_url( $url );
    }

	/**
     * Get URL for the LaterPay signup form.
     *
	 * @param   string $next_url
	 * @param   boolean$use_jsevents
     *
	 * @return  string $url
	 */
    public function get_signup_dialog_url( $next_url, $use_jsevents = false ) {
        if ( $use_jsevents ) {
            $aux = '"&jsevents=1';
        } else {
            $aux = '';
        }
        $url = $this->web_root . '/dialog/login?next=' . urlencode( $next_url ) . $aux . '&cp=' . $this->cp_key;

        return $this->get_dialog_api_url( $url );
    }

	/**
     * Get URL for logging out a user from LaterPay.
     *
	 * @param   string $next_url
	 * @param   boolean$use_jsevents
     *
	 * @return  string $url
	 */
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
	 *
	 * TODO: moving params to an single param-array
	 *
	 * @param   array $data
	 * @param   string $page_type
	 * @param   null|string $product_key
	 * @param   boolean$dialog
	 * @param   boolean$use_jsevents
	 * @param   boolean$skip_add_to_invoice
	 * @param   null|string $transaction_reference
	 *
	 * @return  string $url
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
        $params = $this->sign_and_encode( $data, $base_url, LaterPay_Core_Request::GET );
        $url    = $base_url . '?' . $params;

        LaterPay_Core_Logger::debug('LaterPay_Client::get_web_url', array(
                            'url'       => $this->get_dialog_api_url( $url ),
                            'api_key'   => $this->api_key,
                            'cp_key'    => $this->cp_key,
                            'lptoken'   => $this->lptoken,
                        )
                    );

        return $this->get_dialog_api_url( $url );
    }

	/**
	 *
	 * TODO: moving params to an single param-array
	 *
	 * @param   array $data
	 * @param   null|string $product_key
	 * @param   boolean$dialog
	 * @param   boolean$use_jsevents
	 * @param   boolean$skip_add_to_invoice
	 * @param   null|string $transaction_reference
	 *
	 * @return  string $url
	 */
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

	/**
	 * TODO: moving params to an single param-array
	 *
	 * @param   array $data
	 * @param   string|null $product_key
	 * @param   boolean$dialog
	 * @param   boolean$use_jsevents
	 * @param   boolean$skip_add_to_invoice
	 * @param   null $transaction_reference
	 *
	 * @return  string $url
	 */
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

	/**
	 *
	 * @return bool
	 */
	public function has_token() {
        return ! empty( $this->lptoken );
    }

	/**
	 *
	 * @param   int $article_id
	 * @param   int $threshold
	 * @param   null|string $product_key
	 *
	 * @return  void
	 */
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

        $data = $this->make_request( $this->_get_add_url(), $params, LaterPay_Core_Request::POST );

        if ( isset( $data['status'] ) && $data['status'] == 'invalid_token' ) {
            $this->acquire_token();
        }
    }

	/**
	 *
	 * @param   int|array $article_ids
	 * @param   int  $threshold
	 * @param   null|string $product_key
     *
	 * @return array
	 */
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
     * Sign and encode all request parameters.
     *
     * @param   array  $params array params
     * @param   string $url
     * @param   string $method HTTP method
     *
     * @return  string query params
     */
    public function sign_and_encode( $params, $url, $method = LaterPay_Core_Request::GET ) {
        LaterPay_Core_Logger::debug('LaterPay_Client::sign_and_encode', array($params, $url, $method));

        return LaterPay_Client_Signing::sign_and_encode( $this->api_key, $params, $url, $method );
    }

    /**
     * Check if user has access to a given item / given array of items.
     *
     * @param   array       $article_ids array with posts ids
     * @param   null|string $product_key array with posts ids
     *
     * @return  string json string response
     */
    public function get_access( $article_ids, $product_key = null ) {

        LaterPay_Core_Logger::debug('LaterPay_Client::get_access', array('checking access', $article_ids));

        if ( ! is_array( $article_ids ) ) {
            $article_ids = array( $article_ids );
        }

        $params = array(
            'lptoken'    => $this->lptoken,
            'cp'         => $this->cp_key,
            'article_id' => $article_ids,
        );
        if ( ! empty( $product_key ) ) {
            $params['product'] = $product_key;
        }
        $data = $this->make_request( $this->_get_access_url(), $params );
        $allowed_statuses = array( 'ok', 'invalid_token', 'connection_error' );

        if ( ! in_array( $data['status'], $allowed_statuses ) ) {
            LaterPay_Core_Logger::error( 'get_access::invalid status', array( 'result' => $data ) );
        }

        LaterPay_Core_Logger::debug( 'LaterPay_Client::get_access', array(
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
     *
     * @return  void
     */
    public function acquire_token() {

        $link = LaterPay_Helper_Request::get_current_url();
        $link = $this->_get_token_redirect_url( $link );

        $context = array(
            'link'      => $link,
            'api_key'   => $this->api_key,
            'cp_key'    => $this->cp_key,
            'lptoken'   => $this->lptoken,
        );

        LaterPay_Core_Logger::debug(
            __METHOD__,
            $context
        );

        wp_redirect( $link );
        exit;
    }

    /**
     * Set cookie with token.
     *
     * @param   string  $token token key
     * @return  void
     */
    public function set_token( $token, $redirect = false ) {
        LaterPay_Core_Logger::debug( 'LaterPay_Client::set_token', array(
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
            wp_redirect(LaterPay_Helper_Request::get_current_url());
            exit();
        }
    }

	/**
	 * Delete the token from cookies.
     *
	 * @return  void
	 */
	public function delete_token() {
        LaterPay_Core_Logger::debug( 'LaterPay_Client::delete_token', array(
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
     * Send request to $url.
     *
     * @param   string $url    URL to send request to
     * @param   array  $params
     * @param   string $method
     *
     * @return  array $response
     */
    protected function make_request( $url, $params = array(), $method = LaterPay_Core_Request::GET ) {
	    LaterPay_Core_Logger::debug( 'LaterPay_Client::make_request', array(
                            'url'       => $url,
                            'params'    => $params,
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
            if ( $method == LaterPay_Core_Request::POST ) {
                $raw_response = wp_remote_post(
                                    $url,
                                    array(
                                        'headers'   => $headers,
                                        'body'      => $params,
                                        'timeout'   => 30,
                                    )
                                );
                $raw_response_body = wp_remote_retrieve_body( $raw_response );
            } else {
                $url .= '?' . $params;
                $raw_response = wp_remote_get(
                                    $url,
                                    array(
                                        'headers'   => $headers,
                                        'timeout'   => 30,
                                    )
                                );
                $raw_response_body = wp_remote_retrieve_body( $raw_response );
            }

            LaterPay_Core_Logger::debug( 'LaterPay_Client::make_request', array( $raw_response_body ) );

	        $error_code = wp_remote_retrieve_response_code( $raw_response );
	        if ( $error_code > 400 ) {
		        throw new Exception(
			        wp_remote_retrieve_response_message( $raw_response ),
			        $error_code
		        );
	        }

            $response = json_decode( $raw_response_body, true );

            if ( $response['status'] == 'invalid_token' ) {
                $this->delete_token();
            }
            if ( array_key_exists( 'new_token', $response ) ) {
                $this->set_token( $response['new_token'] );
            }
        } catch ( Exception $e ) {
            LaterPay_Core_Logger::error( 'LaterPay_Client::make_request', array(
                                'message'   => $e->getMessage(),
                                'url'       => $url,
                                'params'    => $params,
                            )
                        );

            $response = array( 'status' => 'connection_error' );
        }

        LaterPay_Core_Logger::debug( 'LaterPay_Client::make_request', array( 'response' => $response ) );

        return $response;
    }

}
