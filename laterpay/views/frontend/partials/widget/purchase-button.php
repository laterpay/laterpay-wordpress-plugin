<?php
/**
 * this template is used for do_action( 'laterpay_purchase_button' );
 */

if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

/**
 * We can't use line-breaks in this template, otherwise wpautop() would add <br> before every attribute
 */

if ( $laterpay['purchase_button_is_hidden'] ) : ?>
    <div>&nbsp;</div>
<?php
    return;
endif;

$args = array_merge( array(
        'href'                          => '#',
        'class'                         => 'lp_js_doPurchase lp_purchase-button',
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

$title = sprintf(
    __( '%s<small class="lp_purchase-link__currency">%s</small>', 'laterpay' ),
    LaterPay_Helper_View::format_number( $laterpay['price'] ),
    $laterpay['currency']
);
?>

<a <?php echo laterpay_sanitized( $arg_str ); ?>><?php echo laterpay_sanitize_output( $title ); ?></a>
