<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a  href="#"
    class="lp_js_do-purchase lp_purchase-link"
    data-laterpay="<?php echo $laterpay['link']; ?>"
    data-icon="b"
    data-post-id="<?php echo $laterpay['post_id']; ?>"
    data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']; ?>">
    <?php if ( $laterpay['revenue_model'] == 'sis' ): ?>
        <?php echo sprintf(
                            __( 'Buy now for %s<small>%s</small>', 'laterpay' ),
                            LaterPay_Helper_View::format_number( (float) $laterpay['price'], 2 ),
                            $laterpay['currency']
            ); ?>
    <?php else: ?>
        <?php echo sprintf(
                            __( 'Buy now for %s<small>%s</small> and pay later', 'laterpay' ),
                            LaterPay_Helper_View::format_number( (float) $laterpay['price'], 2 ),
                            $laterpay['currency']
            ); ?>
    <?php endif; ?>
</a>
