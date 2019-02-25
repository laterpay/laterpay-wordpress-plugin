<?php

// Markup for Identity URL anchor.

if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_identity_container">
    <a class="lp_bought_notification" href="<?php echo esc_url( $laterpay['identify_url'] ); ?>"><?php esc_attr_e( 'I already bought this', 'laterpay' ); ?></a>
</div>
