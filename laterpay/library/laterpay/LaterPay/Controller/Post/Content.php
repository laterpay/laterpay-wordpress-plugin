<?php

class LaterPay_Controller_Post_Content extends LaterPay_Controller_Abstract
{

    /**
     * Ajax method to get the cached article.
     * Required, because there could be a price change in LaterPay and we always need the current article price.
     *
     * @wp-hook wp_ajax_laterpay_article_script, wp_ajax_nopriv_laterpay_article_script
     *
     * @return  void
     */
    public function get_cached_post() {
        global $post, $wp_query, $laterpay_show_statistics;

        // set the global vars so that WordPress thinks it is in a single view
        $wp_query->is_single      = true;
        $wp_query->in_the_loop    = true;
        $laterpay_show_statistics = true;

        // get the content
        $post_id        = absint( $_REQUEST[ 'post_id' ] );
        $post           = get_post( $post_id );
        $post_content   = $post->post_content;

        $content        = apply_filters( 'the_content', $post_content );
        $content        = str_replace( ']]>', ']]&gt;', $content );

        echo $content;
        exit;
    }

    /**
     * Ajax callback to load and echo the modified footer.
     *
     * @wp-hook wp_ajax_laterpay_footer_script, wp_ajax_nopriv_laterpay_footer_script
     *
     * @return  void
     */
    public function get_modified_footer() {
        $this->modify_footer();
        exit;
    }

    /**
     * Generate performance data statistics for post.
     */
    protected function get_post_statistics() {
        if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
            return;
        }
        $post = get_post();
        if ( $post === null ) {
            return;
        }

        // get currency
        $currency = get_option( 'laterpay_currency' );

        // get historical performance data for post
        $payments_history_model = new LaterPay_Model_Payments_History();
        $post_views_model       = new LaterPay_Model_Post_Views();

        // get total revenue and total sales
        $total = array();
        $history_total = (array) $payments_history_model->get_total_history_by_post_id( $post->ID );
        foreach ( $history_total as $item ) {
            $total[$item->currency]['sum']      = round( $item->sum, 2 );
            $total[$item->currency]['quantity'] = $item->quantity;
        }

        // get revenue
        $last30DaysRevenue = array();
        $history_last30DaysRevenue = (array) $payments_history_model->get_last_30_days_history_by_post_id( $post->ID );
        foreach ( $history_last30DaysRevenue as $item ) {
            $last30DaysRevenue[$item->currency][$item->date] = array(
                'sum'       => round( $item->sum, 2 ),
                'quantity'  => $item->quantity,
            );
        }

        $todayRevenue = array();
        $history_todayRevenue = (array) $payments_history_model->get_todays_history_by_post_id( $post->ID );
        foreach ( $history_todayRevenue as $item ) {
            $todayRevenue[$item->currency]['sum']       = round( $item->sum, 2 );
            $todayRevenue[$item->currency]['quantity']  = $item->quantity;
        }

        // get visitors
        $last30DaysVisitors = array();
        $history_last30DaysVisitors = (array) $post_views_model->get_last_30_days_history( $post->ID );
        foreach ( $history_last30DaysVisitors as $item ) {
            $last30DaysVisitors[$item->date] = array(
                'quantity' => $item->quantity,
            );
        }

        $todayVisitors = (array) $post_views_model->get_todays_history( $post->ID );
        $todayVisitors = $todayVisitors[0]->quantity;

        // get buyers (= conversion rate)
        $last30DaysBuyers = array();
        if ( isset( $last30DaysRevenue[$currency] ) ) {
            $revenues = $last30DaysRevenue[$currency];
        } else {
            $revenues = array();
        }
        foreach ( $revenues as $date => $item ) {
            $percentage = 0;
            if ( isset( $last30DaysVisitors[$date] ) && ! empty( $last30DaysVisitors[$date]['quantity'] ) ) {
                $percentage = round( 100 * $item['quantity'] / $last30DaysVisitors[$date]['quantity'] );
            }
            $last30DaysBuyers[$date] = array( 'percentage' => $percentage );
        }

        $todayBuyers = 0;
        if ( ! empty( $todayVisitors ) && isset( $todayRevenue[$currency] ) ) {
            // percentage of buyers (sales divided by visitors)
            $todayBuyers = round( 100 * $todayRevenue[$currency]['quantity'] / $todayVisitors );
        }

        // assign variables
        $statistic_args = array(
            'total'             => $total,
            'last30DaysRevenue' => $last30DaysRevenue,
            'todayRevenue'      => $todayRevenue,
            'last30DaysBuyers'  => $last30DaysBuyers,
            'todayBuyers'       => $todayBuyers,
            'last30DaysVisitors'=> $last30DaysVisitors,
            'todayVisitors'     => $todayVisitors,
        );

