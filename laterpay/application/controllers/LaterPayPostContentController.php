<?php

class LaterPayPostContentController extends LaterPayAbstractController {

    /**
     * Create teaser content for the post
     *
     * @param int object $post
     *
     * @return string
     */
    public function initTeaserContent( $post ) {
        if ( !is_object($post) ) {
            $post = get_post($post);
        }

        if ( empty($post) ) {
            return;
        }

        $meta_value = get_post_meta($post->ID, 'Teaser content', false);
        if ( is_array($meta_value) && count($meta_value) == 0 ) {
            $new_meta_value = LaterPayStringHelper::truncate(
                $post->post_content,
                LATERPAY_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT,
                array (
                    'html'  => true,
                    'words' => true
                )
            );
            add_post_meta($post->ID, 'Teaser content', $new_meta_value, true);
        }
    }

    /**
     * Render post
     *
     * @access public
     */
    public function view( $content ) {
        global $laterpay_show_statistic;
        if ( is_page() ) {
            return $content;
        }
        $post_id = $GLOBALS['post']->ID;

        // get currency
        $currency   = get_option('laterpay_currency');
        $price      = self::getPostPrice($post_id);
        $access     = $GLOBALS['laterpay_access'];

        $link       = self::getLPLink($post_id);
        if ( $price > 0 ) {
            $this->initTeaserContent($GLOBALS['post']);
            // get teaser content
            $teaser_content         = get_post_meta($post_id, 'Teaser content', true);
            $teaser_content_only    = get_option('laterpay_teaser_content_only');
            if ( is_single() ) {
                // check for required privileges to perform action
                if ( current_user_can('manage_options') ) {
                    $access = true;
                    $this->setStatistic();
                } else if ( LaterPayUserHelper::user_has_full_access() ) {
                    $access = true;
                }

                // encrypt content for premium content
                $content = LaterPayFileHelper::getEncryptedContent($post_id, $content, $access);

                $this->assign('post_id',                    $post_id);
                $this->assign('content',                    $content);
                $this->assign('teaser_content',             $teaser_content);
                $this->assign('teaser_content_only',        $teaser_content_only);
                $this->assign('currency',                   $currency);
                $this->assign('price',                      $price);
                $this->assign('is_premium_content',         $price > 0);
                $this->assign('access',                     $access);
                $this->assign('link',                       $link);
                $this->assign('can_show_statistic',         $laterpay_show_statistic ? true: false);
                $this->assign('post_content_cached',        LaterPayCacheHelper::siteUsesPageCaching());
                $this->assign('preview_post_as_visitor',    LaterPayUserHelper::previewPostAsVisitor());
                $this->assign('hide_statistics_pane',       LaterPayUserHelper::isHiddenStatisticsPane());

                $html = $this->getTextView('singlePost');
            } else {
                $this->assign('teaser_content', $teaser_content);

                $html = $this->getTextView('post');
            }
            return $html;
        }

        return $content;
    }

    public function modifyFooter() {
        // if Ajax request
        if ( (LaterPayRequestHelper::isAjax() && isset($_GET['id'])) || isset($_GET['id']) ) {
            $postid = $_GET['id'];
        } else {
            $url = LaterPayStatisticsHelper::getFullUrl($_SERVER);
            $postid = url_to_postid($url);
        }
        if ( !empty($postid) ) {
            $price = self::getPostPrice($postid);
            if ( $price > 0 ) {
                $LaterPayClient = new LaterPayClient();
                $identify_link = $LaterPayClient->getIdentifyUrl();

                $this->assign('post_id',               $postid);
                $this->assign('identify_link',         $identify_link);

                echo $this->getTextView('partials/identifyIframe');
            }
        }
    }

