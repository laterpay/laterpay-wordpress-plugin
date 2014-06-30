<script>
    var wp_nonce_field      = '<?php if ( function_exists("wp_nonce_field") ) wp_nonce_field("laterpay_form"); ?>',
        i18n_cost           = '<?php _e("costs", 'laterpay'); ?>',
        i18n_change         = '<?php _e("Change", 'laterpay'); ?>',
        i18n_cancel         = '<?php _e("Cancel", 'laterpay'); ?>',
        i18n_delete         = '<?php _e("Delete", 'laterpay'); ?>',
        i18n_save           = '<?php _e("Save", 'laterpay'); ?>',
        i18n_selectCategory = '<?php _e("Select Category", 'laterpay'); ?>',
        default_price       = '<?php echo get_option("laterpay_global_price"); ?>',
        currency            = '<?php echo get_option("laterpay_currency"); ?>',
        locale              = '<?php echo get_locale(); ?>',
        category, category_id, price;
</script>


<div class="lp-page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <?php if ( get_option('laterpay_plugin_mode_is_live') == 0 ): ?>
            <a href="#account" id="plugin-mode-indicator" data-icon="h">
                <h2><?php _e('<strong>Test</strong> mode', 'laterpay'); ?></h2>
                <span><?php _e('Earn money in <i>live mode</i>', 'laterpay'); ?></span>
            </a>
        <?php endif; ?>
        <ul class="tabs">
            <?php if ( get_option('laterpay_activate') == '0' ): ?>
                <li id="get-started-tab"><a href="#get_started"><?php _e('Get Started', 'laterpay'); ?></a></li>
            <?php endif; ?>
            <li class="current"><a href="#pricing"><?php _e('Pricing', 'laterpay'); ?></a></li>
            <li><a href="#appearance"><?php _e('Appearance', 'laterpay'); ?></a></li>
            <li><a href="#account"><?php _e('Account', 'laterpay'); ?></a></li>
        </ul>
    </div>

    <div class="lp-wrap">
        <ul class="step-row clearfix">
            <li>
                <div class="pr-type1">
                    <h2><?php _e('Global Default Price', 'laterpay'); ?></h2>
                    <hr>
                    <dfn class="spacer">&nbsp;</dfn>
                    <form id="global-price-form" method="post" action="">
                        <input type="hidden" name="form"    value="global_price_form">
                        <input type="hidden" name="action"  value="pricing">
                        <?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('laterpay_form'); } ?>

                        <p>
                            <?php _e('<strong>Every post</strong> costs', 'laterpay'); ?>
                            <strong>
                                <?php $global_price = ViewHelper::formatNumber((double)get_option('laterpay_global_price'), 2); ?>
                                <input  type="text"
                                        name="laterpay_global_price"
                                        id="global-default-price"
                                        class="lp-input number"
                                        value="<?php echo $global_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e('0.00', 'laterpay'); ?>"
                                        autocomplete="off">
                                <span id="laterpay-global-price-text"><?php echo $global_price; ?></span>
                                <span class="laterpay_currency"><?php echo get_option('laterpay_currency'); ?></span>
                            </strong>
                            <a href="#" class="edit-link laterpay-change-link"  data-icon="d"><?php _e('Change', 'laterpay'); ?></a>
                            <a href="#" class="edit-link laterpay-save-link" style="display:none;" data-icon="f"><?php _e('Save', 'laterpay'); ?></a>
                            <a href="#" class="edit-link laterpay-cancel-link" style="display:none;" data-icon="e"><?php _e('Cancel', 'laterpay'); ?></a>
                        </p>
                    </form>

                </div>
            </li>

            <li>
                <div class="pr-type2">
                    <h2><?php _e('Category Default Prices', 'laterpay'); ?></h2>
                    <hr>
                    <dfn class="spacer"><?php _e('Category default prices overwrite global default prices.', 'laterpay'); ?></dfn>
                    <div id="category-prices">
                        <p><strong><?php echo _e('Every post in category', 'laterpay'); ?> &hellip;</strong></p>
                        <?php foreach ( $Categories as $item ): ?>
                            <form method="post" class="category-price-form">
                                <p>
                                    <input type="hidden" name="form"        value="price_category_form">
                                    <input type="hidden" name="action"      value="pricing">
                                    <input type="hidden" name="category_id" value="<?php echo $item->category_id; ?>">
                                    <?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('laterpay_form'); } ?>

                                    <strong>
                                        <input type="hidden" name="category" value="<?php echo $item->category_name; ?>" class="category-select">
                                        <span class="category-title"><?php echo $item->category_name; ?></span>
                                    </strong>
                                    <?php echo _e('costs', 'laterpay'); ?>
                                    <strong>
                                        <?php $category_price = ViewHelper::formatNumber((double)$item->category_price, 2); ?>
                                        <input  type="text"
                                                name="price"
                                                class="lp-input number"
                                                value="<?php echo ViewHelper::formatNumber($item->category_price, 2); ?>"
                                                style="display:none;"
                                                placeholder="<?php _e('0.00', 'laterpay'); ?>">
                                        <span class="category-price"><?php echo $category_price; ?></span>
                                        <span class="laterpay_currency"><?php echo get_option('laterpay_currency'); ?></span>
                                    </strong>

                                    <a href="#" class="edit-link laterpay-save-link"    data-icon="f" style="display:none;"><?php _e('Save', 'laterpay'); ?></a>
                                    <a href="#" class="edit-link laterpay-cancel-link"  data-icon="e" style="display:none;"><?php _e('Cancel', 'laterpay'); ?></a>
                                    <a href="#" class="edit-link laterpay-change-link"  data-icon="d"><?php _e('Change', 'laterpay'); ?></a>
                                    <a href="#" class="edit-link laterpay-delete-link"  data-icon="g"><?php _e('Delete', 'laterpay'); ?></a>
                                </p>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <p>
                        <a href="#" id="add_category_button" data-icon="c"><?php _e('Set default price for another category', 'laterpay'); ?></a>
                    </p>
                    <form method="post" id="category-price-form-template" class="category-price-form unsaved" style="display:none;">
                        <p>
                            <input type="hidden" name="form"        value="price_category_form">
                            <input type="hidden" name="action"      value="pricing">
                            <input type="hidden" name="category_id" value="">
                            <?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('laterpay_form'); } ?>

                            <strong>
                                <input type="hidden" name="category" value="" class="category-select">
                                <span class="category-title"></span>
                            </strong>
                            <?php echo _e('costs', 'laterpay'); ?>
                            <strong>
                                <input  type="text"
                                        name="price"
                                        class="lp-input number"
                                        value="<?php echo $global_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e('0.00', 'laterpay'); ?>">
                                <span class="category-price"><?php echo $global_price; ?></span>
                                <span class="laterpay_currency"><?php echo get_option('laterpay_currency'); ?></span>
                            </strong>

                            <a href="#" class="edit-link laterpay-save-link"    data-icon="f" style="display:none;"><?php _e('Save', 'laterpay'); ?></a>
                            <a href="#" class="edit-link laterpay-cancel-link"  data-icon="e" style="display:none;"><?php _e('Cancel', 'laterpay'); ?></a>
                            <a href="#" class="edit-link laterpay-change-link"  data-icon="d"><?php _e('Change', 'laterpay'); ?></a>
                            <a href="#" class="edit-link laterpay-delete-link"  data-icon="g"><?php _e('Delete', 'laterpay'); ?></a>
                        </p>
                    </form>
                </div>
            </li>
            <li>
                <div class="pr-type3">
                    <h2><?php _e('Individual Prices', 'laterpay'); ?></h2>
                    <hr>
                    <dfn class="spacer"><?php _e('Individual prices overwrite global and category default prices.', 'laterpay'); ?></dfn>
                    <div>
                        <p><?php _e('You can set individual prices for posts,<br>when adding or editing a post.', 'laterpay'); ?></p>
                    </div>
                </div>
            </li>
        </ul>
        <hr>

        <div class="pr-type4">
            <h2><?php _e('Currency', 'laterpay'); ?></h2>
            <form id="currency_form" method="post">

                <input type="hidden" name="form"    value="currency_form">
                <input type="hidden" name="action"  value="pricing">
                <?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('laterpay_form'); } ?>

                <div>
                    <p><?php _e('All prices are given in', 'laterpay'); ?>
                        <span class="currency-dd">
                            <select name="laterpay_currency" id="laterpay_currency" class="lp-input">
                                <?php foreach ( $Currencies as $item ): ?>
                                    <option<?php if ( $item->short_name == get_option('laterpay_currency') ): ?> selected<?php endif; ?> value="<?php echo $item->short_name; ?>">
                                        <?php echo $item->full_name . ' (' . $item->short_name . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </span>
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>
