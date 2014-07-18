<script>
    var lpVars = window.lpVars || {};
    lpVars.categoryDefaultPrice = <?php echo $category_default_price; ?>;
    lpVars.dynamicPricingData   = <?php echo $dynamic_pricing_data; ?>;
    lpVars.isStandardPost       = <?php echo $price_post_type ? 1 : 0; ?>;
</script>

<input type="hidden" name="price_post_type" value="<?php echo $price_post_type ?>">

<div id="laterpay_post_standard" class="clearfix">
    <p>
        <input type="text"
                id="post-price"
                class="lp-input number"
                name="post-price"
                value="<?php echo LaterPayViewHelper::formatNumber($price, 2); ?>"
                placeholder="<?php _e('0.00', 'laterpay'); ?>">
        <?php echo $currency; ?>
    </p>
</div>

<div id="laterpay-price-type">
    <ul class="lp-toggle clearfix">
        <li class="selected">
            <a href="#" class="use-individual-price"><?php _e('Individual Price', 'laterpay'); ?></a>
        </li>
        <li<?php if ( !count($category_prices) ): ?> class="disabled"<?php endif; ?>>
            <a href="#" class="use-category-default-price" id="use-category-default-price"><?php _e('Category Default Price', 'laterpay'); ?></a>
        </li>
        <li<?php if ( !($global_default_price > 0) ): ?> class="disabled"<?php endif; ?>>
            <a href="#" class="use-global-default-price" data-price="<?php echo $global_default_price; ?>"><?php _e('Global Default<span></span> Price', 'laterpay'); ?></a>
        </li>
    </ul>
    <div id="laterpay-price-type-details">
        <div id="laterpay-dynamic-pricing" class="use-individual-price details-section" style="display:none;">
            <input type="hidden" name="laterpay_start_price">
            <input type="hidden" name="laterpay_end_price">
            <input type="hidden" name="laterpay_change_start_price_after_days">
            <input type="hidden" name="laterpay_transitional_period_end_after_days">
            <input type="hidden" name="laterpay_reach_end_price_after_days">
            <div id="laterpay-widget-container"></div>
                 <div id="container2">
                <p><?php _e('Dynamic pricing presets', 'laterpay'); ?></p>
                <a href="#" class="blockbuster"><?php _e('Blockbuster', 'laterpay'); ?></a>
                <a href="#" class="breaking-news"><?php _e('Breaking News', 'laterpay'); ?></a>
                <a href="#" class="teaser"><?php _e('Teaser', 'laterpay'); ?></a>
                <a href="#" class="long-tail"><?php _e('Long-tail', 'laterpay'); ?></a>
                <a href="#" class="flat"><?php _e('Flat (default)', 'laterpay'); ?></a>
            </div>
        </div>
        <div class="use-category-default-price details-section" style="display:none;">
            <input type="hidden" name="laterpay_post_default_category" value="<?php echo $post_default_category?>">
            <ul>
                <?php foreach($category_prices as $c): ?>
                    <li data-category="<?php echo $c['category_id']; ?>" <?php if ($c['category_id'] == $post_default_category):?>class="selected-category"<?php endif;?>>
                        <a href="#" data-price="<?php echo $c['category_price']; ?>">
                            <span><?php echo $c['category_price']; ?> <?php echo $currency; ?></span><?php echo $c['category_name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<a href="#" id="use-dynamic-pricing" class=""><?php _e('Add dynamic pricing', 'laterpay'); ?></a>
