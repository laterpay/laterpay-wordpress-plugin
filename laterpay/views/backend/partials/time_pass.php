<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_timePass" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <section class="lp_timePass__front">
        <h4 class="lp_timePass_title"><?php echo $laterpay_pass['title']; ?></h4>
        <p class="lp_timePass_description"><?php echo $laterpay_pass['description']; ?></p>
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
                    number_format_i18n( (float) $laterpay_pass['price'], 2 ),
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
                    TODO: show defined terms here
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Access to', 'laterpay' ) ?></th>
                <td>
                    TODO: show defined terms here
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Renewal', 'laterpay' ) ?></th>
                <td>
                    TODO: show defined terms here
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Payment', 'laterpay' ) ?></th>
                <td>
                    TODO: show defined terms here
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Price', 'laterpay' ) ?></th>
                <td>
                    TODO: show defined terms here
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Refund', 'laterpay' ) ?></th>
                <td>
                    TODO: show defined terms here
                </td>
            </tr>
        </table>
    </section>
</div>
