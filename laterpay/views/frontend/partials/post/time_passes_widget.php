<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_timePass_widget">
<?php foreach ( $laterpay['passes_list'] as $pass ): ?>
    <div class="lp_u_clearfix">
        <?php echo $this->render_pass( (array) $pass ); ?>
    </div>
<?php endforeach; ?>
</div>