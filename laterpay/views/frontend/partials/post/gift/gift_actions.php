<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
    $pass  = $laterpay['pass'];
    $title = sprintf(
        '%s<small>%s</small>',
        LaterPay_Helper_View::format_number( $pass['price'] ),
        $laterpay['standard_currency']
    );
?>
<div class="lp_gift-card__actions">
    <?php if ( $laterpay['has_access'] ): ?>
        <?php _e( 'Gift Code', 'laterpay' ); ?>
        <span class="lp_voucherCodeLabel"><?php echo $laterpay['gift_code']; ?></span><br>
        <!--
        <?php _e( 'Redeem at', 'laterpay' ); ?>
        <a href="<?php echo $laterpay['landing_page']; ?>"><?php echo $laterpay['landing_page']; ?></a>
        -->
    <?php else: ?>
        <a href="#" class="lp_js_doPurchase lp_purchaseLink lp_button" title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>" data-icon="b" data-laterpay="<?php echo $pass['url']; ?>" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"><?php echo $title; ?></a>
    <?php endif; ?>
</div>
