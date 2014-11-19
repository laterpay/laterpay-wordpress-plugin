<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_timePass" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <div class="lp_timePass_cover">
        <h2 class="lp_timePass_title"><?php echo $laterpay_pass['title']; ?></h2>
        <p class="lp_timePass_description"><?php echo $laterpay_pass['description']; ?></p>
        <a href="#"
           class="lp_js_doPurchase lp_purchaseLink lp_button"
           title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
           data-icon="b"
           data-preview-as-visitor="1">
               <?php echo sprintf(
                   __( '%s<small>%s</small>', 'laterpay' ), number_format_i18n( (float) $laterpay_pass['price'], 2 ), $standard_currency
               ); ?>
        </a>
        <a href="#" class="lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
    </div>
    <div class="lp_timePass_editorContainer"></div>
    <a href="#" class="lp_changeLink lp_u_inlineBlock" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
    <a href="#" class="lp_saveLink lp_u_inlineBlock" data-icon="f"><?php _e( 'Save', 'laterpay' ); ?></a>
    <a href="#" class="lp_deleteLink lp_u_inlineBlock" data-icon="g"><?php _e( 'Delete', 'laterpay' ); ?></a>
    <a href="#" class="lp_cancelLink lp_u_inlineBlock" data-icon="e"><?php _e( 'Cancel', 'laterpay' ); ?></a>
</div>
