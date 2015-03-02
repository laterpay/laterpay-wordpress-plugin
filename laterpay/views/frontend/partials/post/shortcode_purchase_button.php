<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
?>

<a href="#"
   class="lp_purchaseLinkShortcode lp_js_doPurchase lp_purchase-link lp_button"
   title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
   data-icon="b"
   data-laterpay="<?php echo $laterpay['link']; ?>"
   data-post-id="<?php echo $laterpay['post_id']; ?>"
   data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']; ?>"
   ><?php
       echo sprintf(
               __( '%s<small class="lp_purchase-link__currency">%s</small>', 'laterpay' ), LaterPay_Helper_View::format_number( $laterpay['price'] ), $laterpay['currency']
       );
       ?></a>