        $this->assign( 'statistic', $statistic_args );

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
            $client = new LaterPay_Core_Client( $this->config );
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
     * Update incorrect token or create one, if it doesn't exist.
     *
     * @wp-hook template_redirect
     *
     * @return void
     */
    public function create_token() {
        $GLOBALS[ 'laterpay_access' ] = false;

        $is_frontend                = is_singular() || is_home() || is_search() || is_archive();
        $browser_supports_cookies   = LaterPay_Helper_Browser::browser_supports_cookies();
        $browser_is_crawler         = LaterPay_Helper_Browser::is_crawler();

        $context = array(
            'is_frontend'       => $is_frontend,
            'support_cookies'   => $browser_supports_cookies,
            'is_crawler'        => $browser_is_crawler
        );

        LaterPay_Core_Logger::debug(
            __METHOD__,
            $context
        );

        if ( ! $is_frontend || ! $browser_supports_cookies || $browser_is_crawler ) {
            return;
        }

        $laterpay_client = new LaterPay_Core_Client( $this->config );
        if ( isset( $_GET[ 'lptoken' ] ) ) {
            $laterpay_client->set_token( $_GET['lptoken'], true );
        }

        if ( ! $laterpay_client->has_token() ) {
            $laterpay_client->acquire_token();
        }

        // get the current post
        $post = get_post();
        if ( $post === null ) {
            LaterPay_Core_Logger::error( __METHOD__ . ' post not found!' );
            return;
        }

        LaterPay_Core_Logger::debug( __METHOD__, array( 'post' => $post ) );

        $price  = LaterPay_Helper_Pricing::get_post_price( $post->ID );
        $access = false;

        if ( $price != 0 ) {
            $result = $laterpay_client->get_access( array( $post->ID ) );

            if ( ! empty( $result ) && isset( $result[ 'articles' ] ) && isset( $result[ 'articles' ][ $post->ID ] ) ) {
                $access = $result['articles'][ $post->ID ]['access'];
            }
        }

        $GLOBALS['laterpay_access'] = $access;
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
        $client         = new LaterPay_Core_Client( $this->config );

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
     * Helper function to detect, if the current post is a single post and can be parsed in frontend.
     *
     * @return bool true|false
     */
    protected function post_is_a_laterpay_post() {
        // only modify the post_content on singular pages
        if ( ! is_singular() || ! in_the_loop() ) {
            return false;
        }

        // check if LaterPay is enabled for the current post type
        $post_type = get_post_type();
        if ( ! in_array( $post_type, $this->config->get( 'content.allowed_post_types' ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Modify the post content of paid posts.
     *
     * Depending on the configuration, the content of paid posts is modified and several elements are added to the content:
     * If the user is an admin, a statistics pane with performance data for the current post is shown.
     * Depending on the settings in the appearance tab, only the teaser content or the teaser content plus an excerpt of
     * the full content is returned for user who have not bought the post.
     * A LaterPay purchase link and / or a LaterPay purchase button is shown.
     *
     * @wp-hook the_content
     *
     * @param string $content
     *
     * @return string $content
     */
    public function modify_post_content( $content ) {
        global $laterpay_show_statistics;

        $post = get_post();
        if ( $post === null ) {
            return $content;
        }
        $post_id = $post->ID;

        if ( ! $this->post_is_a_laterpay_post() ) {
            return $content;
        }

        // get pricing data
        $currency   = get_option( 'laterpay_currency' );
        $price      = LaterPay_Helper_Pricing::get_post_price( $post_id );
        if ( $price == 0 ) {
            return $content;
        }
        $is_premium_content = $price > 0;

        // get information if user has access to content
        $access = $GLOBALS['laterpay_access'];

        // get purchase link
        $purchase_link = $this->get_laterpay_purchase_link( $post_id );

        // get teaser content
        $teaser_content             = get_post_meta( $post_id, 'laterpay_post_teaser', true );
        $teaser_content_only        = get_option( 'laterpay_teaser_content_only' );
        $is_ajax                    = defined( 'DOING_AJAX' ) && DOING_AJAX;
        $user_can_read_statistic    = LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id );
        $preview_post_as_visitor    = LaterPay_Helper_User::preview_post_as_visitor( $post );
        $hide_statistics_pane       = LaterPay_Helper_User::statistics_pane_is_hidden();

        // get post statistics if user has the required capabilities
        if ( $user_can_read_statistic ) {
            $access = true;
            $this->get_post_statistics();
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
            'access'                    => $access,
            'link'                      => $purchase_link,
            'preview_post_as_visitor'   => $preview_post_as_visitor,
            'hide_statistics_pane'      => $hide_statistics_pane,
        );
        $this->assign( 'laterpay', $view_args );

        // starting the output
        $html = '';

        // add the post statistics, if enabled
        if( ( $user_can_read_statistic || $laterpay_show_statistics ) && $this->config->get( 'logging.access_logging_enabled' ) && $is_premium_content ) {
            $html .= $this->get_text_view( 'frontend/partials/post/statistic' );
        }

        // return the full unmodified content, if post is free or was already bought by user
        if ( ( ! $is_premium_content || $access ) && ! $preview_post_as_visitor ) {
            return $html . $content;
        }

        // return only a placeholder, if caching is enabled and it's not an Ajax request
        if ( (bool) $this->config->get( 'caching.compatible_mode' ) && ! $is_ajax ) {
            return $this->get_text_view( 'frontend/partials/post/single_cached' );
        }

        // add a purchase button as very first element of the content
        $html .= '<div class="clearfix">';
        $html .= $this->get_text_view( 'frontend/partials/post/purchase_button' );
        $html .= '</div>';

        // add the teaser content
        $html .= $this->get_text_view( 'frontend/partials/post/teaser' );

        if ( $teaser_content_only ) {
            // add teaser content plus a purchase link after the teaser content
            $html .= $this->get_text_view( 'frontend/partials/post/purchase_link' );
        } else {
            // add excerpt of full content, covered by an overlay with a purchase button
            $html .= $this->get_text_view( 'frontend/partials/post/purchase_box' );
        }

        return $html;
    }

    /**
     * Add the LaterPay iframe to the footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function modify_footer() {
        $post = get_post();
        if ( $post === null ) {
            return;
        }

        $price = LaterPay_Helper_Pricing::get_post_price( $post->ID );
        if ( $price > 0 ) {
            $laterpay_client    = new LaterPay_Core_Client( $this->config );
            $identify_link      = $laterpay_client->get_identify_url();

            $this->assign( 'post_id',       $post->ID );
            $this->assign( 'identify_link', $identify_link );

            echo $this->get_text_view( 'frontend/partials/identify/iframe' );
        }
    }

}
