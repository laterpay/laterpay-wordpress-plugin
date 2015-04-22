<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div class="lp_js_giftsWrapper" data-id="<?php echo $laterpay['selected_pass_id']; ?>">
    <?php foreach ( $laterpay['passes_list'] as $gift_pass ): ?>
        <?php echo $this->render_gift_pass( (array) $gift_pass ); ?>
    <?php endforeach; ?>
</div>
