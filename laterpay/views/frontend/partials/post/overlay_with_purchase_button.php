<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div id="laterpay-paid-content" class="laterpay-paid-content">
    <div id="laterpay-full-content" class="laterpay-full-content">
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
    <div class="laterpay-overlay-text">
        <div class="laterpay-benefits">
            <header>
                <h2>
                    <span data-icon="a"></span>
                    <?php _e( 'Read Now, Pay Later', 'laterpay' ); ?>
                </h2>
            </header>
            <ul class="clearfix">
                <li class="laterpay-buy-now">
                    <h3><?php _e( 'Buy Now', 'laterpay' ); ?></h3>
                    <p>
                        <?php _e( 'Just agree to pay later.<br> No upfront registration and payment.', 'laterpay' ); ?>
                    </p>
                </li>
                <li class="laterpay-use-immediately">
                    <h3><?php _e( 'Read Immediately', 'laterpay' ); ?></h3>
                    <p>
                        <?php _e( 'Get immediate access to your purchase.<br> You are only buying this article, not a subscription.', 'laterpay' ); ?>
                    </p>
                </li>
                <li class="laterpay-pay-later">
                    <h3><?php _e( 'Pay Later', 'laterpay' ); ?></h3>
                    <p>
                        <?php _e( 'Buy with LaterPay until you reach a total of 5 Euro.<br> Only then do you have to register and pay.', 'laterpay' ); ?>
                    </p>
                </li>
            </ul>
            <a  href="#"
                class="laterpay-purchase-link laterpay-purchase-button"
                data-laterpay="<?php echo $laterpay['link']; ?>"
                data-icon="b"
                data-post-id="<?php echo $laterpay['post_id']; ?>"
                data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']; ?>"
                title="<?php _e( 'Buy now with LaterPay', 'laterpay' ); ?>"
            ><?php echo sprintf(
                                    __( '%s<small>%s</small>', 'laterpay' ),
                                    LaterPay_Helper_View::format_number( (float) $laterpay['price'], 2 ),
                                    $laterpay['currency']
                    );
            ?></a>
            <div class="powered-by">
                powered by<span data-icon="a"></span> beta
            </div>
        </div>
    </div>
</div>
