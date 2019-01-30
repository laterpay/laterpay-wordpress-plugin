<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php

$purchase_url = ( ! empty( $laterpay['url'] ) ) ? $laterpay['url'] : '#';
$image_path   = ( ! empty( $laterpay['custom_image_path'] ) ) ? $laterpay['custom_image_path'] : '';

if ( empty( $image_path ) ) :
    ?>
    <a class="lp_purchase-overlay__purchase" data-purchase-action="buy" href="<?php echo esc_url( $purchase_url ); ?>" style="background-color: <?php echo esc_attr( $laterpay['button_background_color'] ); ?>; color: <?php echo esc_attr( $laterpay['button_text_color'] ); ?>;">
        <span data-icon="b"></span>
        <span data-buy-label="true" class="lp_purchase-overlay__submit-text"><?php echo esc_html( $laterpay['button_text'] ); ?></span>
    </a>
    <?php
else:
    ?>
    <a href="<?php echo esc_url( $purchase_url ) ?>">
        <img src="<?php echo esc_url( $image_path ); ?>" />
    </a>
    <?php
endif;
?>
