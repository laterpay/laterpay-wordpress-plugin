<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if ( ! $laterpay['purchase_button_positioned_manually'] ): ?>
    <div>
        <?php do_action( 'laterpay_purchase_button' ); ?>
    </div>
<?php endif; ?>