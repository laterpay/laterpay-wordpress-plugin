<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php
$is_vip = laterpay_check_is_vip();
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
        // laterpay[account_obj] is instance of LaterPay_Controller_Admin_Account
        $laterpay['account_obj']->get_menu(); ?>

    </div>


    <div class="lp_pagewrap">

        <div class="lp_main_area">
            <div class="lp_greybox lp_mt lp_mr lp_mb">
                <?php esc_html_e( 'The LaterPay plugin is in', 'laterpay' ); ?>
                <div class="lp_toggle">
                    <form id="laterpay_plugin_mode" method="post">
                        <input type="hidden" name="form" value="laterpay_plugin_mode">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) {
                            wp_nonce_field( 'laterpay_form' );
                        } ?>
                        <label class="lp_toggle__label">
                            <input type="checkbox"
                                   id="lp_js_togglePluginMode"
                                   class="lp_toggle__input"
                                   name="plugin_is_in_live_mode"
                                   value="1"
                                <?php if ( $laterpay['plugin_is_in_live_mode'] ) {
                                    echo 'checked';
                                } ?>>
                            <span class="lp_toggle__text" data-on="LIVE" data-off="TEST"></span>
                            <span class="lp_toggle__handle"></span>
                        </label>
                    </form>
                </div><?php esc_html_e( 'mode.', 'laterpay' ); ?>
                <span data-icon="m" class="lp_info_icon" id="lp_plugin_mode_info"></span>
                <div id="lp_plugin_mode_info_modal" style="display:none;">
                    <p>
                        <?php
                        echo esc_html__( 'In Test mode, only WordPress administrators will be able to see the LaterPay paywall. You must complete step 4 below to enable Live mode. This will display the paywall to your followers and allow you to begin accepting payments.', 'laterpay' );
                        ?>
                    </p>
                    <button type="button" class="button button-secondary lp_mt- lp_mb- lp_js_ga_cancel lp_info_close"><?php esc_html_e( 'Close', 'laterpay' ); ?></button>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label"><span class="lp_step_span"><?php esc_html_e( 'Step 1', 'laterpay' ); ?>:</span> <?php esc_html_e( 'Select Your Region', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        printf(
                            esc_html__( 'If you select \'Europe\', all prices will be displayed and charged in Euro (EUR), and the plugin will connect to the LaterPay Europe platform. %1$s If you select \'United States\', all prices will be displayed and charged in U.S. Dollar (USD), and the plugin will connect to the LaterPay U.S. platform.', 'laterpay' ),
                            "<br/>"
                        );
                        ?>
                    </p>
                    <form id="laterpay_region" method="post">
                        <input type="hidden" name="form" value="laterpay_region_change">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) {
                            wp_nonce_field( 'laterpay_form' );
                        } ?>
                        <select id="lp_js_apiRegionSection" name="laterpay_region" class="lp_input">
                            <option value="eu" <?php selected( $laterpay['region'], 'eu' ); ?>><?php esc_html_e( 'Europe (EUR)', 'laterpay' ); ?></option>
                            <option value="us" <?php selected( $laterpay['region'], 'us' ); ?>><?php esc_html_e( 'United States (USD)', 'laterpay' ); ?></option>
                        </select>
                    </form>
                    <p id="lp_js_regionNotice" <?php if ( $laterpay['region'] === 'us' ) : ?>class="hidden"<?php endif; ?>>
                        <dfn class="lp_region_notice" data-icon="n">
                            <?php
                            printf(
                                esc_html__( '%1$sImportant:%2$s The minimum value for "Pay Now" prices in the U.S. region is %1$s$1.99%2$s %3$s
                            If you have already set "Pay Now" prices lower than 1.99, make sure to change them before you switch to the U.S. region. %3$s
                            If you haven\'t done any configuration yet, you can safely switch the region without further adjustments.', 'laterpay' ),
                                "<b>",
                                "</b>",
                                "<br/>"
                            );
                            ?>
                        </dfn>
                    </p>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Step 2', 'laterpay' ); ?>:</span>
                    <?php esc_html_e( 'Create LaterPay Account', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php esc_html_e( 'A LaterPay Account is required in order to process financial transactions. You may skip this step if you have already created a LaterPay account.', 'laterpay' ); ?>
                    </p>
                    <a class="lp_purchase-overlay__submit" href="#">
                        <span class="lp_purchase-overlay__submit-text lp_sign_up"><?php esc_html_e( 'Sign Up' ); ?></span>
                    </a>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Step 3', 'laterpay' ); ?>:</span>
                    <?php esc_html_e( 'Getting Started', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        esc_html_e( 'It may take up to 5 business days for our banking partners to verify your information. You may go ahead and get started setting up your plugin while it is in "Test mode" then, once we\'ve emailed you to let you know your account is live, come back and complete step 4 below in order to begin accepting payments.', 'laterpay' );
                        ?>
                    </p>
                    <p>
                        <?php
                        printf(
                            esc_html__( '%s Click here to read our Getting Started Guide%s or simply click through the remaining tabs and check out the LaterPay features available on your Edit Post pages to see all of the options available. To help you get started, all of the default values are automatically set to our experts\' recommendations.', 'laterpay' ),
                            "<a href='#' class='lp_info_link'>",
                            '</a>'
                        )
                        ?>
                    </p>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Step 4', 'laterpay' ); ?>:</span>
                    <?php esc_html_e( 'Accept Payments', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        esc_html_e( 'It may take up to 5 business days for our banking partners to verify your information. Once we\'ve emailed you to let you know your account is live, follow the steps below to begin accepting payments.', 'laterpay' );
                        ?>
                    </p>
                    <ol>
                        <li><?php printf( esc_html__( '%sClick here%s to log in to your LaterPay account', 'laterpay' ), "<a id='lp_js_showMerchantContracts' href='#' target='_blank' data-href-eu=" . esc_url( $laterpay['credentials_url_eu'] ) . " data-href-us=" . esc_url( $laterpay['credentials_url_us'] ) . " class='lp_info_link'>", '</a>' ); ?></li>
                        <li>
                            <?php esc_html_e( 'Navigate to the developer tab & copy and paste your Merchant ID & API Key into the corresponding boxes below', 'laterpay' ); ?>
                            <br />
                            <?php printf( esc_html__( '(?) Don\'t see a developer tab? Click here to contact our support team', 'laterpay' ), "<a href='#' class='lp_info_link'>", '</a>' ); ?>
                        </li>
                        <li><?php esc_html_e( 'Ensure that the toggle at the top of the page is switched to LIVE mode', 'laterpay' ); ?></li>
                    </ol>
                    <div id="lp_js_liveCredentials" class="lp_api-credentials">
                        <ul class="lp_api-credentials__list">
                            <li class="lp_api-credentials__list-item">
                                <span class="lp_iconized-input" data-icon="i"></span>
                                <form id="laterpay_live_merchant_id" method="post">
                                    <input type="hidden" name="form" value="laterpay_live_merchant_id">
                                    <input type="hidden" name="action" value="laterpay_account">
                                    <?php if ( function_exists( 'wp_nonce_field' ) ) {
                                        wp_nonce_field( 'laterpay_form' );
                                    } ?>

                                    <input type="text"
                                           id="lp_js_liveMerchantId"
                                           class="lp_js_validateMerchantId lp_api-credentials__input"
                                           name="laterpay_live_merchant_id"
                                           value="<?php echo esc_attr( $laterpay['live_merchant_id'] ); ?>"
                                           maxlength="22"
                                           required>
                                    <label for="laterpay_live_merchant_id"
                                           alt="<?php esc_attr_e( 'Paste Live Merchant ID here', 'laterpay' ); ?>"
                                           placeholder="<?php esc_attr_e( 'Merchant ID', 'laterpay' ); ?>">
                                    </label>
                                </form>
                            </li>
                            <li class="lp_api-credentials__list-item">
                                <span class="lp_iconized-input" data-icon="j"></span>
                                <form id="laterpay_live_api_key" method="post">
                                    <input type="hidden" name="form" value="laterpay_live_api_key">
                                    <input type="hidden" name="action" value="laterpay_account">
                                    <?php if ( function_exists( 'wp_nonce_field' ) ) {
                                        wp_nonce_field( 'laterpay_form' );
                                    } ?>

                                    <input type="text"
                                           id="lp_js_liveApiKey"
                                           class="lp_js_validateApiKey lp_api-credentials__input"
                                           name="laterpay_live_api_key"
                                           value="<?php echo esc_attr( $laterpay['live_api_key'] ); ?>"
                                           maxlength="32"
                                           required>
                                    <label for="laterpay_sandbox_api_key"
                                           alt="<?php esc_attr_e( 'Paste Live API Key here', 'laterpay' ); ?>"
                                           placeholder="<?php esc_attr_e( 'API Key', 'laterpay' ); ?>">
                                    </label>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="lp_side_area">
            <div class="lp_clearfix lp_info">
                <div class="lp_side_info">
                    <h2><?php esc_html_e( 'Who is LaterPay?', 'laterpay' ); ?></h2>
                    <p>
                        <?php printf(
                            esc_html__( 'Meet the online payment system that cares about the user experience as much as you do %1$s %1$s
                    With LaterPay, your users can purchase digital content and services, or make contributions and donations, with a single click—a frictionless experience that turns traffic into transactions.%1$s %1$s
                    Requiring upfront registration and payment results in customer abandon rates of up to 98%%. %1$s
                    LaterPay\'s patented Pay Later revenue model instead defers the registration process until a customer’s purchases reach a $5 threshold. %1$s
                    Only then, once your content’s value is firmly established, is the customer asked to register and pay. %1$s
                    This results in shopping cart conversion rates of over 80%%. %1$s
                    LaterPay’s frictionless customer onboarding helps you turn traffic into transactions.', 'laterpay' ),
                            "<br/>"
                        ); ?>
                    </p>
                </div>
                <div class="lp_side_info">
                    <h2><?php esc_html_e( 'FAQ\'s', 'laterpay' ); ?></h2>
                    <h3><?php esc_html_e( 'Having Trouble with Page Cache?', 'laterpay' ); ?></h3>

                    <div>
                        <p><?php esc_html_e( 'You need to whitelist the following cookies from caching in order for page-cache to work properly with laterpay.', 'laterpay' ); ?></p>
                        <ol>
                            <li>laterpay_token</li>
                            <li>laterpay_purchased_gift_card</li>
                            <li>laterpay_tracking_code</li>
                        </ol>
                        <p><?php esc_html_e( 'We have already taken care of this if you\'re on a WordPress VIP Environment.', 'laterpay' ); ?></p>
                    </div>

                    <?php
                    // Only show info if on WPEngine environment.
                    if ( function_exists( 'is_wpe' ) && is_wpe() ) {
                        ?>
                        <h3><?php esc_html_e( 'Having Trouble on WPEngine?', 'laterpay' ); ?></h3>

                        <div>
                            <p><?php printf( '%1$s  <code>%2$s</code> %3$s', esc_html__( 'If you\'re facing the issue on WPEngine even after whitelisting requested cookies, please check if any of your active plugin/theme is using', 'laterpay' ), esc_html__( 'session*', 'laterpay' ), esc_html__( 'functions.', 'laterpay' ) ); ?></p>
                            <p><?php printf( '%1$s <a href=%2$s target="_blank" class="lp_info_link">%3$s</a> %4$s', esc_html__( 'Please', 'laterpay' ), esc_url( 'https://wpengine.com/support/cookies-and-php-sessions/' ), esc_html__( ' Check this', 'laterpay' ), esc_html__( 'for more information regarding session usage on WPEngine.', 'laterpay' ) ); ?></p>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="lp_side_info">
                    <h2><?php esc_html_e( 'Support', 'laterpay' ); ?></h2>
                    <p>
                        <?php
                        printf(
                            esc_html__( '%1$sClick here%3$s or email %2$ssupport@laterpay.net%3$s to provide feedback or to reach our customer service team.', 'laterpay' ),
                            "<a href='https://www.laterpay.net/contact-support' target='_blank'>",
                            "<a href='mailto:support@laterpay.net'>",
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
