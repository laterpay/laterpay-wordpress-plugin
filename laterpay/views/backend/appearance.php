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
        <div class="lp_row lp_fl-clearfix">
            <h2><?php _e( 'Preview of Paid Content', 'laterpay' ); ?></h2>
            <form id="laterpay_paid_content_preview_form" method="post">
                <input type="hidden" name="form"    value="paid_content_preview">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                <label class="lp_fl-left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="1"
                            class="lp_js_toggle-preview-mode lp_js_style-input"
                            <?php if ( $show_teaser_content_only ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content only', 'laterpay' ); ?>
                    <div class="lp_preview-mode-1"></div>
                </label>
                <label class="lp_fl-left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="0"
                            class="lp_js_toggle-preview-mode lp_js_style-input"
                            <?php if ( ! $show_teaser_content_only ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content + full content, covered by overlay', 'laterpay' ); ?>
                    <div class="lp_preview-mode-2"></div>
                </label>
            </form>
        </div>

        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Enabled Post Types', 'laterpay' ); ?></h2>
            <form id="laterpay_enabled_post_types_form" method="post">
                <input type="hidden" name="form"    value="enabled_post_types">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php wp_nonce_field('laterpay_form'); ?>
                <?php
                    $enabled_post_types = $config->get( 'content.enabled_post_types' );
                    $all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
                    foreach( $all_post_types as $slug => $post_type ) {
                ?>
                    <label for="supported_post_type_<?php echo $slug; ?>" class="lp_d-block lp_m-b025">
                        <input type="checkbox"
                               id="supported_post_type_<?php echo $slug; ?>"
                               name="enabled_post_types[]"
                               value="<?php echo $slug; ?>"
                               class="lp_js_style-input"
                               <?php echo ( in_array( $slug, $enabled_post_types ) ) ? ' checked="checked" ' : ''; ?>
                        />
                        <?php echo $post_type->labels->name; ?>
                    </label>
                <?php
                    }
                ?>
            </form>
        </div>

        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'LaterPay Invoice Indicator', 'laterpay' ); ?></h2>
            <dfn class="lp_fl-clearfix">
                <?php _e( 'Insert this PHP snippet into your theme to show your users their LaterPay invoice balance.', 'laterpay' ); ?><br>
                <?php _e( 'The LaterPay invoice indicator is served by LaterPay. Its styling can not be changed.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_invoice-indicator-img lp_fl-left lp_m-0 lp_m-t1" src="data:image/png;base64,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">
            <code class="lp_code-snippet lp_d-block">
                <div class="lp_triangle lp_outer-triangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_invoice_indicator' ); ?>" ); ?>
            </code>
        </div>
    </div>

</div>
