<script>
    var i18n_api_updated            = "<?php _e('The API key you entered is a valid LaterPay API key', 'laterpay'); ?>",
        i18n_api_invalid            = "<?php _e('The API key you entered is not a valid LaterPay API key!', 'laterpay'); ?>",
        i18n_merchant_id_updated    = "<?php _e('The Merchant ID you entered is a valid LaterPay Merchant ID.', 'laterpay'); ?>",
        i18n_merchant_id_invalid    = "<?php _e('The Merchant ID you entered is not a valid LaterPay Merchant ID!', 'laterpay'); ?>";
</script>

<div class="lp-page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <a href="#account" id="plugin-mode-indicator" data-icon="h" <?php if ( get_option('laterpay_plugin_mode_is_live') != 0 ) echo "style='display:none'"; ?>>
            <h2><?php _e('<strong>Test</strong> mode', 'laterpay'); ?></h2>
            <span><?php _e('Earn money in <i>live mode</i>', 'laterpay'); ?></span>
        </a>
        <ul class="tabs">
            <?php if ( get_option('laterpay_activate') == '0' ): ?>
                <li id="get-started-tab"><a href="#get_started"><?php _e('Get Started', 'laterpay'); ?></a></li>
            <?php endif; ?>
            <li><a href="#pricing"><?php _e('Pricing', 'laterpay'); ?></a></li>
            <li><a href="#appearance"><?php _e('Appearance', 'laterpay'); ?></a></li>
            <li class="current"><a href="#account"><?php _e('Account', 'laterpay'); ?></a></li>
        </ul>
    </div>

    <div class="lp-wrap">
        <div class="lp-form-row clearfix">
            <h2><?php _e('LaterPay API Credentials', 'laterpay'); ?></h2>

            <div class="w2-5 left">
                <h3><?php _e('Sandbox Environment', 'laterpay'); ?></h3>
                <dfn><?php _e('for testing purposes' , 'laterpay'); ?></dfn>
                <form id="laterpay_sandbox_merchant_id_form" method="post">
                    <input type="hidden" name="form"   value="laterpay_sandbox_merchant_id">
                    <input type="hidden" name="action" value="account">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <ul class="clearfix">
                        <li class="left w1-5">
                            <strong><?php _e('Merchant ID', 'laterpay'); ?></strong>
                        </li>
                        <li class="left">
                            <span class="input-icon merchant-id-icon" data-icon="i"></span>
                            <input type="text"
                                maxlength="22"
                                id="laterpay_sandbox_merchant_id"
                                name="laterpay_sandbox_merchant_id"
                                class="lp-input merchant-id-input"
                                value="<?php echo get_option('laterpay_sandbox_merchant_id'); ?>"
                                placeholder="<?php _e('Paste Sandbox Merchant ID here', 'laterpay'); ?>"/>
                        </li>
                    </ul>
                </form>
                <form id="laterpay_sandbox_api_key_form" method="post">
                    <input type="hidden" name="form"   value="laterpay_sandbox_api_key">
                    <input type="hidden" name="action" value="account">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <ul class="clearfix">
                        <li class="left w1-5">
                            <strong><?php _e('API Key', 'laterpay'); ?></strong>
                        </li>
                        <li class="left">
                            <span class="input-icon api-key-icon" data-icon="j"></span>
                            <input type="text"
                                maxlength="32"
                                id="laterpay_sandbox_api_key"
                                name="laterpay_sandbox_api_key"
                                class="lp-input api-key-input"
                                value="<?php echo get_option('laterpay_sandbox_api_key'); ?>"
                                placeholder="<?php _e('Paste Sandbox API Key here', 'laterpay'); ?>"/>
                        </li>
                    </ul>
                </form>
            </div>

            <div class="w2-5 left">
                <h3><?php _e('Live Environment', 'laterpay'); ?></h3>
                <dfn><?php _e('for processing real financial transactions', 'laterpay'); ?></dfn>
                <form id="laterpay_live_merchant_id_form" method="post">
                    <input type="hidden" name="form"   value="laterpay_live_merchant_id">
                    <input type="hidden" name="action" value="account">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <ul class="clearfix">
                        <li class="left w1-5">
                            <strong class="live"><?php _e('Merchant ID', 'laterpay'); ?></strong>
                        </li>
                        <li class="left">
                            <span class="input-icon merchant-id-icon live" data-icon="i"></span>
                            <input type="text"
                                maxlength="22"
                                id="laterpay_live_merchant_id"
                                name="laterpay_live_merchant_id"
                                class="lp-input merchant-id-input"
                                value="<?php echo get_option('laterpay_live_merchant_id'); ?>"
                                placeholder="<?php _e('Paste Live Merchant ID here', 'laterpay'); ?>"/>
                        </li>
                    </ul>
                </form>
                <form id="laterpay_live_api_key_form" method="post">
                    <input type="hidden" name="form"    value="laterpay_live_api_key">
                    <input type="hidden" name="action"  value="account">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <ul class="clearfix">
                        <li class="left w1-5">
                            <strong class="live"><?php _e('API Key', 'laterpay'); ?></strong>
                        </li>
                        <li class="left">
                            <span class="input-icon api-key-icon live" data-icon="j"></span>
                            <input type="text"
                                maxlength="32"
                                name="laterpay_live_api_key"
                                id="laterpay_live_api_key"
                                class="lp-input api-key-input"
                                value="<?php echo get_option('laterpay_live_api_key'); ?>"
                                placeholder="<?php _e('Paste Live API Key here', 'laterpay'); ?>"/>
                        </li>
                    </ul>
                    <ul id="request-live-credentials"
                        class="clearfix"
                        <?php if ( get_option('laterpay_live_api_key') && get_option('laterpay_sandbox_api_key') ) echo ' style="display:none;"'; ?>>
                        <li class="left w1-5">
                            &nbsp;
                        </li>
                        <li class="left">
                            <a href="#" class="button button-primary">
                                <?php _e('Request Live API Credentials', 'laterpay'); ?>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
        <dfn class="credentials-hint">
            <?php echo sprintf(__('Go to your <a href="%s">LaterPay Merchantbackend</a> to get your LaterPay API credentials.', 'laterpay'), LATERPAY_MERCHANTBACKEND_URL); ?>
        </dfn>
        <hr>

        <div class="lp-form-row">
            <h2><?php _e('Plugin Mode', 'laterpay'); ?></h2>
            <?php _e('This site is in', 'laterpay'); ?><div class="switch">
                <form id="plugin_mode" method="post">
                    <input type="hidden" name="form"    value="plugin_mode_is_live">
                    <input type="hidden" name="action"  value="account">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <label class="switch-label">
                        <input type="checkbox"
                                name="plugin_mode_is_live_checkbox"
                                id="plugin-mode-toggle"
                                class="switch-input"
                                data-error="<?php _e('Switching into Live mode requires a valid Live Merchant ID and Live API Key.', 'laterpay'); ?>"
                                <?php if ( get_option('laterpay_plugin_mode_is_live') == 1 ): ?>checked<?php endif; ?>>
                        <input type="hidden"
                                name="plugin_mode_is_live"
                                id="plugin_mode_hidden_input"
                                value="<?php if ( get_option('laterpay_plugin_mode_is_live') == 1 ) { echo 1; } else { echo 0; } ?>">
                        <span class="switch-text" data-on="LIVE" data-off="TEST"></span>
                        <span class="switch-handle"></span>
                    </label>
                </form>
            </div><?php _e('mode.', 'laterpay'); ?>

            <dfn id="plugin_mode_live_text"<?php if ( get_option('laterpay_plugin_mode_is_live') != 1 ) echo " style='display:none;'"; ?>>
                <?php _e('Your visitors <strong>can now purchase with LaterPay</strong>.', 'laterpay'); ?><br>
                <?php _e('All payments are booked and credited to your account.', 'laterpay'); ?>
            </dfn>
            <dfn id="plugin_mode_test_text"<?php if ( get_option('laterpay_plugin_mode_is_live') == 1 ) echo " style='display:none;'"; ?>>
                <?php _e('Payments are only simulated and <strong>not actually booked</strong>.', 'laterpay'); ?><br>
                <?php _e('LaterPay is <strong>not visible for regular visitors</strong>.', 'laterpay'); ?>
            </dfn>
        </div>

    </div>

</div>
