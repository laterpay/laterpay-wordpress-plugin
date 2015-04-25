<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php
    $pass  = $laterpay['pass'];
    $title = sprintf(
        '%s<small class="lp_purchase-link__currency">%s</small>',
        LaterPay_Helper_View::format_number( $pass['price'] ),
        $laterpay['standard_currency']
    );
?>
<div class="lp_gift-card__actions">
    <?php if ( $laterpay['has_access'] ) : ?>
        <?php echo laterpay_sanitize_output( __( 'Gift Code', 'laterpay' ) ); ?>
        <span class="lp_voucher__code"><?php echo laterpay_sanitize_output( $laterpay['gift_code'] ); ?></span><br>
        <!--
        <?php echo laterpay_sanitize_output( __( 'Redeem at', 'laterpay' ) ); ?>
        <a href="<?php echo esc_url_raw( $laterpay['landing_page'] ); ?>"><?php echo laterpay_sanitize_output( $laterpay['landing_page'] ); ?></a>
        -->
    <?php else : ?>
        <a href="#" class="lp_js_doPurchase lp_purchase-button" title="<?php echo laterpay_sanitize_output( __( 'Buy now with LaterPay', 'laterpay' ) ); ?>" data-icon="b" data-laterpay="<?php echo esc_attr( $pass['url'] ); ?>" data-preview-as-visitor="<?php echo esc_attr( $laterpay['preview_post_as_visitor'] ); ?>"><?php echo laterpay_sanitize_output( $title ); ?></a>
    <?php endif; ?>
</div>
