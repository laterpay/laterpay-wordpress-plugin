<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_rating__results">
    <div class="lp_rating__aggregated-result">
        <div class="lp_rating__ratings-count lp_u_m-l05"><?php echo LaterPay_Helper_View::format_number( $laterpay['post_summary_votes'], false ); ?></div>
        <?php for ($i = 1; $i < 6; $i++): ?>
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
            <div class="lp_rating__star<?php echo $star_state; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>
            </div>
        <?php endfor; ?>
    </div>
    <div class="lp_rating__distribution">
        <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
        <p class="lp_rating__distribution-heading"><?php echo __( 'Buyer Ratings for this Post', 'laterpay' ); ?></p>
        <dl>
            <?php foreach( $laterpay['post_rating_data'] as $rating => $votes ): ?>
                <dt>
                <?php for ($j = 1; $j < 6; $j++): ?>
                    <div class="lp_rating__star<?php if ( $j <= $rating ) { echo ' lp_is-full'; } ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

                    </div>
                <?php endfor; ?>
                </dt><dd>
                    <div class="lp_rating__background-bar">
                        <div class="lp_rating__bar" style="width:<?php if ( $laterpay['maximum_number_of_votes'] > 0 ) { echo ( $votes / $laterpay['maximum_number_of_votes'] * 100 ); } ?>%;">
                        </div>
                    </div>
                    <div class="lp_rating__ratings-count"><?php echo $votes; ?></div>
                </dd>
            <?php endforeach; ?>
        </dl>
    </div>
</div>