    /**
     * Set up post statistics
     */
    protected function setStatistic() {
        if ( !LATERPAY_ACCESS_LOGGING_ENABLED ) {
            return;
        }

        $post_id = $GLOBALS['post']->ID;
        // get currency
        $currency = get_option('laterpay_currency');

        // get historical performance data for post
        $LaterPayModelHistory   = new LaterPayModelHistory();
        $LaterPayModelPostViews = new LaterPayModelPostViews();

        // get total revenue and total sales
        $total = array();
        $history_total = (array)$LaterPayModelHistory->getTotalHistoryByPostId($post_id);
        foreach ( $history_total as $key => $item ) {
            $total[$item->currency]['sum']      = round($item->sum, 2);
            $total[$item->currency]['quantity'] = $item->quantity;
        }

        // get revenue
        $last30DaysRevenue = array();
        $history_last30DaysRevenue = (array)$LaterPayModelHistory->getLast30DaysHistoryByPostId($post_id);
        foreach ( $history_last30DaysRevenue as $item ) {
            $last30DaysRevenue[$item->currency][$item->date] = array(
                'sum'       => round($item->sum, 2),
                'quantity'  => $item->quantity,
            );
        }

        $todayRevenue = array();
        $history_todayRevenue = (array)$LaterPayModelHistory->getTodayHistoryByPostId($post_id);
        foreach ( $history_todayRevenue as $item ) {
            $todayRevenue[$item->currency]['sum']       = round($item->sum, 2);
            $todayRevenue[$item->currency]['quantity']  = $item->quantity;
        }

        // get visitors
        $last30DaysVisitors = array();
        $history_last30DaysVisitors = (array)$LaterPayModelPostViews->getLast30DaysHistory($post_id);
        foreach ( $history_last30DaysVisitors as $item ) {
            $last30DaysVisitors[$item->date] = array(
                'quantity' => $item->quantity,
            );
        }

        $todayVisitors = (array)$LaterPayModelPostViews->getTodayHistory($post_id);
        $todayVisitors = $todayVisitors[0]->quantity;

        // get buyers (= conversion rate)
        $last30DaysBuyers = array();
        if ( isset($last30DaysRevenue[$currency]) ) {
            $revenues = $last30DaysRevenue[$currency];
        } else {
            $revenues = array();
        }
        foreach ( $revenues as $date => $item ) {
            $percentage = 0;
            if ( isset($last30DaysVisitors[$date]) && !empty($last30DaysVisitors[$date]['quantity']) ) {
                $percentage = round(100 * $item['quantity'] / $last30DaysVisitors[$date]['quantity']);
            }
            $last30DaysBuyers[$date] = array( 'percentage' => $percentage );
        }

        $todayBuyers = 0;
        if ( !empty($todayVisitors) && isset($todayRevenue[$currency]) ) {
            // percentage of buyers (sales/visitors)
            $todayBuyers = round(100 * $todayRevenue[$currency]['quantity'] / $todayVisitors);
        }

        // assign variables
        $this->assign('total',                 $total);

        $this->assign('last30DaysRevenue',     $last30DaysRevenue);
        $this->assign('todayRevenue',          $todayRevenue);

        $this->assign('last30DaysBuyers',      $last30DaysBuyers);
        $this->assign('todayBuyers',           $todayBuyers);

        $this->assign('last30DaysVisitors',    $last30DaysVisitors);
        $this->assign('todayVisitors',         $todayVisitors);
    }

    /**
     * Get post price
     *
     * @param int $post_id
     *
     * @return float
     */
    public static function getPostPrice( $post_id ) {
        // get post-specific price
        $price_post = get_post_meta($post_id, 'Pricing Post', true);

        // get category default price
        $category = get_the_category($post_id);
        $LaterPayModelCategory = new LaterPayModelCategory();
        $price_post_category = $LaterPayModelCategory->getPriceByCategoryId($category[0]->term_id);

        // get global default price
        $price_post_global = get_option('laterpay_global_price');

        // determine effective price for post
        $pricing_type = get_post_meta($post_id, 'Pricing Post Type', true);
        if ( !$pricing_type ) {
            if ( !empty($price_post) ) {
                $price = $price_post;
            } else if ( !is_null($price_post_category) ) {
                $price = $price_post_category;
            } else if ( !empty($price_post_global) ) {
                $price = $price_post_global;
            } else {
                $price = 0;
            }
        } else {
            $price = self::getAdvancedPrice($GLOBALS['post']);
        }

        return (float)$price;
    }

    /**
     * Add purchase to purchase history
     *
     * @access public
     */
    public static function buyPost() {
        if ( 'index.php' == $GLOBALS['pagenow'] ) {
            if ( isset($_GET['buy']) && $_GET['buy'] ) {
                $data['post_id']        = $_GET['post_id'];
                $data['id_currency']    = $_GET['id_currency'];
                $data['price']          = $_GET['price'];
                $data['date']           = $_GET['date'];
                $data['ip']             = $_GET['ip'];
                $data['hash']           = $_GET['hash'];
                $hash = $_GET['hash'];
                $url = LaterPayRequestHelper::getCurrentUrl();
                $url = preg_replace('/hash=.*?($|&)/', '', $url);
                $url = preg_replace('/&$/',            '', $url);
                // check hash for purchase
                if ( md5(md5($url) . LATERPAY_SALT) == $hash ) {
                    $LaterPayModelHistory = new LaterPayModelHistory();
                    $LaterPayModelHistory->setPaymentHistory($data);
                }
                $url = preg_replace('/post_id=.*?($|&)/',      '', $url);
                $url = preg_replace('/id_currency=.*?($|&)/',  '', $url);
                $url = preg_replace('/price=.*?($|&)/',        '', $url);
                $url = preg_replace('/date=.*?($|&)/',         '', $url);
                $url = preg_replace('/ip=.*?($|&)/',           '', $url);
                $url = preg_replace('/buy=.*?($|&)/',          '', $url);
                $url = preg_replace('/&$/',                    '', $url);
                header('Location: ' . $url);
                die;
            }
        }
    }

