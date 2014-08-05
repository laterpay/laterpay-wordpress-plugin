<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a href="#"
    class="laterpay-purchase-link laterpay-purchase-button"
    post-id="<?php echo $post_id; ?>"
    title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
    data-laterpay="<?php echo $link; ?>"
    data-icon="b"
    data-preview-as-visitor="<?php echo $preview_post_as_visitor; ?>"
>
    <?php
        echo sprintf(
            __( '%s<small>%s</small>', 'laterpay' ),
            $price,
            $currency
        );
    ?>
</a>
