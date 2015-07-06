<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ) : ?>
            <a href="<?php echo esc_url_raw( add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ) ); ?>"
                class="lp_plugin-mode-indicator"
                data-icon="h">
                <h2 class="lp_plugin-mode-indicator__title"><?php echo laterpay_sanitize_output( __( 'Test mode', 'laterpay' ) ); ?></h2>
                <span class="lp_plugin-mode-indicator__text"><?php echo laterpay_sanitize_output( __( 'Earn money in <i>live mode</i>', 'laterpay' ) ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo laterpay_sanitized( $laterpay['top_nav'] ); ?>
    </div>

    <div class="lp_pagewrap">
        <div class="lp_greybox lp_mt lp_mb lp_mr">
            <?php echo laterpay_sanitize_output( __( 'Posts can', 'laterpay' ) ); ?>
            <div class="lp_toggle">
                <form id="lp_js_changePurchaseModeForm" method="post" action="">
                    <input type="hidden" name="form"    value="change_purchase_mode_form">
                    <input type="hidden" name="action"  value="laterpay_pricing">
                    <label class="lp_toggle__label lp_toggle__label-pass">
                        <input type="checkbox"
                               name="only_time_pass_purchase_mode"
                               class="lp_js_onlyTimePassPurchaseModeInput lp_toggle__input"
                               value="1"
								<?php if ( $laterpay['only_time_pass_purchases_allowed'] ) { echo 'checked'; } ?>
                        >
                        <span class="lp_toggle__text"></span>
                        <span class="lp_toggle__handle"></span>
                    </label>
                </form>
            </div>
            <?php echo laterpay_sanitize_output( __( 'cannot be purchased individually.', 'laterpay' ) ); ?>
        </div>

        <div class="lp_js_hideInTimePassOnlyMode lp_layout lp_mb++">
            <div class="lp_price-section lp_layout__item lp_1/2 lp_pdr">
                <h2><?php echo laterpay_sanitize_output( __( 'Global Default Price', 'laterpay' ) ); ?></h2>

                <form id="lp_js_globalDefaultPriceForm" method="post" action="" class="lp_price-settings">
                    <input type="hidden" name="form"    value="global_price_form">
                    <input type="hidden" name="action"  value="laterpay_pricing">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                    <div id="lp_js_globalDefaultPriceShowElements" class="lp_greybox">
                        <?php echo laterpay_sanitize_output( __( 'Every post costs', 'laterpay' ) ); ?>
                        <span id="lp_js_globalDefaultPriceDisplay" class="lp_price-settings__value-text">
                            <?php echo laterpay_sanitize_output( $laterpay['global_default_price'] ); ?>
                        </span>
                        <span class="lp_js_currency lp_currency">
                            <?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?>
                        </span>
                        <span id="lp_js_globalDefaultPriceRevenueModelDisplay" class="lp_badge">
                            <?php echo laterpay_sanitize_output( $laterpay['global_default_price_revenue_model'] ); ?>
                        </span>

                        <a href="#" id="lp_js_editGlobalDefaultPrice" class="lp_edit-link--bold lp_change-link lp_rounded--right" data-icon="d"></a>
                    </div>

                    <div id="lp_js_globalDefaultPriceEditElements" class="lp_greybox--outline lp_mb-" style="display:none;">
                        <table class="lp_table--form">
                            <thead>
                                <tr>
                                    <th colspan="2">
                                        <?php echo laterpay_sanitize_output( __( 'Edit Global Default Price', 'laterpay' ) ); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <?php echo laterpay_sanitize_output( __( 'Price', 'laterpay' ) ); ?>
                                    </th>
                                    <td>
                                        <input  type="text"
                                                id="lp_js_globalDefaultPriceInput"
                                                class="lp_js_priceInput lp_input lp_number-input"
                                                name="laterpay_global_price"
                                                value="<?php echo esc_attr( $laterpay['global_default_price'] ); ?>"
                                                placeholder="<?php echo esc_attr( LaterPay_Helper_View::format_number( 0 ) ); ?>">
                                        <span class="lp_js_currency lp_currency"><?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo laterpay_sanitize_output( __( 'Revenue Model', 'laterpay' ) ); ?>
                                    </th>
                                    <td>
                                        <div class="lp_js_revenueModel lp_button-group">
                                            <label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                    <?php if ( $laterpay['global_default_price_revenue_model'] == 'ppu' || ! $laterpay['global_default_price_revenue_model'] ) { echo 'lp_is-selected'; } ?>
                                                    <?php if ( $laterpay['global_default_price'] > 5 ) { echo 'lp_is-disabled'; } ?>">
                                                <input type="radio" name="laterpay_global_price_revenue_model" class="lp_js_revenueModelInput" value="ppu"<?php if ( $laterpay['global_default_price_revenue_model'] == 'ppu' || ( ! $laterpay['global_default_price_revenue_model'] && $laterpay['global_default_price'] < 5 ) ) { echo ' checked'; } ?>>PPU
                                            </label><!--
                                         --><label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                    <?php if ( $laterpay['global_default_price_revenue_model'] == 'sis' ) { echo 'lp_is-selected'; } ?>
                                                    <?php if ( $laterpay['global_default_price'] < 1.49 ) { echo 'lp_is-disabled'; } ?>">
                                                <input type="radio" name="laterpay_global_price_revenue_model" class="lp_js_revenueModelInput" value="sis"<?php if ( $laterpay['global_default_price_revenue_model'] == 'sis' ) { echo ' checked'; } ?>>SIS
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <a href="#" id="lp_js_saveGlobalDefaultPrice" class="button button-primary"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                                        <a href="#" id="lp_js_cancelEditingGlobalDefaultPrice" class="lp_inline-block lp_pd--05-1"><?php echo laterpay_sanitize_output( __( 'Cancel', 'laterpay' ) ); ?></a>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div><!--
         --><div class="lp_price-section lp_layout__item lp_1/2 lp_pdr">
                <h2>
                    <?php echo laterpay_sanitize_output( __( 'Category Default Prices', 'laterpay' ) ); ?>
                    <a href="#" id="lp_js_addCategoryDefaultPrice" class="button button-primary lp_heading-button" data-icon="c">
                        <?php echo laterpay_sanitize_output( __( 'Create', 'laterpay' ) ); ?>
                    </a>
                </h2>

                <div id="lp_js_categoryDefaultPriceList">
                    <?php foreach ( $laterpay['categories_with_defined_price'] as $category ) : ?>
                        <form method="post" class="lp_js_categoryDefaultPriceForm lp_category-price-form">
                            <input type="hidden" name="form"        value="price_category_form">
                            <input type="hidden" name="action"      value="laterpay_pricing">
                            <input type="hidden" name="category_id" class="lp_js_categoryDefaultPriceCategoryId" value="<?php echo esc_attr( $category->category_id ); ?>">
                            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                            <?php $category_price         = LaterPay_Helper_View::format_number( $category->category_price ); ?>
                            <?php $category_revenue_model = $category->revenue_model; ?>

                            <div class="lp_js_categoryDefaultPriceShowElements lp_greybox lp_mb-">
                                <?php echo laterpay_sanitize_output( __( 'Every post in', 'laterpay' ) ); ?>
                                <span class="lp_js_categoryDefaultPriceCategoryTitle lp_inline-block">
                                    <?php echo laterpay_sanitize_output( $category->category_name ); ?>
                                </span>
                                <?php echo laterpay_sanitize_output( __( 'costs', 'laterpay' ) ); ?>
                                <span class="lp_js_categoryDefaultPriceDisplay lp_category-price">
                                    <?php echo laterpay_sanitize_output( $category_price ); ?>
                                </span>
                                <span class="lp_js_currency lp_currency">
                                    <?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?>
                                </span>
                                <span class="lp_js_revenueModelLabelDisplay lp_badge">
                                    <?php echo laterpay_sanitize_output( $category_revenue_model ); ?>
                                </span>

                                <a href="#" class="lp_js_deleteCategoryDefaultPrice lp_edit-link--bold lp_delete-link lp_rounded--right" data-icon="g"></a>
                                <a href="#" class="lp_js_editCategoryDefaultPrice lp_edit-link--bold lp_change-link" data-icon="d"></a>
                            </div>

                            <div class="lp_js_categoryDefaultPriceEditElements lp_greybox--outline lp_mb-" style="display:none;">
                                <table class="lp_table--form">
                                    <thead>
                                        <tr>
                                            <th colspan="2">
                                                <?php echo laterpay_sanitize_output( __( 'Edit Category Default Price', 'laterpay' ) ); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>
                                                <?php echo laterpay_sanitize_output( __( 'Category', 'laterpay' ) ); ?>
                                            </th>
                                            <td>
                                                <input type="hidden" name="category" value="<?php echo esc_attr( $category->category_name ); ?>" class="lp_js_selectCategory">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <?php echo laterpay_sanitize_output( __( 'Price', 'laterpay' ) ); ?>
                                            </th>
                                            <td>
                                                <input  type="text"
                                                        name="price"
                                                        class="lp_js_priceInput lp_js_categoryDefaultPriceInput lp_input lp_number-input"
                                                        value="<?php echo esc_attr( LaterPay_Helper_View::format_number( $category->category_price ) ); ?>"
                                                        placeholder="<?php echo esc_attr( LaterPay_Helper_View::format_number( 0 ) ); ?>">
                                                <span class="lp_js_currency lp_currency"><?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                <?php echo laterpay_sanitize_output( __( 'Revenue Model', 'laterpay' ) ); ?>
                                            </th>
                                            <td>
                                                <div class="lp_js_revenueModel lp_button-group">
                                                    <label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                            <?php if ( $category_revenue_model == 'ppu' || ( ! $category_revenue_model && $category_price <= 5 ) ) { echo 'lp_is-selected'; } ?>
                                                            <?php if ( $category_price > 5 ) { echo 'lp_is-disabled'; } ?>">
                                                        <input type="radio" name="laterpay_category_price_revenue_model_<?php echo esc_attr( $category->category_id ); ?>" class="lp_js_revenueModelInput" value="ppu"<?php if ( $category_revenue_model == 'ppu' || ( ! $category_revenue_model && $category_price <= 5 ) ) { echo ' checked'; } ?>>PPU
                                                    </label>
                                                    <label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                            <?php if ( $category_revenue_model == 'sis' || ( ! $category_revenue_model && $category_price > 5 ) ) { echo 'lp_is-selected'; } ?>
                                                            <?php if ( $category_price < 1.49 ) { echo 'lp_is-disabled'; } ?>">
                                                        <input type="radio" name="laterpay_category_price_revenue_model_<?php echo esc_attr( $category->category_id ); ?>" class="lp_js_revenueModelInput" value="sis"<?php if ( $category_revenue_model == 'sis' || ( ! $category_revenue_model && $category_price > 5 ) ) { echo ' checked'; } ?>>SIS
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>
                                                <a href="#" class="lp_js_saveCategoryDefaultPrice button button-primary"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                                                <a href="#" class="lp_js_cancelEditingCategoryDefaultPrice lp_inline-block lp_pd--05-1"><?php echo laterpay_sanitize_output( __( 'Cancel', 'laterpay' ) ); ?></a>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    <?php endforeach; ?>

                    <div class="lp_js_emptyState lp_empty-state"<?php if ( ! empty( $laterpay['categories_with_defined_price'] ) ) { echo ' style="display:none;"'; } ?>>
                        <h2>
                            <?php echo laterpay_sanitize_output( __( 'Set prices by category', 'laterpay' ) ); ?>
                        </h2>
                        <p>
                            <?php echo laterpay_sanitize_output( __( 'Category default prices are convenient for selling different categories of content at different standard prices.<br>Individual prices can be set when editing a post.', 'laterpay' ) ); ?>
                        </p>
                        <p>
                            <?php echo laterpay_sanitize_output( __( 'Click the "Create" button to set a default price for a category.', 'laterpay' ) ); ?>
                        </p>
                    </div>
                </div>

                <form method="post" id="lp_js_categoryDefaultPriceTemplate" class="lp_js_categoryDefaultPriceForm lp_category-price-form lp_is-unsaved" style="display:none;">
                    <input type="hidden" name="form"        value="price_category_form">
                    <input type="hidden" name="action"      value="laterpay_pricing">
                    <input type="hidden" name="category_id" value="" class="lp_js_categoryDefaultPriceCategoryId">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                    <div class="lp_js_categoryDefaultPriceShowElements lp_greybox lp_mb-" style="display:none;">
                        <?php echo laterpay_sanitize_output( __( 'Every post in', 'laterpay' ) ); ?>
                        <span class="lp_js_categoryDefaultPriceCategoryTitle lp_inline-block">
                        </span>
                        <?php echo laterpay_sanitize_output( __( 'costs', 'laterpay' ) ); ?>
                        <span class="lp_js_categoryDefaultPriceDisplay lp_category-price">
                        </span>
                        <span class="lp_js_currency lp_currency">
                            <?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?>
                        </span>
                        <span class="lp_js_revenueModelLabelDisplay lp_badge">
                        </span>

                        <a href="#" class="lp_js_deleteCategoryDefaultPrice lp_edit-link--bold lp_delete-link lp_rounded--right" data-icon="g"></a>
                        <a href="#" class="lp_js_editCategoryDefaultPrice lp_edit-link--bold lp_change-link" data-icon="d"></a>
                    </div>

                    <div class="lp_js_categoryDefaultPriceEditElements lp_greybox--outline lp_mb-">
                        <table class="lp_table--form">
                            <thead>
                                <tr>
                                    <th colspan="2">
                                        <?php echo laterpay_sanitize_output( __( 'Add a Category Default Price', 'laterpay' ) ); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <?php echo laterpay_sanitize_output( __( 'Category', 'laterpay' ) ); ?>
                                    </th>
                                    <td>
                                        <input type="hidden" name="category" value="" class="lp_js_selectCategory">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo laterpay_sanitize_output( __( 'Price', 'laterpay' ) ); ?>
                                    </th>
                                    <td>
                                        <input  type="text"
                                                name="price"
                                                class="lp_js_priceInput lp_js_categoryDefaultPriceInput lp_input lp_number-input"
                                                value="<?php echo esc_attr( $laterpay['global_default_price'] ); ?>"
                                                placeholder="<?php echo esc_attr( LaterPay_Helper_View::format_number( 0 ) ); ?>">
                                        <span class="lp_js_currency lp_currency"><?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo laterpay_sanitize_output( __( 'Revenue Model', 'laterpay' ) ); ?>
                                    </th>
                                    <td>
                                        <div class="lp_js_revenueModel lp_button-group">
                                            <label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                    <?php if ( $laterpay['global_default_price_revenue_model'] == 'ppu' || ( ! $laterpay['global_default_price_revenue_model'] && $laterpay['global_default_price'] < 5 ) ) { echo 'lp_is-selected'; } ?>">
                                                <input type="radio" name="laterpay_category_price_revenue_model" class="lp_js_revenueModelInput" value="ppu"<?php if ( $laterpay['global_default_price_revenue_model'] == 'ppu' || ( ! $laterpay['global_default_price_revenue_model'] && $laterpay['global_default_price'] < 5 ) ) { echo ' checked'; } ?>>PPU
                                            </label><!--
                                         --><label class="lp_js_revenueModelLabel lp_button-group__button lp_1/3
                                                    <?php if ( $laterpay['global_default_price_revenue_model'] == 'sis' ) { echo 'lp_is-selected'; } ?>
                                                    <?php if ( $laterpay['global_default_price'] < 1.49 ) { echo 'lp_is-disabled'; } ?>">
                                                <input type="radio" name="laterpay_category_price_revenue_model" class="lp_js_revenueModelInput" value="sis"<?php if ( $laterpay['global_default_price_revenue_model'] == 'sis' ) { echo ' checked'; } ?>>SIS
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <a href="#" class="lp_js_saveCategoryDefaultPrice button button-primary"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                                        <a href="#" class="lp_js_cancelEditingCategoryDefaultPrice lp_inline-block lp_pd--05-1"><?php echo laterpay_sanitize_output( __( 'Cancel', 'laterpay' ) ); ?></a>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <div id="lp_time-passes" class="lp_mt+ lp_mb++">
            <h2>
                <?php echo laterpay_sanitize_output( __( 'Time Passes', 'laterpay' ) ); ?>
                <a href="#" id="lp_js_addTimePass" class="button button-primary lp_heading-button" data-icon="c">
                    <?php echo laterpay_sanitize_output( __( 'Create', 'laterpay' ) ); ?>
                </a>
            </h2>

            <div id="lp_js_timePassEditor" class="lp_time-passes__list lp_layout">
                <?php foreach ( $laterpay['passes_list'] as $pass ) : ?>
                    <div class="lp_js_timePassWrapper lp_time-passes__item lp_layout__item lp_clearfix" data-pass-id="<?php echo esc_attr( $pass['pass_id'] ); ?>">
                        <div class="lp_time-pass__id-wrapper">
                            <?php echo laterpay_sanitize_output( __( 'Pass', 'laterpay' ) ); ?>
                            <span class="lp_js_timePassId lp_time-pass__id"><?php echo laterpay_sanitize_output( $pass['pass_id'] ); ?></span>
                        </div>
                        <div class="lp_js_timePassPreview lp_left">
                            <?php echo laterpay_sanitized( $this->render_time_pass( $pass ) ); ?>
                        </div>

                        <div class="lp_js_timePassEditorContainer lp_time-pass-editor"></div>

                        <a href="#" class="lp_js_saveTimePass button button-primary lp_mt- lp_mb- lp_hidden"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                        <a href="#" class="lp_js_cancelEditingTimePass lp_inline-block lp_pd- lp_hidden"><?php echo laterpay_sanitize_output( __( 'Cancel', 'laterpay' ) ); ?></a>

                        <a href="#" class="lp_js_editTimePass lp_edit-link--bold lp_rounded--topright lp_inline-block" data-icon="d"></a><br>
                        <a href="#" class="lp_js_deleteTimePass lp_edit-link--bold lp_inline-block" data-icon="g"></a>

                        <div class="lp_js_voucherList lp_vouchers">
                            <?php if ( isset( $laterpay['vouchers_list'][ $pass['pass_id'] ] ) ) : ?>
                                <?php foreach ( $laterpay['vouchers_list'][ $pass['pass_id'] ] as $voucher_code => $voucher_price ) : ?>
                                    <div class="lp_js_voucher lp_voucher">
                                        <span class="lp_voucher__code"><?php echo laterpay_sanitize_output( $voucher_code ); ?></span>
                                        <span class="lp_voucher__code-infos">
                                            <?php echo laterpay_sanitize_output( __( 'reduces the price to', 'laterpay' ) ); ?>
                                            <?php echo laterpay_sanitize_output( $voucher_price . ' ' . $laterpay['standard_currency'] ); ?>.<br>
                                            <span class="lp_js_voucherTimesRedeemed">
                                                <?php
                                                    echo laterpay_sanitize_output( ( ! isset( $laterpay['vouchers_statistic'][ $pass['pass_id'] ][ $voucher_code ] ) ) ?
                                                        0 :
                                                        $laterpay['vouchers_statistic'][ $pass['pass_id'] ][ $voucher_code ]
                                                    );
                                                ?>
                                            </span>
                                            <?php echo laterpay_sanitize_output( __( 'times redeemed.', 'laterpay' ) ); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="lp_js_emptyState lp_empty-state"<?php if ( ! empty( $laterpay['passes_list'] ) ) { echo ' style="display:none;"'; } ?>>
                    <h2>
                        <?php echo laterpay_sanitize_output( __( 'Sell bundles of content', 'laterpay' ) ); ?>
                    </h2>
                    <p>
                        <?php echo laterpay_sanitize_output( __( 'With Time Passes you can sell time-limited access to a category or your entire site. Time Passes do not renew automatically.', 'laterpay' ) ); ?>
                    </p>
                    <p>
                        <?php echo laterpay_sanitize_output( __( 'Click the "Create" button to add a Time Pass.', 'laterpay' ) ); ?>
                    </p>
                </div>

                <div id="lp_js_timePassTemplate"
                    class="lp_js_timePassWrapper lp_js_addTimePassWrapper lp_layout__item lp_time-passes__item lp_clearfix lp_hidden"
                    data-pass-id="0">
                    <div class="lp_time-pass__id-wrapper" style="display:none;">
                        <?php echo laterpay_sanitize_output( __( 'Pass', 'laterpay' ) ); ?>
                        <span class="lp_js_timePassId lp_time-pass__id">x</span>
                    </div>

                    <div class="lp_js_timePassPreview lp_left">
                        <?php echo laterpay_sanitized( $this->render_time_pass() ); ?>
                    </div>

                    <div class="lp_js_timePassEditorContainer lp_time-pass-editor">
                        <form id="lp_js_timePassFormTemplate" class="lp_js_timePassEditorForm lp_hidden" method="post">
                            <input type="hidden" name="form"    value="time_pass_form_save">
                            <input type="hidden" name="action"  value="laterpay_pricing">
                            <input type="hidden" name="pass_id" value="0" id="lp_js_timePassEditorHiddenPassId">
                            <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                            <table class="lp_time-pass-editor__column">
                                <tr>
                                    <td colspan="2">
                                        <?php echo laterpay_sanitize_output( __( 'The pass is valid for ', 'laterpay' ) ); ?>
                                        <select name="duration" class="lp_js_switchTimePassDuration lp_input">
                                            <?php echo laterpay_sanitized( LaterPay_Helper_TimePass::get_select_options( 'duration' ) ); ?>
                                        </select>
                                        <select name="period" class="lp_js_switchTimePassPeriod lp_input">
                                            <?php echo laterpay_sanitized( LaterPay_Helper_TimePass::get_select_options( 'period' ) ); ?>
                                        </select>
                                        <?php echo laterpay_sanitize_output( __( 'and grants', 'laterpay' ) ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo laterpay_sanitize_output( __( 'access to', 'laterpay' ) ); ?>
                                    </td>
                                    <td>
                                        <select name="access_to" class="lp_js_switchTimePassScope lp_input lp_1">
                                            <?php echo laterpay_sanitized( LaterPay_Helper_TimePass::get_select_options( 'access' ) ); ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td class="lp_js_timePassCategoryWrapper">
                                        <input type="hidden" name="category_name"   value="" class="lp_js_switchTimePassScopeCategory">
                                        <input type="hidden" name="access_category" value="" class="lp_js_timePassCategoryId">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php echo laterpay_sanitize_output( __( 'The user pays', 'laterpay' ) ); ?>
                                        <input type="text"
                                            class="lp_js_timePassPriceInput lp_input lp_number-input"
                                            name="price"
                                            value="<?php echo esc_attr( LaterPay_Helper_View::format_number( LaterPay_Helper_TimePass::get_default_options( 'price' ) ) ); ?>"
                                            maxlength="6">
                                        <?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?>
                                        <?php echo laterpay_sanitize_output( __( 'later', 'laterpay' ) ); ?><div class="lp_toggle">
                                            <label class="lp_toggle__label lp_toggle__label-pass">
                                                <input type="checkbox"
                                                   name="revenue_model"
                                                   class="lp_js_timePassRevenueModelInput lp_toggle__input"
                                                   value="sis"
													<?php if ( LaterPay_Helper_TimePass::get_default_options( 'revenue_model' ) === 'sis' ) { echo 'checked'; } ?>>
                                                <span class="lp_toggle__text"></span>
                                                <span class="lp_toggle__handle"></span>
                                            </label>
                                        </div><?php echo laterpay_sanitize_output( __( 'immediately', 'laterpay' ) ); ?>
                                    </td>
                                </tr>
                            </table>

                            <table class="lp_time-pass-editor__column">
                                <tr>
                                    <td>
                                        <?php echo laterpay_sanitize_output( __( 'Title', 'laterpay' ) ); ?>
                                    </td>
                                    <td>
                                        <input type="text"
                                            name="title"
                                            class="lp_js_timePassTitleInput lp_input lp_1"
                                            value="<?php echo esc_attr( LaterPay_Helper_TimePass::get_default_options( 'title' ) ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="lp_rowspan-label">
                                        <?php echo laterpay_sanitize_output( __( 'Description', 'laterpay' ) ); ?>
                                    </td>
                                    <td rowspan="2">
                                        <textarea
                                            class="lp_js_timePassDescriptionTextarea lp_timePass_description-input lp_input lp_1"
                                            name="description">
                                            <?php echo esc_textarea( LaterPay_Helper_TimePass::get_description() ); ?>
                                        </textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            </table>

                            <div class="lp_js_voucherEditor lp_mt- lp_mb">
                                <?php echo laterpay_sanitize_output( __( 'Offer this time pass at a reduced price of', 'laterpay' ) ); ?>
                                <input type="text"
                                       name="voucher_price"
                                       class="lp_js_voucherPriceInput lp_input lp_number-input"
                                       value="<?php echo esc_attr( LaterPay_Helper_View::format_number( LaterPay_Helper_TimePass::get_default_options( 'price' ) ) ); ?>"
                                       maxlength="6">
                                <span><?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?></span>
                                <a href="#" class="lp_js_generateVoucherCode lp_edit-link lp_add-link" data-icon="c">
                                    <?php echo laterpay_sanitize_output( __( 'Generate voucher code', 'laterpay' ) ); ?>
                                </a>


                                <div class="lp_js_voucherPlaceholder"></div>
                            </div>

                        </form>
                    </div>

                    <a href="#" class="lp_js_saveTimePass button button-primary lp_mt- lp_mb-"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                    <a href="#" class="lp_js_cancelEditingTimePass lp_inline-block lp_pd-"><?php echo laterpay_sanitize_output( __( 'Cancel', 'laterpay' ) ); ?></a>

                    <a href="#" class="lp_js_editTimePass lp_edit-link--bold lp_rounded--topright lp_inline-block lp_hidden" data-icon="d"></a><br>
                    <a href="#" class="lp_js_deleteTimePass lp_edit-link--bold lp_inline-block lp_hidden" data-icon="g"></a>

                    <div class="lp_js_voucherList lp_vouchers"></div>
                </div>
            </div>
        </div>


        <?php # TODO: remove this in release 0.9.12 ?>
        <a href="" id="lp_js_showDeprecatedFeatures"><?php echo laterpay_sanitize_output( __( 'Show deprecated features', 'laterpay' ) ); ?></a>

        <div class="lp_js_deprecated-feature">
            <p>
                <span class="lp_badge lp_mr-">PPU</span><strong><dfn>Pay-per-Use</dfn></strong><br>
                <dfn>
                    <?php echo laterpay_sanitize_output( __( sprintf( 'The user pays later once his LaterPay invoice reaches 5 %s.', $laterpay['standard_currency'] ), 'laterpay' ) ); ?><br>
                    <?php echo laterpay_sanitize_output( __( sprintf( 'You can choose PPU for prices from 0.05 - 5.00 %s.', $laterpay['standard_currency'] ), 'laterpay' ) ); ?>
                </dfn>
            </p>
            <p>
                <span class="lp_badge lp_mr-">SIS</span><strong><dfn>Single Sale</dfn></strong><br>
                <dfn>
                    <?php echo laterpay_sanitize_output( __( 'The user has to log in to LaterPay and pay immediately.', 'laterpay' ) ); ?><br>
                    <?php echo laterpay_sanitize_output( __( sprintf( 'You can choose SIS for prices from 1.49 - 149.99 %s.', $laterpay['standard_currency'] ), 'laterpay' ) ); ?>
                </dfn>
            </p>

            <div class="lp_mb">
                <form id="lp_js_landingPageForm" method="post">
                    <input type="hidden" name="form" value="save_landing_page">
                    <input type="hidden" name="action" value="laterpay_pricing">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                    <label><?php echo laterpay_sanitize_output( __( 'Forward users to this URL after they have redeemed a gift card:', 'laterpay' ) ); ?></label>
                    <input type="text" name="landing_url" class="lp_input lp_js_landingPageInput" value="<?php echo esc_attr( $laterpay['landing_page'] ); ?>">
                    <a href="#" id="lp_js_landingPageSave" class="lp_edit-link lp_save-link lp_inline-block lp_ml lp_pd--0-05" data-icon="f"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                </form>
            </div>

            <div class="lp_bulk-price">
                <h2><?php echo laterpay_sanitize_output( __( 'Bulk Price Editor', 'laterpay' ) ); ?></h2>
                <form id="lp_js_bulkPriceEditorForm" method="post" class="lp_bulk-price__form">
                    <input type="hidden" name="form" value="bulk_price_form" id="lp_js_bulkPriceEditorHiddenFormInput">
                    <input type="hidden" name="action" value="laterpay_pricing">
                    <input type="hidden" name="bulk_operation_id" value="" id="lp_js_bulkPriceEditorHiddenIdInput">
                    <input type="hidden" name="bulk_message" value="" id="lp_js_bulkPriceEditorHiddenMessageInput">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                    <div>
                        <p>
                            <select name="bulk_action" id="lp_js_selectBulkAction" class="lp_input">
                                <?php foreach ( $laterpay['bulk_actions'] as $action_value => $action_name ) : ?>
                                    <option value="<?php echo esc_attr( $action_value ); ?>">
                                        <?php echo laterpay_sanitize_output( $action_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="bulk_selector" id="lp_js_selectBulkObjects" class="lp_input lp_is-disabled">
                                <?php foreach ( $laterpay['bulk_selectors'] as $selector_value => $selector_name ) : ?>
                                    <option value="<?php echo esc_attr( $selector_value ); ?>">
                                        <?php echo laterpay_sanitize_output( $selector_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ( $laterpay['bulk_categories'] ) : ?>
                            <select name="bulk_category" id="lp_js_selectBulkObjectsCategory" class="lp_input">
                                <?php foreach ( $laterpay['bulk_categories'] as $category ) : ?>
                                    <option value="<?php echo esc_attr( $category->term_id ); ?>">
                                        <?php echo laterpay_sanitize_output( $category->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>

                            <?php if ( $laterpay['bulk_categories_with_price'] ) : ?>
                            <select
                                id="lp_js_selectBulkObjectsCategoryWithPrice"
                                class="lp_input"
                                name="bulk_category_with_price"
                                style="display:none;">
                                <?php foreach ( $laterpay['bulk_categories_with_price'] as $category_with_price ) : ?>
                                    <option value="<?php echo esc_attr( $category_with_price->category_id ); ?>"
                                            data-price="<?php echo esc_attr( LaterPay_Helper_View::format_number( $category_with_price->category_price ) ); ?>">
                                        <?php echo laterpay_sanitize_output( $category_with_price->category_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>

                            <span id="lp_js_bulkPriceEditorAmountPreposition" class="lp_inline-block lp_mr- lp_ml-"><?php echo laterpay_sanitize_output( __( 'to', 'laterpay' ) ); ?></span>
                            <input type="text"
                                name="bulk_price"
                                id="lp_js_setBulkChangeAmount"
                                class="lp_input lp_number-input"
                                value="<?php echo esc_attr( $laterpay['global_default_price'] ); ?>"
                                placeholder="<?php echo esc_attr( __( '0.00', 'laterpay' ) ); ?>">
                            <select name="bulk_change_unit" id="lp_js_selectBulkChangeUnit" class="lp_input lp_bulkPriceUnit lp_is-disabled">
                                <option value="<?php echo esc_attr( $laterpay['standard_currency'] ); ?>">
                                    <?php echo laterpay_sanitize_output( $laterpay['standard_currency'] ); ?>
                                </option>
                                <option value="percent">%</option>
                            </select>
                            <button type="submit"
                                id="lp_js_applyBulkOperation"
                                class="button button-primary lp_ml+">
                                <?php echo laterpay_sanitize_output( __( 'Update Prices', 'laterpay' ) ); ?>
                            </button>
                            <a href="#" id="lp_js_saveBulkOperation" class="lp_edit-link lp_save-link lp_inline-block lp_ml lp_pd--0-05" data-icon="f"><?php echo laterpay_sanitize_output( __( 'Save', 'laterpay' ) ); ?></a>
                        </p>
                    </div>
                </form>
                <?php if ( $laterpay['bulk_saved_operations'] ) : ?>
                    <?php foreach ( $laterpay['bulk_saved_operations'] as $bulk_operation_id => $bulk_operation_data ) : ?>
                        <p class="lp_saved-bulk-operation" data-value="<?php echo esc_attr( $bulk_operation_id ); ?>">
                            <a href="#" class="lp_js_deleteSavedBulkOperation lp_edit-link lp_delete-link" data-icon="g"><?php echo laterpay_sanitize_output( __( 'Delete', 'laterpay' ) ); ?></a>
                            <a href="#" class="lp_js_applySavedBulkOperation button button-primary lp_ml+"><?php echo laterpay_sanitize_output( __( 'Update Prices', 'laterpay' ) ); ?></a>
                            <span class="lp_saved-bulk-operation__message"><?php echo laterpay_sanitize_output( stripslashes( $bulk_operation_data['message'] ) ); ?></span>
                        </p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