    /**
     * Update incorrect token and create token if it doesn't exist
     *
     * @access public
     */
    public static function tokenHook() {
        $GLOBALS['laterpay_access'] = false;

        $is_feed = self::is_feed();

        Logger::debug(
            'LaterPayPostContentController::tokenHook',
            array(
                !is_admin(),
                !self::is_login_page(),
                !self::is_cron_page(),
                !$is_feed,
                LaterPayBrowserHelper::browser_supports_cookies(),
                !LaterPayBrowserHelper::is_crawler()
            )
        );

        if ( !is_admin() && !self::is_login_page() && !self::is_cron_page() && !$is_feed && LaterPayBrowserHelper::browser_supports_cookies() && !LaterPayBrowserHelper::is_crawler() ) {

            Logger::debug('LaterPayPostContentController::tokenHook', array($_SERVER['REQUEST_URI']));

            $LaterPayClient = new LaterPayClient();
            if ( isset($_GET['lptoken']) ) {
                $LaterPayClient->setToken($_GET['lptoken'], true);
            }

            if ( !$LaterPayClient->hasToken() ) {
                $LaterPayClient->acquireToken();
            }

            // if Ajax request
            if ( (LaterPayRequestHelper::isAjax() && isset($_GET['id'])) || isset($_GET['id']) ) {
                $postid = $_GET['id'];
            } else {
                $url = LaterPayStatisticsHelper::getFullUrl($_SERVER);
                $postid = url_to_postid($url);
            }
            if ( !empty($postid) ) {
                $price = self::getPostPrice($postid);
                if ( $price > 0 ) {
                    $result = $LaterPayClient->getAccess($postid);
                    $access = false;
                    if ( !empty($result) && isset($result['articles'][$postid]) ) {
                        $access = $result['articles'][$postid]['access'];
                    }
                    $GLOBALS['laterpay_access'] = $access;
                }
            }
        }
    }

    /**
     * Check if current page is login page
     *
     * @return boolean is login page
     *
     * @access public
     */
    public static function is_login_page() {
        return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
    }

    /**
     * Check if current page is cron page
     *
     * @return boolean is cron page
     *
     * @access public
     */
    public static function is_cron_page() {
        return in_array($GLOBALS['pagenow'], array('wp-cron.php'));
    }

    /**
     * Check if current page is laterpay resource link
     *
     * @return boolean is cron page
     *
     * @access public
     */
    public static function is_resource_link() {
        return in_array($GLOBALS['pagenow'], array('lp-get.php'));
    }

    /**
     * Check if current request is RSS feed
     *
     * @return boolean is feed
     *
     * @access public
     */
    public static function is_feed() {
        $is_feed    = false;
        $qv         = wp_parse_args($_SERVER['QUERY_STRING']);
        $url        = parse_url($_SERVER['REQUEST_URI']);

        if ( (isset($qv['feed']) && '' != $qv['feed']) || (isset($url['path']) && preg_match('/feed/', $url['path'])) ) {
            $is_feed = true;
        }

        return $is_feed;
    }

    /**
     * Get current price for post with advanced pricing scheme defined
     *
     * @param object $post post
     *
     * @return float price
     *
     * @access public
     */
    public static function getAdvancedPrice( $post ) {
        if ( function_exists('date_diff') ) {
            $date_time = new DateTime(date('Y-m-d'));
            $days_since_publication = $date_time->diff(new DateTime(date('Y-m-d', strtotime($post->post_date))))->format("%a");
        } else {
            $d1 = strtotime(date('Y-m-d'));
            $d2 = strtotime($post->post_date);
            $diff_secs = abs($d1 - $d2);
            $days_since_publication = floor($diff_secs / (3600 * 24));
        }

        if ( self::isBeforeTransitionalPeriod($post, $days_since_publication) ) {
            $price = get_post_meta($post->ID, 'laterpay_start_price', true);
        } else {
            if ( self::isAfterTransitionalPeriod($post, $days_since_publication) ) {
                $price = get_post_meta($post->ID, 'laterpay_end_price', true);
            } else {    // transitional period between start and end of dynamic price change
                $price = self::calculateTransitionalPrice($post, $days_since_publication);
            }
        }

        return round($price, 2);
    }

    /**
     * Check if current date is after set date for end of dynamic price change
     *
     * @param object $post                   post
     * @param int    $days_since_publication days
     *
     * @return boolean
     */
    private static function isAfterTransitionalPeriod( $post, $days_since_publication ) {
        return get_post_meta($post->ID, 'laterpay_transitional_period_end_after_days', true) <= $days_since_publication || get_post_meta($post->ID, 'laterpay_transitional_period_end_after_days', true) == 0;
    }

