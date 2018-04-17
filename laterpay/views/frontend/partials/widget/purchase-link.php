<?php

if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

/**
 * we can't use line-breaks in this template, otherwise wpautop() would add <br> before every attribute
 */
$args = array_merge( array(
    'href'                          => '#',
    'class'                         => 'lp_js_doPurchase lp_js_purchaseLink lp_purchase-link',
    'title'                         => __( 'Buy now with LaterPay', 'laterpay' ),
    'data-icon'                     => 'b',
    'data-laterpay'                 => $laterpay['link'],
    'data-post-id'                  => $laterpay['post_id'],
    ),
    $laterpay['attributes']
);
$arg_str = '';
foreach ( $args as $key => $value ) {
    $arg_str .= ' ' . $key . '="' . esc_attr( $value ) . '" ';
}

if ( $laterpay['revenue_model'] === 'sis' ) :
    /* translators: %1$s purchase text, %2$s formatted price, %3$s currency tpye */
    $link_text = sprintf( '%1$s %2$s<small class="lp_purchase-link__currency">%3$s</small>', esc_html__( 'Buy now for', 'laterpay' ), esc_html( LaterPay_Helper_View::format_number( $laterpay['price'] ) ), esc_html( $laterpay['currency'] ) );
else :
    /* translators: %1$s purchase text, %2$s formatted price, %3$s currency tpye, %4$s purchase text */
    $link_text = sprintf( '%1$s %2$s<small class="lp_purchase-link__currency">%3$s</small> %4$s', esc_html__( 'Buy now for', 'laterpay' ), esc_html( LaterPay_Helper_View::format_number( $laterpay['price'] ) ), esc_html( $laterpay['currency'] ), esc_html__( 'and pay later', 'laterpay' ) );
endif;
if ( isset( $laterpay['link_text'] ) ) {
    $link_text = $laterpay['link_text'];
    $link_text = str_replace( array('{price}', '{currency}'), array( LaterPay_Helper_View::format_number( $laterpay['price'] ), $laterpay['currency'] ), $link_text );
}
?>

<a <?php echo $arg_str; ?>><?php echo $link_text; // phpcs:ignore ?></a>
