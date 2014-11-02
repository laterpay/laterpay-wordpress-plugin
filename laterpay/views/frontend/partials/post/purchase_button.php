<?php
/**
 * this template is used for do_action( 'laterpay_purchase_button' );
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * we can't use line-breaks in this template, otherwise wpautop() would add <br> before every attribute
 */
$args = array(
    'href'                      => '#',
    'class'                     => 'lp_js_do-purchase lp_purchaseLink lp_button',
    'title'                     => __( 'Buy now with LaterPay', 'laterpay' ),
    'data-icon'                 => 'b',
    'data-laterpay'             => $laterpay[ 'link' ],
    'data-post-id'              => $laterpay[ 'post_id' ],
    'data-preview-as-visitor'   => $laterpay[ 'preview_post_as_visitor' ]
);
$arg_str = '';
foreach ( $args as $key => $value ) {
    $arg_str .= ' ' . $key . '="' . esc_attr( $value ) . '" ';
}

$title = sprintf(
    __( '%s<small>%s</small>', 'laterpay' ),
    LaterPay_Helper_View::format_number( (float) $laterpay[ 'price' ], 2 ),
    $laterpay[ 'currency' ]
);
?>

<a <?php echo $arg_str; ?>><?php echo $title; ?></a>
