<?php

class LaterPayAdminController extends LaterPayAbstractController {
    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    public function __call($name, $arguments) {
        if ( substr($name, 0, 4) == 'run_') {
            return $this->run( strtolower(substr($name, 4)) );
        } elseif ( substr($name, 0, 5) == 'help_') {
            return $this->help( strtolower(substr($name, 5)) );
        }
    }

    public function loadAssets() {
        parent::loadAssets();
        global $laterpay_version;

        // load LaterPay-specific CSS
        wp_register_style(
            'laterpay-backend',
            LATERPAY_ASSETS_PATH . '/css/laterpay-backend.css',
            array(),
            $laterpay_version
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_enqueue_style('laterpay-backend');
        wp_enqueue_style('open-sans');

        // load LaterPay-specific JS
        wp_register_script(
            'laterpay-backend',
            LATERPAY_ASSETS_PATH . '/js/laterpay-backend.js',
            array('jquery'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-backend');

        // load HTML5 shim for IE <= 9 only
        if ( LaterPayBrowserHelper::is_ie() && LaterPayBrowserHelper::get_browser_major_version() <= 9 ) {
            wp_register_script(
                'html5-shim-ie',
                'http://html5shim.googlecode.com/svn/trunk/html5.js'
            );
            wp_enqueue_script('html5-shim-ie');
        }
    }

    /**
     * Constructor for class LaterPayController, processes the output pages
     */
    public function run($tab = '') {
        $this->loadAssets();

        if ( (isset($_GET['tab'])) ) {
            $tab = $_GET['tab'];
        }
        $activated = get_option('laterpay_plugin_is_activated', '');
        // return default tab, if no specific tab is requested
        if ( empty($tab) ) {
            if ( $activated == '0' ) {
                $tab            = 'get_started';
                $_GET['tab']    = 'get_started';
            } else {
                $tab            = 'pricing';
                $_GET['tab']    = 'pricing';
            }
        }
        // return default tab, if plugin is already activated and get started tab is requested
        if ( $activated == '1' && $tab == 'get_started' ) {
            $tab                = 'pricing';
            $_GET['tab']        = 'pricing';
        }

        if ( $activated === '' ) { // never activated before
            $tab            = 'get_started';
            $_GET['tab']    = 'get_started';
        }

        switch ( $tab ) {
        // render get started tab
        case 'get_started':
            $LaterPayGetStartedController = new LaterPayGetStartedController();
            $LaterPayGetStartedController->page();
            break;

        default:

        // render pricing tab
        case 'pricing':
            $LaterPayPricingController = new LaterPayPricingController();
            $LaterPayPricingController->page();
            break;

        // render appearance tab
        case 'appearance':
            $LaterPayAppearanceController = new LaterPayAppearanceController();
            $LaterPayAppearanceController->page();
            break;

        // render account tab
        case 'account':
            $LaterPayAccountController = new LaterPayAccountController();
            $LaterPayAccountController->page();
            break;
        }
    }
    
    /**
     * 
     */
    public function help($tab = '') {
//        if ( (isset($_GET['page'])) ) {
//            $page = $_GET['page'];
//        }
//        $screen = get_current_screen();
//        if ( $screen->id != $page ) {
//            return;
//        }

        switch ( $tab ) {
        
        case 'wp_edit_post':
        case 'wp_add_post':
            $this->_renderPostEditPageHelp();
            break;
        // render help for get started tab
        case 'get_started':
            break;
        // render pricing tab
        case 'pricing':
            $this->_renderPricingTabHelp();
            break;
        // render appearance tab
        case 'appearance':
            $this->_renderAppearanceTabHelp();
            break;
        // render account tab
        case 'account':
            $this->_renderAccountTabHelp();
            break;
        default:
            break;
        }
    }
    
    /**
     * Add help for view post page
     * 
     * @return null
     */
    protected function _renderPostViewPageHelp( ) {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'      => 'laterpay_post_edit_page_teaser',
            'title'   => __('Teaser', 'laterpay'),
            'content' => __('
                <p>The plugin provides statistics for the following data for each post:</p>
                <ul>
                    <li>Total sales: The total number of sales of this particular post </li>
                    <li>Total revenue: The total revenue of this particular post </li>
                    <li>Today\'s revenue </li>
                    <li>Today\'s visitors </li>
                    <li>Today\'s conversion rate: The share of visitors that actually purchased </li>
                    <li>History charts for sales, revenue, and conversion rate of the last 30 days </li>
                </ul>
                <p>
                Please note that the provided statistics are only indicators and not binding for payouts in any 
                way. 
                </p>', 
             'laterpay'
            ),
        ));
    }
    
    /**
     * Add help for add/edit post page
     * 
     * @return null
     */
    protected function _renderPostEditPageHelp( ) {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'      => 'laterpay_post_edit_page_teaser',
            'title'   => __('Teaser', 'laterpay'),
            'content' => __('
                <p>The teaser should give your visitors a first impression of the content you want to sell. You 
                don’t have to provide a teaser for every single post on your blog: The LaterPay plugin already 
                took care of that and used the first 120 words (per default) of each post for the teaser 
                content. </p>
                Though, we recommend providing a specifically created teaser for each post to increase your 
                sales.</p>', 
             'laterpay'
            ),
        ));
        $screen->add_help_tab(array(
            'id'      => 'laterpay_post_setting_prices',
            'title'   => __('Setting Prices', 'laterpay'),
            'content' => __('
                <p>You can set an individual price for each post. This price can be set between (including) 0.05 
                Euro and (including) 5.00 Euro. If you set an individual price, category default prices you 
                might have set for the post’s category won’t apply anymore until you reactive them by clicking 
                „Apply category default price“.
                </p>', 
             'laterpay'
            ),
        ));
        $screen->add_help_tab(array(
            'id'      => 'laterpay_post_advance_pricing',
            'title'   => __('Advanced Pricing Options', 'laterpay'),
            'content' => __('
                <p>You can define advanced price settings for each post to adjust prices automatically over 
                time. You can choose from several presets and adjust them according to your needs. 
                E.g. you could sell a breaking news post for 0.49 Euro (high interest within the first 24 hours) 
                and automatically reduce the price to 0.05 Euro on the second day.
                </p>', 
             'laterpay'
            ),
        ));
    }
    
    /**
     * Add help for pricing tab
     * 
     * @return null
     */
    protected function _renderPricingTabHelp( ) {
        $screen = get_current_screen();
        // Add  help tabs
        $screen->add_help_tab(array(
            'id'      => 'laterpay_pricing_global_price',
            'title'   => __('Global Default Price', 'laterpay'),
            'content' => __('
                <p>The plugin provides a code snippet you can insert into your theme that displays the user\'s 
                    current LaterPay invoice total and provides a direct link to his LaterPay user backend. You 
                    don\'t have to integrate this snippet, but we recommend it for transparency reasons.</p>', 
             'laterpay'
            ),
        ));
        $screen->add_help_tab(array(
            'id'      => 'laterpay_pricing_currency',
            'title'   => __('Currency', 'laterpay'),
            'content' => __('
                <p>You can choose between different currencies for your blog. Changing the standard currency 
                will not convert the prices you have set. Only the currency code next to the price is changed. 
                So, if your global default price is 0.10 Euro and you change the default currency to U.S. 
                dollar, the global default price will be 0.10 U.S. dollar. 
                </p>', 
             'laterpay'
            ),
        ));
        $screen->add_help_tab(array(
            'id'      => 'laterpay_pricing_category_price',
            'title'   => __('Category Default Price', 'laterpay'),
            'content' => __('
                <p>A category default price is applied to all posts in a given category that don’t have an 
                individual price. The category default price overwrites the global default price. 
                So, if your global default price 0.15 Euro but a post belongs to a category with a category 
                default price of 0.30 Euro, the post is sold for 0.30 Euro. 
                </p>', 
             'laterpay'
            ),
        ));
    }
    
    /**
     * Add help for appearance tab
     * 
     * @return null
     */
    protected function _renderAppearanceTabHelp( ) {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'      => 'laterpay_appearance_preview_mode',
            'title'   => __('Preview Mode', 'laterpay'),
            'content' => __('
                <p>The preview mode defines, how teaser content is shown to your visitors. You can choose 
                between two preview modes:</p>
                <ul>
                <li>1) Teaser only: This mode shows only the teaser with an unobtrusive purchase link below. </li>
                <li>2) Teaser + overlay: This mode shows the teaser and an excerpt of the full content under a 
                semi-transparent overlay that briefly explains LaterPay. The plugin never loads the entire 
                content before a user has purchased it.</li>
                </ul>', 
             'laterpay'
            ),
        ));
    }
    
    /**
     * Add help for account tab
     * 
     * @return null
     */
    protected function _renderAccountTabHelp( ) {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'      => 'laterpay_account_invoice_indicator',
            'title'   => __('Invoice Indicator', 'laterpay'),
            'content' => __('
                <p>The plugin provides a code snippet you can insert into your theme that displays the user\'s 
                    current LaterPay invoice total and provides a direct link to his LaterPay user backend. You 
                    don\'t have to integrate this snippet, but we recommend it for transparency reasons.</p>', 
             'laterpay'
            ),
        ));
        $screen->add_help_tab(array(
            'id'      => 'laterpay_account_plugin_mode',
            'title'   => __('API Credentials and Plugin Mode', 'laterpay'),
            'content' => __('
                <p>You can use the LaterPay WordPress plugin in two modes: 
                Test mode: The test mode lets you test your plugin configuration. While providing the full 
                plugin functionality, no real transactions are processed. 
                Your visitors will be able to distinguish between test and live mode through a banner in all 
                LaterPay dialogs. </p>
                <p>
                We highly recommend configuring and testing the integration of the LaterPay WordPress 
                plugin into your site on a test system, not on your production system. 
                Live mode: In live mode, all your transactions will be processed. For legal reasons, LaterPay 
                has to identify you as a merchant. Please mail us the signed merchant contract and the 
                necessary identification documents and we will send you LaterPay API credentials for 
                switching your plugin to live mode. </p>', 
             'laterpay'
            ),
        ));
    }

    /**
     * Add pointers to pages
     *
     * @access public
     */
    public function modifyFooter() {
        $dismissed_pointers = explode(',', (string)get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));
        $pointers = array();
        // add pointer to LaterPay plugin in admin menu
        if ( get_option('laterpay_plugin_is_activated') == '0' && !in_array(self::ADMIN_MENU_POINTER, $dismissed_pointers) ) {
            $pointers[] = self::ADMIN_MENU_POINTER;
        }
        // add pointers to LaterPay features on add / edit post page
        if ( !in_array(self::POST_PRICE_BOX_POINTER, $dismissed_pointers) ) {
            $pointers[] = self::POST_PRICE_BOX_POINTER;
        }
        if ( !in_array(self::POST_TEASER_CONTENT_POINTER, $dismissed_pointers) ) {
            $pointers[] = self::POST_TEASER_CONTENT_POINTER;
        }

        $this->assign('pointers', $pointers);

        echo $this->getTextView('partials/adminFooter');
    }

    /**
     * Process Ajax requests
     *
     * @access public
     */
    public static function pageAjax() {
        if ( isset($_POST['form']) ) {
            // check for required privileges to perform action
            if ( !LaterPayUserHelper::can('laterpay_read_post_statistics', null, false) ) {
                echo Zend_Json::encode(
                    array(
                        'success' => false,
                        'message' => __('You don´t have sufficient user privileges to do this.', 'laterpay')
                    )
                );
                die;
            }

            if ( function_exists('check_admin_referer') ) {
                check_admin_referer('laterpay_form');
            }

            switch ( $_POST['form'] ) {
                case 'post_page_preview':
                    $current_user = wp_get_current_user();
                    if ( !($current_user instanceof WP_User) ) {
                        echo Zend_Json::encode(
                            array(
                                'success' => false,
                                'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                            )
                        );
                        die;
                    }
                    $result = add_user_meta($current_user->ID, 'laterpay_preview_post_as_visitor', $_POST['preview_post'], true)
                            || update_user_meta($current_user->ID, 'laterpay_preview_post_as_visitor', $_POST['preview_post']);
                    echo Zend_Json::encode(
                        array(
                            'success' => true,
                            'message' => __('Updated.', 'laterpay')
                        )
                    );
                    die;
                    break;

                case 'hide_statistics_pane':
                    $current_user = wp_get_current_user();
                    if ( !($current_user instanceof WP_User) ) {
                        die;
                    }
                    $result = add_user_meta($current_user->ID, 'laterpay_hide_statistics_pane', $_POST['hide_statistics_pane'], true)
                            || update_user_meta($current_user->ID, 'laterpay_hide_statistics_pane', $_POST['hide_statistics_pane']);
                    die;
                    break;

                default:
                    echo Zend_Json::encode(
                        array(
                            'success' => false,
                            'message' => __('An error occurred when trying to save your settings. Please try again.', 'laterpay')
                        )
                    );
                    die;
                    break;
            }
        }
    }

}
