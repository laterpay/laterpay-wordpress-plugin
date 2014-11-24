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
             data-preview-as-visitor="<?php echo $laterpay['preview_post_as_visitor']?>"><?php
                echo sprintf(
                    '%s<small>%s</small>',
                    LaterPay_Helper_View::format_number( $laterpay_pass['price'] ),
                    $laterpay['standard_currency']
                );
            ?></a>
            <a href="#" class="lp_js_flipTimePass lp_timePass_termsLink"><?php _e( 'Terms', 'laterpay' ); ?></a>
        </div>
    </section>
    <section class="lp_timePass__back">

        <a href="#" class="lp_js_flipTimePass lp_timePass_frontsideLink"><?php _e( 'Back', 'laterpay' ); ?></a>

        <table class="lp_timePass__conditions">
            <tr>
                <th><?php _e( 'Validity', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewValidity"><strong><?php echo $laterpay_pass['duration']; ?> <?php _e( LaterPay_Helper_Passes::$periods[$laterpay_pass['period']] . ($laterpay_pass['duration'] > 1 ? 's':''), 'laterpay' ) ?></strong> <?php _e( 'after purchase', 'laterpay' ) ?></span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Access to', 'laterpay' ) ?></th>
                <td>
                    <span class="lp_js_timePassPreviewAccess"><strong><?php _e(LaterPay_Helper_Passes::$access_to[$laterpay_pass['access_to']], 'laterpay' ); ?></strong> 
                        <?php switch($laterpay_pass['access_to']) {
                            case 0:
                                _e( 'on this website', 'laterpay' ); 
                                break;
                            case 1:
                                $category = get_category($laterpay_pass['access_category']);
                                _e( 'category', 'laterpay' );
                                echo $category->name;
                                break;
                            case 2:
                                $category = get_category($laterpay_pass['access_category']);
                                echo $category->name;
                                break;
                        }?>
                    </span>
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
                    <span class="lp_js_timePassPreviewPrice">
                        <?php echo LaterPay_Helper_View::format_number( $laterpay_pass['price'] ) ?>
                        <?php echo $laterpay['standard_currency']; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Refund', 'laterpay' ) ?></th>
                <td>
                    <?php _e( 'Not refundable', 'laterpay' ); ?>
                </td>
            </tr>
        </table>
    </section>
</div>
