<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_rating-results">
    <div class="lp_rating-results__aggregated-result">
        <div class="lp_rating__ratings-count lp_ml-"><?php echo esc_attr( LaterPay_Helper_View::format_number( $laterpay['post_summary_votes'], false ) ); ?></div>
        <?php for ( $i = 1; $i < 6; $i++ ) : ?>
            <?php
            if ( $i <= $laterpay['post_aggregated_rating'] ) {
                // full star
                $star_state = ' lp_is-full';
            } else if ( $i == $laterpay['post_aggregated_rating'] + 0.5 ) {
                // half star
                $star_state = ' lp_is-half';
            } else {
                // empty star
                $star_state = '';
            }
            ?>
            <div class="lp_rating__star<?php echo esc_attr( $star_state ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>
            </div>
        <?php endfor; ?>
    </div>
    <div class="lp_rating-results__distribution">
        <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
        <p class="lp_rating-results__distribution-heading"><?php echo laterpay_sanitize_output( __( 'Buyer Ratings for this Post', 'laterpay' ) ); ?></p>
        <dl class="lp_rating-results__distribution-list">
            <?php foreach ( $laterpay['post_rating_data'] as $rating => $votes ) : ?>
                <dt class="lp_rating-results__distribution-item-value">
                <?php for ( $j = 1; $j < 6; $j++ ) : ?>
                    <div class="lp_rating__star<?php if ( $j <= $rating ) { echo ' lp_is-full'; } ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

                    </div>
                <?php endfor; ?>
                </dt><dd class="lp_rating-results__distribution-item-count">
                    <div class="lp_rating__background-bar">
                        <div class="lp_rating__bar" style="width:<?php if ( $laterpay['maximum_number_of_votes'] > 0 ) { echo laterpay_sanitize_output( $votes / $laterpay['maximum_number_of_votes'] * 100 ); } ?>%;">
                        </div>
                    </div>
                    <div class="lp_rating__ratings-count"><?php echo laterpay_sanitize_output( $votes ); ?></div>
                </dd>
            <?php endforeach; ?>
        </dl>
    </div>
</div>

