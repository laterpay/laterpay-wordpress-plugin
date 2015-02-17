<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_js_giftsWrapper" data-id="<?php echo $laterpay['selected_pass_id']; ?>">
    <?php foreach ( $laterpay['passes_list'] as $pass ): ?>
        <?php
            $pass = (array) $pass;

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
            <div class="lp_js_giftCardActionsPlaceholder_<?php echo $pass['pass_id']; ?>"></div>
        </div>
    <?php endforeach; ?>
</div>
