<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php if ( true === $laterpay['diplay_preview_pane'] ) : ?>
<div id="lp_js_previewModeContainer" class="lp_post-preview-mode <?php if ( true === $laterpay['hide_preview_mode_pane'] ) { echo ' lp_is-hidden'; } ?>">
    <form id="lp_js_previewModeVisibilityForm" method="post">
    <input type="hidden" name="action" value="laterpay_preview_mode_visibility">
    <input type="hidden" id="lp_js_previewModeVisibilityInput" name="hide_preview_mode_pane" value="<?php echo (int)$laterpay['hide_preview_mode_pane'];?>">
    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_togglePreviewModeVisibility" class="lp_post-preview-mode__visibility-toggle" data-icon="l"></a>
    <h2 class="lp_post-preview-mode__title" data-icon="a"><?php esc_html_e( 'Post Preview Mode', 'laterpay' ); ?></h2>
    <?php if ( ! $laterpay['plugin_is_in_live_mode'] ) : ?>
        <div id="lp_visibilityToggleIndicator">
            <div id="left">
                <a href="<?php echo esc_url( add_query_arg( LaterPay_Helper_Request::laterpay_encode_url_params( array( 'page' => $laterpay['admin_menu']['account']['url'] ) ), admin_url( 'admin.php' ) ) ); ?>"
                   class="lp_plugin-mode-indicator"
                   data-icon="h">
                    <h2 class="lp_plugin-mode-indicator__title"><?php esc_html_e( 'Test mode', 'laterpay' ); ?></h2>
                    <span class="lp_plugin-mode-indicator__text">
                    <?php
                    /* translators: %1$s info text1, %2$s info text2*/
                    printf( '%1$s<i> %2$s</i>', esc_html__( 'Earn money in', 'laterpay' ), esc_html__( 'live mode', 'laterpay' ) );
                    ?>
                </span>
                </a>
            </div>
            <div id="right">
                <?php
                printf(
                    "%s <a href='%s' class='lp_info_link'>%s</a> %s",
                    esc_html__( 'Complete the steps on the', 'laterpay' ),
                    esc_url( add_query_arg( LaterPay_Helper_Request::laterpay_encode_url_params( array( 'page' => $laterpay['admin_menu']['account']['url'] ) ), admin_url( 'admin.php' ) ) ),
                    esc_html__( 'account tab', 'laterpay' ),
                    esc_html__( 'to make this visible to visitors', 'laterpay' )
                );
                ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="lp_post-preview-mode__plugin-preview-mode">
        <?php esc_html_e( 'Preview post as', 'laterpay' ); ?> <strong><?php esc_html_e( 'Admin', 'laterpay' ); ?></strong>
        <div class="lp_toggle">
            <form id="lp_js_previewModeForm" method="post">
                <input type="hidden" name="action" value="laterpay_post_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                <label class="lp_toggle__label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_togglePreviewMode"
                            class="lp_toggle__input"
                            <?php if ( true === $laterpay['preview_post_as_visitor'] ) : ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_previewModeInput"
                            value="<?php if ( true === $laterpay['preview_post_as_visitor']) { echo 1; } else { echo 0; } ?>">
                    <span class="lp_toggle__text" data-on="" data-off=""></span>
                    <span class="lp_toggle__handle"></span>
                </label>
            </form>
        </div>
        <strong><?php esc_html_e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
<?php endif; ?>
