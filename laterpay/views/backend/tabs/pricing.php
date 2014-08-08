<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp-page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <?php if ( ! $plugin_is_in_live_mode ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $admin_menu['account']['url'] ), admin_url( 'admin.php' ) ); ?>" id="plugin-mode-indicator" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $top_nav; ?>
    </div>

    <div class="lp-wrap">
        <ul class="step-row clearfix">
            <li>
                <div class="pr-type1">
                    <h2><?php _e( 'Global Default Price', 'laterpay' ); ?></h2>
                    <hr>
                    <dfn class="spacer">&nbsp;</dfn>
                    <form id="global-price-form" method="post" action="">
                        <input type="hidden" name="form"    value="global_price_form">
                        <input type="hidden" name="action"  value="laterpay_pricing">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                        <p>
                            <?php _e( '<strong>Every post</strong> costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="laterpay_global_price"
                                        id="global-default-price"
                                        class="lp-input number"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                                        autocomplete="off">
                                <span id="laterpay-global-price-text"><?php echo $global_default_price; ?></span>
                                <span class="laterpay_currency"><?php echo $standard_currency; ?></span>
                            </strong>
                            <a href="#" class="edit-link laterpay-change-link" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                            <a href="#" class="edit-link laterpay-save-link"   data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="edit-link laterpay-cancel-link" data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                        </p>
                    </form>

                </div>
            </li>

            <li>
                <div class="pr-type2">
                    <h2><?php _e( 'Category Default Prices', 'laterpay' ); ?></h2>
                    <hr>
                    <dfn class="spacer"><?php _e( 'Category default prices overwrite global default prices.', 'laterpay' ); ?></dfn>
                    <div id="category-prices">
                        <p><strong><?php _e( 'Every post in category', 'laterpay' ); ?> &hellip;</strong></p>
                        <?php foreach ( $categories_with_defined_price as $category ): ?>
                            <form method="post" class="category-price-form">
                                <p>
                                    <input type="hidden" name="form"        value="price_category_form">
                                    <input type="hidden" name="action"      value="laterpay_pricing">
                                    <input type="hidden" name="category_id" value="<?php echo $category->category_id; ?>">
                                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                    <strong>
                                        <input type="hidden" name="category" value="<?php echo $category->category_name; ?>" class="category-select">
                                        <span class="category-title"><?php echo $category->category_name; ?></span>
                                    </strong>
                                    <?php _e( 'costs', 'laterpay' ); ?>
                                    <strong>
                                        <?php $category_price = LaterPay_Helper_View::format_number( (float) $category->category_price, 2 ); ?>
                                        <input  type="text"
                                                name="price"
                                                class="lp-input number"
                                                value="<?php echo LaterPay_Helper_View::format_number($category->category_price, 2); ?>"
                                                style="display:none;"
                                                placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                        <span class="category-price"><?php echo $category_price; ?></span>
                                        <span class="laterpay_currency"><?php echo $standard_currency; ?></span>
                                    </strong>

                                    <a href="#" class="edit-link laterpay-save-link"    data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                                    <a href="#" class="edit-link laterpay-cancel-link"  data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                                    <a href="#" class="edit-link laterpay-change-link"  data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                                    <a href="#" class="edit-link laterpay-delete-link"  data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
                                </p>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <p>
                        <a href="#" id="add_category_button" data-icon="c"><?php _e( 'Set default price for another category', 'laterpay' ); ?></a>
                    </p>
                    <form method="post" id="category-price-form-template" class="category-price-form unsaved" style="display:none;">
                        <input type="hidden" name="form"        value="price_category_form">
                        <input type="hidden" name="action"      value="laterpay_pricing">
                        <input type="hidden" name="category_id" value="">

                        <p>
                            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                            <strong>
                                <input type="hidden" name="category" value="" class="category-select">
                                <span class="category-title"></span>
                            </strong>
                            <?php _e( 'costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="price"
                                        class="lp-input number"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                <span class="category-price"><?php echo $global_default_price; ?></span>
                                <span class="laterpay_currency"><?php echo $standard_currency; ?></span>
                            </strong>

                            <a href="#" class="edit-link laterpay-save-link"    data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="edit-link laterpay-cancel-link"  data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                            <a href="#" class="edit-link laterpay-change-link"  data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                            <a href="#" class="edit-link laterpay-delete-link"  data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
                        </p>
                    </form>
                </div>
            </li>
            <li>
                <div class="pr-type3">
                    <h2><?php _e( 'Individual Prices', 'laterpay' ); ?></h2>
                    <hr>
                    <dfn class="spacer"><?php _e( 'Individual prices overwrite global and category default prices.', 'laterpay' ); ?></dfn>
                    <div>
                        <p><?php _e( 'You can set individual prices for posts,<br>when adding or editing a post.', 'laterpay' ); ?></p>
                    </div>
                </div>
            </li>
        </ul>
        <hr>

        <div class="pr-type4">
            <h2><?php _e( 'Currency', 'laterpay' ); ?></h2>
            <form id="currency_form" method="post">
                <input type="hidden" name="form"    value="currency_form">
                <input type="hidden" name="action"  value="laterpay_pricing">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                <div>
                    <p><?php _e( 'All prices are given in', 'laterpay' ); ?>
                        <span class="currency-dd">
                            <select name="laterpay_currency" id="laterpay_currency" class="lp-input">
                                <?php foreach ( $currencies as $currency ): ?>
                                    <option<?php if ( $currency->short_name == $standard_currency ): ?> selected<?php endif; ?> value="<?php echo $currency->short_name; ?>">
                                        <?php echo $currency->full_name . ' (' . $currency->short_name . ')'; ?>
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
