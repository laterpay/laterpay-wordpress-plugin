<?php

if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

if ( $laterpay['purchase_link_is_hidden'] ) {
    return;
}
/**
 * we can't use line-breaks in this template, otherwise wpautop() would add <br> before every attribute
 */
$args = array(
    'href'                          => '#',
    'class'                         => 'lp_js_doPurchase lp_js_purchaseLink lp_purchase-link',
    'title'                         => __( 'Buy now with LaterPay', 'laterpay' ),
    'data-icon'                     => 'b',
    'data-laterpay'                 => $laterpay['link'],
    'data-post-id'                  => $laterpay['post_id'],
    'data-preview-as-visitor'       => $laterpay['preview_post_as_visitor'],
    'data-is-in-visible-test-mode'  => $laterpay['is_in_visible_test_mode'],
);
$arg_str = '';
foreach ( $args as $key => $value ) {
    $arg_str .= ' ' . $key . '="' . esc_attr( $value ) . '" ';
}

if ( $laterpay['revenue_model'] == 'sis' ) :
    $title = sprintf(
        __( 'Buy now for %s<small class="lp_purchase-link__currency">%s</small>', 'laterpay' ),
        LaterPay_Helper_View::format_number( $laterpay['price'] ),
        $laterpay['currency']
    );
else :
    $title = sprintf(
        __( 'Buy now for %s<small class="lp_purchase-link__currency">%s</small> and pay later', 'laterpay' ),
        LaterPay_Helper_View::format_number( $laterpay['price'] ),
        $laterpay['currency']
    );
endif;
?>

<a <?php echo laterpay_sanitized( $arg_str ); ?>><?php echo laterpay_sanitize_output( $title ); ?></a>
