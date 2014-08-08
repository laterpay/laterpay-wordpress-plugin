<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a href="#"
    class="laterpay-purchase-link laterpay-purchase-button"
    post-id="<?php echo $laterpay[ 'post_id' ]; ?>"
    title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
    data-laterpay="<?php echo $laterpay[ 'link' ]; ?>"
    data-icon="b"
    data-preview-as-visitor="<?php echo $laterpay[ 'preview_post_as_visitor' ]; ?>"
><?php
        echo sprintf(
            __( '%s<small>%s</small>', 'laterpay' ),
            LaterPay_Helper_View::format_number( (float) $laterpay[ 'price' ], 2 ),
            $laterpay[ 'currency' ]
        );
    ?></a>
