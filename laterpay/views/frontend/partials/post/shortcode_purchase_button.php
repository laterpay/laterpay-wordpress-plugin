<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<a href="#"
   class="lp_js_doPurchase lp_purchase-button lp_purchase-link--shortcode"
   title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
   data-icon="b"
   data-laterpay="<?php echo $laterpay['link']; ?>"
   data-post-id="<?php echo $laterpay['post_id']; ?>"
   data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']; ?>"
   data-is-in-visible-test-mode="<?php echo $laterpay['is_in_visible_test_mode']; ?>"
   ><?php
       echo sprintf(
               __( '%s<small class="lp_purchase-link__currency">%s</small>', 'laterpay' ), LaterPay_Helper_View::format_number( $laterpay['price'] ), $laterpay['currency']
       );
       ?></a>
