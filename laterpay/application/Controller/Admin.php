<?php

/**
 * LaterPay admin controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin extends LaterPay_Controller_Abstract
{

    const ADMIN_MENU_POINTER            = 'lpwpp01';
    const POST_PRICE_BOX_POINTER        = 'lpwpp02';
    const POST_TEASER_CONTENT_POINTER   = 'lpwpp03';

    /**
     * Show plugin in administrator panel.
     *
     * @return void
     */
    public function add_to_admin_panel() {
        $plugin_page = LaterPay_Helper_View::$pluginPage;
        add_menu_page(
            __( 'LaterPay Plugin Settings', 'laterpay' ),
            'LaterPay',
            'moderate_comments', // allow Super Admin, Admin, and Editor to view the settings page
            $plugin_page,
            array( $this, 'run' ),
            'dashicons-laterpay-logo',
            81
        );

        $page_number    = 0;
        $menu           = LaterPay_Helper_View::get_admin_menu();
        foreach ( $menu as $name => $page ) {
            $slug   = ! $page_number ? $plugin_page : $page['url'];
            $page_id = add_submenu_page(
                $plugin_page,
                $page['title'] . ' | ' . __( 'LaterPay Plugin Settings', 'laterpay' ),
                $page['title'],
                $page['cap'],
                $slug,
                isset( $page['run'] ) ? $page['run'] : array( $this, 'run_' . $name )
            );
            if ( isset( $page['submenu'] ) ) {
                $sub_page   = $page['submenu'];
                add_submenu_page(
                    $sub_page['name'],
                    $sub_page['title'] . ' | ' . __( 'LaterPay Plugin Settings', 'laterpay' ),
                    $sub_page['title'],
                    $page['cap'],
                    $sub_page['url'],
                    array( $this, 'run_' . $sub_page['name'] )
                );
            }
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
            'laterpay-backend',
            $this->config->get( 'js_url' ) . 'laterpay-backend.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'laterpay-backend' );

    }

    /**
     * Add html5shim to the admin_head() for Internet Explorer < 9.
     *
     * @wp-hook admin_head
     *
     * @return void
     */
    public function add_html5shiv_to_admin_head() {
        ?>
        <!--[if lt IE 9]>
        <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <?php
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

        // return default tab, if no specific tab is requested
        if ( empty( $tab ) ) {
            $tab            = 'dashboard';
            $_GET['tab']    = 'dashboard';
        }

        switch ( $tab ) {
            // render time_passes tab
            case 'time_passes':
                $time_passes_controller = new LaterPay_Controller_Admin_TimePass( $this->config );
                $time_passes_controller->render_page();
                break;

            default:
            // render dashboard tab
            case 'dashboard':
                $dashboard_controller = new LaterPay_Controller_Admin_Dashboard( $this->config );
                $dashboard_controller->render_page();
                break;

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

            case 'dashboard':
                $this->render_dashboard_tab_help();
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
                                   'title'   => __( 'LaterPay', 'laterpay' ),
                                   'content' => __( '
                                        <p>
                                            <strong>Setting Prices</strong><br>
                                            You can set an individual price for each post.<br>
                                            Possible prices are either 0 Euro (free) or any value between 0.05 Euro (inclusive) and 149.99 Euro (inclusive).<br>
                                            If you set an individual price, category default prices you might have set for the post\'s category(s)
                                            won\'t apply anymore, unless you make the post use a category default price.
                                        </p>
                                        <p>
                                            <strong>Dynamic Pricing Options</strong><br>
                                            You can define dynamic price settings for each post to adjust prices automatically over time.<br>
                                            <br>
                                            For example, you could sell a "breaking news" post for 0.49 Euro (high interest within the first 24 hours)
                                            and automatically reduce the price to 0.05 Euro on the second day.
                                        </p>
                                        <p>
                                            <strong>Teaser</strong><br>
                                            The teaser should give your visitors a first impression of the content you want to sell.<br>
                                            You don\'t have to provide a teaser for every single post on your site:<br>
                                            by default, the LaterPay plugin uses the first 60 words of each post as teaser content.
                                            <br>
                                            Nevertheless, we highly recommend manually creating the teaser for each post, to increase your sales.
                                        </p>
                                        <p>
                                            <strong>PPU (Pay-per-Use)</strong><br>
                                            If you choose to sell your content as <strong>Pay-per-Use</strong>, a user pays the purchased content <strong>later</strong>. The purchase is added to his LaterPay invoice and he has to log in to LaterPay and pay, once his invoice has reached 5.00 EUR.<br>
                                            LaterPay <strong>recommends</strong> Pay-per-Use for all prices up to 5.00 EUR as they deliver the <strong>best purchase experience</strong> for your users.<br>
                                            PPU is possible for prices between (including) <strong>0.05 EUR</strong> and (including) <strong>5.00 EUR</strong>.
                                        </p>
                                        <p>
                                            <strong>SIS (Single Sale)</strong><br>
                                            If you sell your content as <strong>Single Sale</strong>, a user has to <strong>log in</strong> to LaterPay and <strong>pay</strong> for your content <strong>immediately</strong>.<br>
                                            Single Sales are especially suitable for higher-value content and / or content that immediately occasions costs (e. g. license fees for a video stream).<br>
                                            A Single Sales is possible between (including) <strong>1.49 EUR</strong> and (including) <strong>149.99 EUR</strong>.
                                        </p>',
                                    'laterpay'
                                   ),
                               ) );
    }

    /**
     * Add contextual help for dashboard tab.
     *
     * @return  void
     */
    protected function render_dashboard_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_dashboard_tab_help_conversion',
                                   'title'   => __( 'Conversion', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        The <strong>Conversion</strong> (short for Conversion Rate) is the share of visitors of a specific post, who actually <strong>bought</strong> the post.<br>
                                                        A conversion of 100% would mean that every user who has visited a post page and has read the teaser content had bought the post with LaterPay.<br>
                                                        The conversion rate is one of the most important metrics for selling your content successfully: It indicates, if the price is perceived as adequate and if your content fits your audience\'s interests.
                                                    </p>
                                                    <p>
                                                        The metric <strong>New Customers</strong> indicates the share of your customers who bought with LaterPay for the first time in the reporting period.<br>
                                                        Please note that this is only an approximate value.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_dashboard_tab_help_items_sold',
                                   'title'   => __( 'Items Sold', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        The column <strong>Items Sold</strong> provides an overview of all your sales in the reporting period.
                                                    </p>
                                                    <p>
                                                        <strong>AVG Items Sold</strong> (short for Average Items Sold) indicates how many posts you sold on average per day in the reporting period.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_dashboard_tab_help_gross_revenue',
                                   'title'   => __( 'Committed Revenue', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        <strong>Committed Revenue</strong> is the value of all purchases, for which your users have committed themselves to pay later (or paid immediately in case of a Single Sale purchase).
                                                    </p>
                                                    <p>
                                                        <strong>AVG Revenue</strong> (short for Average Revenue) indicates the average revenue per day in the reporting period.
                                                    </p>
                                                    <p>
                                                        Please note that this <strong>is not the amount of money you will receive with your next LaterPay payout</strong>, as a user will have to pay his invoice only once it reaches 5.00 € and LaterPay will deduct a fee of 15% for each purchase that was actually paid.
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
                                                        Currently, the plugin only supports Euro as default currency, but
                                                        you will soon be able to choose between different currencies for your blog.<br>
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
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_time_passes',
                                   'title'   => __( 'Time Passes', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        <strong>Validity of Time Passes</strong><br>
                                                        With time passes, you can offer your users <strong>time-limited</strong> access to your content. You can define, which content a time pass should cover and for which period of time it should be valid. A time pass can be valid for <strong>all LaterPay content</strong>
                                                        <ul>
                                                            <li>on your <strong>entire website</strong>,</li>
                                                            <li>in one <strong>specific category</strong>, or</li>
                                                            <li>on your entire website <strong>except from a specific category</strong>.</li>
                                                        </ul>
                                                        The <strong>validity period</strong> of a time pass starts with the <strong>purchase</strong> and is defined for a <strong>continuous</strong> use – i.e. it doesn\'t matter, if a user is on your website during the entire validity period. After a time pass has expired, the access to the covered content is automatically refused. Please note: Access to pages which are <strong>still open</strong> when a pass expires will be refused only after <strong>reloading</strong> the respective page. <strong>Any files</strong> (images, documents, presentations...), that were downloaded during the validity period, can still be used after the access has expired – but the user will <strong>not</strong> be able to <strong>download them </strong> without purchasing again.
                                                    </p>
                                                    <p>
                                                        <strong>Deleting Time Passes</strong><br>
                                                        Please be aware, that after <strong>deleting</strong> a time pass, users who have bought this time pass <strong>will lose</strong> their access to the covered content. <strong>Time Passes cannot be restored.</strong>
                                                    </p>
                                                    <p>
                                                        <strong>Time Passes and Individual Sales</strong><br>
                                                        When a user purchases a time pass, he has access to all the content covered by this pass during the validity period. Of course, you can still sell your content individually.<br>
                                                        Example: A user has already purchased the post "New York – a Travel Report" for 0.29 EUR. Now he purchases a Week Pass for the category "Travel Reports" for 0.99 EUR. The category also contains the "New York" post. For one week, he can now read all posts in the category "Travel Reports" for a fixed price of 0.99 EUR. After this week, the access expires automatically. During the validity period, the user will not see any LaterPay purchase buttons for posts in the category "Travel Reports". After the pass has expired, the user will still have access to the post he had previously purchased individually.
                                                    </p>
                                                    <p>
                                                        <strong>Action</strong><br>
                                                        You can display time passes by implementing the <a href="admin.php?page=laterpay-appearance-tab#lp_timePassAppearance" target="_blank">action \'laterpay_time_passes\'</a> into your theme.<br>
                                                        This action will display all time passes which are relevant for the user in the current context and sorts them accordingly.<br>
                                                        Example: You offer a <strong>Week Pass "Sport"</strong> for the category sport, a <strong>Week Pass "News"</strong> for the category "News" and a <strong>Month Pass Entire Website</strong> for all the content on your website.<br>
                                                        Depending on the page he is currently visiting, a user will see different time passes:
                                                        <ul>
                                                            <li>On the post page of a post in the category <strong>"Sport"</strong>, the <strong>Week Pass "Sport"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "News"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                                                            <li>On the post page of a post in the category <strong>"News"</strong>, the <strong>Week Pass "News"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "Sport"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                                                        </ul>
                                                    </p>
                                                    <p>
                                                        <strong>Vouchers</strong><br>
                                                        You can create any number of voucher codes for each time pass. A voucher code allows one (or multiple) user(s) to purchase a time pass for a reduced price. A user can enter a voucher code below the available time passes by clicking <strong>\'Redeem Voucher\'</strong>. If the entered code is a valid voucher code, the price of the time pass, the code is valid for, will be reduced.<br>
                                                        A voucher code can be used <strong>any number of times</strong> and is <strong>not linked</strong> to a specific user.<br>
                                                        If you <strong>delete</strong> a voucher code, this will <strong>not affect</strong> the validity of time passes which have already been purchased using this voucher code.
                                                     </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_bulk_price_editor',
                                   'title'   => __( 'Bulk Price Editor', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        With the <strong>bulk price editor</strong>, you can quickly change <strong>multiple</strong> prices <strong>at a time</strong>.<br>
                                                        The plugin will always try to <strong>maintain the current pricing structure</strong> of your blog, i.e. posts with individual price will still use the individual price, posts with category default price will still use the category default price and posts with global default price will still use the global default price after <strong>most</strong> bulk price actions.<br><br>
                                                        Actions that <strong>maintain</strong> the pricing structure are:
                                                        <ul>
                                                            <li>
                                                                <strong>Set prices of all posts to X EUR.</strong><br>
                                                                This action will set the individual prices of posts with individual price to X EUR, set all category default prices to X EUR, and set the global default price to X EUR.
                                                            </li>
                                                            <li>
                                                                <strong>Increase prices of all posts by X EUR / %.</strong><br>
                                                                This action will increase the individual prices of posts with individual price by X EUR / %, increase all category default prices by X EUR / %, and increase the global default price by X EUR / %.
                                                            </li>
                                                            <li>
                                                                <strong>Reduce prices of all posts by X EUR / %.</strong><br>
                                                                This action will reduce the individual prices of posts with individual price by X EUR / %, reduce all category default prices by X EUR / %, and reduce the global default price by X EUR / %.
                                                            </li>
                                                            <li>
                                                                <strong>Make all posts free.</strong><br>
                                                                This action will set the individual prices of posts with individual price to 0.00 EUR, set all category default prices to 0.00 EUR, and set the global default price to 0.00 EUR.
                                                            </li>
                                                        </ul>
                                                    </p>
                                                    <p>
                                                        Still, some bulk price actions <strong>might change</strong> the current pricing structure:<br>
                                                        <ul>
                                                            <li>
                                                                <strong>Make all posts in category X free.</strong><br>
                                                                This action will set the individual prices of posts with individual price in category X to 0.00 EUR. If a category default price for category X exists, it will be set to 0.00 EUR and applied to all posts with category default price and global default price in this category. If no category default price for category X exists, it will be created, set to 0.00 EUR, and applied to all posts with category default price and global default price in this category.
                                                            </li>
                                                            <li>
                                                                <strong>Reset prices of all posts in category X to category default price.</strong><br>
                                                                This action will apply the category default price to all posts in category x.
                                                            </li>
                                                            <li>
                                                                <strong>Reset prices of all posts to global default price</strong><br>
                                                                This action will delete all category default prices and individual prices, and use the global default price for all posts.
                                                            </li>
                                                        </ul>
                                                    </p>
                                                    <p>
                                                        <strong>Rounding and allowed price ranges</strong><br>
                                                        Please note, that the bulk price editor can change prices only within the allowed price ranges:
                                                        <ul>
                                                            <li>
                                                                If the result of a price change would be <strong>< 0.05 EUR</strong>, this price is changed to <strong>0.05 EUR</strong>.
                                                            </li>
                                                            <li>
                                                                If the result of a price change would be <strong>> 149.99 EUR</strong>, this price is changed to <strong>149.99 EUR</strong>.
                                                            </li>
                                                            <li>
                                                                If the price of a <strong>PPU</strong> (pay-per-use) post is changed to a price <strong>> 5.00 EUR</strong>, this post is changed to a <strong>SIS</strong> (single sale) post.
                                                            </li>
                                                            <li>
                                                                If the price of a <strong>SIS</strong> post is changed to a price <strong>< 1.49 EUR</strong>, this post is changed to a <strong>PPU</strong> post.
                                                            </li>
                                                        </ul>
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_pricing_tab_help_time_passes',
                                   'title'   => __( 'Time Passes', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        <strong>Validity of Time Passes</strong><br>
                                                        With time passes, you can offer your users <strong>time-limited</strong> access to your content. You can define, which content a time pass should cover and for which period of time it should be valid. A time pass can be valid for <strong>all LaterPay content</strong>
                                                    </p>
                                                    <ul>
                                                        <li>on your <strong>entire website</strong>,</li>
                                                        <li>in one <strong>specific category</strong>, or</li>
                                                        <li>on your entire website <strong>except from a specific category</strong>.</li>
                                                    </ul>
                                                    <p>
                                                        The <strong>validity period</strong> of a time pass starts with the <strong>purchase</strong> and is defined for a <strong>continuous</strong> use – i.e. it doesn\'t matter, if a user is on your website during the entire validity period. After a time pass has expired, the access to the covered content is automatically refused. Please note: Access to pages which are <strong>still open</strong> when a pass expires will be refused only after <strong>reloading</strong> the respective page. <strong>Any files</strong> (images, documents, presentations...), that were downloaded during the validity period, can still be used after the access has expired – but the user will <strong>not</strong> be able to <strong>download them </strong> without purchasing again.
                                                    </p>
                                                    <p>
                                                        <strong>Deleting Time Passes</strong><br>
                                                        Please be aware, that after <strong>deleting</strong> a time pass, users who have bought this time pass <strong>will lose</strong> their access to the covered content. <strong>Time Passes cannot be restored.</strong>
                                                    </p>
                                                    <p>
                                                        <strong>Time Passes and Individual Sales</strong><br>
                                                        When a user purchases a time pass, he has access to all the content covered by this pass during the validity period. Of course, you can still sell your content individually.<br>
                                                        Example: A user has already purchased the post "New York – a Travel Report" for 0.29 EUR. Now he purchases a Week Pass for the category "Travel Reports" for 0.99 EUR. The category also contains the "New York" post. For one week, he can now read all posts in the category "Travel Reports" for a fixed price of 0.99 EUR. After this week, the access expires automatically. During the validity period, the user will not see any LaterPay purchase buttons for posts in the category "Travel Reports". After the pass has expired, the user will still have access to the post he had previously purchased individually.
                                                    </p>
                                                    <p>
                                                        <strong>Sidebar Widget</strong><br>
                                                        Time passes are listed in a dedicated <strong>sidebar widget</strong>.You will find it in your WordPress backend in "Design > Widgets".<br>
                                                        The sidebar widget displays all time passes which are available for the user in the current context, sorted by relevance.<br>
                                                        Example: You offer a <strong>Week Pass "Sport"</strong> for the category sport, a <strong>Week Pass "News"</strong> for the category "News" and a <strong>Month Pass Entire Website</strong> for all the content on your website.<br>
                                                        Depending on the page he is currently visiting, a user will see different time passes:
                                                    </p>
                                                    <ul>
                                                        <li>On the post page of a post in the category <strong>"Sport"</strong>, the <strong>Week Pass "Sport"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "News"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                                                        <li>On the post page of a post in the category <strong>"News"</strong>, the <strong>Week Pass "News"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "Sport"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                                                    </ul>
                                                    <p>
                                                        <strong>Vouchers</strong><br>
                                                        You can create any number of voucher codes for each time pass. A voucher code allows one (or multiple) user(s) to purchase a time pass for a reduced price. A user can enter a voucher code in the <strong>sidebar widget</strong> after clicking <strong>"I have a voucher"</strong>. If the entered code is a valid voucher code, the price of the time pass, the code is valid for, will be reduced.<br>
                                                        A voucher code can be used <strong>any number of times</strong> and is <strong>not linked</strong> to a specific user.<br>
                                                        If you <strong>delete</strong> a voucher code, this will <strong>not affect</strong> the validity of time passes which have already been purchased using this voucher code.
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
        $screen->add_help_tab( array(
                                   'id'      => 'laterpay_appearance_tab_help_content_rating',
                                   'title'   => __( 'Rating of Purchased Content', 'laterpay' ),
                                   'content' => __( '
                                                    <p>
                                                        If you enable the <strong>rating of purchased content</strong>, users, who have <strong>already bought</strong> a post, will be
                                                        able to <strong>rate it</strong> on a five stars scale, five stars being the best rating. <br>
                                                        Users, who <strong>haven\'t bought</strong> a post yet, will see a summary of all buyer ratings below the LaterPay purchase button.
                                                    </p>
                                                    <p>
                                                        As the <strong>opinion of buyers</strong> might have a <strong>strong influence</strong> on the buying decisions of your users,
                                                        enabling the rating of purchased content could <strong>boost your sales</strong>.
                                                    </p>',
                                                    'laterpay'
                                                ),
                               ) );
    }

    /**
     * Add contextual help for account tab.
     *
     * @return void
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
                                                            The <strong>Live</strong> environment for production use.<br>
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
     * @return void
     */
    public function modify_footer() {
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

        echo $this->get_text_view( 'backend/partials/pointer_scripts' );
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
        $dismissed_pointers = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $pointers = array();

        if ( ! in_array( LaterPay_Controller_Admin::ADMIN_MENU_POINTER, $dismissed_pointers ) ) {
            $pointers[] = LaterPay_Controller_Admin::ADMIN_MENU_POINTER;
        }
        // add pointers to LaterPay features on add / edit post page
        if ( ! in_array( LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER, $dismissed_pointers ) ) {
            $pointers[] = LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER;
        }
        if ( ! in_array( LaterPay_Controller_Admin::POST_TEASER_CONTENT_POINTER, $dismissed_pointers ) ) {
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
                if ( strpos( $key_value, 'POINTER') !== FALSE ) {
                    $pointers[] = $class_constants[$key_value];
                }
            }
        }

        return $pointers;
    }

    /**
     * Update post prices after category delete.
     *
     * @hook delete_term_taxonomies
     *
     * @return void
     */
    public function update_post_prices_after_category_delete( $category_id ) {
        $category_price_model = new LaterPay_Model_CategoryPrice();
        $category_price_model->delete_prices_by_category_id( $category_id );

        // get posts by category price id
        $post_ids = LaterPay_Helper_Pricing::get_posts_by_category_price_id( $category_id );
        foreach ( $post_ids as $post_id => $meta ) {
            // update post prices
            LaterPay_Helper_Pricing::update_post_data_after_category_delete( $post_id );
        }
    }
}
