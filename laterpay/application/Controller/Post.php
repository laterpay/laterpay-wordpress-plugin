<?php

class LaterPay_Controller_Post extends LaterPay_Controller_Abstract
{

    /**
     * Contains the access state for all loaded posts.
     * @var array
     */
    protected $access = array();

    /**
     * Ajax method to get the cached article.
     * Required, because there could be a price change in LaterPay and we always need the current article price.
     *
     * @wp-hook wp_ajax_laterpay_post_load_purchased_content, wp_ajax_nopriv_laterpay_post_load_purchased_content
     *
     * @return void
     */
    public function ajax_load_purchased_content() {

        if( !isset( $_GET[ 'action' ] ) || $_GET[ 'action' ] !== 'laterpay_post_load_purchased_content' ){
            exit;
        }

        if( !isset( $_GET[ 'nonce' ] ) || !wp_verify_nonce( $_GET[ 'nonce' ], $_GET[ 'action' ] ) ){
            exit;
        }

        if ( ! isset( $_GET[ 'post_id' ] ) ) {
            return;
        }

        $post_id        = absint( $_GET[ 'post_id' ] );
        $post           = get_post( $post_id );

        if ( $post === null ) {
            exit;
        }

        if ( ! $this->is_enabled_post_type( $post->post_type ) ) {
            exit;
        }

        if ( !is_user_logged_in() && ! $this->has_access_to_post( $post ) ) {
            // check for post access only for not logged in users
            exit;
        }
        else if ( is_user_logged_in() && LaterPay_Helper_User::preview_post_as_visitor( $post ) ){
            // if user is logged in and "preview_as_visitor" is activated, return
           exit;
        }

        $content        = apply_filters( 'the_content', $post->post_content );
        $content        = str_replace( ']]>', ']]&gt;', $content );
        echo $content;
        exit;
    }

    /**
     * Save purchase in purchase history.
     *
     * @wp-hook template_redirect
     * @return  void
     */
    public function buy_post() {
        if ( ! isset( $_GET[ 'buy' ] ) ) {
            return;
        }

        // data to create the URL and hash-check
        $url_data = array(
            'post_id'     => $_GET[ 'post_id' ],
            'id_currency' => $_GET[ 'id_currency' ],
            'price'       => $_GET[ 'price' ],
            'date'        => $_GET[ 'date' ],
            'buy'         => $_GET[ 'buy' ],
            'ip'          => $_GET[ 'ip' ],
        );
        $url    = $this->get_after_purchase_redirect_url( $url_data );
        $hash   = $this->get_hash_by_url( $url );
        // update lptoken if we got it
        if ( isset( $_GET['lptoken'] ) ) {
            $client = new LaterPay_Client( $this->config );
            $client->set_token( $_GET['lptoken'] );
        }
        // check if the parameters of $_GET are valid and not manipulated
        if ( $hash === $_GET[ 'hash' ] ) {
            $data = array(
                'post_id'       => $_GET[ 'post_id' ],
                'id_currency'   => $_GET[ 'id_currency' ],
                'price'         => $_GET[ 'price' ],
                'date'          => $_GET[ 'date' ],
                'ip'            => $_GET[ 'ip' ],
                'hash'          => $_GET[ 'hash' ],
            );

            $payment_history_model = new LaterPay_Model_Payments_History();
            $payment_history_model->set_payment_history( $data );
        }

        $post_id        = absint( $_GET[ 'post_id' ] );
        $redirect_url   = get_permalink( $post_id );

        wp_redirect( $redirect_url );
        exit;
    }

    /**
     * Update incorrect token or create one, if it doesn't exist.
     *
     * @wp-hook template_redirect
     *
     * @return void
     */
    public function create_token() {
        $browser_supports_cookies   = LaterPay_Helper_Browser::browser_supports_cookies();
        $browser_is_crawler         = LaterPay_Helper_Browser::is_crawler();

        $context = array(
            'support_cookies'   => $browser_supports_cookies,
            'is_crawler'        => $browser_is_crawler
        );

        LaterPay_Core_Logger::debug(
            __METHOD__,
            $context
        );

        if ( ! $browser_supports_cookies || $browser_is_crawler ) {
            return;
        }

        $laterpay_client = new LaterPay_Client( $this->config );
        if ( isset( $_GET[ 'lptoken' ] ) ) {
            $laterpay_client->set_token( $_GET['lptoken'], true );
        }

        if ( ! $laterpay_client->has_token() ) {
            $laterpay_client->acquire_token();
        }
    }

