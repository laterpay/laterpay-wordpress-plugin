<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php if ( ! $laterpay['time_passes_positioned_manually'] ): ?>
    <div>
        <?php if ( defined( 'DOING_AJAX' ) && DOING_AJAX ): ?>
            <?php
                ob_start();
                do_action( 'laterpay_time_passes' );
                $html = ob_get_contents();
                ob_clean();
                echo $html;
            ?>
        <?php else: ?>
            <?php do_action( 'laterpay_time_passes' ); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
