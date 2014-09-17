<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<script>
    var lpVars = window.lpVars || {};
    lpVars.dynamicPricingData = <?php echo $laterpay_dynamic_pricing_data; ?>;
</script>

<div id="lp_post-price" class="lp_fl-clearfix">
    <p class="lp_fl-right">
        <input type="text"
                class="lp_input lp_number-input lp_fs-3"
                name="post-price"
                value="<?php echo LaterPay_Helper_View::format_number( $laterpay_price, 2 ); ?>"
                placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                <?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'disabled="disabled"'; } ?>>
        <span class="lp_currency lp_p-rel"><?php echo $laterpay_currency; ?></span>
    </p>
    <div class="lp_post-revenue-model lp_p-rel"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo ' style="display:none;"'; } ?>>
        <label  class="lp_d-block lp_m-t125 lp_m-b05 lp_pd-025-05 lp_b-r3 lp_ta-center lp_fw-b lp_tooltip
                        <?php if ( $laterpay_post_revenue_model == 'ppu') { echo ' lp_selected'; } ?>
                        <?php if ( $laterpay_price > 5) { echo ' lp_disabled'; } ?>"
                data-tooltip="<?php _e( 'Pay-per-Use: users pay purchased content later', 'laterpay' ); ?>">
            <input type="radio" name="post_revenue_model" value="ppu"<?php if ( $laterpay_post_revenue_model == 'ppu') { echo ' checked'; } ?>>PPU
        </label>
        <label  class="lp_d-block lp_pd-025-05 lp_b-r3 lp_ta-center lp_fw-b lp_tooltip
                        <?php if ( $laterpay_post_revenue_model == 'ss') { echo ' lp_selected'; } ?>
                        <?php if ( $laterpay_price < 1.49) { echo ' lp_disabled'; } ?>"
                data-tooltip="<?php _e( 'Single Sale: users pay purchased content immediately', 'laterpay' ); ?>">
            <input type="radio" name="post_revenue_model" value="ss"<?php if ( $laterpay_post_revenue_model == 'ss') { echo ' checked'; } ?>>SS
        </label>
    </div>
    <input type="hidden" name="post_price_type" value="<?php echo $laterpay_post_price_type ?>">
</div>

<div id="lp_price-type"<?php if ( in_array( $laterpay_post_price_type, array( LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE, LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) ) ) { echo ' class="lp_expanded"'; } ?>>
     <ul class="lp-button-group lp_fl-clearfix">
        <li class="<?php if ( substr( $laterpay_post_price_type, 0, 16 ) == 'individual price' || ($laterpay_post_price_type == '' && ! ($laterpay_global_default_price > 0)) ) { echo 'lp_selected'; } ?>">
            <a href="#" id="lp_use-individual-price" class="lp_use-individual-price"><?php _e( 'Individual Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo 'lp_selected'; } ?><?php if ( ! count( $laterpay_category_prices ) ) { echo 'lp_disabled'; } ?>">
            <a href="#" id="lp_use-category-default-price" class="lp_use-category-default-price"><?php _e( 'Category Default Price', 'laterpay' ); ?></a>
        </li>
        <li class="<?php if ( $laterpay_post_price_type == LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE || ($laterpay_post_price_type == '' && $laterpay_global_default_price > 0) ) { echo 'lp_selected'; } ?><?php if ( ! ($laterpay_global_default_price > 0) ) { echo 'lp_disabled'; } ?>">
            <a href="#" id="lp_use-global-default-price" class="lp_use-global-default-price" data-price="<?php echo LaterPay_Helper_View::format_number( $laterpay_global_default_price, 2 ); ?>"><?php _e( 'Global Default<span></span> Price', 'laterpay' ); ?></a>
        </li>
    </ul>
    <div id="lp_price-type-details">
        <div id="lp_dynamic-pricing" class="lp_use-individual-price lp_details-section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE ) { echo ' style="display:none;"'; } ?>>
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="lp_dynamic-pricing-widget-container"></div>
        </div>
        <div class="lp_use-category-default-price lp_details-section"<?php if ( $laterpay_post_price_type !== LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE ) { echo ' style="display:none;"'; } ?>>
             <input type="hidden" name="laterpay_post_default_category" value="<?php echo $laterpay_post_default_category?>">
             <ul>
                <?php if ( is_array( $laterpay_category_prices ) ): ?>
                    <?php foreach ( $laterpay_category_prices as $c ): ?>
                        <li data-category="<?php echo $c->category_id; ?>"<?php if ( $c->category_id == $laterpay_post_default_category ): ?> class="lp_selected-category"<?php endif; ?>>
                            <a href="#" data-price="<?php echo LaterPay_Helper_View::format_number($c->category_price, 2); ?>">
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
    <a href="#" id="lp_use-dynamic-pricing" class="lp_dynamic-pricing-applied">
        <?php _e( 'Remove dynamic pricing', 'laterpay' ); ?>
    </a>
<?php else: ?>
    <a  href="#"
        id="lp_use-dynamic-pricing"
        <?php if ( substr( $laterpay_post_price_type, 0, 16 ) !== LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE ) { echo 'style="display:none;"'; } ?>>
        <?php _e( 'Add dynamic pricing', 'laterpay' ); ?>
    </a>
<?php endif; ?>