    /**
     * Prefetch the post access for posts in the loop.
     *
     * In archives or by using the WP_Query-Class, we can prefetch the access
     * for all posts in a single request instead of requesting every single post.
     *
     * @wp-hook the_posts
     *
     * @param array $posts
     *
     * @return array $posts
     */
    public function prefetch_post_access( $posts ) {
        $post_ids = array();
        foreach ( $posts as $post ) {
            $price  = LaterPay_Helper_Pricing::get_post_price( $post->ID );
            if ( $price != 0 ) {
                $post_ids[] = $post->ID;
            }
        }

        if ( empty( $post_ids ) ) {
            return $posts;
        }

        $laterpay_client    = new LaterPay_Client( $this->config );
        $access_result      = $laterpay_client->get_access( $post_ids );

        if ( empty( $access_result ) || ! array_key_exists( 'articles', $access_result ) ) {
            return $posts;
        }

        foreach ( $access_result[ 'articles' ] as $post_id => $state ) {
            $this->access[ $post_id ] = (bool) $state[ 'access' ];
        }

        return $posts;
    }

    /**
     * Checks if user has access to a post.
     *
     * @param   WP_Post $post
     *
     * @return  boolean success
     */
    public function has_access_to_post( WP_Post $post ) {
        $post_id = $post->ID;

        LaterPay_Core_Logger::debug(
            __METHOD__,
            array(
                'post' => $post
            )
        );

        // access already loaded before
        if ( array_key_exists( $post_id, $this->access ) ) {
            return (bool) $this->access[ $post_id ];
        }

        $price  = LaterPay_Helper_Pricing::get_post_price( $post->ID );

        if ( $price != 0 ) {
            $laterpay_client    = new LaterPay_Client( $this->config );
            $result             = $laterpay_client->get_access( array( $post_id ) );

            if ( empty( $result ) || ! array_key_exists( 'articles', $result ) ) {
                return false;
            }

            if ( array_key_exists( $post_id, $result[ 'articles' ] ) ) {
                $access = (bool) $result[ 'articles' ][ $post_id ][ 'access' ];
                $this->access[ $post_id ] = $access;
                return $access;
            }

        }

        return false;
    }

    /**
     * Get the LaterPay purchase link for a post.
     *
     * @param int $post_id
     *
     * @return string url || empty string if something went wrong
     */
    public function get_laterpay_purchase_link( $post_id ) {
        $post = get_post( $post_id );
        if ( $post === null ) {
            return '';
        }

        // re-set the post_id
        $post_id    = $post->ID;

        $currency   = get_option( 'laterpay_currency' );
        $price      = LaterPay_Helper_Pricing::get_post_price( $post_id );

        $currency_model = new LaterPay_Model_Currency();
        $client         = new LaterPay_Client( $this->config );

        // data to register purchase after redirect from LaterPay
        $url_params = array(
            'post_id'     => $post_id,
            'id_currency' => $currency_model->get_currency_id_by_iso4217_code( $currency ),
            'price'       => $price,
            'date'        => time(),
            'buy'         => 'true',
            'ip'          => ip2long( $_SERVER['REMOTE_ADDR'] ),
        );
        $url    = $this->get_after_purchase_redirect_url( $url_params );
        $hash   = $this->get_hash_by_url( $url );

        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => $post_id,
            'purchase_date' => time() . '000', // fix for PHP 32bit
            'pricing'       => $currency . ( $price * 100 ),
            'vat'           => $this->config->get( 'currency.default_vat' ),
            'url'           => $url . '&hash=' . $hash,
            'title'         => $post->post_title,
        );

