<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php if ( ! $laterpay['purchase_button_positioned_manually'] ): ?>
    <div>
        <?php if ( defined( 'DOING_AJAX' ) && DOING_AJAX ): ?>
            <?php
                ob_start();
                do_action( 'laterpay_purchase_button' );
                $html = ob_get_contents();
                ob_clean();
                echo $html;
            ?>
        <?php else: ?>
            <?php do_action( 'laterpay_purchase_button' ); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
