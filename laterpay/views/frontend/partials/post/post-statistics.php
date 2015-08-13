<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php $currency = $laterpay['currency']; ?>

<div id="lp_js_postStatistics" class="lp_post-statistics<?php if ( $laterpay['hide_statistics_pane'] ) { echo ' lp_is-hidden'; } ?>">
    <form id="lp_js_postStatisticsVisibilityForm" method="post">
        <input type="hidden" name="action" value="laterpay_post_statistic_visibility">
        <input type="hidden" id="lp_js_postStatisticsVisibilityInput" name="hide_statistics_pane" value="<?php echo esc_attr( $laterpay['hide_statistics_pane'] );?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_togglePostStatisticsVisibility" class="lp_post-statistics__visibility-toggle" data-icon="l"></a>
    <div class="lp_statistics-empty">
        <h2><?php echo laterpay_sanitize_output( __( 'Statistics have moved to your LaterPay merchant backend', 'laterpay' ) ); ?></h2>
        <p><?php echo laterpay_sanitize_output( __( 'Go to <a href="https://merchant.laterpay.net">merchant.laterpay.net</a> and login to your merchant account to see sales statistics.', 'laterpay' ) ); ?></p>
        <p><?php echo laterpay_sanitize_output( __( 'If you don\'t have access to your merchant backend yet, please contact <a href="mailto:support@laterpay.net">support@laterpay.net</a>.', 'laterpay' ) ); ?></p>
    </div>
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
