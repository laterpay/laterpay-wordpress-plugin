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
            do_action( 'laterpay_account_links', $laterpay['css'], $laterpay['forcelang'], $laterpay['show'], $laterpay['next'] );
            $html = ob_get_contents();
            ob_clean();
            echo $html;
        ?>
    <?php else: ?>
        <?php do_action( 'laterpay_account_links', $laterpay['css'], $laterpay['forcelang'], $laterpay['show'], $laterpay['next'] ); ?>
    <?php endif; ?>
</div>
