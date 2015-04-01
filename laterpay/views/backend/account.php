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
            <h2 class="lp_plugin-mode-indicator__title"><?php _e( 'Test mode', 'laterpay' ); ?></h2>
            <span class="lp_plugin-mode-indicator__text"><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
        </a>

        <?php echo $laterpay['top_nav']; ?>
    </div>


    <div class="lp_pagewrap">
        <div class="lp_clearfix">
            <h2><?php _e( 'LaterPay API Credentials', 'laterpay' ); ?></h2>


            <div class="lp_api-credentials lp_api-credentials--sandbox" data-icon="h">
                <fieldset class="lp_api-credentials__fieldset">
                    <legend class="lp_api-credentials__legend"><?php _e( 'Sandbox Environment', 'laterpay' ); ?></legend>

                    <dfn class="lp_api-credentials__hint">
                        <?php _e( 'for testing with simulated payments', 'laterpay' ); ?>
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
                                    value="<?php echo $laterpay['sandbox_merchant_id']; ?>"
                                    maxlength="22"
                                    required>
                                <label for="laterpay_sandbox_merchant_id"
                                    alt="<?php _e( 'Paste Sandbox Merchant ID here', 'laterpay' ); ?>"
                                    placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>">
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
                                    value="<?php echo $laterpay['sandbox_api_key']; ?>"
                                    maxlength="32"
                                    required>
                                <label for="laterpay_sandbox_api_key"
                                    alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>"
                                    placeholder="<?php _e( 'API Key', 'laterpay' ); ?>">
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
                    <legend class="lp_api-credentials__legend"><?php _e( 'Live Environment', 'laterpay' ); ?></legend>

                    <dfn class="lp_api-credentials__hint">
                        <?php _e( 'for processing real financial transactions', 'laterpay' ); ?>
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
                                    value="<?php echo $laterpay['live_merchant_id']; ?>"
                                    maxlength="22"
                                    required>
                                <label for="laterpay_live_merchant_id"
                                    alt="<?php _e( 'Paste Live Merchant ID here', 'laterpay' ); ?>"
                                    placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>">
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
                                    value="<?php echo $laterpay['live_api_key']; ?>"
                                    maxlength="32"
                                    required>
                                <label for="laterpay_sandbox_api_key"
                                    alt="<?php _e( 'Paste Live API Key here', 'laterpay' ); ?>"
                                    placeholder="<?php _e( 'API Key', 'laterpay' ); ?>">
                                </label>
                            </form>
                        </li>
                        <li class="lp_api-credentials__list-item">
                            <a href="#"
                                id="lp_js_showMerchantContracts"
                                class="button button-primary"
                                <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo 'style="display:none";'; } ?>>
                                <?php _e( 'Request Live API Credentials', 'laterpay' ); ?>
                            </a>
                        </li>
                    </ul>

                </fieldset>
            </div>

        </div>
        <dfn id="lp_js_credentialsHint">
            <?php echo sprintf( __( 'Go to your <a href="%s">LaterPay Merchantbackend</a> to get your LaterPay API credentials.', 'laterpay' ), $config->get( 'api.merchant_backend_url' ) ); ?>
        </dfn>
        <hr class="lp_form-group-separator">


        <div class="lp_account-mode-switch">
            <h2><?php _e( 'Plugin Mode', 'laterpay' ); ?></h2>
            <?php _e( 'The LaterPay plugin is in', 'laterpay' ); ?><div class="lp_toggle">
                <form id="laterpay_plugin_mode" method="post">
                    <input type="hidden" name="form"    value="laterpay_plugin_mode">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
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
            </div><?php _e( 'mode.', 'laterpay' ); ?>
            <div id="lp_js_pluginVisibilitySetting"
                class="lp_inline-block"
                <?php if ( $laterpay['plugin_is_in_live_mode'] ) { echo ' style="display:none;"'; } ?>>
                <?php _e( 'It is invisible', 'laterpay' ); ?><div class="lp_toggle">
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
                </div><?php _e( 'visible to visitors.', 'laterpay' ); ?>
            </div>
        </div>


    </div>

</div>
