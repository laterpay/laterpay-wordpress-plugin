<?php

class LaterPay_Controller_Post_Content extends LaterPay_Controller_Abstract
{

    /**
     * Create teaser content for the post.
     *
     * @param int object $post
     *
     * @return string
     */
    public function init_teaser_content( $post ) {
        if ( ! is_object( $post ) ) {
            $post = get_post( $post );
        }

        if ( empty( $post ) ) {
            return;
        }

        $meta_value = get_post_meta( $post->ID, 'Teaser content', false );
        if ( is_array( $meta_value ) && count( $meta_value ) == 0 ) {
            $new_meta_value = LaterPay_Helper_String::truncate(
                $post->post_content,
                $this->config->get( 'content.auto_generated_teaser_content_word_count' ),
                array (
                    'html'  => true,
                    'words' => true,
                )
            );
            add_post_meta( $post->ID, 'Teaser content', $new_meta_value, true );
        }
    }

    /**
     * Render post.
     */
    public function view( $content ) {
        global $laterpay_show_statistics;
        if ( is_page() ) {
            return $content;
        }
	    $post = get_post();
	    if( $post === null ){
		    return $content;
	    }
        $post_id = $post->ID;

        // get currency
        $currency   = get_option( 'laterpay_currency' );
        $price      = $this->get_post_price( $post_id );
        $access     = $GLOBALS['laterpay_access'];

        $link       = $this->get_laterpay_link( $post_id );
        if ( $price == 0 ) {
	        return $content;
        }

        $this->init_teaser_content( $post );
        // get teaser content
        $teaser_content         = get_post_meta( $post_id, 'Teaser content', true );
        $teaser_content_only    = get_option( 'laterpay_teaser_content_only' );
        if ( is_single() ) {
            // check for required privileges to perform action
            if ( LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id ) ) {
                $access = true;
                $this->get_post_statistics();
            } else if ( LaterPay_Helper_User::user_has_full_access() ) {
                $access = true;
            }

            // encrypt content for premium content
            $content = LaterPay_Helper_File::get_encrypted_content( $post_id, $content, $access );
            $is_premium_content = $price > 0;

            $this->assign( 'post_id',                    $post_id );
            $this->assign( 'content',                    $content );
            $this->assign( 'teaser_content',             $teaser_content );
            $this->assign( 'teaser_content_only',        $teaser_content_only );
            $this->assign( 'currency',                   $currency );
            $this->assign( 'price',                      $price );
            $this->assign( 'is_premium_content',         $is_premium_content );
            $this->assign( 'access',                     $access );
            $this->assign( 'link',                       $link );
            $this->assign( 'can_show_statistic',         LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id ) && (! LaterPay_Helper_Request::is_ajax() || $laterpay_show_statistics) && $this->config->get( 'logging.access_logging_enabled' ) && $is_premium_content );
            $this->assign( 'post_content_cached',        LaterPay_Helper_Cache::site_uses_page_caching() );
            $this->assign( 'preview_post_as_visitor',    LaterPay_Helper_User::preview_post_as_visitor( $post ) );
            $this->assign( 'hide_statistics_pane',       LaterPay_Helper_User::statistics_pane_is_hidden() );

