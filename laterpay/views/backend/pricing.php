<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flash-message" class="lp_flash-message" style="display:none;">
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

                    <form id="lp_js_global-default-price-form" method="post" action="">
                        <input type="hidden" name="form"    value="global_price_form">
                        <input type="hidden" name="action"  value="laterpay_pricing">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                        <a href="#" id="lp_js_cancel-editing-global-default-price" class="lp_edit-link lp_cancel-link lp_fl-right" data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                        <a href="#" id="lp_js_save-global-default-price" class="lp_edit-link lp_save-link lp_fl-right" data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                        <p>
                            <span id="lp_js_global-default-price-revenue-model-label" class="lp_js_revenue-model-label-display lp_revenue-model-label lp_m-r025"><?php echo $global_default_price_revenue_model; ?></span>
                            <span id="lp_js_global-default-price-revenue-model" class="lp_js_revenue-model lp_revenue-model lp_p-rel lp_fl-left" style="display:none;">
                                <label class="lp_js_revenue-model-label lp_revenue-model-label lp_js_global-default-price-revenue-model-label lp_m-r05 lp_m-b025
                                        <?php if ( $global_default_price_revenue_model == 'ppu' || ! $global_default_price_revenue_model ) { echo 'lp_is-selected'; } ?>
                                        <?php if ( $global_default_price > 5 ) { echo 'lp_is-disabled'; } ?>">
                                    <input type="radio" name="laterpay_global_price_revenue_model" class="lp_js_revenue-model-input" value="ppu"<?php if ( $global_default_price_revenue_model == 'ppu' || ( ! $global_default_price_revenue_model && $global_default_price < 5 ) ) { echo ' checked'; } ?>>PPU
                                </label><label class="lp_js_revenue-model-label lp_revenue-model-label lp_js_global-default-price-revenue-model-label lp_m-r05 lp_m-b025
                                        <?php if ( $global_default_price_revenue_model == 'sis' ) { echo 'lp_is-selected'; } ?>
                                        <?php if ( $global_default_price < 1.49) { echo 'lp_is-disabled'; } ?>">
                                    <input type="radio" name="laterpay_global_price_revenue_model" class="lp_js_revenue-model-input" value="sis"<?php if ( $global_default_price_revenue_model == 'sis' ) { echo ' checked'; } ?>>SIS
                                </label>
                            </span>
                            <?php _e( '<strong>Every post</strong> costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="laterpay_global_price"
                                        id="lp_js_global-default-price"
                                        class="lp_js_price-input lp_input lp_number-input"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>"
                                        autocomplete="off">
                                <span id="lp_js_global-default-price-text"><?php echo $global_default_price; ?></span>
                                <span class="lp_js_currency lp_currency"><?php echo $standard_currency; ?></span>
                            </strong>
                            <a href="#" id="lp_js_edit-global-default-price" class="lp_edit-link lp_change-link" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                        </p>
                    </form>

                </div>
            </li>

            <li class="lp_fl-left lp_w-1-3">
                <div class="lp_m-0 lp_m-r1">
                    <h2><?php _e( 'Category Default Prices', 'laterpay' ); ?></h2>
                    <hr class="lp_b-none lp_m-0 lp_m-t05 lp_m-b025">
                    <dfn class="lp_spacer lp_d-block"><?php _e( 'Category default prices overwrite global default prices.', 'laterpay' ); ?></dfn>

                    <div id="lp_js_category-default-prices-list">
                        <p class="lp_m-1-0-0"><strong><?php _e( 'Every post in category', 'laterpay' ); ?> &hellip;</strong></p>
                        <?php foreach ( $categories_with_defined_price as $category ): ?>
                            <form method="post" class="lp_js_category-default-price-form lp_category-price-form">

                                <p class="lp_m-t025">
                                    <input type="hidden" name="form" value="price_category_form">
                                    <input type="hidden" name="action" value="laterpay_pricing">
                                    <input type="hidden" name="category_id" class="lp_js_category-id" value="<?php echo $category->category_id; ?>">
                                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                                    <?php $category_price           = LaterPay_Helper_View::format_number( (float) $category->category_price, 2 ); ?>
                                    <?php $category_revenue_model   = $category->revenue_model; ?>

                                    <div class="lp_js_revenue-model lp_revenue-model lp_p-rel lp_fl-left" style="display:none;">
                                        <label class="lp_js_revenue-model-label lp_revenue-model-label lp_m-r05 lp_m-b025
                                                    <?php if ( $category_revenue_model == 'ppu' || ( ! $category_revenue_model && $category_price <= 5 ) ) { echo 'lp_is-selected'; } ?>
                                                    <?php if ( $category_price > 5) { echo 'lp_is-disabled'; } ?>">
                                            <input type="radio" name="laterpay_category_price_revenue_model_<?php echo $category->category_id; ?>" class="lp_js_revenue-model-input" value="ppu"<?php if ( $category_revenue_model == 'ppu' || ( ! $category_revenue_model && $category_price <= 5 )) { echo ' checked'; } ?>>PPU
                                        </label>
                                        <label class="lp_js_revenue-model-label lp_revenue-model-label lp_m-r05 lp_m-b025
                                                    <?php if ( $category_revenue_model == 'sis' || ( ! $category_revenue_model && $category_price > 5 ) ) { echo 'lp_is-selected'; } ?>
                                                    <?php if ( $category_price < 1.49) { echo 'lp_is-disabled'; } ?>">
                                            <input type="radio" name="laterpay_category_price_revenue_model_<?php echo $category->category_id; ?>" class="lp_js_revenue-model-input" value="sis"<?php if ( $category_revenue_model == 'sis' || ( ! $category_revenue_model && $category_price > 5 ) ) { echo ' checked'; } ?>>SIS
                                        </label>
                                    </div>

                                    <span class="lp_js_revenue-model-label-display lp_revenue-model-label"><?php echo $category_revenue_model; ?></span>
                                    <strong>
                                        <input type="hidden" name="category" value="<?php echo $category->category_name; ?>" class="lp_js_select-category">
                                        <span class="lp_js_category-title lp_category-title lp_d-inl-block"><?php echo $category->category_name; ?></span>
                                    </strong>
                                    <?php _e( 'costs', 'laterpay' ); ?>
                                    <strong>
                                        <input  type="text"
                                                name="price"
                                                class="lp_js_price-input lp_js_category-default-price-input lp_input lp_number-input"
                                                value="<?php echo LaterPay_Helper_View::format_number($category->category_price, 2); ?>"
                                                style="display:none;"
                                                placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                        <span class="lp_js_category-default-price-display lp_category-price"><?php echo $category_price; ?></span>
                                        <span class="lp_js_currency lp_currency"><?php echo $standard_currency; ?></span>
                                    </strong>

                                    <a href="#" class="lp_js_save-category-default-price lp_edit-link lp_save-link" data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_js_cancel-editing-category-default-price lp_edit-link lp_cancel-link" data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_js_edit-category-default-price lp_edit-link lp_change-link" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_js_delete-category-default-price lp_edit-link lp_delete-link" data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
                                </p>
                            </form>
                        <?php endforeach; ?>
                    </div>

                    <a href="#" id="lp_js_add-category-default-price" class="lp_d-block lp_m-t1" data-icon="c"><?php _e( 'Set default price for another category', 'laterpay' ); ?></a>

                    <form method="post" id="lp_js_category-default-price-template" class="lp_js_category-default-price-form lp_category-price-form lp_is_unsaved" style="display:none;">
                        <input type="hidden" name="form" value="price_category_form">
                        <input type="hidden" name="action" value="laterpay_pricing">
                        <input type="hidden" name="category_id" class="lp_js_category-id" value="">

                        <p class="lp_m-t025">
                            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                            <div class="lp_js_revenue-model lp_revenue-model lp_p-rel lp_fl-left">
                                <label class="lp_js_revenue-model-label lp_revenue-model-label lp_m-r05 lp_m-b025
                                        <?php if ( $global_default_price_revenue_model == 'ppu' || ( ! $global_default_price_revenue_model && $global_default_price < 5 ) ) { echo 'lp_is-selected'; } ?>">
                                    <input type="radio" name="laterpay_category_price_revenue_model" class="lp_js_revenue-model-input" value="ppu"<?php if ( $global_default_price_revenue_model == 'ppu' || ( ! $global_default_price_revenue_model && $global_default_price < 5 ) ) { echo ' checked'; } ?>>PPU
                                </label>
                                <label class="lp_js_revenue-model-label lp_revenue-model-label lp_m-r05 lp_m-b025
                                        <?php if ( $global_default_price_revenue_model == 'sis' ) { echo 'lp_is-selected'; } ?>
                                        <?php if ( $global_default_price < 1.49) { echo 'lp_is-disabled'; } ?>">
                                    <input type="radio" name="laterpay_category_price_revenue_model" class="lp_js_revenue-model-input" value="sis"<?php if ( $global_default_price_revenue_model == 'sis' ) { echo ' checked'; } ?>>SIS
                                </label>
                            </div>

                            <span class="lp_js_revenue-model-label-display lp_revenue-model-label"></span>
                            <strong>
                                <input type="hidden" name="category" value="" class="lp_js_select-category">
                                <span class="lp_js_category-title lp_category-title lp_d-inl-block"></span>
                            </strong>
                            <?php _e( 'costs', 'laterpay' ); ?>
                            <strong>
                                <input  type="text"
                                        name="price"
                                        class="lp_js_price-input lp_js_category-default-price-input lp_input lp_number-input"
                                        value="<?php echo $global_default_price; ?>"
                                        style="display:none;"
                                        placeholder="<?php _e( '0.00', 'laterpay' ); ?>">
                                <span class="lp_js_category-default-price-display lp_category-price"><?php echo $global_default_price; ?></span>
                                <span class="lp_js_currency lp_currency"><?php echo $standard_currency; ?></span>
                            </strong>

                            <a href="#" class="lp_js_save-category-default-price lp_edit-link lp_save-link" data-icon="f" style="display:none;"><?php _e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="lp_js_cancel-editing-category-default-price lp_edit-link lp_cancel-link" data-icon="e" style="display:none;"><?php _e( 'Cancel', 'laterpay' ); ?></a>
                            <a href="#" class="lp_js_edit-category-default-price lp_edit-link lp_change-link" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
                            <a href="#" class="lp_js_delete-category-default-price lp_edit-link lp_delete-link" data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
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

        <div class="lp_row lp_m-t3">
            <p>
                <span class="lp_revenue-model-label lp_m-r05">PPU</span><strong><dfn>Pay-per-Use</dfn></strong><br>
                <dfn>
                    <?php _e( sprintf( 'The user pays later once his LaterPay invoice reaches 5 %s.', $standard_currency ), 'laterpay' ); ?><br>
                    <?php _e( sprintf( 'You can choose PPU for prices from 0.05 - 5.00 %s.', $standard_currency ), 'laterpay' ); ?>
                </dfn>
            </p>
            <p>
                <span class="lp_revenue-model-label lp_m-r05">SIS</span><strong><dfn>Single Sale</dfn></strong><br>
                <dfn>
                    <?php _e( 'The user has to log in to LaterPay and pay immediately.', 'laterpay' ); ?><br>
                    <?php _e( sprintf( 'You can choose SIS for prices from 1.49 - 149.99 %s.', $standard_currency ), 'laterpay' ); ?>
                </dfn>
            </p>
        </div>

        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Currency', 'laterpay' ); ?></h2>
            <form id="lp_js_default-currency-form" method="post">
                <input type="hidden" name="form"    value="currency_form">
                <input type="hidden" name="action"  value="laterpay_pricing">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                <div>
                    <p><?php _e( 'All prices are given in', 'laterpay' ); ?>
                        <select name="laterpay_currency" id="lp_js_change-default-currency" class="lp_input">
                            <?php foreach ( $currencies as $currency ): ?>
                                <option<?php if ( $currency->short_name == $standard_currency ): ?> selected<?php endif; ?> value="<?php echo $currency->short_name; ?>">
                                    <?php echo $currency->full_name . ' (' . $currency->short_name . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
