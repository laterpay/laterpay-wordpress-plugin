<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<script>
    var lpVars = window.lpVars || {};
    lpVars.dynamicPricingData = <?php echo $laterpay_dynamic_pricing_data; ?>;
</script>

<div class="lp_post-price lp_fl-clearfix">
    <p class="lp_fl-right">
        <input type="text"
                name="post-price"
                id="lp_js_post-price-input"
                class="lp_input lp_number-input lp_fs-3"
                value="<?php echo LaterPay_Helper_View::format_number( $laterpay_price, 2 ); ?>"
                placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                <?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'disabled="disabled"'; } ?>>
        <span class="lp_currency lp_p-rel"><?php echo $laterpay_currency; ?></span>
    </p>
    <div id="lp_js_post-revenue-model" class="lp_post-revenue-model lp_p-rel">
        <?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) : ?>
            <label class="lp_revenue-model-label lp_m-t125 lp_m-b05 lp_tooltip
                            <?php if ( $laterpay_post_revenue_model == 'ppu') { echo ' lp_is-selected'; } ?>
                            <?php if ( $laterpay_price > 5) { echo ' lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Pay-per-Use: users pay purchased content later', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="ppu"
                    <?php if ( $laterpay_post_revenue_model == 'ppu') { echo 'checked'; } ?>>PPU
            </label>
            <label class="lp_revenue-model-label lp_tooltip
                            <?php if ( $laterpay_post_revenue_model == 'sis') { echo ' lp_is-selected'; } ?>
                            <?php if ( $laterpay_price < 1.49 ) { echo ' lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Single Sale: users pay purchased content immediately', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="sis"
                    <?php if ( $laterpay_post_revenue_model == 'sis') { echo 'checked'; } ?>>SIS
            </label>

        <?php elseif ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE && $laterpay_price > 0.05 ) : ?>
            <label class="lp_revenue-model-label lp_m-t125 lp_m-b05 lp_tooltip
                            <?php if ( $laterpay_category_default_price_revenue_model == 'ppu' || ( ! $laterpay_category_default_price_revenue_model && $laterpay_price <= 5 ) ) { echo ' lp_is-selected'; } else { echo ' lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Pay-per-Use: users pay purchased content later', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="ppu"
                    <?php if ( $laterpay_category_default_price_revenue_model == 'ppu' || ( ! $laterpay_category_default_price_revenue_model && $laterpay_price <= 5 ) ) { echo 'checked'; } ?>>PPU
            </label>
            <label class="lp_revenue-model-label lp_tooltip
                            <?php if ( $laterpay_category_default_price_revenue_model == 'sis' || ( ! $laterpay_category_default_price_revenue_model && $laterpay_price > 5 ) ) { echo 'lp_is-selected'; } else { echo 'lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Single Sale: users pay purchased content immediately', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="sis"
                    <?php if ( $laterpay_category_default_price_revenue_model == 'sis' || ( ! $laterpay_category_default_price_revenue_model && $laterpay_price > 5 ) ) { echo 'checked'; } ?>>SIS
            </label>

        <?php elseif ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE && $laterpay_price > 0.05 ) : ?>
            <label class="lp_revenue-model-label lp_m-t125 lp_m-b05 lp_tooltip
                            <?php if ( $laterpay_global_default_price_revenue_model != 'sis' || ( ! $laterpay_global_default_price_revenue_model && $laterpay_price <= 5 ) ) { echo 'lp_is-selected'; } else { echo 'lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Pay-per-Use: users pay purchased content later', 'laterpay' ); ?>">
                <input type="radio" name="post_revenue_model" value="ppu"<?php if ( $laterpay_global_default_price_revenue_model == 'ppu' || ( ! $laterpay_global_default_price_revenue_model && $laterpay_price < 5 ) ) { echo ' checked'; } ?>>PPU
            </label>
            <label  class="lp_revenue-model-label lp_tooltip
                            <?php if ( $laterpay_global_default_price_revenue_model == 'sis' || ( ! $laterpay_global_default_price_revenue_model && $laterpay_price > 5 ) ) { echo 'lp_is-selected'; } else { echo 'lp_is-disabled'; } ?>"
                    data-tooltip="<?php _e( 'Single Sale: users pay purchased content immediately', 'laterpay' ); ?>">
                <input type="radio" name="post_revenue_model" value="sis"<?php if ( $laterpay_global_default_price_revenue_model == 'sis' ) { echo ' checked'; } ?>>SIS
            </label>
        <?php endif; ?>
    </div>
    <input type="hidden" name="post_price_type" id="lp_js_post-price-type-input" value="<?php echo $laterpay_post_price_type ?>">
</div>

<div id="lp_js_price-type" class="lp_price-type<?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE, LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) ) ) { echo ' lp_is-expanded'; } ?>">
     <ul id="lp_js_price-type-button-group" class="lp_button-group lp_fl-clearfix">
        <li class="<?php if ( substr( $laterpay_post_price_type, 0, 16 ) == 'individual price' || ($laterpay_post_price_type == '' && ! ($laterpay_global_default_price > 0)) ) { echo 'lp_is-selected'; } ?>">
            <a href="#"
                id="lp_js_use-individual-price"
                class="lp_js_price-type-button lp_use-individual-price"><?php _e( 'Individual Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo 'lp_is-selected'; } ?><?php if ( ! count( $laterpay_category_prices ) ) { echo 'lp_is-disabled'; } ?>">
            <a href="#"
                id="lp_js_use-category-default-price"
                class="lp_js_price-type-button lp_use-category-default-price"
                data-revenue-model="ppu"><?php _e( 'Category Default Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE || ($laterpay_post_price_type == '' && $laterpay_global_default_price > 0) ) { echo 'lp_is-selected'; } ?><?php if ( ! ($laterpay_global_default_price > 0) ) { echo 'lp_is-disabled'; } ?>">
            <a href="#"
                id="lp_js_use-global-default-price"
                class="lp_js_price-type-button lp_use-global-default-price"
                data-price="<?php echo LaterPay_Helper_View::format_number( $laterpay_global_default_price, 2 ); ?>"
                data-revenue-model="<?php echo $laterpay_global_default_price_revenue_model; ?>"><?php _e( 'Global Default<span></span> Price', 'laterpay' ); ?></a>
        </li>
    </ul>
    <div id="lp_js_price-type-details" class="lp_price-type-details">
        <div id="lp_js_individual-price-details" class="lp_js_use-individual-price lp_js_details-section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) { echo ' style="display:none;"'; } ?>>
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="lp_js_dynamic-pricing-widget-container" class="lp_dynamic-pricing-widget"></div>
        </div>
        <div id="lp_js_category-price-details" class="lp_js_use-category-default-price lp_use-category-default-price lp_js_details-section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo ' style="display:none;"'; } ?>>
             <input type="hidden" name="laterpay_post_default_category" id="lp_js_post-default-category-input" value="<?php echo $laterpay_post_default_category?>">
             <ul>
                <?php if ( is_array( $laterpay_category_prices ) ): ?>
                    <?php foreach ( $laterpay_category_prices as $c ): ?>
                        <li data-category="<?php echo $c->category_id; ?>"<?php if ( $c->category_id == $laterpay_post_default_category ): ?> class="lp_selected-category"<?php endif; ?>>
                            <a href="#"
                                data-price="<?php echo LaterPay_Helper_View::format_number( $c->category_price, 2 ); ?>"
                                data-revenue-model="<?php echo $c->revenue_model; ?>">
                                <span><?php echo LaterPay_Helper_View::format_number( $c->category_price, 2 ); ?> <?php echo $laterpay_currency; ?></span><?php echo $c->category_name; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</div>

<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ): ?>
    <a href="#" id="lp_js_toggle-dynamic-pricing" class="lp_dynamic-pricing-toggle lp_is-with-dynamic-pricing lp_d-block">
        <?php _e( 'Remove dynamic pricing', 'laterpay' ); ?>
    </a>
<?php else: ?>
    <a  href="#"
        id="lp_js_toggle-dynamic-pricing"
        class="lp_dynamic-pricing-toggle lp_d-block"
        <?php if ( substr( $laterpay_post_price_type, 0, 16 ) !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'style="display:none;"'; } ?>>
        <?php _e( 'Add dynamic pricing', 'laterpay' ); ?>
    </a>
<?php endif; ?>
