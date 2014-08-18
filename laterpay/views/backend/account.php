<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_p-rel">
        <?php if ( ! $plugin_is_in_live_mode ): ?>
        <a href="<?php echo add_query_arg( array( 'page' => $admin_menu['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_plugin-mode-indicator lp_p-abs" data-icon="h">
            <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
            <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
        </a>
        <?php endif; ?>
        <?php echo $top_nav; ?>
    </div>

    <div class="lp_pagewrap">
        <div class="lp_row lp_fl-clearfix">
            <h2><?php _e( 'LaterPay API Credentials', 'laterpay' ); ?></h2>

            <div class="lp_w-1-2 lp_fl-left lp_p-rel lp_sandbox-credentials" data-icon="h">
                <fieldset class="lp_b-r3 lp_b-s1 lp_b-emb lp_m-1 lp_m-b0 lp_m-l0">
                    <legend class="lp_fs-1 lp_fw-b lp_pd-0-05"><?php _e( 'Sandbox Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for testing purposes', 'laterpay' ); ?></dfn>
                    <form id="laterpay_sandbox_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_sandbox_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_fl-clearfix">
                            <li class="lp_fl-left lp_background-icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_fl-left">
                                <span class="input-icon merchant-id-icon" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="laterpay_sandbox_merchant_id"
                                    name="laterpay_sandbox_merchant_id"
                                    class="lp_input lp_merchant-id-input"
                                    value="<?php echo $sandbox_merchant_id; ?>"
                                    required>
                                <label for="laterpay_sandbox_merchant_id" alt="<?php _e( 'Paste Sandbox Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                    <form id="laterpay_sandbox_api_key_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_sandbox_api_key">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_fl-clearfix">
                            <li class="lp_fl-left lp_background-icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_fl-left">
                                <span class="input-icon api-key-icon" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    id="laterpay_sandbox_api_key"
                                    name="laterpay_sandbox_api_key"
                                    class="lp_input lp_api-key-input"
                                    value="<?php echo $sandbox_api_key; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key" alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'API Key', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>

            <div class="lp_w-1-2 lp_fl-left lp_p-rel lp_live-credentials" data-icon="k">
                <fieldset class="lp_b-r3 lp_b-s1 lp_b-emb lp_m-1 lp_m-b0 lp_m-l0">
                    <legend class="lp_fs-1 lp_fw-b lp_pd-0-05"><?php _e( 'Live Environment', 'laterpay' ); ?></legend>
                    <dfn><?php _e( 'for processing real financial transactions', 'laterpay' ); ?></dfn>
                    <form id="laterpay_live_merchant_id_form" method="post">
                        <input type="hidden" name="form"   value="laterpay_live_merchant_id">
                        <input type="hidden" name="action" value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_fl-clearfix">
                            <li class="lp_fl-left lp_background-icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_fl-left">
                                <span class="input-icon merchant-id-icon live" data-icon="i"></span>
                                <input type="text"
                                    maxlength="22"
                                    id="laterpay_live_merchant_id"
                                    name="laterpay_live_merchant_id"
                                    class="lp_input lp_merchant-id-input"
                                    value="<?php echo $live_merchant_id; ?>"
                                    required>
                                <label for="laterpay_live_merchant_id" alt="<?php _e( 'Paste Live Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Merchant ID', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                    </form>
                    <form id="laterpay_live_api_key_form" method="post">
                        <input type="hidden" name="form"    value="laterpay_live_api_key">
                        <input type="hidden" name="action"  value="laterpay_account">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                        <ul class="lp_fl-clearfix">
                            <li class="lp_fl-left lp_background-icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_fl-left">
                                <span class="input-icon api-key-icon live" data-icon="j"></span>
                                <input type="text"
                                    maxlength="32"
                                    name="laterpay_live_api_key"
                                    id="laterpay_live_api_key"
                                    class="lp_input lp_api-key-input"
                                    value="<?php echo $live_api_key; ?>"
                                    required>
                                <label for="laterpay_sandbox_api_key" alt="<?php _e( 'Paste Live API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'API Key', 'laterpay' ); ?>"></label>
                            </li>
                        </ul>
                        <ul id="request-live-credentials" class="lp_fl-clearfix"
                            <?php if ( $live_api_key ) { echo ' style="display:none;"'; } ?>>
                            <li class="lp_fl-left lp_background-icon-spacer">
                                &nbsp;
                            </li>
                            <li class="lp_fl-left">
                                <a href="#" class="button button-primary">
                                    <?php _e( 'Request Live API Credentials', 'laterpay' ); ?>
                                </a>
                            </li>
                        </ul>
                    </form>
                </fieldset>
            </div>
        </div>
        <dfn class="lp_credentials-hint lp_d-block lp_m-1-0">
            <?php echo sprintf( __( 'Go to your <a href="%s">LaterPay Merchantbackend</a> to get your LaterPay API credentials.', 'laterpay' ), $config->get( 'api.merchant_backend_url' ) ); ?>
        </dfn>
        <hr>

        <div class="lp_row">
            <h2><?php _e( 'Plugin Mode', 'laterpay' ); ?></h2>
            <?php _e( 'This site is in', 'laterpay' ); ?><div class="switch">
                <form id="laterpay_plugin_mode" method="post">
                    <input type="hidden" name="form"    value="laterpay_plugin_mode">
                    <input type="hidden" name="action"  value="laterpay_account">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                    <label class="switch-label">
                        <input type="checkbox"
                                name="plugin_is_in_live_mode_checkbox"
                                id="plugin-mode-toggle"
                                class="switch-input"
                                <?php if ( $plugin_is_in_live_mode ): ?>checked<?php endif; ?>>
                        <input type="hidden"
                                name="plugin_is_in_live_mode"
                                id="plugin_mode_hidden_input"
                                value="<?php if ( $plugin_is_in_live_mode ) { echo 1; } else { echo 0; } ?>">
                        <span class="switch-text" data-on="LIVE" data-off="TEST"></span>
                        <span class="switch-handle"></span>
                    </label>
                </form>
            </div><?php _e( 'mode.', 'laterpay' ); ?>

            <dfn id="plugin_mode_live_text" class="lp_d-block"<?php if ( ! $plugin_is_in_live_mode ) { echo " style='display:none;'"; } ?>>
                <?php _e( 'Your visitors <strong>can now purchase with LaterPay</strong>.', 'laterpay' ); ?>
                <?php _e( 'All payments are booked and credited to your account.', 'laterpay' ); ?>
            </dfn>
            <dfn id="plugin_mode_test_text" class="lp_d-block"<?php if ( $plugin_is_in_live_mode ) { echo " style='display:none;'"; } ?>>
                <?php _e( 'Payments are only simulated and <strong>not actually booked</strong>.', 'laterpay' ); ?>
                <?php _e( 'LaterPay is <strong>not visible for regular visitors</strong>.', 'laterpay' ); ?>
            </dfn>
        </div>
    </div>

</div>
