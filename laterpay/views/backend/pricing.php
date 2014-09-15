<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_p-rel">
        <?php if ( ! $plugin_is_in_live_mode ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $admin_menu['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_plugin-mode-indicator lp_p-abs" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $top_nav; ?>
    </div>

    <div class="lp_pagewrap">
        <ul class="lp_row lp_fl-clearfix">
            <li class="lp_fl-left lp_w-1-3">
                <div class="lp_m-0 lp_m-r1">
                    <h2><?php _e( 'Global Default Price', 'laterpay' ); ?></h2>
                    <hr class="lp_b-none lp_m-0 lp_m-t05 lp_m-b025">
                    <dfn class="lp_spacer lp_d-block">&nbsp;</dfn>

                    <form id="lp_global-price-form" method="post" action="">
                        <input type="hidden" name="form"    value="global_price_form">
                        <input type="hidden" name="action"  value="laterpay_pricing">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                        <p>
                            <?php _e( '<strong>Every post</strong> costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="laterpay_global_price"
                                        id="lp_global-default-price"
                                        class="lp_input lp_number-input"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                                        autocomplete="off">
                                <span id="lp_global-price-text"><?php echo $global_default_price; ?></span>
                                <span class="lp_currency"><?php echo $standard_currency; ?></span>
                            </strong>
                            <a href="#" class="lp_edit-link lp_change-link" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                            <a href="#" class="lp_edit-link lp_save-link"   data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="lp_edit-link lp_cancel-link" data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                        </p>
                    </form>

                </div>
            </li>

            <li class="lp_fl-left lp_w-1-3">
                <div class="lp_m-0 lp_m-r1">
                    <h2><?php _e( 'Category Default Prices', 'laterpay' ); ?></h2>
                    <hr class="lp_b-none lp_m-0 lp_m-t05 lp_m-b025">
                    <dfn class="lp_spacer lp_d-block"><?php _e( 'Category default prices overwrite global default prices.', 'laterpay' ); ?></dfn>

                    <div id="lp_category-prices">
                        <p><strong><?php _e( 'Every post in category', 'laterpay' ); ?> &hellip;</strong></p>
                        <?php foreach ( $categories_with_defined_price as $category ): ?>
                            <form method="post" class="lp_category-price-form">
                                <p>
                                    <input type="hidden" name="form"        value="price_category_form">
                                    <input type="hidden" name="action"      value="laterpay_pricing">
                                    <input type="hidden" name="category_id" value="<?php echo $category->category_id; ?>">
                                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                    <strong>
                                        <input type="hidden" name="category" value="<?php echo $category->category_name; ?>" class="lp_category-select">
                                        <span class="lp_category-title lp_d-inl-block"><?php echo $category->category_name; ?></span>
                                    </strong>
                                    <?php _e( 'costs', 'laterpay' ); ?>
                                    <strong>
                                        <?php $category_price = LaterPay_Helper_View::format_number( (float) $category->category_price, 2 ); ?>
                                        <input  type="text"
                                                name="price"
                                                class="lp_input lp_number-input"
                                                value="<?php echo LaterPay_Helper_View::format_number($category->category_price, 2); ?>"
                                                style="display:none;"
                                                placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                        <span class="lp_category-price"><?php echo $category_price; ?></span>
                                        <span class="lp_currency"><?php echo $standard_currency; ?></span>
                                    </strong>

                                    <a href="#" class="lp_edit-link lp_save-link"    data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_edit-link lp_cancel-link"  data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_edit-link lp_change-link"  data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_edit-link lp_delete-link"  data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
                                </p>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" id="lp_add-category-link" class="lp_d-block lp_m-t1" data-icon="c"><?php _e( 'Set default price for another category', 'laterpay' ); ?></a>

                    <form method="post" id="category-price-form-template" class="lp_category-price-form lp_unsaved" style="display:none;">
                        <input type="hidden" name="form"        value="price_category_form">
                        <input type="hidden" name="action"      value="laterpay_pricing">
                        <input type="hidden" name="category_id" value="">

                        <p>
                            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                            <strong>
                                <input type="hidden" name="category" value="" class="lp_category-select">
                                <span class="lp_category-title lp_d-inl-block"></span>
                            </strong>
                            <?php _e( 'costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="price"
                                        class="lp_input lp_number-input"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                <span class="lp_category-price"><?php echo $global_default_price; ?></span>
                                <span class="lp_currency"><?php echo $standard_currency; ?></span>
                            </strong>

                            <a href="#" class="lp_edit-link lp_save-link"    data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="lp_edit-link lp_cancel-link"  data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                            <a href="#" class="lp_edit-link lp_change-link"  data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                            <a href="#" class="lp_edit-link lp_delete-link"  data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
                        </p>
                    </form>
                </div>
            </li>

            <li class="lp_fl-left lp_w-1-3">
                <div class="lp_m-0 lp_m-r1">
                    <h2><?php _e( 'Individual Prices', 'laterpay' ); ?></h2>
                    <hr class="lp_b-none lp_m-0 lp_m-t05 lp_m-b025">
                    <dfn class="lp_spacer lp_d-block"><?php _e( 'Individual prices overwrite global and category default prices.', 'laterpay' ); ?></dfn>

                    <div>
                        <p><?php _e( 'You can set individual prices for posts,<br>when adding or editing a post.', 'laterpay' ); ?></p>
                    </div>
                </div>
            </li>
        </ul>
        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Currency', 'laterpay' ); ?></h2>
            <form id="lp_currency-form" method="post">
                <input type="hidden" name="form"    value="currency_form">
                <input type="hidden" name="action"  value="laterpay_pricing">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                <div>
                    <p><?php _e( 'All prices are given in', 'laterpay' ); ?>
                        <span class="currency-dd">
                            <select name="laterpay_currency" id="lp_currency-select" class="lp_input">
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
