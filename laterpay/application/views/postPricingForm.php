<script>
    var price_category      = "<?php echo $price_category; ?>",
        price_global        = "<?php echo get_option('laterpay_global_price'); ?>",
        data_start          = <?php echo $data; ?>,
        is_standard_post    = "<?php if ( $price_post_type ): ?>1<?php else: ?>0<?php endif; ?>",
        price_currency      = "<?php echo get_option('laterpay_currency'); ?>",
        locale              = "<?php echo get_locale(); ?>",
        i18nDefaultPrice    = "<?php _e('default price', 'laterpay'); ?>",
        i18nTeaserError     = "<?php _e('Paid posts require some teaser content. Please fill in the Teaser Content field.', 'laterpay'); ?>";
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
    <span class="lp-currency"><?php echo get_option('laterpay_currency'); ?></span>
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
        <div class="use-individual-price details-section" style="display:none;">
            <div id="laterpay-dynamic-pricing" style="display:none;">
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
        </div>
        <div class="use-category-default-price details-section" style="display:none;">
            <ul>
                <li data-category="aaa"><a href="#" data-price="0.29"><span>0.29 EUR</span>Category 1</a></li>
                <li data-category="bbb"><a href="#" data-price="0.49"><span>0.49 EUR</span>Category 2</a></li>
                <li data-category="ccc"><a href="#" data-price="0.09"><span>0.09 EUR</span>Category 3</a></li>
                <li data-category="ddd"><a href="#" data-price="0.29"><span>0.29 EUR</span>Category 4</a></li>
                <li data-category="eee"><a href="#" data-price="0.99"><span>0.99 EUR</span>Category 5</a></li>
            </ul>
        </div>
    </div>
</div>

<a href="#" id="use-dynamic-pricing"><?php _e('Add dynamic pricing', 'laterpay'); ?></a>
