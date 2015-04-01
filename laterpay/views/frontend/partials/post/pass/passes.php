<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div>
    <?php if ( defined( 'DOING_AJAX' ) && DOING_AJAX ): ?>
        <?php
            ob_start();
            do_action( 'laterpay_time_passes', $laterpay['variant'], $laterpay['introductory_text'], $laterpay['call_to_action_text'], $laterpay['id'] );
            $html = ob_get_contents();
            ob_end_clean();
            echo $html;
        ?>
    <?php else: ?>
        <?php do_action( 'laterpay_time_passes', $laterpay['variant'], $laterpay['introductory_text'], $laterpay['call_to_action_text'], $laterpay['id'] ); ?>
    <?php endif; ?>
</div>
