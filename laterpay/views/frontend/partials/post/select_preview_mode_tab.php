<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div id="lp_js_postStatistics" class="lp_postStatistics<?php if ( $laterpay['hide_statistics_pane'] ) echo ' lp_is-hidden'; ?>">
    <form id="lp_js_postStatistics_visibilityForm" method="post">
        <input type="hidden" name="action" value="laterpay_post_statistic_visibility">
        <input type="hidden" id="lp_js_postStatistics_visibilityInput" name="hide_statistics_pane" value="<?php echo $laterpay['hide_statistics_pane'];?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_togglePostStatisticsVisibility" class="lp_postStatistics_visibilityToggle" data-icon="l"></a>
    <h2 data-icon="a"><?php _e( 'Post Preview Mode', 'laterpay' ); ?></h2>
    <div class="lp_postStatistics_pluginPreviewMode">
        <?php _e( 'Preview post as', 'laterpay' ); ?> <strong><?php _e( 'Admin', 'laterpay' ); ?></strong>
        <div class="lp_toggle">
            <form id="lp_js_postStatistics_pluginPreviewModeForm" method="post">
                <input type="hidden" name="action" value="laterpay_post_statistic_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                <label class="lp_toggle_label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_togglePostPreviewMode"
                            class="lp_toggle_input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ): ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_postPreviewModeInput"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="lp_toggle_text" data-on="" data-off=""></span>
                    <span class="lp_toggle_handle"></span>
                </label>
            </form>
        </div>
        <strong><?php _e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
