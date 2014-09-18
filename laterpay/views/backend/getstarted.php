<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flash-message" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div id="lp_js_tab-navigation" class="lp_navigation lp_p-rel">
        <?php echo $top_nav; ?>
    </div>

    <div id="lp_js_progress-indicator" class="lp_progress lp_ta-center">
        <span class="lp_progress-background lp_d-inl-block lp_p-rel">
            <span id="lp_js_step-1" class="lp_step-1 lp_step-done lp_d-block lp_p-abs"></span>
            <span id="lp_js_step-2" class="lp_step-2 lp_step-done lp_d-block lp_p-abs"></span>
            <span id="lp_js_step-3" class="lp_step-3 lp_step-todo lp_d-block lp_p-abs"></span>
        </span>
    </div>

    <div class="lp_pagewrap">
        <form id="lp_js_get-started-form" method="post">
            <input type="hidden" name="form"    value="get_started_form">
            <input type="hidden" name="action"  value="laterpay_getstarted">
            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>

            <ul class="lp_setup-steps lp_fl-clearfix">
                <li class="lp_fl-left lp_w-1-3">
                    <div class="lp_setup-step lp_step-1 lp_p-rel lp_pd-r2">
                        <span class="lp_input-icon lp_merchant-id-icon" data-icon="i"></span>
                        <input type="text"
                                maxlength="22"
                                name="get_started[laterpay_sandbox_merchant_id]"
                                id="lp_js_merchant-id-input"
                                class="lp_js_validate-api-credentials lp_input lp_merchant-id-input"
                                value="<?php echo $config->get( 'api.sandbox_merchant_id' ); ?>"
                                required>
                        <label alt="<?php _e( 'Paste Sandbox Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Sandbox Merchant ID', 'laterpay' ); ?>"></label>
                        <span class="lp_input-icon lp_api-key-icon" data-icon="j"></span>
                        <input type="text"
                                maxlength="32"
                                name="get_started[laterpay_sandbox_api_key]"
                                id="lp_js_api-key-input"
                                class="lp_js_validate-api-credentials lp_input lp_api-key-input"
                                value="<?php echo $config->get( 'api.sandbox_api_key' ); ?>"
                                required>
                        <label alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'Sandbox API Key', 'laterpay' ); ?>"></label>
                    </div>
                    <p>
                        <?php _e( 'You can try the plugin immediately<br> with the provided Sandbox API credentials.', 'laterpay' ); ?>
                    </p>
                    <p class="lp_pd-t0">
                        <?php _e( 'To actually sell content, you first have to register with LaterPay as a merchant and request your Live API credentials at <a href="https://merchant.laterpay.net" target="blank">merchant.laterpay.net</a>.', 'laterpay' ); ?>
                    </p>
                </li>

                <li class="lp_fl-left lp_w-1-3">
                    <div class="lp_setup-step lp_step-2 lp_p-rel lp_pd-r2">
                        <p class="lp_p-rel lp_m-0 lp_ta_center">
                            <?php _e( 'The default price for posts is', 'laterpay' ); ?>
                            <span class="lp_nowrap">
                                <input type="text"
                                        name="get_started[laterpay_global_price]"
                                        id="lp_js_global-default-price"
                                        class="lp_input lp_number-input"
                                        value="<?php echo $global_default_price; ?>"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                <select name="get_started[laterpay_currency]" class="lp_input lp_p-rel">
                                    <?php foreach ( $currencies as $currency ): ?>
                                        <option value="<?php echo $currency->short_name; ?>"<?php if ( $currency->short_name == $config->get( 'currency.default' ) ): ?> selected<?php endif; ?>>
                                            <?php echo $currency->short_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                        </p>
                    </div>
                    <p>
                        <?php _e( 'Set a <strong>default price</strong> for all posts (0 makes everything free).<br>You can set more advanced prices later.', 'laterpay' ); ?>
                    </p>
                </li>

                <li class="lp_fl-left lp_w-1-3">
                    <div class="lp_setup-step lp_step-3 lp_p-rel lp_pd-r2 lp_ta-center">
                        <a  href="#"
                            id="lp_js_activate-plugin"
                            class="button button-primary lp_pd-0-1 lp_p-rel lp_fw_b"
                            data-error="<?php _e( 'Please enter your LaterPay API key to activate LaterPay on this site.', 'laterpay' ); ?>">
                            <?php _e( 'Activate LaterPay Test Mode', 'laterpay' ); ?>
                        </a>
                    </div>
                    <p>
                        <?php _e( 'In Test Mode, LaterPay is not visible for regular visitors, but only for admins. Payments are only simulated and not actually booked.', 'laterpay' ); ?>
                    </p>
                    <p class="lp_pd-t0">
                        <?php _e( 'Activate the plugin and go to the “Add Post” page,<br>where you can check out your new options.', 'laterpay' ); ?>
                    </p>
                </li>
            </ul>
        </form>
    </div>

</div>
