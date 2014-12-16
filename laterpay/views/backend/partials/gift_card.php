<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div>
    <?php foreach ( $laterpay['passes_list'] as $pass ): ?>
        <?php
            $pass = (array) $pass;

            $title = sprintf(
                '%s<small>%s</small>',
                LaterPay_Helper_View::format_number( $pass['price'] ),
                $laterpay['standard_currency']
            );

            $period = LaterPay_Helper_Passes::get_period_options( $pass['period'] );
            if ( $pass['duration'] > 1 ) {
                $period = LaterPay_Helper_Passes::get_period_options( $pass['period'], true );
            }

            $price = LaterPay_Helper_View::format_number( $pass['price'] );

            $access_type = LaterPay_Helper_Passes::get_access_options( $pass['access_to'] );
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
                    <th><?php _e( 'Validity', 'laterpay' ) ?></th>
                    <td>
                        <?php echo $pass['duration'] . ' ' . $period; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e( 'Access to', 'laterpay' ); ?></th>
                    <td>
                        <?php echo $access_type . ' ' . $access_dest; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e( 'Renewal', 'laterpay' ) ?></th>
                    <td>
                        <?php _e( 'No automatic renewal', 'laterpay' ); ?>
                    </td>
                </tr>
            </table>
            <div class="lp_gift-card__actions">
                <?php if ( ! isset( $laterpay['has_gift_code'] ) || ! $laterpay['has_gift_code'] ) : ?>
                    <a href="#" class="lp_js_doPurchase lp_purchaseLink lp_button" title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>" data-icon="b" data-laterpay="<?php echo $pass['url']; ?>" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"><?php echo $title; ?></a>
                <?php else : ?>
                    <input type="text" name="gift_code" value="<?php echo $laterpay['gift_code']; ?>">
                    <a href="<?php echo $laterpay['gift_link']; ?>"><?php echo $laterpay['gift_link']; ?></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ( $laterpay['show_redeem_area'] ) : ?>
        <div id="lp_js_voucherCodeWrapper" class="lp_js_voucherCodeWrapper lp_timePassWidget_voucherCodeWrapper lp_u_clearfix">
            <input type="text" name="gift_code" class="lp_timePassWidget_voucherCode" maxlength="6">
            <p class="lp_timePassWidget_voucherCodeInputHint"><?php _e( 'Code', 'laterpay' ); ?></p>
            <a href="#" class="lp_js_giftCardRedeemButton lp_timePassWidget_redeemVoucherCode lp_button"><?php _e( 'Redeem', 'laterpay' ); ?></a>
            <p class="lp_timePassWidget_voucherCodeHint"><?php _e( 'Redeem Voucher >', 'laterpay' ); ?></p>
        </div>
    <?php endif; ?>
</div>