            $html = $this->get_text_view( 'frontend/post/single' );
        }
        else {
            $this->assign( 'teaser_content', $teaser_content );
            $html = $this->get_text_view( 'frontend/post/teaser' );
        }
        return $html;

    }

	/**
	 * Adding the LaterPay-iFrame to Footer
	 * @return void
	 */
	public function modify_footer() {

	    $post = get_post();
	    if( $post === null ){
		    return;
	    }

        $price = self::get_post_price( $post->ID );
        if ( $price > 0 ) {
            $LaterPay_Client = new LaterPay_Core_Client( $this->config );
            $identify_link = $LaterPay_Client->get_identify_url();

            $this->assign( 'post_id',       $post->ID );
            $this->assign( 'identify_link', $identify_link );

            echo $this->get_text_view( 'frontend/partials/identify/iframe' );
        }

    }

    /**
     * Set up post statistics.
     */
    protected function get_post_statistics() {
        if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
            return;
        }
		$post = get_post();
	    if( $post === null ){
		    return;
	    }

        // get currency
        $currency = get_option( 'laterpay_currency' );

        // get historical performance data for post
        $LaterPay_Payments_History_Model   = new LaterPay_Model_Payments_History();
        $LaterPay_Post_Views_Model = new LaterPay_Model_Post_Views();

        // get total revenue and total sales
        $total = array();
        $history_total = (array) $LaterPay_Payments_History_Model->get_total_history_by_post_id( $post->ID );
        foreach ( $history_total as $key => $item ) {
            $total[$item->currency]['sum']      = round($item->sum, 2);
            $total[$item->currency]['quantity'] = $item->quantity;
        }

        // get revenue
        $last30DaysRevenue = array();
        $history_last30DaysRevenue = (array) $LaterPay_Payments_History_Model->get_last_30_days_history_by_post_id( $post->ID );
        foreach ( $history_last30DaysRevenue as $item ) {
            $last30DaysRevenue[$item->currency][$item->date] = array(
                'sum'       => round( $item->sum, 2 ),
                'quantity'  => $item->quantity,
            );
        }

        $todayRevenue = array();
        $history_todayRevenue = (array) $LaterPay_Payments_History_Model->get_todays_history_by_post_id( $post->ID );
        foreach ( $history_todayRevenue as $item ) {
            $todayRevenue[$item->currency]['sum']       = round( $item->sum, 2 );
            $todayRevenue[$item->currency]['quantity']  = $item->quantity;
        }

        // get visitors
        $last30DaysVisitors = array();
        $history_last30DaysVisitors = (array) $LaterPay_Post_Views_Model->get_last_30_days_history( $post->ID );
        foreach ( $history_last30DaysVisitors as $item ) {
            $last30DaysVisitors[$item->date] = array(
                'quantity' => $item->quantity,
            );
        }

        $todayVisitors = (array) $LaterPay_Post_Views_Model->get_todays_history( $post->ID );
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
            // percentage of buyers (sales/visitors)
            $todayBuyers = round( 100 * $todayRevenue[$currency]['quantity'] / $todayVisitors );
        }

        // assign variables
        $this->assign( 'total',                 $total );

        $this->assign( 'last30DaysRevenue',     $last30DaysRevenue );
        $this->assign( 'todayRevenue',          $todayRevenue );

        $this->assign( 'last30DaysBuyers',      $last30DaysBuyers );
        $this->assign( 'todayBuyers',           $todayBuyers );

        $this->assign( 'last30DaysVisitors',    $last30DaysVisitors );
        $this->assign( 'todayVisitors',         $todayVisitors );
    }

    /**
     * Get post price, depending on applied price type of post.
     *
     * @param int $post_id
     *
     * @return float
     */
    public static function get_post_price( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        $post_price_type = get_post_meta( $post_id, 'Pricing Post Type', true );
        switch ( $post_price_type ) {
            // backwards compatibility: Pricing Post Type used to be stored as 0 or 1; TODO: remove with release 1.0
            case '0':
            case '1':
            case 'individual price':
                $price = get_post_meta( $post_id, 'Pricing Post', true );
                break;

            case 'individual price, dynamic':
                $price = self::get_dynamic_price( get_post( ) );
                break;

            case 'category default price':
                $LaterPay_Category_Model  = new LaterPay_Model_Category();
                $category_id            = get_post_meta( $post_id, 'laterpay_post_default_category', true );
                $price                  = $LaterPay_Category_Model->get_price_by_category_id( (int) $category_id );
                break;

            case 'global default price':
                $price = $global_default_price;
                break;

            default:
                if ( $global_default_price > 0 ) {
                    $price = $global_default_price;
                    // there's no post price type present, so we add it
                    add_post_meta( $post_id, 'Pricing Post Type', 'global default price', true );
                } else {
                    $price = 0;
                }
                break;
        }

        return (float) $price;
    }

    /**
     * Save purchase in purchase history.
     */
    public static function buy_post() {
        if ( 'index.php' == $GLOBALS['pagenow'] ) {
            if ( isset( $_GET['buy'] ) && $_GET['buy'] ) {
                $data = array();
                $data['post_id']        = $_GET['post_id'];
                $data['id_currency']    = $_GET['id_currency'];
                $data['price']          = $_GET['price'];
                $data['date']           = $_GET['date'];
                $data['ip']             = $_GET['ip'];
                $data['hash']           = $_GET['hash'];
                $hash = $_GET['hash'];
                $url = LaterPay_Helper_Request::get_current_url();
                $url = preg_replace( '/hash=.*?($|&)/', '', $url );
                $url = preg_replace( '/&$/',            '', $url );
                // check hash for purchase
                if ( md5( md5( $url ) . AUTH_SALT ) == $hash ) {
                    $LaterPay_Payments_History_Model = new LaterPay_Model_Payments_History();
                    $LaterPay_Payments_History_Model->set_payment_history( $data );
                }
                $url = preg_replace( '/post_id=.*?($|&)/',      '', $url );
                $url = preg_replace( '/id_currency=.*?($|&)/',  '', $url );
                $url = preg_replace( '/price=.*?($|&)/',        '', $url );
                $url = preg_replace( '/date=.*?($|&)/',         '', $url );
                $url = preg_replace( '/ip=.*?($|&)/',           '', $url );
                $url = preg_replace( '/buy=.*?($|&)/',          '', $url );
                $url = preg_replace( '/&$/',                    '', $url );
                header( 'Location: ' . $url );
                die;
            }
        }
    }

    /**
     * Update incorrect token or create token, if it doesn't exist.
     */
    public function token_hook() {
        $GLOBALS['laterpay_access'] = false;

        $is_feed = self::is_feed();

        LaterPay_Core_Logger::debug(
            'LaterPay_Post_Content_Controller::token_hook',
            array(
                ! is_admin(),
                ! self::is_login_page(),
                ! self::is_cron_page(),
                ! $is_feed,
                LaterPay_Helper_Browser::browser_supports_cookies(),
                ! LaterPay_Helper_Browser::is_crawler()
            )
        );

        if ( ! is_admin() && ! self::is_login_page() && ! self::is_cron_page() && ! $is_feed && LaterPay_Helper_Browser::browser_supports_cookies() && ! LaterPay_Helper_Browser::is_crawler() ) {

            LaterPay_Core_Logger::debug( 'LaterPay_Post_Content_Controller::token_hook', array( $_SERVER['REQUEST_URI'] ) );

            $LaterPay_Client = new LaterPay_Core_Client( $this->config );
            if ( isset($_GET['lptoken']) ) {
                $LaterPay_Client->set_token( $_GET['lptoken'], true );
            }

            if ( ! $LaterPay_Client->has_token() ) {
                $LaterPay_Client->acquire_token();
            }

            // if Ajax request
            if ( (LaterPay_Helper_Request::is_ajax() && isset( $_GET['id'] )) || isset( $_GET['id'] ) ) {
                $postid = $_GET['id'];
            } else {
                $url = LaterPay_Helper_Statistics::get_full_url( $_SERVER );
                $postid = url_to_postid( $url );
            }
            if ( ! empty($postid) ) {
                $price = self::get_post_price($postid);
                if ( $price > 0 ) {
                    $result = $LaterPay_Client->get_access( $postid );
                    $access = false;
                    if ( ! empty( $result ) && isset( $result['articles'][$postid] ) ) {
                        $access = $result['articles'][$postid]['access'];
                    }
                    $GLOBALS['laterpay_access'] = $access;
                }
            }
        }
    }

    /**
     * Check if current page is login page.
     *
     * @return boolean is login page
     */
    public static function is_login_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
    }

    /**
     * Check if current page is cron page.
     *
     * @return boolean is cron page
     */
    public static function is_cron_page() {
        return in_array( $GLOBALS['pagenow'], array( 'wp-cron.php' ) );
    }

    /**
     * Check if current page is LaterPay resource link.
     *
     * @return boolean is resource link
     */
    public static function is_resource_link() {
        return in_array( $GLOBALS['pagenow'], array( 'laterpay-get-script.php' ) );
    }

    /**
     * Check if current request is RSS feed.
     *
     * @return boolean is feed
     */
    public static function is_feed() {
        $is_feed    = false;
        $qv         = wp_parse_args( $_SERVER['QUERY_STRING'] );
        $url        = parse_url( $_SERVER['REQUEST_URI'] );

        if ( ( isset( $qv['feed'] ) && '' != $qv['feed']) || (isset( $url['path'] ) && preg_match( '/feed/', $url['path'] )) ) {
            $is_feed = true;
        }

        return $is_feed;
    }

    /**
     * Get current price for post with dynamic pricing scheme defined.
     *
     * @param object $post
     *
     * @return float price
     */
    public static function get_dynamic_price( $post ) {
        if ( function_exists( 'date_diff' ) ) {
            $date_time = new DateTime( date( 'Y-m-d' ) );
            $days_since_publication = $date_time->diff( new DateTime( date( 'Y-m-d', strtotime( $post->post_date ) ) ) )->format( '%a' );
        } else {
            $d1 = strtotime( date( 'Y-m-d' ) );
            $d2 = strtotime( $post->post_date );
            $diff_secs = abs( $d1 - $d2 );
            $days_since_publication = floor( $diff_secs / ( 3600 * 24 ) );
        }

        if ( self::is_before_transitional_period( $post, $days_since_publication ) ) {
            $price = get_post_meta( $post->ID, 'laterpay_start_price', true );
        } else {
            if ( self::is_after_transitional_period( $post, $days_since_publication ) ) {
                $price = get_post_meta( $post->ID, 'laterpay_end_price', true );
            } else {    // transitional period between start and end of dynamic price change
                $price = self::calculate_transitional_price( $post, $days_since_publication );
            }
        }

        $rounded_price = round( $price, 2 );
        if ( $rounded_price < 0.05 ) {
            $rounded_price = 0;
        }

        return $rounded_price;
    }

    /**
     * Check if current date is after set date for end of dynamic price change.
     *
     * @param object $post
     * @param int    $days_since_publication
     *
     * @return boolean
     */
    private static function is_after_transitional_period( $post, $days_since_publication ) {
        return get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) <= $days_since_publication || get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true ) == 0;
    }

    /**
     * Check if current date is before set date for end of dynamic price change.
     *
     * @param object $post
     * @param int    $days_since_publication
     *
     * @return boolean
     */
    private static function is_before_transitional_period( $post, $days_since_publication ) {
        return get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true ) >= $days_since_publication;
    }

    /**
     * Calculate transitional price between start price and end price based on linear equation.
     *
     * @param type $post
     * @param int  $days_since_publication
     *
     * @return float
     */
    private static function calculate_transitional_price( $post, $days_since_publication ) {
        $end_price          = get_post_meta( $post->ID, 'laterpay_end_price', true );
        $start_price        = get_post_meta( $post->ID, 'laterpay_start_price', true );
        $days_until_end     = get_post_meta( $post->ID, 'laterpay_transitional_period_end_after_days', true );
        $days_until_start   = get_post_meta( $post->ID, 'laterpay_change_start_price_after_days', true );

        $coefficient = ( $end_price - $start_price ) / ( $days_until_end - $days_until_start );

        return $start_price + ( $days_since_publication - $days_until_start ) * $coefficient;
    }

    /**
     * Get the LaterPay link for the post.
     *
     * @param int $post_id
     *
     * @return string
     */
    public function get_laterpay_link( $post_id ) {
        $currency   = get_option( 'laterpay_currency' );
        $price      = self::get_post_price( $post_id );

        $currency_model = new LaterPay_Model_Currency();
        $client = new LaterPay_Core_Client( $this->config );

        // data to register purchase after redirect from LaterPay
        $data = array(
            'post_id'     => $post_id,
            'id_currency' => $currency_model->get_currency_id_by_iso4217_code( $currency ),
            'price'       => $price,
            'date'        => time(),
            'buy'         => 'true',
            'ip'          => ip2long( $_SERVER['REMOTE_ADDR'] ),
        );
        $url = LaterPay_Helper_Request::get_current_url();
        if ( strpos( $url, '?' ) !== false || strpos( $url, '&' ) !== false ) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= http_build_query( $data );
        $hash = md5( md5( $url ) . AUTH_SALT );
        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => $post_id,
            'purchase_date' => time() . '000', // fix for PHP 32bit
            'pricing'       => $currency . ( $price * 100 ),
            'vat'           => $this->config->get( 'currency.default_vat' ),
            'url'           => $url . '&hash=' . $hash,
            'title'         => $GLOBALS['post']->post_title,
        );

        return $client->get_add_url( $params );
    }

    /**
     * Prepend LaterPay purchase button to title (heading) of post on single post pages.
     *
     * @param object $the_title title
     *
     * @return object
     */
    public function modify_post_title( $the_title ) {
        if ( in_the_loop() ) {
            $post                       = get_post();
            $post_id                    = $post->ID;
            $price                      = self::get_post_price( $post_id );
            $float_price                = (float) $price;
            $is_premium_content         = $float_price > 0;
            $access                     = $GLOBALS['laterpay_access'] ||
                                            LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post ) ||
                                            LaterPay_Helper_User::user_has_full_access();
            $link                       = $this->get_laterpay_link( $post_id );
            $preview_post_as_visitor    = LaterPay_Helper_User::preview_post_as_visitor( $post );
            $post_content_cached        = LaterPay_Helper_Cache::site_uses_page_caching();

            // only render one instance of the purchase button on single premium posts - don't prepend it to related posts etc.
            if ( $is_premium_content && is_single() && ! is_page() && did_action( 'the_title' ) === 0 ) {
                if ( $post_content_cached && ! LaterPay_Helper_Request::is_ajax() ) {
                    $this->assign( 'post_id', $post_id );

                    $the_title = $this->get_text_view( 'frontend/partials/post/title' );
                } else {
                    if ( ( ! $access || $preview_post_as_visitor ) ) {
                        $currency           = get_option( 'laterpay_currency' );

                        $purchase_button    = '<a href="#" class="laterpay-purchase-link laterpay-purchase-button" data-laterpay="' . $link . '" data-icon="b" post-id="';
                        $purchase_button   .= $post_id . '" title="' . __( 'Buy now with LaterPay', 'laterpay' ) . '" ';
                        $purchase_button   .= 'data-preview-as-visitor="' . $preview_post_as_visitor . '">';
                        $purchase_button   .= sprintf(
                                                    __( '%s<small>%s</small>', 'laterpay' ),
                                                    LaterPay_Helper_View::format_number( $price, 2 ),
                                                    $currency
                                                );
                        $purchase_button   .= '</a>';

                        $the_title          = $purchase_button . $the_title;
                    }
                }
            }
        }

        return $the_title;
    }
}
