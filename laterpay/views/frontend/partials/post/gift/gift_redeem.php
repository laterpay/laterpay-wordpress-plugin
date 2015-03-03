<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php $gift_pass = $laterpay['pass_data']; ?>

<div>
    <?php if ( $gift_pass ) : ?>
        <?php echo $this->render_gift_pass( $gift_pass, true ); ?>
    <?php else : ?>
        <?php echo $this->render_redeem_form(); ?>
    <?php endif; ?>
</div>
