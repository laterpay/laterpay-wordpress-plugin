<script>
    var locale                          = "<?php echo get_locale(); ?>",
        i18n_outsideAllowedPriceRange   = "<?php _e('The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay'); ?>",
        i18n_invalidMerchantId          = "<?php _e('The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID!', 'laterpay'); ?>",
        i18n_invalidApiKey              = "<?php _e('The API key you entered is not a valid LaterPay Sandbox API key!', 'laterpay'); ?>";
</script>

<div class="lp-page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <ul class="tabs getstarted">
            <li class="current"><a href="#"><?php _e('Get Started', 'laterpay'); ?></a></li>
            <li><a href="#"><?php _e('Pricing', 'laterpay'); ?></a></li>
            <li><a href="#"><?php _e('Appearance', 'laterpay'); ?></a></li>
            <li><a href="#"><?php _e('Account', 'laterpay'); ?></a></li>
        </ul>
    </div>

    <div class="steps-progress">
        <span class="progress-line">
            <span class="st-1 todo"></span>
            <span class="st-2 todo"></span>
            <span class="st-3 todo"></span>
        </span>
    </div>

    <div class="lp-wrap">
        <form id="get_started_form" method="post">
            <input type="hidden" name="form"    value="get_started_form">
            <input type="hidden" name="action"  value="getstarted">
            <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
            <ul class="step-row clearfix">
                <li>
                    <div class="progress-step first">
                        <span class="input-icon merchant-id-icon" data-icon="i"></span>
                        <input type="text"
                                maxlength="22"
                                name="get_started[laterpay_sandbox_merchant_id]"
                                class="lp-input merchant-id-input"
                                value=""
                                placeholder="<?php _e('Paste Sandbox Merchant ID here', 'laterpay'); ?>"><br>
                        <span class="input-icon api-key-icon" data-icon="j"></span>
                        <input type="text"
                                maxlength="32"
                                name="get_started[laterpay_sandbox_api_key]"
                                value="<?php echo get_option('laterpay_sandbox_api_key'); ?>"
                                class="lp-input api-key-input"
                                placeholder="<?php _e('Paste Sandbox API Key here', 'laterpay'); ?>">
                    </div>
                    <p>
                        <?php _e('Get your <strong>Sandbox Merchant ID</strong> and <strong>Sandbox API Key</strong><br>from your', 'laterpay'); ?> <a href="<?php echo LATERPAY_MERCHANTBACKEND_URL ?>" target="_blank"><?php _e('LaterPay Merchantbackend', 'laterpay'); ?></a>.
                    </p>
                </li>
                <li>
                    <div class="progress-step">
                        <p class="centered">
                            <?php _e('The default price for posts is', 'laterpay'); ?>
                            <input type="text"
                                    name="get_started[laterpay_global_price]"
                                    id="global-default-price"
                                    class="lp-input number"
                                    value="<?php echo LATERPAY_GLOBAL_PRICE_DEFAULT; ?>"
                                    placeholder="<?php _e('0.00' ,'laterpay'); ?>">
                            <select name="get_started[laterpay_currency]" class="lp-input">
                                <?php foreach ($Currency->getCurrencies() as $item): ?>
                                    <option value="<?php echo $item->short_name; ?>"<?php if ( $item->short_name == LATERPAY_CURRENCY_DEFAULT ): ?> selected<?php endif; ?>>
                                        <?php echo $item->short_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <p>
                        <?php _e('Set a <strong>default price</strong> for all posts (0 makes everything free).<br>You can set more advanced prices later.', 'laterpay'); ?>
                    </p>
                </li>
                <li>
                    <div class="progress-step last">
                        <a href="#" class="button button-primary activate-lp" data-error="<?php _e('Please enter your LaterPay API key to activate LaterPay on this site.', 'laterpay'); ?>">
                            <?php _e('Activate LaterPay on this site!', 'laterpay'); ?>
                        </a>
                    </div>
                    <p>
                        <?php _e('Save settings and go to the “Add Post” page,<br>where you can check out your new options.', 'laterpay'); ?>
                    </p>
                </li>
            </ul>
        </form>
    </div>

</div>
