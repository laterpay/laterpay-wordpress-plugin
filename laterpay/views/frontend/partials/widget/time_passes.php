<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div class="lp_timePassWidget <?php echo $laterpay_widget['time_pass_widget_class']; ?>">
    <?php if ( $laterpay_widget['time_pass_introductory_text'] ): ?>
        <p class="lp_timePass_introductoryText"><?php echo $laterpay_widget['time_pass_introductory_text']; ?></p>
    <?php endif; ?>

    <?php foreach ( $laterpay_widget['passes_list'] as $pass ): ?>
        <?php echo $this->render_time_pass( (array) $pass ); ?>
    <?php endforeach; ?>

    <?php if ( $laterpay_widget['has_vouchers'] ): ?>
        <?php if ( $laterpay_widget['time_pass_call_to_action_text'] ): ?>
             <p class="lp_timePass_callToActionText"><?php echo $laterpay_widget['time_pass_call_to_action_text']; ?></p>
         <?php endif; ?>

        <div id="lp_js_voucherCodeWrapper" class="lp_js_voucherCodeWrapper lp_timePassWidget_voucherCodeWrapper lp_u_clearfix">
            <input type="text" name="time_pass_voucher_code" class="lp_js_voucherCodeInput lp_timePassWidget_voucherCode" maxlength="6">
            <p class="lp_timePassWidget_voucherCodeInputHint"><?php _e( 'Code', 'laterpay' ); ?></p>
            <div class="lp_js_voucherRedeemButton lp_timePassWidget_redeemVoucherCode"><?php _e( 'Redeem', 'laterpay' ); ?></div>
            <p class="lp_timePassWidget_voucherCodeHint"><?php _e( 'Redeem Voucher >', 'laterpay' ); ?></p>
        </div>
    <?php endif; ?>
</div>
