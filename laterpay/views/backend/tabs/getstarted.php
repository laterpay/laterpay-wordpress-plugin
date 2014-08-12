<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp-page wp-core-ui get-started">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <?php echo $top_nav; ?>
    </div>

    <div class="steps-progress">
        <span class="progress-line">
            <span class="st-1 done"></span>
            <span class="st-2 done"></span>
            <span class="st-3 todo"></span>
        </span>
    </div>

    <div class="lp-wrap">
        <form id="get_started_form" method="post">
            <input type="hidden" name="form"    value="get_started_form">
            <input type="hidden" name="action"  value="laterpay_getstarted">
            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
            <ul class="step-row clearfix">
                <li>
                    <div class="progress-step first">
                        <span class="input-icon merchant-id-icon" data-icon="i"></span>
                        <input type="text"
                                maxlength="22"
                                name="get_started[laterpay_sandbox_merchant_id]"
                                class="lp-input merchant-id-input"
                                value="<?php echo $config->get( 'api.sandbox_merchant_id' ); ?>"
                                required>
                        <label alt="<?php _e( 'Paste Sandbox Merchant ID here', 'laterpay' ); ?>" placeholder="<?php _e( 'Sandbox Merchant ID', 'laterpay' ); ?>"></label>
                        <span class="input-icon api-key-icon" data-icon="j"></span>
                        <input type="text"
                                maxlength="32"
                                name="get_started[laterpay_sandbox_api_key]"
                                value="<?php echo $config->get( 'api.sandbox_api_key' ); ?>"
                                class="lp-input api-key-input"
                                required>
                        <label alt="<?php _e( 'Paste Sandbox API Key here', 'laterpay' ); ?>" placeholder="<?php _e( 'Sandbox API Key', 'laterpay' ); ?>"></label>
                    </div>
                    <p>
                        <?php _e( 'You can try the plugin immediately<br> with the provided Sandbox API credentials.', 'laterpay' ); ?>
                    </p>
                    <p>
                        <?php _e( 'To actually sell content, you first have to register with LaterPay as a merchant and request your Live API credentials at <a href="https://merchant.laterpay.net" target="blank">merchant.laterpay.net</a>.', 'laterpay' ); ?>
                    </p>
                </li>
                <li>
                    <div class="progress-step middle">
                        <p class="centered">
                            <?php _e( 'The default price for posts is', 'laterpay' ); ?>
                            <span class="nowrap">
                                <input type="text"
                                        name="get_started[laterpay_global_price]"
                                        id="global-default-price"
                                        class="lp-input number"
                                        value="<?php echo $global_default_price; ?>"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                <select name="get_started[laterpay_currency]" class="lp-input">
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
                <li>
                    <div class="progress-step last">
                        <a href="#" class="button button-primary activate-lp" data-error="<?php _e( 'Please enter your LaterPay API key to activate LaterPay on this site.', 'laterpay' ); ?>">
                            <?php _e( 'Activate LaterPay Test Mode', 'laterpay' ); ?>
                        </a>
                    </div>
                    <p>
                        <?php _e( 'In Test Mode, LaterPay is not visible for regular visitors, but only for admins. Payments are only simulated and not actually booked.', 'laterpay' ); ?>
                    </p>
                    <p>
                        <?php _e( 'Activate the plugin and go to the “Add Post” page,<br>where you can check out your new options.', 'laterpay' ); ?>
                    </p>
                </li>
            </ul>
        </form>
    </div>

</div>
