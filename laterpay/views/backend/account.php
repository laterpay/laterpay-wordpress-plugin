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
                        <?php wp_nonce_field( 'laterpay_form' ); ?>
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
                <p class="lp_tooltip lp_tooltip_p lp_tooltip_account_p" data-tooltip="<?php esc_attr_e( 'In Test mode, only WordPress administrators will be able to see the LaterPay paywall. You must complete step 3 below to enable Live mode. This will display the paywall to your followers and allow you to begin accepting payments.', 'laterpay' ); ?>">
                    <span data-icon="m" class="lp_js_postPriceSpan"></span>
                </p>
            </div>

            <div class="lp_clearfix" id="lp_cache_warning" style="<?php echo 1 === absint( get_option( 'laterpay_show_cache_msg' ) ) ? '' : 'display:none;' ?>">
                <p class="live-success-msg"><?php esc_html_e( 'Congratulations, you are now accepting payments through LaterPay!', 'laterpay' ); ?></p>
                <p data-icon="n" class="live-cache-warning">
                    <?php
                    printf(
                        esc_html__( 'We recommend %sclearing your cache%s in order to ensure that the paywall is visible everyone.', 'laterpay' ),
                        '<a href="https://www.wpbeginner.com/beginners-guide/how-to-clear-your-cache-in-wordpress" target="_blank">',
                        '</a>'
                    );
                    ?>
                    <a class="hide-msg" id="hide_cache_warning"><?php esc_html_e( 'Hide message.', 'laterpay' ); ?></a>
                </p>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label"><span class="lp_step_span"><?php esc_html_e( 'Step 1', 'laterpay' ); ?>:</span> <?php esc_html_e( 'Select Currency', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <form id="laterpay_region" method="post">
                        <input type="hidden" name="form" value="laterpay_region_change">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php wp_nonce_field( 'laterpay_form' ); ?>
                        <select id="lp_js_apiRegionSection" name="laterpay_region" class="lp_input">
                            <option value="eu" <?php selected( $laterpay['region'], 'eu' ); ?>><?php esc_html_e( 'EURO (€)', 'laterpay' ); ?></option>
                            <option value="us" <?php selected( $laterpay['region'], 'us' ); ?>><?php esc_html_e( 'USD ($)', 'laterpay' ); ?></option>
                        </select>
                    </form>
                    <p id="lp_js_regionNotice" class="hidden">
                        <dfn class="lp_region_notice" data-icon="n">
                            <?php
                            printf(
                                esc_html__( '%1$sImportant:%2$s The minimum value for "Pay Now" prices in the U.S. region is %1$s$1.99.%2$s %3$sIf you previously set "Pay Now" prices lower than 1.99, these will need to be adjusted accordingly. %3$s', 'laterpay' ),
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
                    <?php esc_html_e( 'Get Started', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        printf(
                            esc_html__( 'Click through the remaining tabs from left to right. We will provide tips and instructions at the top of each page or click here to read our %sGetting Started Guide%s for step by step instructions.', 'laterpay' ),
                            "<a href='https://www.laterpay.net/academy/getting-started-with-the-laterpay-wordpress-plugin' class='lp_info_link' target='_blank'>",
                            '</a>'
                        ); ?>
                    </p>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Step 3', 'laterpay' ); ?>:</span>
                    <?php esc_html_e( 'Link to LaterPay', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        esc_html_e( 'A LaterPay Account is required to process financial transaction (so that we can pay you).', 'laterpay' );
                        ?>
                    </p>
                    <a class="lp_purchase-overlay__submit" id="lp_account_login" target="_blank" href="#" data-href-eu='https://web.laterpay.net/dialog/entry/?redirect_to=/merchant/#/login' data-href-us='https://web.uselaterpay.com/dialog/entry/?redirect_to=/merchant/#/login'>
                        <span class="lp_purchase-overlay__submit-text lp_sign_up"><?php esc_html_e( 'Login', 'laterpay' ); ?></span>
                    </a>
                    <?php esc_html_e( 'or', 'laterpay' ); ?>
                    <a class="lp_purchase-overlay__submit" href="https://www.laterpay.net/signup/merchant" target="_blank">
                        <span class="lp_purchase-overlay__submit-text lp_sign_up"><?php esc_html_e( 'Sign Up', 'laterpay' ); ?></span>
                    </a>
                    <p>
                        <?php
                            esc_html_e( 'Once you have created your account, you will be instructed to copy and paste your Merchant ID & API Key into the boxes below. This ensures that your plugin is linked to your account.', 'laterpay' );
                        ?>
                    </p>
                    <div id="lp_js_liveCredentials" class="lp_api-credentials">
                        <ul class="lp_api-credentials__list">
                            <li class="lp_api-credentials__list-item">
                                <span class="lp_iconized-input" data-icon="i"></span>
                                <form id="laterpay_live_merchant_id" method="post">
                                    <input type="hidden" name="form" value="laterpay_live_merchant_id">
                                    <input type="hidden" name="action" value="laterpay_account">
                                    <?php wp_nonce_field( 'laterpay_form' ); ?>

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
                                    <p class="lp_tooltip lp_tooltip_p lp_tooltip_account_p" data-tooltip="<?php esc_attr_e( 'This is required in order to ensure that you receive payments. Log in to your LaterPay account, navigate to the Developer tab & copy and paste the information into the corresponding boxes.', 'laterpay' ); ?>">
                                        <span data-icon="m" class="lp_js_postPriceSpan"></span>
                                    </p>
                                </form>
                            </li>
                            <li class="lp_api-credentials__list-item">
                                <span class="lp_iconized-input" data-icon="j"></span>
                                <form id="laterpay_live_api_key" method="post">
                                    <input type="hidden" name="form" value="laterpay_live_api_key">
                                    <input type="hidden" name="action" value="laterpay_account">
                                    <?php wp_nonce_field( 'laterpay_form' ); ?>

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
                                    <p class="lp_tooltip lp_tooltip_p lp_tooltip_account_p" data-tooltip="<?php esc_attr_e( 'This is required in order to ensure that you receive payments. Log in to your LaterPay account, navigate to the Developer tab & copy and paste the information into the corresponding boxes.', 'laterpay' ); ?>">
                                        <span data-icon="m" class="lp_js_postPriceSpan"></span>
                                    </p>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp_clearfix">
                <label class="lp_step_label">
                    <span class="lp_step_span"><?php esc_html_e( 'Step 4', 'laterpay' ); ?>:</span>
                    <?php esc_html_e( 'Go Live', 'laterpay' ); ?>
                </label>
                <div class="lp_info_div">
                    <p>
                        <?php
                        esc_html_e( 'How can I be sure that I am ready to go live?', 'laterpay' );
                        ?>
                    </p>
                    <ul class="lp_go_live">
                        <li data-icon="f"><?php esc_html_e( 'Set up your Pricing', 'laterpay' ); ?></li>
                        <li data-icon="f"><?php esc_html_e( 'Preview your site while logged into WordPress to ensure everything is displayed as expected', 'laterpay' ); ?></li>
                        <li data-icon="f"><?php esc_html_e( 'Create and link to your LaterPay account so that all revenue can be sent to your bank account', 'laterpay' ); ?></li>
                    </ul>
                    <p>
                        <?php
                        esc_html_e( 'Optional Configurations:', 'laterpay' );
                        ?>
                    </p>
                    <ul class="lp_go_live">
                        <li data-icon="f"><?php esc_html_e( 'Adjust the Appearance to match your brand colors', 'laterpay' ); ?></li>
                        <li data-icon="f"><?php esc_html_e( 'Explore the Advanced tab to learn about additional features available', 'laterpay' ); ?></li>
                    </ul>
                    <br />
                    <b><?php esc_html_e( 'Click the toggle at the top of the page to switch to "LIVE" mode and start generating revenue!', 'laterpay' ); ?></b>
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
                    Requiring upfront registration and payment results in customer abandon rates of up to 98%%. LaterPay\'s patented Pay Later revenue model instead defers the registration process until a customer’s purchases reach a $5 threshold. Only then, once your content’s value is firmly established, is the customer asked to register and pay. This results in shopping cart conversion rates of over 80%%. LaterPay’s frictionless customer onboarding helps you turn traffic into transactions.', 'laterpay' ),
                            "<br/>"
                        ); ?>
                    </p>
                </div>
                <?php $this->render_faq_support(); ?>
            </div>
        </div>
    </div>
</div>
