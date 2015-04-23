<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div>
    <?php do_action( 'laterpay_time_passes', $laterpay['variant'], $laterpay['introductory_text'], $laterpay['call_to_action_text'], $laterpay['id'] ); ?>
</div>
