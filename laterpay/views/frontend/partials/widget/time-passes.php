<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div id="lp_js_timePassWidget" class="lp_time-pass-widget">
    <?php if ( $laterpay_widget['time_pass_introductory_text'] ) : ?>
        <p class="lp_time-pass__introductory-text"><?php echo laterpay_sanitize_output( $laterpay_widget['time_pass_introductory_text'] ); ?></p>
    <?php endif; ?>

    <?php foreach ( $laterpay_widget['passes_list'] as $time_pass ) : ?>
        <?php echo laterpay_sanitized( $this->render_time_pass( $time_pass ) ); ?>
    <?php endforeach; ?>

    <?php if ( $laterpay_widget['has_vouchers'] ) : ?>
        <?php if ( $laterpay_widget['time_pass_call_to_action_text'] ) : ?>
             <p class="lp_time-pass__call-to-action-text"><?php echo laterpay_sanitize_output( $laterpay_widget['time_pass_call_to_action_text'] ); ?></p>
        <?php endif; ?>

        <div id="lp_js_voucherCodeWrapper" class="lp_redeem-code__wrapper lp_clearfix">
            <input type="text" name="time_pass_voucher_code" class="lp_js_voucherCodeInput lp_redeem-code__value lp_is-hidden" maxlength="6">
            <p class="lp_redeem-code__input-hint"><?php echo laterpay_sanitize_output( __( 'Code', 'laterpay' ) ); ?></p>
            <div class="lp_js_voucherRedeemButton lp_redeem-code__button lp_button"><?php echo laterpay_sanitize_output( __( 'Redeem', 'laterpay' ) ); ?></div>
            <p class="lp_redeem-code__hint"><?php echo laterpay_sanitize_output( __( 'Redeem Voucher >', 'laterpay' ) ); ?></p>
        </div>
    <?php endif; ?>
</div>
