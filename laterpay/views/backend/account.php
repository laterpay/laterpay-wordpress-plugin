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
        <a  href="<?php echo add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ); ?>"
            id="lp_js_pluginModeIndicator"
            class="lp_plugin-mode-indicator"
            <?php if ( $laterpay['plugin_is_in_live_mode'] ): ?>style="display:none;"<?php endif; ?>
            data-icon="h">
            <h2 class="lp_plugin-mode-indicator__title"><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
            <span class="lp_plugin-mode-indicator__text"><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
        </a>
        <?php echo $laterpay['top_nav']; ?>
    </div>

    <div class="lp_pagewrap lp_account">
        <div class="lp_row lp_clearfix">
            <h2><?php _e( 'LaterPay API Credentials', 'laterpay' ); ?></h2>

            <div class="lp_1/2 lp_left lp_relative lp_api-credentials lp_api-credentials--sandbox" data-icon="h">
                <fieldset class="lp_fieldset">
                    <legend class="lp_fieldset__legend"><?php _e( 'Sandbox Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for testing purposes', 'laterpay' ); ?></dfn>
                    <form id="laterpay_sandbox_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_sandbox_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_clearfix">
                            <li class="lp_icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_left lp_account-input">
                                <span class="lp_iconized-input lp_merchant-id-icon" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="lp_js_sandboxMerchantId"
                                    name="laterpay_sandbox_merchant_id"
                                    class="lp_js_validateMerchantId lp_input lp_account-input__input"
                                    value="<?php echo $laterpay['sandbox_merchant_id']; ?>"
                                    required>
                                <label for="laterpay_sandbox_merchant_id" alt="<?php _e( 'Paste Sandbox Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                    <form id="laterpay_sandbox_api_key_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_sandbox_api_key">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_clearfix">
                            <li class="lp_icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_left lp_account-input">
                                <span class="lp_iconized-input lp_api-key-icon" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    id="lp_js_sandboxApiKey"
                                    name="laterpay_sandbox_api_key"
                                    class="lp_js_validateApiKey lp_input lp_account-input__input"
                                    value="<?php echo $laterpay['sandbox_api_key']; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key" alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'API Key', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>

            <div class="lp_1/2 lp_left lp_relative lp_api-credentials lp_api-credentials--live lp_js_liveCredentials <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' lp_is-live'; } ?>" data-icon="k">
                <fieldset class="lp_fieldset">
                    <legend class="lp_fieldset__legend"><?php _e( 'Live Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for processing real financial transactions', 'laterpay' ); ?></dfn>
                    <form id="laterpay_live_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_live_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_clearfix">
                            <li class="lp_icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_left lp_account-input">
                                <span class="lp_iconized-input lp_merchant-id-icon live" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="lp_js_liveMerchantId"
                                    name="laterpay_live_merchant_id"
                                    class="lp_js_validateMerchantId lp_input lp_account-input__input"
                                    value="<?php echo $laterpay['live_merchant_id']; ?>"
                                    required>
                                <label for="laterpay_live_merchant_id" alt="<?php _e( 'Paste Live Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                    <form id="laterpay_live_api_key_form" method="post">
                        <input type="hidden" name="form"    value="laterpay_live_api_key">
                        <input type="hidden" name="action"  value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_clearfix">
                            <li class="lp_icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_left lp_account-input">
                                <span class="lp_iconized-input lp_api-key-icon live" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    name="laterpay_live_api_key"
                                    id="lp_js_liveApiKey"
                                    class="lp_js_validateApiKey lp_input lp_account-input__input"
                                    value="<?php echo $laterpay['live_api_key']; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key"
                                        alt="<?php _e( 'Paste Live API Key here', 'laterpay' ); ?>"
                                        placeholder="<?php _e( 'API Key', 'laterpay' ); ?>">
                                </label>
                            </li>
                        </ul>
                        <ul class="lp_clearfix">
                            <li class="lp_icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_left">
                                <a href="#" id="lp_js_showMerchantContracts" class="lp_request-live-credentials button button-primary" <?php if ( $laterpay['plugin_is_in_live_mode'] == 1 ) { echo 'style="display:none";'; } ?> >
                                    <?php _e( 'Request Live API Credentials', 'laterpay' ); ?>
                                </a>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>
        </div>
        <dfn id="lp_js_credentialsHint" class="lp_block lp_m-0">
            <?php echo sprintf( __( 'Go to your <a href="%s">LaterPay Merchantbackend</a> to get your LaterPay API credentials.', 'laterpay' ), $config->get( 'api.merchant_backend_url' ) ); ?>
        </dfn>
        <hr class="lp_form-group-separator">

        <div class="lp_row lp_account-mode-switch">
            <h2><?php _e( 'Plugin Mode', 'laterpay' ); ?></h2>
            <?php _e( 'The LaterPay plugin is in', 'laterpay' ); ?><div class="lp_toggle">
                <form id="laterpay_plugin_mode" method="post">
                    <input type="hidden" name="form"    value="laterpay_plugin_mode">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                    <label class="lp_toggle__label">
                        <input type="checkbox"
                                name="plugin_is_in_live_mode"
                                id="lp_js_togglePluginMode"
                                class="lp_toggle__input"
                                value="1"
                                <?php if ( $laterpay['plugin_is_in_live_mode'] ): ?>checked<?php endif; ?>>
                        <span class="lp_toggle__text" data-on="LIVE" data-off="TEST"></span>
                        <span class="lp_toggle__handle"></span>
                    </label>
                </form>
            </div><?php _e( 'mode.', 'laterpay' ); ?>
            <div id="lp_js_pluginVisibilitySetting" class="lp_inline-block"
                <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'It is invisible' , 'laterpay' ); ?><div class="lp_toggle">
                    <form id="laterpay_test_mode" method="post">
                        <input type="hidden" name="form"    value="laterpay_test_mode">
                        <input type="hidden" name="action"  value="laterpay_account">
                        <input type="hidden" id="lp_js_hasInvalidSandboxCredentials" name="invalid_credentials" value="0">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <label class="lp_toggle__label lp_toggle__label-pass">
                            <input type="checkbox"
                                   name="plugin_is_in_visible_test_mode"
                                   id="lp_js_toggleVisibilityInTestMode"
                                   class="lp_toggle__input"
                                   value="1"
                                   <?php if ( $laterpay['plugin_is_in_visible_test_mode'] ) { echo 'checked'; } ?>>
                            <span class="lp_toggle__text" data-on="" data-off="">
                            </span>
                            <span class="lp_toggle__handle"></span>
                        </label>
                    </form>
                </div><?php _e( 'visible to visitors.', 'laterpay' ); ?>
            </div>

            <dfn id="lp_js_pluginModeLiveText" class="lp_block"<?php if ( ! $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'Your visitors <strong>can now purchase with LaterPay</strong>. All payments are booked and credited to your account.', 'laterpay' ); ?>
            </dfn>
            <dfn id="lp_js_pluginModeTestText" class="lp_block"<?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'Payments are only simulated and <strong>not actually booked</strong>.', 'laterpay' ); ?>
            </dfn>
        </div>

    </div>

</div>
