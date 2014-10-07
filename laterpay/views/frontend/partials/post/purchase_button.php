<?php
/**
 * this template is used for do_action( 'laterpay_purchase_button' );
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<a href="#"
    class="lp_js_do-purchase lp_purchase-link lp_button"
    title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
    data-icon="b"
    data-laterpay="<?php echo $laterpay[ 'link' ]; ?>"
    data-post-id="<?php echo $laterpay[ 'post_id' ]; ?>"
    data-preview-as-visitor="<?php echo $laterpay[ 'preview_post_as_visitor' ]; ?>"
><?php
        echo sprintf(
            __( '%s<small>%s</small>', 'laterpay' ),
            LaterPay_Helper_View::format_number( (float) $laterpay[ 'price' ], 2 ),
            $laterpay[ 'currency' ]
        );
    ?></a>
