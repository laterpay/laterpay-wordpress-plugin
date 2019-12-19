<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

$allowed_types = array( 'timepass', 'subscription' );

$pass_id   = 0;
$pass_data = ( ! empty( $laterpay['pass_data'] ) && is_array( $laterpay['pass_data'] ) ) ? $laterpay['pass_data'] : [];

if ( ! empty( $pass_data ) ) {
    $pass_id = ( ! empty( $pass_data['pass_id'] ) ) ? $pass_data['pass_id'] : '';
    $pass_id = ( empty( $pass_id ) && ! empty( $pass_data['id'] ) ) ? $pass_data['id'] : $pass_id;
}

$lp_entity_type = ( ! empty( $laterpay['type'] ) && in_array( $laterpay['type'], $allowed_types, true ) ) ? $laterpay['type'] : '';
$lp_entity_type = strtolower( trim( $lp_entity_type ) );

?>

<div id="lp_js_giftCardWrapper" class="lp_js_giftCodeWrapper lp_js_dataDeferExecution lp_redeem-code__wrapper lp_clearfix">
    <input type="text" name="gift_code" class="lp_js_giftCardCodeInput lp_redeem-code__value" maxlength="6">
    <p class="lp_redeem-code__input-hint"><?php esc_html_e( 'Code', 'laterpay' ); ?></p>
    <a href="#" class="lp_js_giftCardRedeemButton lp_redeem-code__button lp_button" data-type="<?php echo esc_attr( $lp_entity_type ); ?>" data-id="<?php echo absint( $pass_id ); ?>">
        <?php esc_html_e( 'Redeem', 'laterpay' ); ?>
    </a>
</div>

<a href="#" id="fakebtn" class="lp_js_doPurchase" style="display:none;" data-laterpay="" data-preview-as-visitor="<?php echo esc_attr( $laterpay['preview_post_as_visitor'] ); ?>"></a>
