<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php
$title = sprintf(
    '%s<small class="lp_purchase-link__currency">%s</small>',
    LaterPay_Helper_View::format_number( $laterpay_subscription['price'] ),
    $laterpay['standard_currency']
);

$period = LaterPay_Helper_TimePass::get_period_options( $laterpay_subscription['period'] );
if ( $laterpay_subscription['duration'] > 1 ) {
    $period = LaterPay_Helper_TimePass::get_period_options( $laterpay_subscription['period'], true );
}

$price = LaterPay_Helper_View::format_number( $laterpay_subscription['price'] );

$access_type = LaterPay_Helper_TimePass::get_access_options( $laterpay_subscription['access_to'] );
$access_dest = __( 'on this website', 'laterpay' );
$category = get_category( $laterpay_subscription['access_category'] );
if ( $laterpay_subscription['access_to'] != 0 ) {
    $access_dest = $category->name;
}
?>

<div class="lp_js_subscription lp_time-pass lp_time-pass-<?php echo esc_attr( $laterpay_subscription['id'] ); ?>" data-sub-id="<?php echo esc_attr( $laterpay_subscription['id'] ); ?>">

    <section class="lp_time-pass__front">
        <h4 class="lp_js_subscriptionPreviewTitle lp_time-pass__title"><?php echo laterpay_sanitize_output( $laterpay_subscription['title'] ); ?></h4>
        <p class="lp_js_subscriptionPreviewDescription lp_time-pass__description"><?php echo laterpay_sanitize_output( $laterpay_subscription['description'] ); ?></p>
        <div class="lp_time-pass__actions">
            <a href="#" class="lp_js_doPurchase lp_js_purchaseLink lp_purchase-button" title="<?php echo esc_attr( __( 'Buy now with LaterPay', 'laterpay' ) ); ?>" data-icon="b" data-laterpay="<?php echo esc_attr( isset( $laterpay_subscription['url'] ) ? $laterpay_subscription['url'] : '' ); ?>" data-preview-as-visitor="<?php echo esc_attr( isset( $laterpay_subscription['preview_post_as_visitor'] ) ? $laterpay_subscription['preview_post_as_visitor'] : '' ); ?>"><?php echo laterpay_sanitize_output( $title ); ?></a><a href="#" class="lp_js_flipSubscription lp_time-pass__terms"><?php echo laterpay_sanitize_output( __( 'Terms', 'laterpay' ) ); ?></a>
        </div>
    </section>

    <section class="lp_time-pass__back">
        <a href="#" class="lp_js_flipSubscription lp_time-pass__front-side-link"><?php echo laterpay_sanitize_output( __( 'Back', 'laterpay' ) ); ?></a>
        <table class="lp_time-pass__conditions">
            <tbody>
            <tr>
                <th class="lp_time-pass__condition-title"><?php echo laterpay_sanitize_output( __( 'Validity', 'laterpay' ) ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_subscriptionPreviewValidity"><?php echo laterpay_sanitize_output( $laterpay_subscription['duration'] . ' ' . $period ); ?></span>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php echo laterpay_sanitize_output( __( 'Access to', 'laterpay' ) ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_subscriptionPreviewAccess"><?php echo laterpay_sanitize_output( $access_type . ' ' . $access_dest ); ?></span>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php echo laterpay_sanitize_output( __( 'Renewal', 'laterpay' ) ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <?php echo laterpay_sanitize_output( __( 'After', 'laterpay' ) . ' ' . $laterpay_subscription['duration'] . ' ' . $period ); ?>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php echo laterpay_sanitize_output( __( 'Price', 'laterpay' ) ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_subscriptionPreviewPrice"><?php echo laterpay_sanitize_output( $price . ' ' . $laterpay['standard_currency'] ); ?></span>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php echo laterpay_sanitize_output( __( 'Cancellation', 'laterpay' ) ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <?php echo laterpay_sanitize_output( __( 'Cancellable anytime', 'laterpay' ) ); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </section>

</div>
