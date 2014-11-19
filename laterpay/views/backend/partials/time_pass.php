<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_timePass" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <h4 class="lp_timePass_title"><?php echo $laterpay_pass['title']; ?></h4>
    <p class="lp_timePass_description"><?php echo $laterpay_pass['description']; ?></p>
    <div class="lp_timePass_actions">
        <a href="#"
         class="lp_purchaseLink lp_button"
         title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
         data-icon="b"
         data-preview-as-visitor="1">
         <?php
            echo sprintf(
                '%s<small>%s</small>',
                number_format_i18n( (float) $laterpay_pass['price'], 2 ),
                $laterpay['standard_currency']
            );
        ?>
        </a>
        <a href="#" class="lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
    </div>
</div>
