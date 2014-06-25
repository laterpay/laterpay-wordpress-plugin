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

<div id="laterpay_post_standard"<?php if ( $price_post_type ): ?> style="display:none;"<?php endif; ?>>
    <p>
        <?php _e('This post costs', 'laterpay'); ?>
        <input type="text"
                id="post-price"
                class="lp-input number"
                name="pricing-post"
                value="<?php echo ViewHelper::formatNumber($price, 2); ?>"
                placeholder="<?php _e('0.00', 'laterpay'); ?>">
        <?php echo get_option('laterpay_currency'); ?>
    </p>
    <p>
        <?php if ( !is_null($price_category) ): ?>
            <a href="#" id="set_price_category">
                <?php _e('Apply category default price', 'laterpay'); ?> (<?php echo $price_category; ?> <?php echo get_option('laterpay_currency'); ?>)
            </a>
        <?php endif; ?>
        <?php if ( !is_null(get_option('laterpay_global_price')) ): ?>
            <a href="#" id="set_price_global"<?php if ( !is_null($price_category) ): ?> style="display:none;"<?php endif; ?>>
                <?php _e('Apply global default price', 'laterpay'); ?> (<?php echo get_option('laterpay_global_price'); ?> <?php echo get_option('laterpay_currency'); ?>)
            </a>
        <?php endif; ?>
    </p>
    <p>
        <?php _e('Advanced pricing options', 'laterpay'); ?>
        <a href="#" id="show-advanced"><?php _e('Show', 'laterpay'); ?></a>
    </p>
</div>

<div id="laterpay_post_advanced"<?php if ( !$price_post_type ): ?> style="display:none;"<?php endif; ?>>
    <span><?php _e('Advanced pricing options', 'laterpay'); ?></span>
    <a href="#" id="show-standard"><?php _e('Hide', 'laterpay'); ?></a>
    <input type="hidden" name="laterpay_start_price">
    <input type="hidden" name="laterpay_end_price">
    <input type="hidden" name="laterpay_change_start_price_after_days">
    <input type="hidden" name="laterpay_transitional_period_end_after_days">
    <input type="hidden" name="laterpay_reach_end_price_after_days">
    <div id="container"></div>
    <div id="container2">
        <p><?php _e('Dynamic pricing presets', 'laterpay'); ?></p>
        <a href="#" class="blockbuster"><?php _e('Blockbuster', 'laterpay'); ?></a>
        <a href="#" class="breaking-news"><?php _e('Breaking News', 'laterpay'); ?></a>
        <a href="#" class="teaser"><?php _e('Teaser', 'laterpay'); ?></a>
        <a href="#" class="long-tail"><?php _e('Long-tail', 'laterpay'); ?></a>
        <a href="#" class="flat"><?php _e('Flat (default)', 'laterpay'); ?></a>
    </div>
</div>
