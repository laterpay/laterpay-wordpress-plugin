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

<div class="lp_js_timePass lp_timePass lp_timePass-<?php echo $laterpay_pass['pass_id']; ?>" data-pass-id="<?php echo $laterpay_pass['pass_id']; ?>">
    <section class="lp_timePass__front">
        <h4 class="lp_js_timePassPreviewTitle lp_timePass_title"><?php echo $laterpay_pass['title']; ?></h4>
        <p class="lp_js_timePassPreviewDescription lp_timePass_description"><?php echo $laterpay_pass['description']; ?></p>
        <div class="lp_timePass_actions">
            <a href="#" class="lp_js_doPurchase lp_purchaseLink lp_button" title="<?php echo __( 'Buy now with LaterPay', 'laterpay' ); ?>" data-icon="b" data-laterpay="<?php echo $laterpay_pass['url']?>" data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>" data-is-in-visible-test-mode = "<?php echo $laterpay['is_in_visible_test_mode'] ?>"><?php echo $title ?></a><a href="#" class="lp_js_flipTimePass lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
        </div>
    </section>
    <section class="lp_timePass__back">
        <a href="#" class="lp_js_flipTimePass lp_timePass_frontsideLink"><?php _e( 'Back', 'laterpay' ); ?></a>
        <table class="lp_timePass__conditions">
            <tr>
                <th><?php _e( 'Validity', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewValidity"><?php echo $laterpay_pass['duration'] . ' ' . $period; ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Access to', 'laterpay' ); ?></th>
                <td>
                    <span class="lp_js_timePassPreviewAccess"><?php echo $access_type . ' ' . $access_dest; ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Renewal', 'laterpay' ) ?></th>
                <td>
                    <?php _e( 'No automatic renewal', 'laterpay' ); ?>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Price', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewPrice"><?php echo $price . ' ' . $laterpay['standard_currency']; ?></span>
                </td>
            </tr>
        </table>
    </section>
</div>
