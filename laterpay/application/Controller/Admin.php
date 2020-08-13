<?php

/**
 * LaterPay admin controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin extends LaterPay_Controller_Base
{

    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_admin_head' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'add_html5shiv_to_admin_head' ),
            ),
            'laterpay_admin_menu' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'add_to_admin_panel' ),
            ),
            'laterpay_admin_menu_data' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'get_admin_menu' ),
            ),
            'laterpay_admin_footer_scripts' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'modify_footer' ),
            ),
            'laterpay_post_edit' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'help_wp_edit_post' ),
            ),
            'laterpay_post_new' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'help_wp_add_post' ),
            ),
            'laterpay_admin_enqueue_scripts' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'add_plugin_admin_assets' ),
                array( 'add_admin_pointers_script' ),
            ),
        );
    }

    /**
     * Show plugin in administrator panel.
     *
     * @return void
     */
    public function add_to_admin_panel() {
        if( get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $plugin_page = LaterPay_Helper_View::$pluginPage;
        } else {
            $plugin_page = 'laterpay-account-tab';
        }
        add_menu_page(
            __( 'Laterpay Plugin Settings', 'laterpay' ),
            'Laterpay',
            'moderate_comments', // allow Super Admin, Admin, and Editor to view the settings page
            $plugin_page,
            array( $this, 'run' ),
            'dashicons-laterpay-logo',
            81
        );

        $menu = LaterPay_Helper_View::get_admin_menu();
        foreach ( $menu as $name => $page ) {
            $slug    = $page['url'];
            $page_id = add_submenu_page(
                $plugin_page,
                $page['title'] . ' | ' . __( 'Laterpay Plugin Settings', 'laterpay' ),
                $page['title'],
                $page['cap'],
                $slug,
                isset( $page['run'] ) ? $page['run'] : array( $this, 'run_' . $name )
            );
            if ( isset( $page['submenu'] ) ) {
                $sub_page   = $page['submenu'];
                add_submenu_page(
                    $sub_page['name'],
                    $sub_page['title'] . ' | ' . __( 'Laterpay Plugin Settings', 'laterpay' ),
                    $sub_page['title'],
                    $page['cap'],
                    $sub_page['url'],
                    array( $this, 'run_' . $sub_page['name'] )
                );
            }
            LaterPay_Hooks::add_wp_action( 'load-' . $page_id, 'laterpay_load_' . $page_id );
            $help_action = isset( $page['help'] ) ? $page['help'] : array( $this, 'help_' . $name );
            laterpay_event_dispatcher()->add_listener( 'laterpay_load_' . $page_id, $help_action );
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
        if ( substr( $name, 0, 4 ) === 'run_' ) {
            return $this->run( strtolower( substr( $name, 4 ) ) );
        } elseif ( substr( $name, 0, 5 ) === 'help_' ) {
            return $this->help( strtolower( substr( $name, 5 ) ) );
        }
    }

    /**
     * @see LaterPay_Core_View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load LaterPay-specific CSS
        wp_register_style(
            'laterpay-backend',
            $this->config->get( 'css_url' ) . 'laterpay-backend.css',
            array(),
            $this->config->get( 'version' )
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_register_style(
            'laterpay-select2',
            $this->config->get( 'css_url' ) . 'vendor/select2.min.css',
            array(),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'open-sans' );
        wp_enqueue_style( 'laterpay-select2' );
        wp_enqueue_style( 'laterpay-backend' );

        // load LaterPay-specific JS
        wp_register_script(
            'laterpay-velocity',
            $this->config->get( 'js_url' ) . 'vendor/velocity.min.js',
            array(),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-backend',
            $this->config->get( 'js_url' ) . 'laterpay-backend.js',
            array( 'jquery', 'laterpay-velocity' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'laterpay-velocity' );
        wp_enqueue_script( 'laterpay-backend' );

    }

    /**
     * Add html5shim to the admin_head() for Internet Explorer < 9.
     *
     * @wp-hook admin_head
     * @param LaterPay_Core_Event $event
     * @return void
     */
    public function add_html5shiv_to_admin_head( LaterPay_Core_Event $event ) {
        $view_args = array(
            'scripts' => array(
                '//html5shim.googlecode.com/svn/trunk/html5.js',
            ),
        );
        $this->assign( 'laterpay', $view_args );

        $this->render( 'backend/partials/html5shiv' );
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

        if ( '' === $tab ) {
            $tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
        }

        switch ( $tab ) {
            case 'pricing':
                $pricing_controller = new LaterPay_Controller_Admin_Pricing( $this->config );
                $pricing_controller->render_page();
                break;

            // render contributions tab
            case 'contributions':
                $contributions_controller = new LaterPay_Controller_Admin_Contributions( $this->config );
                $contributions_controller->render_page();
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

            // render advanced tab
            case 'advanced':
                $advanced_controller = new LaterPay_Controller_Admin_Advanced( $this->config );
                $advanced_controller->render_page();
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
     * @return void
     */
    protected function render_add_edit_post_page_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'laterpay_add_edit_post_page_help',
            'title'   => __( 'Laterpay', 'laterpay' ),
            'content' => __( '
            <p>
                <strong>Setting Prices</strong><br>
                You can set an individual price for each post.<br>
                Possible prices are either 0.00 (free) or any value between 0.05 (inclusive) and 149.99 (inclusive).<br>
                If you set an individual price, category default prices you might have set for the post\'s category(s)
                won\'t apply anymore, unless you make the post use a category default price.
            </p>
            <p>
                <strong>Dynamic Pricing Options</strong><br>
                You can define dynamic price settings for each post to adjust prices automatically over time.<br>
                <br>
                For example, you could sell a "breaking news" post for 0.49 (high interest within the first 24 hours)
                and automatically reduce the price to 0.05 on the second day.
            </p>
            <p>
                <strong>Teaser</strong><br>
                The teaser should give your visitors a first impression of the content you want to sell.<br>
                You don\'t have to provide a teaser for every single post on your site:<br>
                by default, the Laterpay plugin uses the first 60 words of each post as teaser content.
                <br>
                Nevertheless, we highly recommend manually creating the teaser for each post, to increase your sales.
            </p>
            <p>
                <strong>Pay Later</strong><br>
                If you choose to sell your content as <strong>Pay Later</strong>, a user pays the purchased content <strong>later</strong>. The purchase is added to his Laterpay invoice and he has to log in to Laterpay and pay, once his invoice has reached 5.00 (EUR or USD).<br>
                Laterpay <strong>recommends</strong> Pay Later for all prices up to 5.00 as they deliver the <strong>best purchase experience</strong> for your users.<br>
                PPU is possible for prices between (including) <strong>0.05</strong> and (including) <strong>5.00</strong>.
            </p>
            <p>
                <strong>Pay Now</strong><br>
                If you sell your content as <strong>Pay Now</strong>, a user has to <strong>log in</strong> to Laterpay and <strong>pay</strong> for your content <strong>immediately</strong>.<br>
                Pay Now are especially suitable for higher-value content and / or content that immediately occasions costs (e. g. license fees for a video stream).<br>
                Pay Now are possible for prices between (including) <strong>1.00 € (in Europe) / $ 1.99 (in the U.S.)</strong> and (including) <strong>149.99</strong>.
            </p>', 'laterpay'
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

        // Add LaterPay content contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_help_content',
                'title'   => __( 'Laterpay Content', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sUse the Laterpay Content section to select what types of WordPress content you would like to sell using Laterpay. %3$s The most common types are Pages, Posts, and Media but additional options may be available depending on the other plugins that you have installed.%2$s
                    %1$s%4$sTIP:%5$s If you are not sure what kind of content you would like to sell, we recommend starting with Posts & Media. This should ensure that your Pages (typically your Home Page, About Us Page, etc) remain free while you can monetize the majority of your other content (typically Posts).%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>'
                ),
            )
        );

        // Add Global Default Price contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_help_global_default_price',
                'title'   => __( 'Global Default Price', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sSetting the Global Default Price will determine the standard behavior of your monetized content. There are three options to choose from and we will go through each one in detail and provide a few examples to help determine which is the best option based on your strategy:%2$s
                    %6$s
                    %8$s%4$sFREE unless price is set on post page or by category%5$s%9$s
                        %4$sDescription:%5$s All articles will be free by default. Time Passes & Subscriptions will only be displayed if the article matches a Category Default Price or has an Individual Article Price set on the Post Page.%3$s%3$s
                    %8$s%4$sPosts cannot be purchased individually%5$s%9$s
                        %4$sDescription:%5$s Only Time Passes & Subscriptions will be displayed in the purchase dialog.%3$s%3$s
                    %8$s%4$sSet individual article default price%5$s%9$s
                        %4$sDescription:%5$s All single pieces of content will be for sale at this price unless overridden.%3$s%3$s
                    %7$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>',
                    '<ol>',
                    '</ol>',
                    '<li>',
                    '</li>'

                ),
            )
        );

        // Add Category Default Price contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_help_category_default_price',
                'title'   => __( 'Category Default Prices', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sA category default price is applied to all posts in a given category that don\'t have an individual price assigned to them on the edit post page.%2$s
                    %1$sA category default price overwrites the global default price. If a post belongs to multiple categories, you can choose on the add / edit post page, which category default price should be effective.%2$s
                    %1$sFor example, if you have set a global default price of 0.15, but a post belongs to a category with a category default price of 0.30, that post will sell for 0.30.%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>'
                ),
            )
        );

        // Add Time Passes contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_help_time_passes',
                'title'   => __( 'Time Passes', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sWith time passes, you can offer your users time-limited access to your content. You can define which content a time pass should cover and for which period of time it should be valid. A time pass can be valid for all Laterpay content%2$s
                    %6$s
                        %8$son your entire website,%9$s
                        %8$sin specific category/ies, or%9$s
                        %8$son your entire website except from a specific category/ies.%9$s
                    %7$s
                    %1$sThe validity period of a time pass starts with the purchase and is defined for a continuous use – i.e. it doesn\'t matter, if a user is on your website during the entire validity period. After a time pass has expired, the access to the covered content is automatically refused. %2$s
                    %4$sDeleting Time Passes%5$s
                    %1$sIf you delete a time pass, users who have bought this time pass will still have access to the covered content. Deleted time passes can\'t be restored.%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>',
                    '<ul>',
                    '</ul>',
                    '<li>',
                    '</li>'
                ),
            )
        );

        // Add Subscriptions contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_help_subscriptions',
                'title'   => __( 'Subscriptions', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sSubscriptions work exactly like time passes, with a simple difference: They renew automatically.%2$s
                    %1$sWhen a user purchases a subscription, they have access to all the content covered by this subscription during the validity period. Of course, you can still sell your content individually.%2$s
                    %4$sDeleting a Subscription%5$s
                    %1$sIf you delete a subscription it, it will continue to renew for users who have an active subscription until the user cancels it. Existing subscribers will still have access to the content in their subscription. New users won\'t be able to buy the subscription anymore.%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>'
                ),
            )
        );

        // Add Vouchers contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_vouchers',
                'title'   => __( 'Vouchers', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sVoucher codes can be generated for time passes and subscriptions. To create a voucher code, simply click “+ Generate Voucher Code” at the bottom of the time pass or subscription box. A random 6 character code will be generated but this can be overridden with a custom 6 characters if you choose.%2$s
                    %1$sYou can create any number of voucher codes. A voucher code allows one (or multiple) user(s) to purchase a time pass or subscription for a reduced price. A user can enter a voucher code right below the time passes by clicking "I have a voucher". If the entered code is a valid voucher code, the price of the respective offer will be reduced.%2$s
                    %6$s
                        A few key things to note when using voucher codes:
                        %8$sEach active voucher can be redeemed an unlimited number of times%9$s
                        %8$sIf a new user signs up for a subscription using a voucher code, the voucher code will reduce the price for the entirety of the subscription%9$s
                    %7$s
                    %1$sFor example, if you have a monthly subscription regularly priced at 10 per month and generate a voucher code which makes that subscription available for 5 per month, anyone who signs up for that subscription using the voucher code will pay 5 every month.%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>',
                    '<ul>',
                    '</ul>',
                    '<li>',
                    '</li>'
                ),
            )
        );

        // Add additional content monetize contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_pricing_tab_additional_ways',
                'title'   => __( 'Additional Ways to Monetize Content', 'laterpay' ),
                'content' => sprintf( __(
                    '%4$sIndividual Article Price%5$s
                    %1$sIf you ever need to override a price for a specific article, you may do so on the WordPress Edit Post page. To do this simply:%2$s
                    %6$s
                        %8$sNavigate to the post you would like to override%9$s
                        %8$sChoose to Edit that post using the WordPress Admin%9$s
                        %8$sIn the right sidebar you should see a Laterpay section where you can select to use the Global Default Price, a Category Default Price (when applicable), or to set an Individual Price. By selecting the Individual Price, you will be able to override any other defaults for this specific post.%9$s
                    %7$s
                    Check out other advanced pricing options on the %10$sAdvanced Tab%11$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<strong>',
                    '</strong>',
                    '<ol>',
                    '</ol>',
                    '<li>',
                    '</li>',
                    '<a href="' . esc_url( add_query_arg( LaterPay_Helper_Request::laterpay_encode_url_params( array( 'page' => 'laterpay-account-tab' ) ), admin_url( 'admin.php' ) ) ) . '">',
                    '</a>'
                ),
            )
        );

        // Add a sidebar for general help.
        $screen->set_help_sidebar(
            '<br/><p><strong>' . esc_html__( 'Need additional help?', 'laterpay' ) . '</strong></p>' .
            '<p>' . sprintf( esc_html__( 'For more instruction on setting up Pricing, %sclick here%s.', 'laterpay' ), '<a href="https://www.laterpay.net/academy/wordpress-pricing" target="_blank">', '</a>' ) . '</p>'
        );
    }

    /**
     * Add contextual help for appearance tab.
     *
     * @return  void
     */
    protected function render_appearance_tab_help() {
        $screen = get_current_screen();

        // Add appearance configuration contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_appearance_tab_help_configure_appearance',
                'title'   => __( 'Configure Appearance', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sThe top portion of this page allows you to customize how your pricing options are displayed. Below are the different options available. By checking & un-checking the corresponding checkboxes, you can easily see how the overlay will be displayed using the preview section to the right.%2$s
                    %1$sOnce you have your display options configured, %6$sbe sure to click save%7$s at the bottom of the Configure Appearance section to apply these changes to your site.%2$s
                     %4$s
                        %8$s
                        %6$sShow purchase button above article%7$s - By enabling this option, a button displaying the article purchase price will be displayed at the top right of the post.
                        %4$s
                            %8$s
                            %6$sCustomize position of purchase button%7$s - Use the provided WordPress code to customize the position of the button described above.
                            %9$s
                        %5$s
                        %9$s

                        %8$s
                        %6$sShow Purchase Overlay%7$s - Display an overlay with the available purchase options over your paid content.
                        %9$s

                        %8$s
                        %6$sHeader%7$s - Adjust the header text that is displayed at the top of the Purchase Overlay.
                        %9$s

                        %8$s
                        %6$sShow Laterpay Introduction%7$s - In the Purchase Overlay, provide information describing Laterpay to your customers.
                        %9$s

                        %8$s
                        %6$sShow Time Passes & Subscriptions below modal%7$s - Display Time Pass &/or Subscription options as tickets at the bottom of your content.
                        %4$s
                            %8$s
                            %6$sCustomize position of Time Passes & Subscriptions%7$s - Use the provided WordPress code to customize the position of the items described above.
                            %9$s
                        %5$s
                        %9$s

                        %8$s
                        %6$sAdd custom HTML section below payment button%7$s - In this section you can choose to add custom HTML or text content at the bottom of the Purchase Overlay.
                        %9$s

                        %8$s
                        %6$sShow valid payment options%7$s - Display a footer section at the bottom of the Purchase Overlay with images of the various payment options available.
                        %9$s
                     %5$s
                    ',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<ul>',
                    '</ul>',
                    '<strong>',
                    '</strong>',
                    '<li>',
                    '</li>'
                ),
            )
        );

        // Add customize colors contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_appearance_tab_help_customize_colors',
                'title'   => __( 'Customize Colors', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sUse the lower half of this page to customize the colors of the Laterpay elements. This section can be used to ensure that the color scheme matches your %3$s theme and brand. The following customizations are available:%2$s
                     %4$s
                        %8$s
                        %6$sHeader background color%7$s - The header background color defines a custom color used as the background of the Purchase Overlay header.
                        %9$s

                        %8$s
                        %6$sPurchase option background color%7$s - This option defines a custom color for the background of the Purchase Overlay.
                        %9$s

                        %8$s
                        %6$sMain text color%7$s - The main text color defines the text color for the sub-headers (purchase options) within the Purchase Overlay.
                        %9$s

                        %8$s
                        %6$sDescription text color%7$s - This option sets the text color for all standard, non-bolded text within the Purchase Overlay.
                        %9$s

                        %8$s
                        %6$sPurchase button background color%7$s - The purchase button color allows you to define a custom background color for the purchase button.
                        %9$s

                        %8$s
                        %6$sPurchase button hover color%7$s - This is the color displayed when a user hovers their mouse over the purchase button.
                        %9$s

                        %8$s
                        %6$sPurchase button text color%7$s - Here you may define the color of the text displayed in the purchase button.
                        %9$s

                        %8$s
                        %6$sLink main color%7$s - The link main color defines the text color for all links added by the Laterpay plugin.
                        %9$s

                        %8$s
                        %6$sLink hover color%7$s - This is the color displayed when a user hovers their mouse over links.
                        %9$s

                        %8$s
                        %6$sFooter background color%7$s - The footer background color defines the background color of the footer, payment options section.
                        %9$s

                        %8$s
                        %6$sRestore default values%7$s - By clicking "Restore default values," all colors will be restored to the original Laterpay default values.
                        %9$s
                     %5$s
                    ',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<ul>',
                    '</ul>',
                    '<strong>',
                    '</strong>',
                    '<li>',
                    '</li>'
                ),
            )
        );
    }

    /**
     * Add contextual help for account tab.
     *
     * @return void
     */
    protected function render_account_tab_help() {
        $screen = get_current_screen();

        // Add API Credentials contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_account_tab_help_api_credentials',
                'title'   => __( 'API Credentials', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sIn order to receive payments, you first need a Laterpay account. %3$sOnce this is set up, you need Laterpay API credentials, consisting of the following to link your WordPress plugin to your Laterpay account.%2$s
                    %10$s
                    %8$s %6$sMerchant ID%7$s (a 22-character string) and%9$s
                    %8$s %6$sAPI Key%7$s (a 32-character string).%9$s
                    %11$s
                    %4$sVisit our website to read more about how to become a content provider or to sign up with Laterpay.%5$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<a href="https://www.laterpay.net/solutions/influencers" target="blank">',
                    '</a>',
                    '<strong>',
                    '</strong>',
                    '<li>',
                    '</li>',
                    '<ul>',
                    '</ul>'
                ),
            )
        );

        // Add plugin mode contextual help.
        $screen->add_help_tab( array(
                'id'      => 'laterpay_account_tab_help_plugin_mode',
                'title'   => __( 'Plugin Mode', 'laterpay' ),
                'content' => sprintf( __(
                    '%1$sYou can run the Laterpay plugin in two modes:%2$s
                    %4$s
                        %8$s
                        %6$sTest Mode%7$s - This allows you to test your plugin configuration.%3$s
                        While providing the full plugin functionality, payments are only simulated and not actually processed. %3$sThe plugin will only be visible to admin users, not to visitors.%3$s
                        This is the default setting after activating the plugin for the first time.
                        %9$s
                        %8$s
                        %6$sLive Mode%7$s - In live mode, the plugin is publicly visible and manages access to paid content.%3$s
                        All payments are actually processed.%3$s
                        %9$s
                    %5$s
                        %1$s Using the Laterpay plugin usually requires some adjustments of your theme. Therefore, we recommend installing, configuring, and testing the Laterpay plugin on a test system before activating it on your production system.%2$s',
                    'laterpay' ),
                    '<p>',
                    '</p>',
                    '<br/>',
                    '<ul>',
                    '</ul>',
                    '<strong>',
                    '</strong>',
                    '<li>',
                    '</li>'
                ),
            )
        );

        // Add a sidebar for general help.
        $screen->set_help_sidebar(
            '<br/><p><strong>' . esc_html__( 'Need additional help?', 'laterpay' ) . '</strong></p>' .
            '<p>' . sprintf( esc_html__( 'Check out the %Laterpay WordPress Plugin Knowledge Base here.%s', 'laterpay' ), '<a href="https://www.laterpay.net/academy/tag/wordpress" target="_blank">', '</a>' ) . '</p>'
        );
    }

    /**
     * Add WordPress pointers to pages.
     *
     * @param LaterPay_Core_Event $event
     * @return void
     */
    public function modify_footer( LaterPay_Core_Event $event ) {
        $pointers = LaterPay_Controller_Admin::get_pointers_to_be_shown();

        // don't render the partial, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        // assign pointers
        $view_args = array(
            'pointers' => $pointers,
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( 'backend/partials/pointer-scripts', null, true );
    }

    /**
     * Load LaterPay stylesheet with LaterPay vector logo on all pages where the admin menu is visible.
     *
     * @return void
     */
    public function add_plugin_admin_assets() {
        wp_register_style(
            'laterpay-admin',
            $this->config->css_url . 'laterpay-admin.css',
            array(),
            $this->config->version
        );

        wp_enqueue_style( 'laterpay-admin' );

        // apply colors config
        LaterPay_Helper_View::apply_colors( 'laterpay-admin' );
    }

    /**
     * Hint at the newly installed plugin using WordPress pointers.
     *
     * @return void
     */
    public function add_admin_pointers_script() {
        $pointers = LaterPay_Controller_Admin::get_pointers_to_be_shown();

        // don't enqueue the assets, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }

    /**
     * Return the pointers that have not been shown yet.
     *
     * @return array $pointers
     */
    public function get_pointers_to_be_shown() {
        $dismissed_pointers = explode( ',', (string) LaterPay_Helper_User::get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $pointers = array();

        if ( ! in_array( LaterPay_Controller_Admin::ADMIN_MENU_POINTER, $dismissed_pointers, true ) ) {
            $pointers[] = LaterPay_Controller_Admin::ADMIN_MENU_POINTER;
        }
        // add pointers to LaterPay features on add / edit post page
        if ( ! in_array( LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER, $dismissed_pointers, true ) ) {
            $pointers[] = LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER;
        }
        if ( ! in_array( LaterPay_Controller_Admin::POST_TEASER_CONTENT_POINTER, $dismissed_pointers, true ) ) {
            $pointers[] = LaterPay_Controller_Admin::POST_TEASER_CONTENT_POINTER;
        }

        return $pointers;
    }

    /**
     * Return all pointer constants from current class.
     *
     * @return array $pointers
     */
    public static function get_all_pointers() {
        $reflection         = new ReflectionClass( __CLASS__ );
        $class_constants    = $reflection->getConstants();
        $pointers           = array();

        if ( $class_constants ) {
            foreach ( array_keys( $class_constants ) as $key_value ) {
                if ( strpos( $key_value, 'POINTER' ) !== false ) {
                    $pointers[] = $class_constants[ $key_value ];
                }
            }
        }

        return $pointers;
    }


    /**
     * Get links to be rendered in the plugin backend navigation.
     *
     * @param LaterPay_Core_Event $event
     */
    public function get_admin_menu( LaterPay_Core_Event $event ) {
        $menu = (array) $event->get_result();

        // @link http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table
        // cap "activate_plugins"   => Super Admin, Admin
        // cap "moderate_comments"  => Super Admin, Admin, Editor

        $menu['pricing'] = array(
            'url'   => 'laterpay-pricing-tab',
            'title' => __( 'Paywall', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $menu['contributions'] = array(
            'url'   => 'laterpay-contributions-tab',
            'title' => esc_html__( 'Contributions', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $menu['appearance'] = array(
            'url'   => 'laterpay-appearance-tab',
            'title' => __( 'Appearance', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $menu['account'] = array(
            'url'   => 'laterpay-account-tab',
            'title' => __( 'Account', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        if ( ! get_option( 'laterpay_plugin_is_in_live_mode' ) ) {
            $menu = array_reverse( $menu );
        }

        // Adding advanced tab to the end of menu, so that it isn't affected by plugin mode.
        $menu['advanced'] = array(
            'url'   => 'laterpay-advanced-tab',
            'title' => esc_html__( 'Advanced', 'laterpay' ),
            'cap'   => 'activate_plugins',
        );

        $event->set_result( $menu );
    }

    /**
     * Register a common script if not already registered to be used at multiple places.
     *
     * @param $page              string Current Page Name.
     * @param $data_for_localize array  Array of data to be localized.
     */
    public static function register_common_scripts( $page, $data_for_localize = [] ) {

        if ( false === wp_script_is( 'laterpay-common', 'registered' ) ) {
            $config = laterpay_get_plugin_config();

            wp_register_script(
                'laterpay-common',
                $config->get( 'js_url' ) . 'laterpay-common.js',
                array(),
                $config->get( 'version' ),
                true
            );
        }

        $lp_config_id        = self::get_tracking_id();
        $lp_user_tracking_id = self::get_tracking_id( 'user' );
        $merchant_key        = self::get_merchant_id_for_ga();

        $ga_data = [
            'current_page'        => esc_js( $page ),
            'sandbox_merchant_id' => ( ! empty( $merchant_key ) ) ? esc_js( $merchant_key ) : '',
            'lp_tracking_id'      => ( ! empty( $lp_config_id ) ) ? esc_html( $lp_config_id ) : '',
            'lp_user_tracking_id' => ( ! empty( $lp_user_tracking_id ) ) ? esc_html( $lp_user_tracking_id ) : '',
        ];

        if ( 'pricing' === $page ) {
            $ga_data['live_merchant_id'] = get_option( 'laterpay_live_merchant_id' );
            $ga_data['sb_merchant_id']   = get_option( 'laterpay_sandbox_merchant_id' );
        }

        // Allowed pages for notice and instruction.
        $lp_update_notice_allowed_page = [ 'pricing', 'appearance', 'advanced', 'account', 'contributions' ];

        // Check if current page is in allowed page.
        if ( in_array( $page, $lp_update_notice_allowed_page, true ) ) {
            // Following vars are not for GA but for update notice.
            $ga_data['update_highlights'] = get_option( 'lp_update_highlights', [] );

            if ( ! empty( $ga_data['update_highlights']['version'] ) ) {
                $version_update_number                   = $ga_data['update_highlights']['version'];
                $ga_data['update_highlights']['version'] = '';
                $ga_data['update_highlights']['notice']  = '';
                $ga_data['update_highlights_nonce']      = wp_create_nonce( 'update_highlights_nonce' );
            }

            $data_for_localize['lp_instructional_info'] = [];
            $data_for_localize['ajaxUrl']               = admin_url( 'admin-ajax.php' );
            $data_for_localize['learn_more']            = __( 'Learn More', 'laterpay' );

            $tab_information = [
                'appearance'    => sprintf( __( '%sOptional%s Use the appearance tab to configure your payment button colors and how your pricing options are displayed.', 'laterpay' ), '<b>', '</b>' ),
                'pricing'       => sprintf( __( '%sREQUIRED%s Use this tab to configure your default prices. Prices can also be set for an individual post on the edit post page.', 'laterpay' ), '<b>', '</b>' ),
                'advanced'      => sprintf( __( '%sOptional%s Here we highlight advanced features & settings like selling downloadable content and promoting your subscriptions. Scroll through to learn more!', 'laterpay' ), '<b>', '</b>' ),
                'contributions' => sprintf( __( '%sOptional%s To request contributions, use the editor below to configure your contributions request then copy the shortcode anywhere on your site.', 'laterpay' ), '<b>', '</b>' ),
            ];

            $tab_information_status = get_option( 'lp_tabular_info' );

            foreach ( $tab_information as $key => $value ) {
                if ( isset( $tab_information_status[ $key ]  ) && 1 === absint( $tab_information_status[ $key ] ) ) {
                    $data_for_localize['lp_instructional_info'][ $key ] = $value;
                }
            }

            if ( ! empty( $data_for_localize['lp_instructional_info'] ) ) {
                $data_for_localize['read_tabular_nonce'] = wp_create_nonce( 'read_tabular_info_nonce' );
            }
        }

        $final_data = array_merge( $ga_data, $data_for_localize );

        wp_localize_script(
            'laterpay-common',
            'lpCommonVar',
            $final_data
        );
    }

    /**
     * Get Tracking Id of specified type.
     *
     * @param string $type Type whose tracking id to get.
     *
     * @return string
     */
    public static function get_tracking_id( $type = '' ) {

        $config = laterpay_get_plugin_config();

        if ( 'user' === $type ) {
            $lp_user_tracking_data = get_option( 'laterpay_user_tracking_data' );

            // Check if Personal Tracking Setting is Enabled.
            $is_enabled_lp_user_tracking = ( ! empty( $lp_user_tracking_data['laterpay_ga_personal_enabled_status'] ) &&
                                             1 === intval( $lp_user_tracking_data['laterpay_ga_personal_enabled_status'] ) );

            // Add user tracking id if enabled.
            if ( $is_enabled_lp_user_tracking && ! empty( $lp_user_tracking_data['laterpay_ga_personal_ua_id'] ) ) {
                return $lp_user_tracking_data['laterpay_ga_personal_ua_id'];
            }

        } else {

            // Get current status of Google Analytics Settings.
            $lp_tracking_data      = get_option( 'laterpay_tracking_data' );

            // Check if LaterPay Tracking Setting is Enabled.
            $is_enabled_lp_tracking = ( ! empty( $lp_tracking_data['laterpay_ga_enabled_status'] ) &&
                                        1 === intval( $lp_tracking_data['laterpay_ga_enabled_status'] ) );

            // Add LaterPay Tracking Id if enabled. We will be using config value, not the one stored in option,
            // to make sure correct tracking id is, available for GA.
            if ( $is_enabled_lp_tracking && ! empty( $lp_tracking_data['laterpay_ga_ua_id'] ) ) {
                $lp_is_plugin_live = LaterPay_Helper_View::is_plugin_in_live_mode();
                if ( $lp_is_plugin_live ) {
                    return $config->get( 'tracking_ua_id.live' );
                } else {
                    return $config->get( 'tracking_ua_id.sandbox' );
                }
            }

        }

        return '';
    }

    /**
     * Get merchant id based on plugin status.
     *
     * @return string
     */
    public static function get_merchant_id_for_ga() {

        $sb_merch_key   = get_option( 'laterpay_sandbox_merchant_id' );
        $live_merch_key = get_option( 'laterpay_live_merchant_id' );

        if ( LaterPay_Helper_View::is_plugin_in_live_mode() ) {
            return $live_merch_key;
        }

        return $sb_merch_key;
    }

}
