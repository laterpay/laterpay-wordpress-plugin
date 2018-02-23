<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>


    <div class="lp_navigation">
        <a  href="<?php echo esc_url_raw( add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ) ); ?>"
            id="lp_js_pluginModeIndicator"
            class="lp_plugin-mode-indicator"
            <?php if ( $laterpay['plugin_is_in_live_mode'] ) : ?>style="display:none;"<?php endif; ?>
            data-icon="h">
            <h2 class="lp_plugin-mode-indicator__title"><?php echo laterpay_sanitize_output( __( 'Test mode', 'laterpay' ) ); ?></h2>
            <span class="lp_plugin-mode-indicator__text"><?php echo laterpay_sanitize_output( __( 'Earn money in <i>live mode</i>', 'laterpay' ) ); ?></span>
        </a>

        <?php echo laterpay_sanitized( $laterpay['top_nav'] ); ?>
    </div>


    <div class="lp_pagewrap">

        <div class="lp_greybox lp_mt lp_mr lp_mb">
            <?php echo laterpay_sanitize_output( __( 'The LaterPay plugin is in', 'laterpay' ) ); ?><div class="lp_toggle">
                <form id="laterpay_plugin_mode" method="post">
                    <input type="hidden" name="form"    value="laterpay_plugin_mode">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                    <label class="lp_toggle__label">
                        <input type="checkbox"
                                id="lp_js_togglePluginMode"
                                class="lp_toggle__input"
                                name="plugin_is_in_live_mode"
                                value="1"
                                <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo 'checked'; } ?>>
                        <span class="lp_toggle__text" data-on="LIVE" data-off="TEST"></span>
                        <span class="lp_toggle__handle"></span>
                    </label>
                </form>
            </div><?php echo laterpay_sanitize_output( __( 'mode.', 'laterpay' ) ); ?>
            <div id="lp_js_pluginVisibilitySetting"
                class="lp_inline-block"
                <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php echo laterpay_sanitize_output( __( 'It is invisible', 'laterpay' ) ); ?><div class="lp_toggle">
                    <form id="laterpay_test_mode" method="post">
                        <input type="hidden" name="form"    value="laterpay_test_mode">
                        <input type="hidden" name="action"  value="laterpay_account">
                        <input type="hidden" id="lp_js_hasInvalidSandboxCredentials" name="invalid_credentials" value="0">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <label class="lp_toggle__label lp_toggle__label-pass">
                            <input type="checkbox"
                                   id="lp_js_toggleVisibilityInTestMode"
                                   class="lp_toggle__input"
                                   name="plugin_is_in_visible_test_mode"
                                   value="1"
                                <?php if ( $laterpay['plugin_is_in_visible_test_mode'] ) { echo 'checked'; } ?>>
                            <span class="lp_toggle__text" data-on="" data-off=""></span>
                            <span class="lp_toggle__handle"></span>
                        </label>
                    </form>
                </div><?php echo laterpay_sanitize_output( __( 'visible to visitors.', 'laterpay' ) ); ?>
            </div>
        </div>

        <div id="lp_js_apiCredentialsSection" class="lp_clearfix">

            <div class="lp_api-credentials lp_api-credentials--sandbox" data-icon="h">
                <fieldset class="lp_api-credentials__fieldset">
                    <legend class="lp_api-credentials__legend"><?php echo laterpay_sanitize_output( __( 'Sandbox Environment', 'laterpay' ) ); ?></legend>

                    <dfn class="lp_api-credentials__hint">
                        <?php echo laterpay_sanitize_output( __( 'for testing with simulated payments', 'laterpay' ) ); ?>
                    </dfn>

                    <ul class="lp_api-credentials__list">
                        <li class="lp_api-credentials__list-item">
                            <span class="lp_iconized-input" data-icon="i"></span>
                            <form id="laterpay_sandbox_merchant_id" method="post">
                                <input type="hidden" name="form"   value="laterpay_sandbox_merchant_id">
                                <input type="hidden" name="action" value="laterpay_account">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                <input type="text"
                                    id="lp_js_sandboxMerchantId"
                                    class="lp_js_validateMerchantId lp_api-credentials__input"
                                    name="laterpay_sandbox_merchant_id"
                                    value="<?php echo esc_attr( $laterpay['sandbox_merchant_id'] ); ?>"
                                    maxlength="22"
                                    required>
                                <label for="laterpay_sandbox_merchant_id"
                                    alt="<?php echo esc_attr( __( 'Paste Sandbox Merchant ID here', 'laterpay' ) ); ?>"
                                    placeholder="<?php echo esc_attr( __( 'Merchant ID', 'laterpay' ) ); ?>">
                                </label>
                            </form>
                        </li>
                        <li class="lp_api-credentials__list-item">
                            <span class="lp_iconized-input" data-icon="j"></span>
                            <form id="laterpay_sandbox_api_key" method="post">
                                <input type="hidden" name="form"   value="laterpay_sandbox_api_key">
                                <input type="hidden" name="action" value="laterpay_account">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                <input type="text"
                                    id="lp_js_sandboxApiKey"
                                    class="lp_js_validateApiKey lp_api-credentials__input"
                                    name="laterpay_sandbox_api_key"
                                    value="<?php echo esc_attr( $laterpay['sandbox_api_key'] ); ?>"
                                    maxlength="32"
                                    required>
                                <label for="laterpay_sandbox_api_key"
                                    alt="<?php echo esc_attr( __( 'Paste Sandbox API Key here', 'laterpay' ) ); ?>"
                                    placeholder="<?php echo esc_attr( __( 'API Key', 'laterpay' ) ); ?>">
                                </label>
                            </form>
                        </li>
                    </ul>

                </fieldset>
            </div>

            <div id="lp_js_liveCredentials"
                class="lp_api-credentials lp_api-credentials--live<?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' lp_is-live'; } ?>"
                data-icon="k">
                <fieldset class="lp_api-credentials__fieldset">
                    <legend class="lp_api-credentials__legend"><?php echo laterpay_sanitize_output( __( 'Live Environment', 'laterpay' ) ); ?></legend>

                    <dfn class="lp_api-credentials__hint">
                        <?php echo laterpay_sanitize_output( __( 'for processing real financial transactions', 'laterpay' ) ); ?>
                    </dfn>

                    <ul class="lp_api-credentials__list">
                        <li class="lp_api-credentials__list-item">
                            <span class="lp_iconized-input" data-icon="i"></span>
                            <form id="laterpay_live_merchant_id" method="post">
                                <input type="hidden" name="form"   value="laterpay_live_merchant_id">
                                <input type="hidden" name="action" value="laterpay_account">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                <input type="text"
                                    id="lp_js_liveMerchantId"
                                    class="lp_js_validateMerchantId lp_api-credentials__input"
                                    name="laterpay_live_merchant_id"
                                    value="<?php echo esc_attr( $laterpay['live_merchant_id'] ); ?>"
                                    maxlength="22"
                                    required>
                                <label for="laterpay_live_merchant_id"
                                    alt="<?php echo esc_attr( __( 'Paste Live Merchant ID here', 'laterpay' ) ); ?>"
                                    placeholder="<?php echo esc_attr( __( 'Merchant ID', 'laterpay' ) ); ?>">
                                </label>
                            </form>
                        </li>
                        <li class="lp_api-credentials__list-item">
                            <span class="lp_iconized-input" data-icon="j"></span>
                            <form id="laterpay_live_api_key" method="post">
                                <input type="hidden" name="form"    value="laterpay_live_api_key">
                                <input type="hidden" name="action"  value="laterpay_account">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                <input type="text"
                                    id="lp_js_liveApiKey"
                                    class="lp_js_validateApiKey lp_api-credentials__input"
                                    name="laterpay_live_api_key"
                                    value="<?php echo esc_attr( $laterpay['live_api_key'] ); ?>"
                                    maxlength="32"
                                    required>
                                <label for="laterpay_sandbox_api_key"
                                    alt="<?php echo esc_attr( __( 'Paste Live API Key here', 'laterpay' ) ); ?>"
                                    placeholder="<?php echo esc_attr( __( 'API Key', 'laterpay' ) ); ?>">
                                </label>
                            </form>
                        </li>
                        <li class="lp_api-credentials__list-item">
                            <a href="#"
                               data-href-eu="<?php echo esc_url($laterpay['credentials_url_eu']);?>"
                               data-href-us="<?php echo esc_url($laterpay['credentials_url_us']);?>"
                                id="lp_js_showMerchantContracts"
                                class="button button-primary"
                                target="_blank"
                                <?php if ( ! empty( $laterpay['live_merchant_id'] ) && ! empty( $laterpay['live_api_key'] ) ) { echo 'style="display:none";'; } ?>>
                                <?php echo laterpay_sanitize_output( __( 'Request Live API Credentials', 'laterpay' ) ); ?>
                            </a>
                        </li>
                    </ul>
                </fieldset>
            </div>
        </div>

        <div class="lp_clearfix">
            <fieldset class="lp_fieldset">
                <legend class="lp_legend"><?php echo laterpay_sanitize_output( __( 'Region and Currency', 'laterpay' ) ); ?></legend>

                <p class="lp_bold"><?php echo laterpay_sanitize_output( __( 'Select the region for your LaterPay merchant account', 'laterpay' ) ); ?></p>

                <p>
                    <dfn>
                        <?php echo laterpay_sanitize_output( __( "Is the selling company or person based in Europe or in the United States?<br>
                        If you select 'Europe', all prices will be displayed and charged in Euro (EUR), and the plugin will connect to the LaterPay Europe platform.<br>
                        If you select 'United States', all prices will be displayed and charged in U.S. Dollar (USD), and the plugin will connect to the LaterPay U.S. platform. 
                        ", 'laterpay' ) ); ?>
                    </dfn>
                </p>

                <form id="laterpay_region" method="post">
                    <input type="hidden" name="form"    value="laterpay_region_change">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                    <select id="lp_js_apiRegionSection" name="laterpay_region" class="lp_input">
                        <option value="eu" <?php if ( $laterpay['region'] === 'eu' ) echo 'selected'; ?>><?php echo laterpay_sanitize_output( __( 'Europe (EUR)', 'laterpay' ) ); ?></option>
                        <option value="us" <?php if ( $laterpay['region'] === 'us' ) echo 'selected'; ?>><?php echo laterpay_sanitize_output( __( 'United States (USD)', 'laterpay' ) ); ?></option>
                    </select>
                </form>

                <p id="lp_js_regionNotice" <?php if ( $laterpay['region'] === 'us' ) : ?>class="hidden"<?php endif; ?>>
                    <dfn class="lp_region_notice" data-icon="n">
                        <?php echo laterpay_sanitize_output( __( "<b>Important:</b> The minimum value for \"Pay Now\" prices in the U.S. region is <b>$1.99</b>.<br>
                        If you have already set \"Pay Now\" prices lower than 1.99, make sure to change them before you switch to the U.S. region.<br>
                        If you haven't done any configuration yet, you can safely switch the region without further adjustments. 
                        ", 'laterpay' ) ); ?>
                    </dfn>
                </p>
            </fieldset>
        </div>

    </div>

</div>
