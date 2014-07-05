<script>
    lpVars = window.lpVars || {};
    lpVars.categoryDefaultPrice = <?php echo $category_default_price; ?>,
    lpVars.dynamicPricingData   = <?php echo $dynamic_pricing_data; ?>,
    lpVars.isStandardPost       = <?php echo $price_post_type ? 1 : 0; ?>;
</script>

<input type="hidden" name="price_post_type" value="<?php echo $price_post_type ?>">

<div class="post-price-wrapper">
    <input type="text"
            id="post-price"
            class="lp-input number"
            name="pricing-post"
            value="<?php echo ViewHelper::formatNumber($price, 2); ?>"
            placeholder="<?php _e('0.00', 'laterpay'); ?>">
            <?php #if ( $price_post_type ) echo 'disabled'; ?>
    <span class="lp-currency"><?php echo $currency; ?></span>
</div>

<div id="laterpay-price-type">
    <ul class="lp-toggle clearfix">
        <li class="selected">
            <a href="#" class="use-individual-price"><?php _e('Individual Price', 'laterpay'); ?></a>
        </li>
        <li>
            <a href="#" class="use-category-default-price"><?php _e('Category Default Price', 'laterpay'); ?></a>
        </li>
        <?php if ( !is_null($global_price) ): ?>
            <li>
                <a href="#" class="use-global-default-price" data-price="<?php echo $global_price; ?>"><?php _e('Global Default Price', 'laterpay'); ?></a>
            </li>
        <?php endif; ?>
    </ul>
    <div id="laterpay-price-type-details">
        <div id="laterpay-dynamic-pricing" class="use-individual-price details-section" style="display:none;">
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="container"></div>
<!--                 <div id="container2">
                <p><?php _e('Dynamic pricing presets', 'laterpay'); ?></p>
                <a href="#" class="blockbuster"><?php _e('Blockbuster', 'laterpay'); ?></a>
                <a href="#" class="breaking-news"><?php _e('Breaking News', 'laterpay'); ?></a>
                <a href="#" class="teaser"><?php _e('Teaser', 'laterpay'); ?></a>
                <a href="#" class="long-tail"><?php _e('Long-tail', 'laterpay'); ?></a>
                <a href="#" class="flat"><?php _e('Flat (default)', 'laterpay'); ?></a>
            </div> -->
        </div>
        <div class="use-category-default-price details-section" style="display:none;">
            <ul>
                <li class="selected-category" data-category="8"><a href="#" data-price="0.29"><span>0.29 EUR</span>Category 1</a></li>
                <li data-category="5"><a href="#" data-price="0.49"><span>0.49 EUR</span>Category 2</a></li>
                <li data-category="3"><a href="#" data-price="0.09"><span>0.09 EUR</span>Category 3</a></li>
                <li data-category="1"><a href="#" data-price="0.29"><span>0.29 EUR</span>Category 4</a></li>
                <li data-category="7"><a href="#" data-price="0.99"><span>0.99 EUR</span>Category 5</a></li>
            </ul>
        </div>
    </div>
</div>

<a href="#" id="use-dynamic-pricing"><?php _e('Add dynamic pricing', 'laterpay'); ?></a>
