<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
    $title = sprintf(
        '%s<small>%s</small>',
        LaterPay_Helper_View::format_number( $laterpay_gift_card['price'] ),
        $laterpay['standard_currency']
    );

    $period = $period = LaterPay_Helper_Passes::get_period_options( $laterpay_gift_card['period'] );
    if ( $laterpay_gift_card['duration'] > 1 ) {
        $period = LaterPay_Helper_Passes::get_period_options( $laterpay_gift_card['period'], true );
    }

    $price = LaterPay_Helper_View::format_number( $laterpay_gift_card['price'] );

    $access_type = LaterPay_Helper_Passes::get_access_options( $laterpay_gift_card['access_to'] );
    $access_dest = __( 'on this website', 'laterpay' );
    $category = get_category( $laterpay_gift_card['access_category'] );
    if ( $laterpay_gift_card['access_to'] != 0 ) {
        $access_dest = $category->name;
    }
?>

<div class="lp_js_giftCard lp_gift-card lp_gift-card--<?php echo $laterpay_gift_card['gift_card_id']; ?>">
    <h4 class="lp_gift-card__title"><?php echo $laterpay_gift_card['title']; ?></h4>
    <p class="lp_gift-card__description"><?php echo $laterpay_gift_card['description']; ?></p>
    <table class="lp_gift-card___conditions">
        <tr>
            <th><?php _e( 'Validity', 'laterpay' ) ?></th>
            <td>
                <?php echo $laterpay_gift_card['duration'] . ' ' . $period; ?>
            </td>
        </tr>
        <tr>
            <th><?php _e( 'Access to', 'laterpay' ); ?></th>
            <td>
                <?php echo $access_type . ' ' . $access_dest; ?>
            </td>
        </tr>
        <tr>
            <th><?php _e( 'Renewal', 'laterpay' ) ?></th>
            <td>
                <?php _e( 'No automatic renewal', 'laterpay' ); ?>
            </td>
        </tr>
    </table>
    <div class="lp_gift-card__actions">
        <a href="#"
            class="lp_js_doPurchase lp_purchaseLink lp_button"
            title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
            data-icon="b"
            data-laterpay="<?php echo $laterpay_gift_card['url']?>"
            data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>">
            <?php echo $title ?>
        </a>
    </div>
</div>
