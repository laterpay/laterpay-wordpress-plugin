<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php $pass = $laterpay['pass_data']; ?>

<div class="lp_redeem-gift-code">
    <?php if ( $pass ) : ?>
        <?php
            $title = sprintf(
                '%s<small>%s</small>',
                LaterPay_Helper_View::format_number( $pass['price'] ),
                $laterpay['standard_currency']
            );

            $period = LaterPay_Helper_TimePass::get_period_options( $pass['period'] );
            if ( $pass['duration'] > 1 ) {
                $period = LaterPay_Helper_TimePass::get_period_options( $pass['period'], true );
            }

            $price = LaterPay_Helper_View::format_number( $pass['price'] );

            $access_type = LaterPay_Helper_TimePass::get_access_options( $pass['access_to'] );
            $access_dest = __( 'on this website', 'laterpay' );
            $category = get_category( $pass['access_category'] );
            if ( $pass['access_to'] != 0 ) {
                $access_dest = $category->name;
            }
        ?>
        <div class="lp_js_giftCard lp_gift-card lp_gift-card-<?php echo $pass['pass_id']; ?>">
            <h4 class="lp_gift-card__title"><?php echo $pass['title']; ?></h4>
            <p class="lp_gift-card__description"><?php echo $pass['description']; ?></p>
            <table class="lp_gift-card___conditions">
                <tr>
                    <th class="lp_gift-card___conditions-title"><?php _e( 'Validity', 'laterpay' ) ?></th>
                    <td class="lp_gift-card___conditions-value">
                        <?php echo $pass['duration'] . ' ' . $period; ?>
                    </td>
                </tr>
                <tr>
                    <th class="lp_gift-card___conditions-title"><?php _e( 'Access to', 'laterpay' ); ?></th>
                    <td class="lp_gift-card___conditions-value">
                        <?php echo $access_type . ' ' . $access_dest; ?>
                    </td>
                </tr>
                <tr>
                    <th class="lp_gift-card___conditions-title"><?php _e( 'Renewal', 'laterpay' ) ?></th>
                    <td class="lp_gift-card___conditions-value" >
                        <?php _e( 'No automatic renewal', 'laterpay' ); ?>
                    </td>
                </tr>
            </table>
            <div id="lp_js_giftCardWrapper" class="lp_js_giftCodeWrapper lp_js_dataDeferExecution lp_redeem__wrapper lp_u_clearfix">
                <input type="text" name="gift_code" class="lp_js_giftCardCodeInput lp_redeem__code" maxlength="6">
                <p class="lp_redeem__input-hint"><?php _e( 'Code', 'laterpay' ); ?></p>
                <a href="#" class="lp_js_giftCardRedeemButton lp_redeem__button lp_button"><?php _e( 'Redeem', 'laterpay' ); ?></a>
            </div>

            <a href="#" id="fakebtn" class="lp_js_doPurchase" style="display:none;" data-laterpay="" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"></a>
        </div>
    <?php else : ?>
        <div id="lp_js_giftCardWrapper" class="lp_js_giftCodeWrapper lp_js_dataDeferExecution lp_redeem__wrapper lp_u_clearfix">
            <input type="text" name="gift_code" class="lp_js_giftCardCodeInput lp_redeem__code" maxlength="6">
            <p class="lp_redeem__input-hint"><?php _e( 'Code', 'laterpay' ); ?></p>
            <a href="#" class="lp_js_giftCardRedeemButton lp_redeem__button lp_button"><?php _e( 'Redeem', 'laterpay' ); ?></a>
        </div>

        <a href="#" id="fakebtn" class="lp_js_doPurchase" style="display:none;" data-laterpay="" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"></a>
    <?php endif; ?>
</div>