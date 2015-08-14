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

    <div class="lp_wrap">
        <div id="lp_js_standardKpiTab" class="lp_dashboard">
            <div class="lp_empty-state lp_statistics-empty">
                <h2><?php echo laterpay_sanitize_output( __( 'Statistics have moved to your LaterPay merchant backend', 'laterpay' ) ); ?></h2>
                <p><?php echo laterpay_sanitize_output( __( 'Go to <a href="https://merchant.laterpay.net">merchant.laterpay.net</a> and login to your merchant account to see sales statistics.', 'laterpay' ) ); ?><br/>
                <?php echo laterpay_sanitize_output( __( 'If you don\'t have access to your merchant backend yet, please contact <a href="mailto:support@laterpay.net">support@laterpay.net</a>.', 'laterpay' ) ); ?>
                </p>
            </div>
        </div>

    </div>

</div>
