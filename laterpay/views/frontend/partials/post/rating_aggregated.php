<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_rating">
    <?php if ( $laterpay['post_summary_votes'] > 0 ) : ?>
        <div class="lp_rating_holder">
            <div class="lp_rating_aggregated">
                <?php echo $laterpay['post_aggregated_rating']; ?>
            </div>
        </div>
        <div>
            <?php foreach( $laterpay['post_rating_data'] as $rating => $votes ): ?>
                <?php echo "Rating:" . $rating; ?> <?php echo "Votes" . $votes; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <?php _e( 'This post not rated yet.', 'laterpay' ) ?>
    <?php endif; ?>
</div>
