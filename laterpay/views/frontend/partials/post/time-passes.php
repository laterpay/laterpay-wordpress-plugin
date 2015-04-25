<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php if ( ! $laterpay['time_passes_positioned_manually'] ) : ?>
    <div>
        <?php do_action( 'laterpay_time_passes' ); ?>
    </div>
<?php endif; ?>