        return $client->get_add_url( $params );
    }

    /**
     * Return the URL hash for a given URL.
     *
     * @param   string $url
     *
     * @return  string $hash
     */
    protected function get_hash_by_url( $url ) {
        return md5( md5( $url ) . wp_salt() );
    }

    /**
     * Generate the URL to which the user is redirected to after buying a given post.
     *
     * @param array $data
     *
     * @return String $url
     */
    protected function get_after_purchase_redirect_url( array $data ) {
        $url = get_permalink( $data[ 'post_id' ] );

        if ( ! $url ) {
            LaterPay_Core_Logger::error(
                __METHOD__ . ' could not find an URL for the given post_id',
                array( 'data' => $data )
            );
            return $url;
        }

        $url = add_query_arg( $data, $url );

        return $url;
    }

    /**
     * Helper function to check if LaterPay is enabled for the given post type.
     *
     * @param   string $post_type
     *
     * @return  bool true|false
     */
    protected function is_enabled_post_type( $post_type ) {
        if ( ! in_array( $post_type, $this->config->get( 'content.allowed_post_types' ) ) ) {
            return false;
        }
        return true;
    }

    /**
     * Check if current page is login page.
     *
     * @return boolean
     */
    public static function is_login_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
    }

    /**
     * Check if current page is cron page.
     *
     * @return boolean
     */
    public static function is_cron_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-cron.php' ) );
    }

    /**
     * Callback to generate a LaterPay purchase button within the theme that can be freely positioned.
     * When doing this, you should set config option 'content.show_purchase_button' to FALSE to disable the default
     * rendering of a purchase button at the beginning of the post content, thus avoiding multiple purchase buttons
     * on the post page.
     *
     * @wp-hook laterpay_purchase_button
     *
     * @return  void
     */
    public function the_purchase_button() {
        // check if the current post is purchasable
        if ( LaterPay_Helper_Pricing::is_post_purchasable() ) {
            return;
        }

        $post = get_post();

        // check if the current post was already purchased
        if ( ! $this->has_access_to_post( $post ) ) {
            return;
        }

        $view_args = array(
            'post_id'                   => $post->ID,
            'link'                      => $this->get_laterpay_purchase_link( $post->ID ),
            'currency'                  => get_option( 'laterpay_currency' ),
            'price'                     => LaterPay_Helper_Pricing::get_post_price( $post->ID ),
            'preview_post_as_visitor'   => LaterPay_Helper_User::preview_post_as_visitor( $post ),
        );

        $this->assign( 'laterpay', $view_args );

        echo $this->get_text_view( 'frontend/partials/post/purchase_button' );
    }

    /**
     * Modify the post content of paid posts.
     *
     * Depending on the configuration, the content of paid posts is modified and several elements are added to the content:
     * If the user is an admin, a statistics pane with performance data for the current post is shown.
     * LaterPay purchase button is shown before the content.
     * Depending on the settings in the appearance tab, only the teaser content or the teaser content plus an excerpt of
     * the full content is returned for user who have not bought the post.
     * A LaterPay purchase link or a LaterPay purchase button is shown after the content.
     *
     * @wp-hook the_content
     *
     * @param string $content
     *
     * @return string $content
     */
    public function modify_post_content( $content ) {

        $post = get_post();
        if ( $post === null ) {
            return $content;
        }
        $post_id = $post->ID;

        if ( ! $this->is_enabled_post_type( $post->post_type ) ) {
            return $content;
        }

        // get pricing data
        $currency   = get_option( 'laterpay_currency' );
        $price      = LaterPay_Helper_Pricing::get_post_price( $post_id );

        // No price found for this post? return the content
        if ( $price == 0 ) {
            return $content;
        }

        // get purchase link
        $purchase_link = $this->get_laterpay_purchase_link( $post_id );

        // teaser content
        $teaser_content = get_post_meta( $post_id, 'laterpay_post_teaser', true );

        // output states
        $teaser_content_only        = get_option( 'laterpay_teaser_content_only' );
        $user_can_read_statistic    = LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id );
        $preview_post_as_visitor    = LaterPay_Helper_User::preview_post_as_visitor( $post );

        // caching and ajax
        $caching_is_active              = (bool) $this->config->get( 'caching.compatible_mode' );
        $is_ajax_and_caching_is_active  = defined( 'DOING_AJAX' ) && DOING_AJAX && $caching_is_active;

        // check if user has access to content (because he already bought it)
        $access = $this->has_access_to_post( $post );

        // if user can read the statistics, we can switch to 'admin' mode and have to load the correct content
        if ( $user_can_read_statistic ) {
            $access = true;
        }

        // encrypt files contained in premium posts
        $content = LaterPay_Helper_File::get_encrypted_content( $post_id, $content, $access );

        // assign all required vars to the view templates
        $view_args = array(
            'post_id'                   => $post_id,
            'content'                   => $content,
            'teaser_content'            => $teaser_content,
            'teaser_content_only'       => $teaser_content_only,
            'currency'                  => $currency,
            'price'                     => $price,
            'link'                      => $purchase_link,
            'preview_post_as_visitor'   => $preview_post_as_visitor,
        );
        $this->assign( 'laterpay', $view_args );

        // start collecting the output
        $html = '';

        // return the teaser content on non-singular pages (archive, feed, tax, author, search, ...)
        if ( ! is_singular() ) {
            return $this->get_text_view( 'frontend/partials/post/teaser' );
        }

        /**
         * return the full encrypted content, if ..
         * ..the post was bought by user
         * ..logged_in_user does not preview the post as visitor
         * ..caching is not activated or caching is activated and content is loaded via ajax request
         */
        if ( $access && ! $preview_post_as_visitor && ( ! $caching_is_active || $is_ajax_and_caching_is_active ) ) {
            return $content;
        }

        // add a purchase button as very first element of the content
        if ( (bool) $this->config->get( 'content.show_purchase_button' ) ) {
            $html .= '<div class="clearfix">';
            $html .= $this->get_text_view( 'frontend/partials/post/purchase_button' );
            $html .= '</div>';
        }

        // add the the teaser content
        $html .= $this->get_text_view( 'frontend/partials/post/teaser' );

        if ( $teaser_content_only ) {
            // add teaser content plus a purchase link after the teaser content
            $html .= $this->get_text_view( 'frontend/partials/post/purchase_link' );
        } else {
            // add excerpt of full content, covered by an overlay with a purchase button
            $html .= $this->get_text_view( 'frontend/partials/post/overlay_with_purchase_button' );
        }

        if ( $caching_is_active ) {
            // if caching is enabled, we've to wrap the teaser in a div to replace it when the post is purchased
            return '<div id="laterpay-cache-wrapper">' . $html . '</div>';
        }

        return $html;

    }

    /**
     * Load the LaterPay identify iframe in the footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function modify_footer() {
        if ( ! is_singular() || ! LaterPay_Helper_Pricing::is_post_purchasable() ) {
            return;
        }

        $laterpay_client    = new LaterPay_Client( $this->config );
        $identify_link      = $laterpay_client->get_identify_url();

        $this->assign( 'post_id',       get_the_ID() );
        $this->assign( 'identify_link', $identify_link );

        echo $this->get_text_view( 'frontend/partials/identify_iframe' );
    }

    /**
     * Load LaterPay stylesheets.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_stylesheets() {
        wp_register_style(
            'laterpay-post-view',
            $this->config->css_url . 'laterpay-post-view.css',
            array(),
            $this->config->version
        );
        wp_register_style(
            'laterpay-dialogs',
            'https://static.sandbox.laterpaytest.net/webshell_static/client/1.0.0/laterpay-dialog/css/dialog.css'
        );

        // always enqueue 'laterpay-post-view' to ensure that shortcode [laterpay_premium_download] has styling
        wp_enqueue_style( 'laterpay-post-view' );

        // only enqueue the styles when the current post is purchasable
        if ( ! is_singular() || ! LaterPay_Helper_Pricing::is_post_purchasable() ) {
            return;
        }

        wp_enqueue_style( 'laterpay-dialogs' );
    }

    /**
     * Load LaterPay Javascript libraries.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_scripts() {

        $laterpay_src = 'static.laterpay.net';
        if( $this->config->get( 'script_debug_mode' ) ){
            $laterpay_src = 'static.dev.laterpaytest.net';
        }

        wp_register_script(
            'laterpay-yui',
            'https://' . $laterpay_src . '/yui/3.13.0/build/yui/yui-min.js',
            array(),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-config',
            'https://' . $laterpay_src . '/client/1.0.0/config.js',
            array( 'laterpay-yui' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-peity',
            $this->config->get( 'js_url' ) . 'vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-post-view',
            $this->config->get( 'js_url' ) . 'laterpay-post-view.js',
            array( 'jquery', 'laterpay-peity' ),
            $this->config->get( 'version' ),
            true
        );

        // pass localized strings and variables to script
        $client         = new LaterPay_Client( $this->config );
        $balance_url    = $client->get_controls_balance_url();
        wp_localize_script(
            'laterpay-post-view',
            'lpVars',
            array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'post_id'       => get_the_ID(),
                'debug'         => (bool) $this->config->get( 'debug_mode' ),
                'caching'       => (bool) $this->config->get( 'caching.compatible_mode' ),
                'nonces'        => array(
                    'content'   => wp_create_nonce( 'laterpay_post_load_purchased_content' ),
                    'statistic' => wp_create_nonce( 'laterpay_post_statistic_render' ),
                ),
                'lpBalanceUrl'  => $balance_url,
                'i18nAlert'     => __( 'In Live mode, your visitors would now see the LaterPay purchase dialog.', 'laterpay' ),
                'i18nOutsideAllowedPriceRange' => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' )
            )
        );

        // only enqueue the scripts when the current post is purchasable
        if ( ! is_singular() || ! LaterPay_Helper_Pricing::is_post_purchasable() ) {
            return;
        }

        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-config' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-post-view' );
    }

}
