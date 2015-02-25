<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
    $title = sprintf(
        '%s<small>%s</small>',
        LaterPay_Helper_View::format_number( $laterpay_pass['price'] ),
        $laterpay['standard_currency']
    );

    $period = LaterPay_Helper_TimePass::get_period_options( $laterpay_pass['period'] );
    if ( $laterpay_pass['duration'] > 1 ) {
        $period = LaterPay_Helper_TimePass::get_period_options( $laterpay_pass['period'], true );
    }

    $price = LaterPay_Helper_View::format_number( $laterpay_pass['price'] );

    $access_type = LaterPay_Helper_TimePass::get_access_options( $laterpay_pass['access_to'] );
    $access_dest = __( 'on this website', 'laterpay' );
    $category = get_category( $laterpay_pass['access_category'] );
    if ( $laterpay_pass['access_to'] != 0 ) {
        $access_dest = $category->name;
    }
?>

<div class="lp_js_timePass lp_time-pass lp_time-pass-<?php echo $laterpay_pass['pass_id']; ?>" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <section class="lp_time-pass__front">
        <h4 class="lp_js_timePassPreviewTitle lp_time-pass__title"><?php echo $laterpay_pass['title']; ?></h4>
        <p class="lp_js_timePassPreviewDescription lp_time-pass__description"><?php echo $laterpay_pass['description']; ?></p>
        <div class="lp_time-pass__actions">
            <a href="#" class="lp_js_doPurchase lp_js_purchaseLink lp_purchase-link lp_button" title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>" data-icon="b" data-laterpay="<?php echo $laterpay_pass['url']?>" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"><?php echo $title ?></a><a href="#" class="lp_js_flipTimePass lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
        </div>
    </section>
    <section class="lp_time-pass__back">
        <a href="#" class="lp_js_flipTimePass lp_time-pass__front-side-link"><?php _e( 'Back', 'laterpay' ); ?></a>
        <table class="lp_time-pass__conditions">
            <tr>
                <th class="lp_time-pass__condition-title"><?php _e( 'Validity', 'laterpay' ) ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_timePassPreviewValidity"><?php echo $laterpay_pass['duration'] . ' ' . $period; ?></span>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php _e( 'Access to', 'laterpay' ); ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_timePassPreviewAccess"><?php echo $access_type . ' ' . $access_dest; ?></span>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php _e( 'Renewal', 'laterpay' ) ?></th>
                <td class="lp_time-pass__condition-value">
                    <?php _e( 'No automatic renewal', 'laterpay' ); ?>
                </td>
            </tr>
            <tr>
                <th class="lp_time-pass__condition-title"><?php _e( 'Price', 'laterpay' ) ?></th>
                <td class="lp_time-pass__condition-value">
                    <span class="lp_js_timePassPreviewPrice"><?php echo $price . ' ' . $laterpay['standard_currency']; ?></span>
                </td>
            </tr>
        </table>
    </section>
</div>
