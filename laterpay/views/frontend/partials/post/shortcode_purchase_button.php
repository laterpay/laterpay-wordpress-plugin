<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<a href="#"
   class="lp_js_doPurchase lp_purchase-button lp_purchase-link--shortcode"
   title="<?php echo esc_attr( __( 'Buy now with LaterPay', 'laterpay' ) ); ?>"
   data-icon="b"
   data-laterpay="<?php echo esc_attr( $laterpay['link'] ); ?>"
   data-post-id="<?php echo esc_attr( $laterpay['post_id'] ); ?>"
   data-preview-as-visitor="<?php echo esc_attr( $laterpay['preview_post_as_visitor'] ); ?>"
   data-is-in-visible-test-mode="<?php echo esc_attr( $laterpay['is_in_visible_test_mode'] ); ?>"
   ><?php
       echo laterpay_sanitize_output( sprintf(
           __( '%s<small class="lp_purchase-link__currency">%s</small>', 'laterpay' ), LaterPay_Helper_View::format_number( $laterpay['price'] ), $laterpay['currency']
       ) );
        ?></a>
