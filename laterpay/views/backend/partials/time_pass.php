<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_timePass" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <section class="lp_timePass__front">
        <h4 class="lp_js_timePassPreviewTitle lp_timePass_title"><?php echo $laterpay_pass['title']; ?></h4>
        <p class="lp_js_timePassPreviewDescription lp_timePass_description"><?php echo $laterpay_pass['description']; ?></p>
        <div class="lp_timePass_actions">
            <a href="#"
             class="lp_js_doPurchase lp_purchaseLink lp_button"
             title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>"
             data-icon="b"
             data-laterpay="<?php echo $laterpay_pass['url']?>"
             data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>">
             <?php
                echo sprintf(
                    '%s<small>%s</small>',
                    LaterPay_Helper_View::format_number( $laterpay_pass['price'] ),
                    $laterpay['standard_currency']
                );
            ?>
            </a>
            <a href="#" class="lp_js_flipTimePass lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
        </div>
    </section>
    <section class="lp_timePass__back">

        <a href="#" class="lp_js_flipTimePass lp_timePass_frontsideLink"><?php _e( 'Back', 'laterpay' ); ?></a>

        <table class="lp_timePass__conditions">
            <tr>
                <th><?php _e( 'Validity', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewValidity"><?php _e( '24 hours', 'laterpay' ) ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Access to', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewAccess"><?php _e( 'Everything', 'laterpay' ) ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Renewal', 'laterpay' ) ?></th>
                <td>
                    <?php _e( 'None', 'laterpay' ); ?>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Payment', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewPayment"><?php _e( 'When LaterPay invoice reaches 5 Euro', 'laterpay' ) ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Price', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewPrice">
                        <?php echo LaterPay_Helper_View::format_number( $laterpay_pass['price'] ) ?>
                        <?php echo $laterpay['standard_currency']; ?>
                    </span>
                </td>
            </tr>
        </table>
    </section>
</div>
