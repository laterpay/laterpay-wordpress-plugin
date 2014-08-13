<?php

class LaterPay_Controller_Admin extends LaterPay_Controller_Abstract
{

    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    /**
     * Show plugin in administrator panel.
     *
     * @return  void
     */
    public function add_to_admin_panel() {
        $plugin_page = LaterPay_Helper_View::$pluginPage;
        add_menu_page(
            __( 'LaterPay Plugin Settings', 'laterpay' ),
            'LaterPay',
            'edit_plugins',
            $plugin_page,
            array( $this, 'run' ),
            'dashicons-laterpay-logo',
            81
        );

        $activated = get_option( 'laterpay_plugin_is_activated', '' );
        // don't render submenu links, if the plugin was never activated before
        if ( $activated === '' ) {
            return;
        }
        $page_number    = 0;
        $menu           = LaterPay_Helper_View::get_admin_menu();
        foreach ( $menu as $name => $page ) {
            // don't render 'get started' submenu link, if the plugin was activated before
            if ( $activated !== '' && $name == 'get_started' ) {
                continue;
            }

            $slug = ! $page_number ? $plugin_page : $page['url'];

            $page_id = add_submenu_page(
                $plugin_page,
                $page['title'] . ' | ' . __( 'LaterPay Plugin Settings', 'laterpay' ),
                $page['title'],
                'edit_plugins',
                $slug,
                array( $this, 'run_' . $name )
            );
            add_action( 'load-' . $page_id, array( $this, 'help_' . $name ) );
            $page_number++;
        }
    }

    /**
     *
     * @param string $name
     * @param mixed  $args
     *
     * @return void
     */
    public function __call( $name, $args ) {
        if ( substr( $name, 0, 4 ) == 'run_' ) {
            return $this->run( strtolower( substr( $name, 4 ) ) );
        } elseif ( substr( $name, 0, 5 ) == 'help_' ) {
            return $this->help( strtolower( substr( $name, 5 ) ) );
        }
    }

    /**
     * @see LaterPay_Controller_Abstract::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load LaterPay-specific CSS
        wp_register_style(
            'laterpay-backend',
            $this->config->css_url . 'laterpay-backend.css',
            array(),
            $this->config->version
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_enqueue_style( 'laterpay-backend' );
        wp_enqueue_style( 'open-sans' );

        // load LaterPay-specific JS
        wp_register_script(
            'laterpay-backend',
            $this->config->js_url . 'laterpay-backend.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-backend' );

        // load HTML5 shim for IE <= 9 only
        if ( LaterPay_Helper_Browser::is_ie() && LaterPay_Helper_Browser::get_browser_major_version() <= 9 ) {
            wp_register_script(
                'html5-shim-ie',
                'http://html5shim.googlecode.com/svn/trunk/html5.js'
            );
            wp_enqueue_script( 'html5-shim-ie' );
        }
    }

    /**
     * Constructor for class LaterPayController, processes the tabs in the plugin backend.
     *
     * @param string $tab
     *
     * @return void
     */
    public function run( $tab = '' ) {
        $this->load_assets();

        if ( isset( $_GET['tab'] ) ) {
            $tab = $_GET['tab'];
        }

        $activated = get_option( 'laterpay_plugin_is_activated', '' );

        // always return the get started tab, if the plugin has never been activated before
        if ( $activated === '' ) {
            $tab            = 'get_started';
            $_GET['tab']    = 'get_started';
        }
        // return default tab, if no specific tab is requested
        if ( empty( $tab ) ) {
            $tab            = 'pricing';
            $_GET['tab']    = 'pricing';
        }
        // return default tab, if plugin is already activated and get started tab is requested
        if ( $activated == '1' && $tab == 'get_started' ) {
            $tab            = 'pricing';
            $_GET['tab']    = 'pricing';
        }

        switch ( $tab ) {
            // render get started tab
            case 'get_started':
                $get_started_controller = new LaterPay_Controller_Admin_GetStarted( $this->config );
                $get_started_controller->render_page();
                break;

            default:

            // render pricing tab
            case 'pricing':
                $pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
                $pricing_controller->render_page();
                break;

            // render appearance tab
            case 'appearance':
                $appearance_controller = new LaterPay_Controller_Admin_Appearance( $this->config );
                $appearance_controller->render_page();
                break;

            // render account tab
            case 'account':
                $account_controller = new LaterPay_Controller_Admin_Account( $this->config );
                $account_controller->render_page();
                break;
        }
    }

    /**
     * Render contextual help, depending on the current page.
     *
     * @param string $tab
     *
     * @return void
     */
    public function help( $tab = '' ) {
        switch ( $tab ) {
            case 'wp_edit_post':
            case 'wp_add_post':
                $this->render_add_edit_post_page_help();
                break;

            case 'get_started':
                break;

            case 'pricing':
                $this->render_pricing_tab_help();
                break;

            case 'appearance':
                $this->render_appearance_tab_help();
                break;

            case 'account':
                $this->render_account_tab_help();
                break;

            default:
                break;
        }
    }

