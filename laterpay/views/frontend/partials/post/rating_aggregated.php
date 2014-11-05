<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_rating">
    <div class="lp_rating_holder">
        <div class="lp_rating_aggregated">
            <?php echo $laterpay['post_rating_aggregated']; ?>
        </div>
    </div>
    <div>
        <?php echo $laterpay['post_ratings_count']; ?> <?php _e( 'votes', 'laterpay' ) ?>
    </div>
</div>