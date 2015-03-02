<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php
    if ( $laterpay['only_time_pass_purchases_allowed'] ) {
        echo __( 'Buy a time pass to read the full content.', 'laterpay' );
        return;
    }
?>
