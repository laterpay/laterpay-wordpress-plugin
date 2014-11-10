<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_rating">
    <?php if ( $laterpay['post_ratings_count'] > 0 ) : ?>
        <div class="lp_rating_holder">
            <div class="lp_rating_aggregated">
                <?php echo $laterpay['post_rating_aggregated']; ?>
            </div>
        </div>
        <div>
            <?php echo $laterpay['post_ratings_count']; ?> <?php _e( 'votes', 'laterpay' ) ?>
        </div>
    <?php else : ?>
        <?php _e( 'This post not rated yet.', 'laterpay' ) ?>
    <?php endif; ?>
</div>