    /**
     * Add contextual help for add / edit post page.
     *
     * @return  void
     */
    protected function render_add_edit_post_page_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_add_edit_post_page_help',
                                   'title'   => __( 'LaterPay', 'laterpay' ),
                                   'content' => __( '
                                        <p>
                                            <strong>Setting Prices</strong><br>
                                            You can set an individual price for each post.<br>
                                            Possible prices are either 0 Euro (free) or any value between 0.05 Euro (inclusive) and 5.00 Euro (inclusive).<br>
                                            If you set an individual price, category default prices you might have set for the post\'s category(s)
                                            won\'t apply anymore, unless you make the post use a category default price.
                                        </p>
                                        <p>
                                            <strong>Advanced Pricing Options</strong><br>
                                            You can define advanced price settings for each post to adjust prices automatically over time.<br>
                                            Choose from several presets and adjust them according to your needs.
                                            <br>
                                            For example, you could sell a breaking news post for 0.49 Euro (high interest within the first 24 hours)
                                            and automatically reduce the price to 0.05 Euro on the second day.
                                        </p>
                                        <p>
                                            <strong>Teaser</strong><br>
                                            The teaser should give your visitors a first impression of the content you want to sell.<br>
                                            You don\'t have to provide a teaser for every single post on your site:<br>
                                            by default, the LaterPay plugin uses the first 60 words of each post as teaser content.
                                            <br>
                                            Nevertheless, we highly recommend manually creating the teaser for each post, to increase your sales.
                                        </p>',
                                    'laterpay'
                                   ),
                               ) );
    }

    /**
     * Add contextual help for pricing tab.
     *
     * @return  void
     */
    protected function render_pricing_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_global_default_price',
                                   'title'   => __( 'Global Default Price', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        The global default price is used for all posts, for which no
                                                        category default price or individual price has been set.<br>
                                                        Accordingly, setting the global default price to 0 Euro makes
                                                        all articles free, for which no category default price or
                                                        individual price has been set.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_category_default_price',
                                   'title'   => __( 'Category Default Prices', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        A category default price is applied to all posts in a given
                                                        category that don\'t have an individual price.<br>
                                                        A category default price overwrites the global default price.<br>
                                                        If a post belongs to multiple categories, you can choose on
                                                        the add / edit post page, which category default price should
                                                        be effective.<br>
                                                        For example, if you have set a global default price of 0.15 Euro,
                                                        but a post belongs to a category with a category default price
                                                        of 0.30 Euro, that post will sell for 0.30 Euro.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_currency',
                                   'title'   => __( 'Currency', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        You can choose between different currencies for your blog.<br>
                                                        Changing the standard currency will not convert the prices you
                                                        have set.
                                                        Only the currency code next to the price is changed.<br>
                                                        For example, if your global default price is 0.10 Euro and you
                                                        change the default currency to U.S. dollar, the global default
                                                        price will be 0.10 U.S. dollar.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
    }

    /**
     * Add contextual help for appearance tab.
     *
     * @return  void
     */
    protected function render_appearance_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_appearance_tab_help_preview_mode',
                                   'title'   => __( 'Preview Mode', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        The preview mode defines, how teaser content is shown to your
                                                        visitors.<br>
                                                        You can choose between two preview modes:
                                                    </p>
                                                    <ul>
                                                        <li>
                                                            <strong>Teaser only</strong> &ndash; This mode shows only
                                                            the teaser with an unobtrusive purchase link below.
                                                        </li>
                                                        <li>
                                                            <strong>Teaser + overlay</strong> &ndash; This mode shows
                                                            the teaser and an excerpt of the full content under a
                                                            semi-transparent overlay that briefly explains LaterPay.<br>
                                                            The plugin never loads the entire content before a user has
                                                            purchased it.
                                                        </li>
                                                    </ul>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_appearance_tab_help_invoice_indicator',
                                   'title'   => __( 'Invoice Indicator', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        The plugin provides a code snippet you can insert into your
                                                        theme that displays the user\'s current LaterPay invoice total
                                                        and provides a direct link to his LaterPay user backend.<br>
                                                        You <em>don\'t have to</em> integrate this snippet, but we
                                                        recommend it for transparency reasons.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
    }

    /**
     * Add contextual help for account tab.
     *
     * @return  void
     */
    protected function render_account_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_account_tab_help_api_credentials',
                                   'title'   => __( 'API Credentials', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        To access the LaterPay API, you need LaterPay API credentials,
                                                        consisting of
                                                    </p>
                                                    <ul>
                                                        <li><strong>Merchant ID</strong> (a 22-character string) and</li>
                                                        <li><strong>API Key</strong> (a 32-character string).</li>
                                                    </ul>
                                                    <p>
                                                        LaterPay runs two completely separated API environments that
                                                        need <strong>different API credentials:</strong>
                                                    </p>
                                                    <ul>
                                                        <li>
                                                            The <strong>Sandbox</strong> environment for testing and
                                                            development use.<br>
                                                            In this environment you can play around with LaterPay
                                                            without fear, as your transactions will only be simulated
                                                            and not actually be processed.<br>
                                                            LaterPay guarantees no particular service level of
                                                            availability for this environment.
                                                        </li>
                                                        <li>
                                                            The <strong>Live</strong> environment for production use.</br>
                                                            In this environment all transactions will be actually
                                                            processed and credited to your LaterPay merchant account.<br>
                                                            The LaterPay SLA for availability and response time apply.
                                                        </li>
                                                    </ul>
                                                    <p>
                                                        The LaterPay plugin comes with a set of <strong>public Sandbox
                                                        credentials</strong> to allow immediate testing use.
                                                    </p>
                                                    <p>
                                                        If you want to switch to <strong>Live mode</strong> and sell
                                                        content, you need your individual <strong>Live API credentials.
                                                        </strong><br>
                                                        Due to legal reasons, we can email you those credentials only
                                                        once we have received a <strong>signed merchant contract</strong>
                                                        including <strong>all necessary identification documents</strong>
                                                        by ground mail.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_account_tab_help_plugin_mode',
                                   'title'   => __( 'Plugin Mode', 'laterpay' ),
                                   'content' => __( '
                                                    <p>You can run the LaterPay plugin in two modes:</p>
                                                    <ul>
                                                        <li>
                                                            <strong>Test Mode</strong> &ndash; The test mode lets you
                                                            test your plugin configuration.<br>
                                                            While providing the full plugin functionality, payments are
                                                            only simulated and not actually processed.<br>
                                                            The plugin will <em>only</em> be visible to admin users,
                                                            not to visitors.
                                                        </li>
                                                        <li>
                                                            <strong>Live Mode</strong> &ndash; In live mode, the plugin
                                                            is publicly visible and manages access to paid content.<br>
                                                            All payments are actually processed.
                                                        </li>
                                                    </ul>
                                                    <p>
                                                        Using the LaterPay plugin usually requires some adjustments on
                                                        your theme.<br>
                                                        Therefore, we recommend installing, configuring, and testing
                                                        the LaterPay plugin on a test system before activating it on
                                                        your production system.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
    }

    /**
     * Add WordPress pointers to pages.
     *
     * @return  void
     */
    public function modify_footer() {
        $dismissed_pointers = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $pointers = array();

        // add pointer to LaterPay plugin in admin menu
        if (
            get_option( 'laterpay_plugin_is_activated', '' ) == '' &&
            ! in_array( self::ADMIN_MENU_POINTER, $dismissed_pointers )
        ) {
            $pointers[] = self::ADMIN_MENU_POINTER;
        }
        // add pointers to LaterPay features on add / edit post page
        if ( ! in_array( self::POST_PRICE_BOX_POINTER, $dismissed_pointers ) ) {
            $pointers[] = self::POST_PRICE_BOX_POINTER;
        }
        if ( ! in_array( self::POST_TEASER_CONTENT_POINTER, $dismissed_pointers ) ) {
            $pointers[] = self::POST_TEASER_CONTENT_POINTER;
        }

        $this->assign( 'pointers', $pointers );

        echo $this->get_text_view( 'backend/partials/footer' );
    }

    /**
     * Process Ajax requests for post previewing settings.
     *
     * @return void
     */
    public static function process_ajax_requests() {
        if ( isset( $_POST['form'] ) ) {
            // check for required capabilities to perform action
            if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => __("You don't have sufficient user capabilities to do this.", 'laterpay' )
                    )
                );
            }

            if ( function_exists( 'check_admin_referer' ) ) {
                check_admin_referer( 'laterpay_form' );
            }

            switch ( $_POST['form'] ) {
                case 'post_page_preview':
                    $current_user = wp_get_current_user();
                    if ( ! ( $current_user instanceof WP_User ) ) {
                        wp_send_json(
                            array(
                                'success' => false,
                                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                            )
                        );
                    }
                    $result = add_user_meta( $current_user->ID, 'laterpay_preview_post_as_visitor', $_POST['preview_post'], true )
                              || update_user_meta( $current_user->ID, 'laterpay_preview_post_as_visitor', $_POST['preview_post'] );
                    wp_send_json(
                        array(
                            'success' => true,
                            'message' => __( 'Updated.', 'laterpay' )
                        )
                    );
                    break;

                case 'hide_statistics_pane':
                    $current_user = wp_get_current_user();
                    if ( ! ( $current_user instanceof WP_User ) ) {
                        die;
                    }
                    $result = add_user_meta( $current_user->ID, 'laterpay_hide_statistics_pane', $_POST['hide_statistics_pane'], true )
                              || update_user_meta( $current_user->ID, 'laterpay_hide_statistics_pane', $_POST['hide_statistics_pane'] );
                    die;
                    break;

                default:
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                    break;
            }
        }
    }

}
