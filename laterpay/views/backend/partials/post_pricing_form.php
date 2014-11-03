<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<script>
    var lpVars = window.lpVars || {};
    lpVars.dynamicPricingData = <?php echo $laterpay_dynamic_pricing_data; ?>;
    lpVars.limits = <?php echo $laterpay_dynamic_pricing_limits; ?>;
</script>

<div class="lp_postPrice lp_u_clearfix">
    <p class="lp_u_right">
        <input type="text"
                name="post-price"
                id="lp_js_postPrice_priceInput"
                class="lp_input lp_numberInput lp_u_fs-3"
                value="<?php echo LaterPay_Helper_View::format_number( $laterpay_price, 2 ); ?>"
                placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                <?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'disabled="disabled"'; } ?>>
        <span class="lp_currency lp_u_relative"><?php echo $laterpay_currency; ?></span>
    </p>

    <div id="lp_js_postPrice_revenueModel" class="lp_postRevenueModel lp_u_relative">
            <label class="lp_revenueModelLabel lp_u_m-t125 lp_u_m-b05 lp_tooltip
                    <?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE, LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) ) ) : ?>
                        <?php if ( $laterpay_post_revenue_model == 'ppu' ) { echo ' lp_is-selected'; } ?>
                        <?php if ( $maximum_price_in_lifecycle > LaterPay_Helper_Pricing::ppusis_max ) { echo ' lp_is-disabled'; } ?>
                    <?php else : ?>
                        <?php if ( $laterpay_post_revenue_model == 'sis' || $maximum_price_in_lifecycle > LaterPay_Helper_Pricing::ppusis_max ) { echo ' lp_is-disabled'; } ?>
                    <?php endif; ?>"
                    data-tooltip="<?php _e( 'Pay-per-Use: users pay purchased content later', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="ppu"
                    <?php if ( $laterpay_post_revenue_model == 'ppu' ) { echo 'checked'; } ?>>PPU
            </label>
            <label class="lp_revenueModelLabel lp_tooltip
                    <?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE, LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) ) ) : ?>
                        <?php if ( $laterpay_post_revenue_model == 'sis' ) { echo ' lp_is-selected'; } ?>
                        <?php if ( $maximum_price_in_lifecycle < LaterPay_Helper_Pricing::sis_min ) { echo ' lp_is-disabled'; } ?>
                    <?php else : ?>
                        <?php if ( $laterpay_post_revenue_model == 'ppu' ) { echo ' lp_is-disabled'; } ?>
                    <?php endif; ?>"
                    data-tooltip="<?php _e( 'Single Sale: users pay purchased content immediately', 'laterpay' ); ?>">
                <input type="radio"
                    name="post_revenue_model"
                    value="sis"
                    <?php if ( $laterpay_post_revenue_model == 'sis' ) { echo 'checked'; } ?>>SIS
            </label>
    </div>

    <input type="hidden" name="post_price_type" id="lp_js_postPrice_priceTypeInput" value="<?php echo $laterpay_post_price_type ?>">
</div>

<div id="lp_js_priceType" class="lp_priceType<?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE, LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) ) ) { echo ' lp_is-expanded'; } ?>">
     <ul id="lp_js_priceType_buttonGroup" class="lp_buttonGroup lp_u_clearfix">
        <li class="<?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE, LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ))  ) { echo 'lp_is-selected'; } ?>">
            <a href="#"
                id="lp_js_useIndividualPrice"
                class="lp_js_priceType_button lp_use-individual-price"><?php _e( 'Individual Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo 'lp_is-selected'; } ?><?php if ( ! count( $laterpay_category_prices ) ) { echo 'lp_is-disabled'; } ?>">
            <a href="#"
                id="lp_js_useCategoryDefaultPrice"
                class="lp_js_priceType_button lp_useCategoryDefaultPrice"><?php _e( 'Category Default Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE ) { echo 'lp_is-selected'; } ?>">
            <a href="#"
                id="lp_js_useGlobalDefaultPrice"
                class="lp_js_priceType_button lp_use-global-default-price"
                data-price="<?php echo LaterPay_Helper_View::format_number( $laterpay_global_default_price, 2 ); ?>"
                data-revenue-model="<?php echo $laterpay_global_default_price_revenue_model; ?>"><?php _e( 'Global Default<span></span> Price', 'laterpay' ); ?></a>
        </li>
    </ul>
    <div id="lp_js_priceTypeDetails" class="lp_priceType_details">
        <div id="lp_js_priceTypeDetails_individualPrice" class="lp_js_useIndividualPrice lp_js_priceTypeDetails_section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) { echo ' style="display:none;"'; } ?>>
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="lp_js_dynamicPricing_widgetContainer" class="lp_dynamicPricing"></div>
        </div>
        <div id="lp_js_priceTypeDetails_categoryDefaultPrice" class="lp_js_useCategoryDefaultPrice lp_useCategoryDefaultPrice lp_js_priceTypeDetails_section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo ' style="display:none;"'; } ?>>
             <input type="hidden" name="laterpay_post_default_category" id="lp_js_postDefaultCategoryInput" value="<?php echo $laterpay_post_default_category?>">
             <ul>
                <?php if ( is_array( $laterpay_category_prices ) ): ?>
                    <?php foreach ( $laterpay_category_prices as $c ): ?>
                        <li data-category="<?php echo $c->category_id; ?>"<?php if ( $c->category_id == $laterpay_post_default_category ): ?> class="lp_is-selectedCategory"<?php endif; ?>>
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
    <?php if ( $laterpay_post_status != LaterPay_Helper_Pricing::STATUS_POST_PUBLISHED ):?>
        <?php _e( 'The dynamic pricing will <strong>start</strong>, once you have <strong>published</strong> this post.', 'laterpay' ); ?>
    <?php else: ?>
        <a href="#" id="lp_js_resetDynamicPricingStartDate" class="lp_dynamic-pricing-reset lp_is-with-dynamic-pricing lp_d-block" post_id="<?php echo $laterpay_post_id; ?>">
            <?php _e( 'Restart dynamic pricing', 'laterpay' ); ?>
        </a>
    <?php endif; ?>
    <a href="#" id="lp_js_toggleDynamicPricing" class="lp_dynamicPricingToggle lp_is-with-dynamic-pricing lp_u_block">
        <?php _e( 'Remove dynamic pricing', 'laterpay' ); ?>
    </a>
<?php else: ?>
    <a  href="#"
        id="lp_js_toggleDynamicPricing"
        class="lp_dynamicPricingToggle lp_u_block"
        <?php if ( substr( $laterpay_post_price_type, 0, 16 ) !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'style="display:none;"'; } ?>>
        <?php _e( 'Add dynamic pricing', 'laterpay' ); ?>
    </a>
<?php endif; ?>
