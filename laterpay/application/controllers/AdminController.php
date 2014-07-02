<?php

class AdminController extends AbstractController {
    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    public function loadAssets() {
        parent::loadAssets();
        global $laterpay_version;

        // load LaterPay-specific CSS
        wp_register_style(
            'laterpay-backend',
            LATERPAY_ASSET_PATH . '/css/laterpay-backend.css',
            array(),
            $laterpay_version
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038'
        );
        wp_enqueue_style('laterpay-backend');
        wp_enqueue_style('open-sans');

        // load LaterPay-specific JS
        wp_register_script(
            'laterpay-backend',
            LATERPAY_ASSET_PATH . '/js/laterpay-backend.js',
            array('jquery'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-backend');

        // load HTML5 shim for IE <= 9 only
        if ( BrowserHelper::is_ie() && BrowserHelper::get_browser_major_version() <= 9 ) {
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
    public function run() {
        $this->loadAssets();

        if ( (isset($_GET['tab'])) ) {
            $tab = $_GET['tab'];
        } else {
            $tab = '';
        }

        // return default tab, if no specific tab is requested
        if ( empty($tab) ) {
            if ( get_option('laterpay_plugin_is_activated') == '0' ) {
                $tab            = 'get_started';
                $_GET['tab']    = 'get_started';
            } else {
                $tab            = 'pricing';
                $_GET['tab']    = 'pricing';
            }
        }
        // return default tab, if plugin is already activated and get started tab is requested
        if ( get_option('laterpay_plugin_is_activated') == '1' && $tab == 'get_started' ) {
            $tab                = 'pricing';
            $_GET['tab']        = 'pricing';
        }

        switch ( $tab ) {
        // render get started tab
        case 'get_started':
            $GetStartedController = new GetStartedController();
            $GetStartedController->page();
            break;

        default:

        // render pricing tab
        case 'pricing':
            $PricingController = new PricingController();
            $PricingController->page();
            break;

        // render appearance tab
        case 'appearance':
            $AppearanceController = new AppearanceController();
            $AppearanceController->page();
            break;

        // render account tab
        case 'account':
            $AccountController = new AccountController();
            $AccountController->page();
            break;
        }
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
        if ( !in_array(self::ADMIN_MENU_POINTER, $dismissed_pointers) ) {
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

        echo $this->getTextView('adminFooter');
    }

    /**
     * Process Ajax requests
     *
     * @access public
     */
    public static function pageAjax() {
        if ( isset($_POST['form']) ) {
            // check for required privileges to perform action
            if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
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
