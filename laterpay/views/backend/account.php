<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flashMessage" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_u_relative">
        <a  href="<?php echo add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ); ?>"
            id="lp_js_pluginModeIndicator"
            class="lp_pluginModeIndicator lp_u_absolute"
            <?php if ( $laterpay['plugin_is_in_live_mode'] ): ?>style="display:none;"<?php endif; ?>
            data-icon="h">
            <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
            <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
        </a>
        <?php echo $laterpay['top_nav']; ?>
    </div>

    <div class="lp_pagewrap">
        <div class="lp_row lp_u_clearfix">
            <h2><?php _e( 'LaterPay API Credentials', 'laterpay' ); ?></h2>

            <div class="lp_u_w-1-2 lp_u_left lp_u_relative lp_sandboxCredentials" data-icon="h">
                <fieldset class="lp_u_b-r3 lp_u_b-1 lp_u_b-embossed lp_u_m-1 lp_u_m-b0 lp_u_m-l0">
                    <legend class="lp_u_fs-1 lp_u_bold lp_u_pd-0-05"><?php _e( 'Sandbox Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for testing purposes', 'laterpay' ); ?></dfn>
                    <form id="laterpay_sandbox_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_sandbox_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_u_clearfix">
                            <li class="lp_u_left lp_backgroundIconSpacer">
                                &nbsp;
                            </li>
                            <li class="lp_u_left">
                                <span class="lp_iconizedInput lp_merchant-id-icon" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="lp_js_sandboxMerchantId"
                                    name="laterpay_sandbox_merchant_id"
                                    class="lp_js_validateMerchantId lp_input lp_merchantIdInput"
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
                        <ul class="lp_u_clearfix">
                            <li class="lp_u_left lp_backgroundIconSpacer">
                                &nbsp;
                            </li>
                            <li class="lp_u_left">
                                <span class="lp_iconizedInput lp_api-key-icon" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    id="lp_js_sandboxApiKey"
                                    name="laterpay_sandbox_api_key"
                                    class="lp_js_validateApiKey lp_input lp_apiKeyInput"
                                    value="<?php echo $laterpay['sandbox_api_key']; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key" alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'API Key', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>

            <div class="lp_u_w-1-2 lp_u_left lp_u_relative lp_liveCredentials<?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' lp_is-live'; } ?>" data-icon="k">
                <fieldset class="lp_u_b-r3 lp_u_b-1 lp_u_b-embossed lp_u_m-1 lp_u_m-b0 lp_u_m-l0">
                    <legend class="lp_u_fs-1 lp_u_bold lp_u_pd-0-05"><?php _e( 'Live Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for processing real financial transactions', 'laterpay' ); ?></dfn>
                    <form id="laterpay_live_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_live_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_u_clearfix">
                            <li class="lp_u_left lp_backgroundIconSpacer">
                                &nbsp;
                            </li>
                            <li class="lp_u_left">
                                <span class="lp_iconizedInput lp_merchant-id-icon live" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="lp_js_liveMerchantId"
                                    name="laterpay_live_merchant_id"
                                    class="lp_js_validateMerchantId lp_input lp_merchantIdInput"
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
                        <ul class="lp_u_clearfix">
                            <li class="lp_u_left lp_backgroundIconSpacer">
                                &nbsp;
                            </li>
                            <li class="lp_u_left">
                                <span class="lp_iconizedInput lp_api-key-icon live" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    name="laterpay_live_api_key"
                                    id="lp_js_liveApiKey"
                                    class="lp_js_validateApiKey lp_input lp_apiKeyInput"
                                    value="<?php echo $laterpay['live_api_key']; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key" alt="<?php _e( 'Paste Live API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'API Key', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                        <ul class="lp_u_clearfix">
                            <li class="lp_u_left lp_backgroundIconSpacer">
                                &nbsp;
                            </li>
                            <li class="lp_u_left">
                                <a href="#" id="lp_js_showMerchantContracts" class="lp_requestLiveCredentials button button-primary">
                                    <?php _e( 'Request Live API Credentials', 'laterpay' ); ?>
                                </a>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>
        </div>
        <dfn id="lp_js_credentialsHint" class="lp_u_block lp_u_m-1-0">
            <?php echo sprintf( __( 'Go to your <a href="%s">LaterPay Merchantbackend</a> to get your LaterPay API credentials.', 'laterpay' ), $config->get( 'api.merchant_backend_url' ) ); ?>
        </dfn>
        <hr class="lp_u_m-1-0 lp_u_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Plugin Mode', 'laterpay' ); ?></h2>
            <?php _e( 'This site is in', 'laterpay' ); ?><div class="lp_toggle">
                <form id="laterpay_plugin_mode" method="post">
                    <input type="hidden" name="form"    value="laterpay_plugin_mode">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                    <label class="lp_toggle_label">
                        <input type="checkbox"
                                name="plugin_is_in_live_mode_checkbox"
                                id="lp_js_togglePluginMode"
                                class="lp_toggle_input"
                                <?php if ( $laterpay['plugin_is_in_live_mode'] ): ?>checked<?php endif; ?>>
                        <input type="hidden"
                                name="plugin_is_in_live_mode"
                                id="lp_js_pluginMode_hiddenInput"
                                value="<?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo 1; } else { echo 0; } ?>">
                        <span class="lp_toggle_text" data-on="LIVE" data-off="TEST"></span>
                        <span class="lp_toggle_handle"></span>
                    </label>
                </form>
            </div><?php _e( 'mode.', 'laterpay' ); ?>

            <dfn id="lp_js_pluginMode_liveText" class="lp_u_block"<?php if ( ! $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'Your visitors <strong>can now purchase with LaterPay</strong>. All payments are booked and credited to your account.', 'laterpay' ); ?>
            </dfn>
            <dfn id="lp_js_pluginMode_testText" class="lp_u_block"<?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'Payments are only simulated and <strong>not actually booked</strong>. LaterPay is <strong>not visible for regular visitors</strong>.', 'laterpay' ); ?>
            </dfn>
        </div>
    </div>

</div>
