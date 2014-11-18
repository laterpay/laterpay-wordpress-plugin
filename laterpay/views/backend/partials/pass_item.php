<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_pass-container" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <div class="lp_pass-cover">
        <h2><?php echo $laterpay_pass['title']; ?></h2>
        <p><?php echo $laterpay_pass['description']; ?></p>
        <a href="#"
           class="lp_purchasePassLink lp_purchaseLink lp_button"
           title="Buy now with LaterPay"
           data-icon="b"
           data-preview-as-visitor="1">
               <?php echo sprintf(
                   __( '%s<small>%s</small>', 'laterpay' ), number_format_i18n( (float) $laterpay_pass['price'] ), 'EUR'
               ); ?>
        </a>
        <a href="#" class="lp_passTermsLink">Terms</a>
    </div>
    <div class="lp_passes_editor_container"></div>
    <div class="lp_passes_buttons_container">
        <a href="#" class="lp_changeLink lp_u_inlineBlock" data-icon="d"><?php _e( 'Change', 'laterpay' ); ?></a>
        <a href="#" class="lp_saveLink lp_u_inlineBlock" data-icon="d"><?php _e( 'Save', 'laterpay' ); ?></a>
        <a href="#" class="lp_deleteLink lp_u_inlineBlock" data-icon="e"><?php _e( 'Delete', 'laterpay' ); ?></a>
        <a href="#" class="lp_cancelLink lp_u_inlineBlock" data-icon="d"><?php _e( 'Cancel', 'laterpay' ); ?></a>
    </div>
</div>
