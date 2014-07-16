<?php

class LaterPayAdminController extends LaterPayAbstractController {
    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    public function __call($name, $arguments) {
        if ( substr($name, 0, 3) == 'run') {
            return $this->run( strtolower(substr($name, 3)) );
        } elseif ( substr($name, 0, 4) == 'help') {
            return $this->run( strtolower(substr($name, 4)) );
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
        if ( (isset($_GET['page'])) ) {
            $page = $_GET['page'];
        }
        // TODO: fix calls
        switch ( $tab ) {
        // render help for get started tab
        case 'get_started':
            $this->_render_help($page);
            break;
        // render pricing tab
        case 'pricing':
            $this->_render_help($page);
            break;
        // render appearance tab
        case 'appearance':
            $this->_render_help($page);
            break;
        // render account tab
        case 'account':
            $this->_render_help($page);
            break;
        default:
            $this->_render_help($page);
            break;
        }
    }
    /**
     * 
     * @param type $page
     * @return type
     * TODO: use as generic, fill data
     */
    protected function _render_help( $page ) {
        $screen = get_current_screen();
        //
        // Check if current screen is settings page we registered
        // Don't add help tab if it's not
        if ( $screen->id != $page )
            return;

        // Add help tabs
        $screen->add_help_tab(array(
            'id' => 'thsp_first_tab',
            'title' => __('First tab', 'thsp_contextual_help'),
            'content' => __('
			<p>Yeah, you can even embed videos, nice!</p>
			<iframe width="560" height="315" src="http://www.youtube.com/embed/RBA-lH2a6E8" frameborder="0" allowfullscreen></iframe>
			', 'thsp_contextual_help'
            ),
        ));

        $screen->add_help_tab(array(
            'id' => 'thsp_second_tab',
            'title' => __('Second tab', 'thsp_contextual_help'),
            'content' => __('
			<p>I\'m just a second tab that no one will ever click.</p>
			', 'thsp_contextual_help'
            ),
        ));

        // Set help sidebar
        $screen->set_help_sidebar(
                '
		<ul>
			<li><a href="http://thematosoup.com">' . __('Our website', 'ts-fab') . '</a></li>
			<li><a href="http://twitter.com/#!/thematosoup">Twitter</a></li>
			<li><a href="http://www.facebook.com/ThematoSoup">Facebook</a></li>
			<li><a href="http://plus.google.com/104360438826479763912">Google+</a></li>
			<li><a href="http://www.linkedin.com/company/thematosoup">LinkedIn</a></li>
		</ul>
		'
        );
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
