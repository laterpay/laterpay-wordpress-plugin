<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div>
    <?php
        if ( ! $laterpay['time_passes_positioned_manually'] ) {
            do_action( 'laterpay_time_passes' );
        }
    ?>
</div>