<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

// Values are used more than once in the view, so create variables for reues.
$is_vip         = laterpay_check_is_vip();
$access_url     = admin_url( 'options-general.php?page=laterpay#lpaccess' );
$appearance_url = admin_url( 'options-general.php?page=laterpay#lpappearance' );
$technical_url  = admin_url( 'options-general.php?page=laterpay#lptechnical' );
$analytics_url  = admin_url( 'options-general.php?page=laterpay#lpanalytics' );
$settings_url   = admin_url( 'options-general.php?page=laterpay' );
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>


    <div class="lp_navigation">
        <a href="<?php echo esc_url( add_query_arg( LaterPay_Helper_Request::laterpay_encode_url_params( array( 'page' => $laterpay['admin_menu']['account']['url'] ) ), admin_url( 'admin.php' ) ) ); ?>"
           id="lp_js_pluginModeIndicator"
           class="lp_plugin-mode-indicator"
            <?php if ( $laterpay['plugin_is_in_live_mode'] ) : ?>style="display:none;"<?php endif; ?>
           data-icon="h">
            <h2 class="lp_plugin-mode-indicator__title"><?php esc_html_e( 'Test mode', 'laterpay' ); ?></h2>
            <span class="lp_plugin-mode-indicator__text"><?php printf( '%1$s <i> %2$s </i>', esc_html__( 'Earn money in', 'laterpay' ), esc_html__( 'live mode', 'laterpay' ) ); ?></span>
        </a>

        <?php
        // laterpay[advanced_obj] is instance of LaterPay_Controller_Admin_Advanced
        $laterpay['advanced_obj']->get_menu(); ?>

    </div>


    <div class="lp_pagewrap">

        <div class="lp_main_area">
            <h2><?php esc_html_e( 'Advanced Features', 'laterpay' ); ?></h2>
            <div class="lp_clearfix" id="lp_analytics_info">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Analytics', 'laterpay' ); ?></span>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            esc_html_e( 'Laterpay\'s Analytics Dashboard helps track your sales over time so that you can easily see how your content is performing and which posts are driving the highest revenues.', 'laterpay' );
                            ?>
                        </p>
                        <a id='lp_js_showMerchantDashboard' href="#" target='_blank' data-href-eu='https://web.laterpay.net/dialog/entry/?redirect_to=/merchant/#/login' data-href-us='https://web.uselaterpay.com/dialog/entry/?redirect_to=/merchant/#/login' class='lp_info_link'><?php esc_html_e( 'Click here to view your dashboard.', 'laterpay' ); ?></a>
                    </div>
                    <a id='lp_js_showMerchantDashboardImage' href="#" target='_blank' data-href-eu='https://web.laterpay.net/dialog/entry/?redirect_to=/merchant/#/login' data-href-us='https://web.uselaterpay.com/dialog/entry/?redirect_to=/merchant/#/login'>
                        <img class="lp_advanced_info_img_no_width" src="<?php echo esc_url( $this->config->get( 'image_url' ) . 'laterpay-analytics.png' ); ?>">
                    </a>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sCharge%s For Downloadable Content', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            printf(
                                esc_html__(
                                    'Using a custom %sshortcode%s, you can easily charge for downloadable content (such as PDFs). This short code allows you to customize the content, heading, description, and background image.', 'laterpay' ),
                                '<a href="https://en.support.wordpress.com/shortcodes/" target="_blank" class="lp_info_link_black">',
                                '</a>'
                            );
                            ?>
                        </p>
                        <a href="https://www.laterpay.net/academy/how-to-charge-for-downloadable-content-in-the-laterpay-wordpress-plugin" target='_blank' class='lp_info_link'><?php esc_html_e( 'Click here for detailed instructions.', 'laterpay' ); ?></a>
                    </div>
                    <a href="https://www.laterpay.net/academy/how-to-charge-for-downloadable-content-in-the-laterpay-wordpress-plugin" target='_blank'>
                        <img class="lp_advanced_info_img_normal" src="<?php echo esc_url( $this->config->get( 'image_url' ) . 'laterpay-downloadable-content.png' ); ?>">
                    </a>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sCreate%s Subscription Button', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            printf(
                                esc_html__(
                                    'With this %sshortcode%s, you can create a button to promote a subscription or time pass anywhere on your site. The button background color, font color, and text can be easily customized or you can upload an image to use for the button.', 'laterpay' ),
                                '<a href="https://en.support.wordpress.com/shortcodes/" target="_blank" class="lp_info_link_black">',
                                '</a>'
                            );
                            ?>
                        </p>
                        <a href="https://www.laterpay.net/academy/how-to-create-a-subscription-button-in-the-laterpay-wordpress-plugin" target='_blank' class='lp_info_link'><?php esc_html_e( 'Click here for detailed instructions.', 'laterpay' ); ?></a>
                    </div>
                    <a href="https://www.laterpay.net/academy/how-to-create-a-subscription-button-in-the-laterpay-wordpress-plugin" target='_blank' >
                        <img class="lp_advanced_info_img_wide" src="<?php echo esc_url( $this->config->get( 'image_url' ) . 'laterpay-subscription-button.png' ); ?>">
                    </a>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sDYNAMIC%s ACCESS', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info lp_advanced_info_dynamic">
                        <p>
                            <?php
                            printf(
                                esc_html__(
                                    'Create a customized experience with our newest %1$sshortcode%2$s & %3$sfunction%2$s. Using Laterpay\'s Dynamic Access feature, you can show or hide content - ads, promotional pop-ups, bonus features, virtually anything you can imagine - based on the level of access a user has purchased.', 'laterpay' ),
                                '<a href="https://en.support.wordpress.com/shortcodes/" target="_blank" class="lp_info_link_black">',
                                '</a>',
                                '<a href="https://codex.wordpress.org/Function_Reference/" target="_blank" class="lp_info_link_black">'
                            );
                            ?>
                        </p>
                        <a href="https://www.laterpay.net/academy/wordpress-dynamic-access" target='_blank' class='lp_info_link'><?php esc_html_e( 'Click here for detailed instructions.', 'laterpay' ); ?></a>
                    </div>
                    <div class="lp_info_list_div">
                        <ul class="lp_info_list">
                            <li data-icon="f"><?php esc_html_e( 'Ad-Free Experience', 'laterpay' ); ?></li>
                            <li data-icon="f"><?php esc_html_e( 'Hide Promotions after Purchase', 'laterpay' ); ?></li>
                            <li data-icon="f"><?php esc_html_e( 'Restrict Bonus Features to Subscribers', 'laterpay' ); ?></li>
                            <li data-icon="f"><?php esc_html_e( 'Much More!', 'laterpay' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sDynamic%s Pricing', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            esc_html_e( 'Dynamic pricing is a feature available on the Edit Post page. Once there, simply choose “Individual Price” in the Pricing for this Post section then click “+ Add dynamic pricing” at the bottom. Once you’ve completed this, you can drag and drop the points on the graph to adjust pricing based on the days since the article was originally published.', 'laterpay' );
                            ?>
                        </p>
                    </div>
                    <img class="lp_advanced_info_img_normal" src="<?php echo esc_url( $this->config->get( 'image_url' ) . 'laterpay-dynamic-pricing.png' ); ?>">
                </div>
            </div>

            <h2><?php esc_html_e( 'Advanced Settings', 'laterpay' ); ?></h2>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Access', 'laterpay' ); ?></span>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            printf(
                                "<a href='%s' target='_blank' class='lp_info_link'>%s</a> %s",
                                esc_url( $access_url ),
                                esc_html__( 'Click here to adjust your Laterpay user access settings.', 'laterpay' ),
                                esc_html__( 'Within these setting you can:', 'laterpay' )
                            );
                            ?>
                        </p>
                        <ul>
                            <li><?php esc_html_e( 'Require end-users to login prior to purchase', 'laterpay' ); ?></li>
                            <li><?php esc_html_e( 'Give unrestricted access to specific user roles', 'laterpay' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Appearance', 'laterpay' ); ?></span>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            printf(
                                "<a href='%s' target='_blank' class='lp_info_link'>%s</a> %s",
                                esc_url( $appearance_url ),
                                esc_html__( 'Click here to access additional appearance configurations,', 'laterpay' ),
                                esc_html__( 'including:', 'laterpay' )
                            );
                            ?>
                        </p>
                        <ul>
                            <li><?php esc_html_e( 'Length of default teaser content', 'laterpay' ); ?></li>
                            <li><?php esc_html_e( 'Length of blurred content displayed behind paywall', 'laterpay' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sDelete%s Account', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <div class="lp_info_div">
                    <!-- This following empty div is used for adding deactivation survey form on user action. -->
                    <div id="put-goodbye-form-laterpay" class="put-goodbye-form"></div>
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            if ( true === $is_vip ) {
                                printf(
                                    esc_html__( '%sWarning!%s This operation deletes ALL Laterpay plugin data.', 'laterpay' ),
                                    "<b>",
                                    "</b>"
                                );
                            } else {
                                printf(
                                    esc_html__( '%sWarning!%s This operation deactivates the Laterpay plugin and deletes ALL its data.', 'laterpay' ),
                                    "<b>",
                                    "</b>"
                                );
                            }
                            ?>
                            <br />
                            <?php esc_html_e( 'You will lose all appearance settings and pricing configurations. This cannot be undone.', 'laterpay' ); ?>
                        </p>

                        <div id="lp_plugin_disable_modal_id" style="display:none;">
                            <?php if ( $is_vip ) { ?>
                                <p><?php esc_html_e( 'Are you sure you want to delete ALL Laterpay Plugin data? You will loose all pricing configurations. This cannot be undone.', 'laterpay' ); ?></p>
                            <?php } else { ?>
                                <p><?php esc_html_e( 'Are you sure you want to deactivate Laterpay plugin and delete ALL its data? You will loose all pricing configurations. This cannot be undone.', 'laterpay' ); ?></p>
                            <?php } ?>
                            <button class="lp_js_disablePluginConfirm button button-primary lp_mt- lp_mb-"><?php echo( ( $is_vip ) ? esc_html__( 'Delete Laterpay Plugin Data', 'laterpay' ) : esc_html__( 'Deactivate Laterpay Plugin', 'laterpay' ) ); ?></button>
                            <button type="button" class="button button-secondary lp_mt- lp_mb- lp_js_ga_cancel"><?php esc_html_e( 'Cancel', 'laterpay' ); ?></button>
                        </div>
                        <button class="lp_js_disablePlugin button button-primary lp_mt- lp_mb-"><?php echo( ( $is_vip ) ? esc_html__( 'Delete Plugin Data', 'laterpay' ) : esc_html__( 'Deactivate Plugin & Delete Data', 'laterpay' ) ); ?></button>
                    </div>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Technical', 'laterpay' ); ?></span>
                </label>
                <div class="lp_info_div">
                    <div class="lp_advanced_info">
                        <p>
                            <?php
                            printf(
                                "<a href='%s' target='_blank' class='lp_info_link'>%s</a> %s",
                                esc_url( $technical_url ),
                                esc_html__( 'Click here for more technical configuration options,', 'laterpay' ),
                                esc_html__( 'including:', 'laterpay' )
                            );
                            ?>
                        </p>
                        <ul>
                            <li><?php esc_html_e( 'Enable caching compatibility mode', 'laterpay' ); ?></li>
                            <li><?php esc_html_e( 'Define fallback behavior in case Laterpay API is not responding', 'laterpay' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <h2><?php esc_html_e( 'Support', 'laterpay' ); ?></h2>

            <div class="lp_clearfix">
                <p><?php printf( esc_html__( 'Having Trouble with Page Cache? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/wordpress-cache" target="_blank" class="lp_info_link">', '</a>' ); ?></p>

                <?php
                // Only show info if on WPEngine environment.
                if ( function_exists( 'is_wpe' ) && is_wpe() ) {
                    ?>
                    <p><?php printf( esc_html__( 'Having Trouble on WPEngine? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/i-am-having-trouble-with-wordpress-engine" target="_blank" class="lp_info_link">', '</a>' ); ?></p>
                    <?php
                }
                ?>
                <p>
                    <?php printf( esc_html__( 'The new version of the plugin is not compatible with my site. How can I rollback? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/rollback-wordpress-plugin" target="_blank" class="lp_info_link">', '</a>' ); ?>
                </p>
                <p>
                    <?php
                    printf(
                        esc_html__( '%1$sClick here%3$s or email %2$ssupport@laterpay.net%3$s to provide feedback or to reach our customer service team.', 'laterpay' ),
                        "<a href='#' id='lp_js_openSupportPage' data-href-eu='https://www.laterpay.net/de/contact-support' data-href-us='https://www.laterpay.net/contact-support' target='_blank' class='lp_info_link'>",
                        "<a href='mailto:support@laterpay.net' class='lp_info_link'>",
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <br />
        </div>
    </div>
</div>