    /**
     * Check if current date is before set date for end of dynamic price change
     *
     * @param object $post                   post
     * @param int    $days_since_publication days
     *
     * @return boolean
     */
    private static function isBeforeTransitionalPeriod( $post, $days_since_publication ) {
        return get_post_meta($post->ID, 'laterpay_change_start_price_after_days', true) >= $days_since_publication;
    }

    /**
     * Calculate transitional price between start price and end price based on linear equation
     *
     * @param type $post
     * @param int  $days_since_publication days
     *
     * @return float
     */
    private static function calculateTransitionalPrice( $post, $days_since_publication ) {
        $end_price          = get_post_meta($post->ID, 'laterpay_end_price', true);
        $start_price        = get_post_meta($post->ID, 'laterpay_start_price', true);
        $days_until_end     = get_post_meta($post->ID, 'laterpay_transitional_period_end_after_days', true);
        $days_until_start   = get_post_meta($post->ID, 'laterpay_change_start_price_after_days', true);

        $coefficient = ($end_price - $start_price) / ($days_until_end - $days_until_start);

        return get_post_meta($post->ID, 'laterpay_start_price', true) + ($days_since_publication - get_post_meta($post->ID, 'laterpay_change_start_price_after_days', true)) * $coefficient;
    }

    /**
     * get the LaterPay link for the post
     *
     * @param object $title title
     *
     * @return object
     */
    public static function getLPLink( $post_id ) {
        $currency = get_option('laterpay_currency');
        $price = self::getPostPrice($post_id);

        $LaterPayModelCurrency = new LaterPayModelCurrency();
        $LaterPayClient = new LaterPayClient();

        // data to register purchase after redirect from LaterPay
        $data = array(
            'post_id'     => $post_id,
            'id_currency' => $LaterPayModelCurrency->getCurrencyIdByShortName($currency),
            'price'       => $price,
            'date'        => time(),
            'buy'         => 'true',
            'ip'          => ip2long($_SERVER['REMOTE_ADDR']),
        );
        $url = LaterPayRequestHelper::getCurrentUrl();
        if ( strpos($url, '?') !== false || strpos($url, '&') !== false ) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= http_build_query($data);
        $hash = md5(md5($url) . LATERPAY_SALT);
        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => $post_id,
            'purchase_date' => time() . '000', // fix for PHP 32bit
            'pricing'       => $currency . ( $price * 100 ),
            'vat'           => LATERPAY_VAT,
            'url'           => $url . '&hash=' . $hash,
            'title'         => $GLOBALS['post']->post_title,
        );

        return $LaterPayClient->getAddUrl($params);
    }

    /**
     * Prepend LaterPay purchase button to title (heading) of post on single post pages
     *
     * @param object $title title
     *
     * @return object
     */
    public function modifyPostTitle( $title ) {
        if ( in_the_loop() ) {
            $post               = get_post();
            $post_id            = $post->ID;
            $price              = self::getPostPrice($post_id);
            $float_price        = (float) $price;
            $is_premium_content = $float_price > 0;
            $access             = $GLOBALS['laterpay_access'] || current_user_can('manage_options') || LaterPayUserHelper::user_has_full_access();
            $link               = self::getLPLink($post_id);
            $preview_post_as_visitor = LaterPayUserHelper::previewPostAsVisitor();
            $post_content_cached = LaterPayCacheHelper::siteUsesPageCaching();

            if ( $is_premium_content && is_single() && !is_page() ) {
                if ( $post_content_cached && !LaterPayRequestHelper::isAjax() ) {
                    $this->assign('post_id',    $post_id);

                    $title = $this->getTextView('partials/postTitle');
                } else {
                    if ( (!$access || $preview_post_as_visitor) ) {
                        $currency           = get_option('laterpay_currency');
                        $purchase_button    = '<a href="#" class="laterpay-purchase-link laterpay-purchase-button" data-laterpay="' . $link . '" data-icon="b" post-id="';
                        $purchase_button   .= $post_id . '" title="' . __('Buy now with LaterPay', 'laterpay') . '" ';
                        $purchase_button   .= 'data-preview-as-visitor="' . $preview_post_as_visitor . '">';
                        $purchase_button   .= sprintf(
                                                    __('%s<small>%s</small>', 'laterpay'),
                                                    LaterPayViewHelper::formatNumber($price, 2),
                                                    $currency
                                                );
                        $purchase_button   .= '</a>';
                        $title              = $purchase_button . $title;
                    }
                }
            }
        }

        return $title;
    }
}
