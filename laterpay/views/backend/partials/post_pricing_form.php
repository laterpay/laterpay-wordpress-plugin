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
                value="<?php echo LaterPay_Helper_View::format_number($laterpay_price, 2); ?>"
                placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                <?php if ( $laterpay_post_price_type !== 'individual price' ) { echo 'disabled="disabled"'; } ?>>
        <span class="lp_currency lp_p-rel"><?php echo $laterpay_currency; ?></span>
    </p>
    <input type="hidden" name="post_price_type" value="<?php echo $laterpay_post_price_type ?>">
</div>

<div id="lp_price-type"<?php if ( $laterpay_post_price_type == 'individual price, dynamic' || $laterpay_post_price_type == 'category default price' ) { echo ' class="expanded"'; } ?>>
     <ul class="lp-toggle lp_fl-clearfix">
        <li class="<?php if ( substr( $laterpay_post_price_type, 0, 16 ) == 'individual price' || ($laterpay_post_price_type == '' && ! ($laterpay_global_default_price > 0)) ) { echo 'selected'; } ?>">
            <a href="#" id="lp_use-individual-price" class="lp_use-individual-price"><?php _e( 'Individual Price', 'laterpay' ); ?></a>
        </li>
        <li class=" <?php if ( $laterpay_post_price_type == 'category default price' ) { echo 'selected'; } ?>
                    <?php if ( ! count( $laterpay_category_prices ) ) { echo 'disabled'; } ?>">
            <a href="#" id="lp_use-category-default-price" class="lp_use-category-default-price"><?php _e( 'Category Default Price', 'laterpay' ); ?></a>
        </li>
        <li class=" <?php if ( $laterpay_post_price_type == 'global default price' || ($laterpay_post_price_type == '' && $laterpay_global_default_price > 0) ) { echo 'selected'; } ?>
                    <?php if ( ! ($laterpay_global_default_price > 0) ) { echo 'disabled'; } ?>">
            <a href="#" id="lp_use-global-default-price" class="lp_use-global-default-price" data-price="<?php echo LaterPay_Helper_View::format_number( $laterpay_global_default_price, 2 ); ?>"><?php _e( 'Global Default<span></span> Price', 'laterpay' ); ?></a>
        </li>
    </ul>
    <div id="lp_price-type-details">
        <div id="lp_dynamic-pricing" class="lp_use-individual-price lp_details-section"<?php if ( $laterpay_post_price_type !== 'individual price, dynamic' ) { echo ' style="display:none;"'; } ?>>
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="lp_dynamic-pricing-widget-container"></div>
            <!-- <div id="container2">
                <p><?php _e( 'Dynamic pricing presets', 'laterpay' ); ?></p>
                <a href="#" class="blockbuster"><?php _e( 'Blockbuster', 'laterpay' ); ?></a>
                <a href="#" class="breaking-news"><?php _e( 'Breaking News', 'laterpay' ); ?></a>
                <a href="#" class="teaser"><?php _e( 'Teaser', 'laterpay' ); ?></a>
                <a href="#" class="long-tail"><?php _e( 'Long-tail', 'laterpay' ); ?></a>
                <a href="#" class="flat"><?php _e( 'Flat (default)', 'laterpay' ); ?></a>
            </div> -->
        </div>
        <div class="lp_use-category-default-price lp_details-section"<?php if ( $laterpay_post_price_type !== 'category default price' ) { echo ' style="display:none;"'; } ?>>
             <input type="hidden" name="laterpay_post_default_category" value="<?php echo $laterpay_post_default_category?>">
             <ul>
                <?php foreach ( $laterpay_category_prices as $c ): ?>
                    <li data-category="<?php echo $c->category_id; ?>"<?php if ( $c->category_id == $laterpay_post_default_category ): ?> class="lp_selected-category"<?php endif; ?>>
                        <a href="#" data-price="<?php echo LaterPay_Helper_View::format_number($c->category_price, 2); ?>">
                            <span><?php echo LaterPay_Helper_View::format_number( $c->category_price, 2 ); ?> <?php echo $laterpay_currency; ?></span><?php echo $c->category_name; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>

<?php if ( $laterpay_post_price_type == 'individual price, dynamic' ): ?>
    <a href="#" id="lp_use-dynamic-pricing" class="lp_dynamic-pricing-applied">
        <?php _e( 'Remove dynamic pricing', 'laterpay' ); ?>
    </a>
<?php else: ?>
    <a  href="#"
        id="lp_use-dynamic-pricing"
        <?php if ( substr( $laterpay_post_price_type, 0, 16 ) !== 'individual price' ) { echo 'style="display:none;"'; } ?>>
        <?php _e( 'Add dynamic pricing', 'laterpay' ); ?>
    </a>
<?php endif; ?>
