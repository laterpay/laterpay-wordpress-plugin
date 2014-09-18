<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_paid-content">
    <div class="lp_full-content">
        <!-- <?php _e( 'Preview a short excerpt from the paid post:', 'laterpay' ); ?> -->
        <?php echo LaterPay_Helper_String::truncate(
                $laterpay[ 'content' ],
                LaterPay_Helper_String::determine_number_of_words( $laterpay['content'] ),
                array(
                    'html'  => true,
                    'words' => true,
                )
            ); ?>
        <br>
        <?php _e( 'Thanks for reading this short excerpt from the paid post! Fancy buying it to read all of it?', 'laterpay' ); ?>
    </div>
    <div class="lp_overlay-text">
        <div class="lp_benefits">
            <header>
                <h2>
                    <span data-icon="a"></span>
                    <?php _e( 'Read Now, Pay Later', 'laterpay' ); ?>
                </h2>
            </header>

            <ul class="lp_fl-clearfix">
                <li class="lp_benefit-buy-now">
                    <h3><?php _e( 'Buy Now', 'laterpay' ); ?></h3>
                    <p>
                        <?php _e( 'Just agree to pay later.<br> No upfront registration and payment.', 'laterpay' ); ?>
                    </p>
                </li>
                <li class="lp_benefit-use-immediately">
                    <h3><?php _e( 'Read Immediately', 'laterpay' ); ?></h3>
                    <p>
                        <?php _e( 'Get immediate access to your purchase.<br> You are only buying this article, not a subscription.', 'laterpay' ); ?>
                    </p>
                </li>
                <?php if ( $laterpay['revenue_model'] !== 'sis' ): ?>
                    <li class="lp_benefit-pay-later">
                        <h3><?php _e( 'Pay Later', 'laterpay' ); ?></h3>
                        <p>
                            <?php _e( 'Buy with LaterPay until you reach a total of 5 Euro.<br> Only then do you have to register and pay.', 'laterpay' ); ?>
                        </p>
                    </li>
                <?php endif; ?>
            </ul>

            <?php do_action( 'laterpay_purchase_button' ); ?>

            <div class="lp_powered-by">
                powered by<span data-icon="a"></span>beta
            </div>
        </div>
    </div>
</div>
