<?php
/**
 * this template is used for do_action( 'laterpay_purchase_button' );
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * We can't use line-breaks in this template, otherwise wpautop() would add <br> before every attribute
 */

if ( $laterpay_widget['purchase_button_is_hidden'] ) : ?>
    <div>&nbsp;</div>
<?php
    return;
endif;

$args = array(
    'href'                          => '#',
    'class'                         => 'lp_js_doPurchase lp_purchaseLink lp_button',
    'title'                         => __( 'Buy now with LaterPay', 'laterpay' ),
    'data-icon'                     => 'b',
    'data-laterpay'                 => $laterpay_widget['link'],
    'data-post-id'                  => $laterpay_widget['post_id'],
    'data-preview-as-visitor'       => $laterpay_widget['preview_post_as_visitor'],
    'data-is-in-visible-test-mode' => $laterpay_widget['is_in_visible_test_mode'],
);
$arg_str = '';
foreach ( $args as $key => $value ) {
    $arg_str .= ' ' . $key . '="' . esc_attr( $value ) . '" ';
}

$title = sprintf(
    __( '%s<small>%s</small>', 'laterpay' ),
    LaterPay_Helper_View::format_number( $laterpay_widget['price'] ),
    $laterpay_widget['currency']
);
?>

<a <?php echo $arg_str; ?>><?php echo $title; ?></a>

<?php if ( isset( $laterpay['show_post_ratings'] ) && $laterpay['show_post_ratings'] ) : ?>
    <div id="lp_js_postRatingPlaceholder"></div>
<?php endif; ?>
