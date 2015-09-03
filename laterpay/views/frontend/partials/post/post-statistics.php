<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php $currency = $laterpay['currency']; ?>

<div id="lp_js_postStatistics" class="lp_post-statistics<?php if ( $laterpay['hide_statistics_pane'] ) { echo ' lp_is-hidden'; } ?>">
    <a href="#" id="lp_js_togglePostStatisticsVisibility" class="lp_post-statistics__visibility-toggle" data-icon="l"></a>
    <div class="lp_post-statistics__plugin-preview-mode">
        <?php echo laterpay_sanitize_output( __( 'Preview post as', 'laterpay' ) ); ?> <strong><?php echo laterpay_sanitize_output( __( 'Admin', 'laterpay' ) ); ?></strong>
        <div class="lp_toggle">
            <form id="lp_js_postStatisticsPluginPreviewModeForm" method="post">
                <input type="hidden" name="action" value="laterpay_post_statistic_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                <label class="lp_toggle__label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_togglePostPreviewMode"
                            class="lp_toggle__input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ) : ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_postPreviewModeInput"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="lp_toggle__text" data-on="" data-off=""></span>
                    <span class="lp_toggle__handle"></span>
                </label>
            </form>
        </div>
        <strong><?php echo laterpay_sanitize_output( __( 'Visitor', 'laterpay' ) ); ?></strong>
    </div>
</div>
