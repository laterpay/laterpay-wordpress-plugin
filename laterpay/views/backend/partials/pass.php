<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_pass-container">
    <div class="lp_pass-cover">
        <h2><?php echo $laterpay_pass['title']; ?></h2>
        <p><?php echo $laterpay_pass['text']; ?></p>
        <a href="javascript:return false;" 
           class="lp_purchasePassLink lp_purchaseLink lp_button" 
           data-pass-id ="<?php echo $laterpay_pass['id']; ?>"
           title="Buy now with LaterPay" 
           data-icon="b" 
           data-preview-as-visitor="1">
               <?php
               echo sprintf(
                       __('%s<small>%s</small>', 'laterpay'), number_format_i18n((float) $laterpay_pass['price'], 2), $laterpay_pass['currency']
               );
               ?>
            <a href="javascript:return false;" class="lp_passTermsLink">Terms</a>
    </div>
    <div class="lp_pass_entity_edit_container">
        <a href="#" class="lp_editLink lp_saveLink lp_u_inlineBlock" data-icon="d" data-pass-id ="<?php echo $laterpay_pass['id']; ?>"><?php _e( 'Edit', 'laterpay' ); ?></a>
        <a href="#" class="lp_deleteLink lp_u_inlineBlock" data-icon="g" data-pass-id ="<?php echo $laterpay_pass['id']; ?>"><?php _e( 'Delete', 'laterpay' ); ?></a>
    </div>
</div>