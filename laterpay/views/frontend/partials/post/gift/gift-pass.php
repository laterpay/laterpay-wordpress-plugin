<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php
$gift_pass = $laterpay_gift['gift_pass'];

$period = LaterPay_Helper_TimePass::get_period_options( $gift_pass['period'] );
if ( $gift_pass['duration'] > 1 ) {
    $period = LaterPay_Helper_TimePass::get_period_options( $gift_pass['period'], true );
}

$price = LaterPay_Helper_View::format_number( $gift_pass['price'] );

$access_type = LaterPay_Helper_TimePass::get_access_options( $gift_pass['access_to'] );
$access_dest = __( 'on this website', 'laterpay' );
$category    = get_category( $gift_pass['access_category'] );

if ( 0 !== absint( $gift_pass['access_to'] ) ) {
    $access_dest = $category->name;
}

$pass_id = ( ! empty( $gift_pass['pass_id'] ) ) ? $gift_pass['pass_id'] : '';
$pass_id = ( empty( $pass_id ) && ! empty( $gift_pass['id'] ) ) ? $gift_pass['id'] : $pass_id;

// Don't render view if entity info is empty.
if ( empty( $pass_id ) ) {
    return;
}

$subscription_data = LaterPay_Helper_Subscription::get_subscription_by_id( $pass_id );
$is_subscription   = ( ! empty( $subscription_data ) && is_array( $subscription_data ) );

?>

<div class="lp_js_giftCard lp_gift-card lp_gift-card-<?php echo esc_attr( $pass_id ); ?> <?php echo $is_subscription ? 'lp_gift-card-subscription' : ''; ?> ">
    <h4 class="lp_gift-card__title"><?php echo esc_html( $gift_pass['title'] ); ?></h4>
    <p class="lp_gift-card__description"><?php echo wp_kses_post( $gift_pass['description'] ); ?></p>
    <table class="lp_gift-card___conditions">
        <tr>
            <th class="lp_gift-card___conditions-title"><?php esc_html_e( 'Validity', 'laterpay' ); ?></th>
            <td class="lp_gift-card___conditions-value">
                <?php
                if ( $is_subscription ) {
                    /* translators: %s: Subscription Period */
                    $subscription_text = sprintf( __( 'Subscription (Renews in a %s, cancellable anytime)', 'laterpay' ), strtolower( $period ) );

                    echo esc_html(
                        sprintf(
                            '%s %s %s',
                            $gift_pass['duration'],
                            $period,
                            $subscription_text
                        )
                    );
                } else {
                    echo esc_html( $gift_pass['duration'] . ' ' . $period );
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="lp_gift-card___conditions-title"><?php esc_html_e( 'Access to', 'laterpay' ); ?></th>
            <td class="lp_gift-card___conditions-value">
                <?php echo esc_html( $access_type . ' ' . $access_dest ); ?>
            </td>
        </tr>
        <tr>
            <th class="lp_gift-card___conditions-title"><?php esc_html_e( 'Renewal', 'laterpay' ); ?></th>
            <td class="lp_gift-card___conditions-value">
                <?php
                if ( $is_subscription ) {
                    esc_html_e( 'Automatically renewed.', 'laterpay' );
                } else {
                    esc_html_e( 'No automatic renewal', 'laterpay' );
                }
                ?>
            </td>
        </tr>
    </table>
    <?php if ( $laterpay_gift['show_redeem'] ) : ?>
        <?php
        $this->render_redeem_form();
        ?>
    <?php else : ?>
        <div class="lp_js_giftCardActionsPlaceholder_<?php echo esc_attr( $gift_pass['pass_id'] ); ?>"></div>
    <?php endif; ?>
</div>
