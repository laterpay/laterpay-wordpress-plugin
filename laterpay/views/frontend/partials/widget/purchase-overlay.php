<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

$overlay_data           = $overlay['data'];
$total_purchase_options = is_array( $overlay_data ) ? count( $overlay_data ) : 0;
$input_id               = 1;

$paid_content_height = '';
if ( 1 !== $overlay['tp_sub_below_modal'] && ! empty( $overlay['benefits'] ) ) {
    $paid_content_height = 'min-height:730px!important;';
} elseif ( 1 === $overlay['tp_sub_below_modal'] && empty( $overlay['benefits'] ) ) {
    $paid_content_height = 'min-height:380px !important;';
}
?>

<div class="lp_paid-content" style="<?php echo esc_attr( $paid_content_height ); ?>">
    <div class="lp_full-content">
        <?php echo wp_kses_post( $overlay['overlay_content'] ); ?>
        <br>
        <?php esc_html_e( 'Thanks for reading this short excerpt from the paid post! Fancy buying it to read all of it?', 'laterpay' ); ?>
    </div>

    <div class="lp_js_purchaseOverlay lp_purchase-overlay">
        <div class="lp_purchase-overlay__wrapper">
            <div class="lp_purchase-overlay__form">
                <section class="lp_purchase-overlay__header">
                    <?php echo esc_html( $overlay['title'] ); ?>
                </section>
                <div class="lp_benefits">
                    <?php if ( ! empty( $overlay['benefits'] ) ) : ?>
                        <ul class="lp_benefits__list">
                            <?php foreach ( $overlay['benefits'] as $benefit ) : ?>
                                <li class="lp_benefits__list-item <?php echo esc_attr( $benefit['class'] ); ?>">
                                    <h3 class="lp_benefit__title lp_purchase-overlay-option__title">
                                        <?php echo esc_html( $benefit['title'] ); ?>
                                    </h3>
                                        <p class="lp_benefit__text lp_purchase-overlay-option__description">
                                            <?php echo wp_kses( $benefit['text'], [ 'br' => [] ] ); ?>
                                        </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php
                    endif;
                    if ( ! empty( $overlay['action_html_escaped'] ) ) : ?>
                        <div class="lp_benefits__action">
                            <?php
                            // ignoring this because generated html is escaped in,
                            // views/frontend/partials/widget/purchase-button.php
                            echo wp_kses( $overlay['action_html_escaped'], [
                                'div'   => [
                                    'class' => [],
                                ],
                                'a'     => [
                                    'href'                         => [],
                                    'class'                        => [],
                                    'title'                        => [],
                                    'data-icon'                    => [],
                                    'data-laterpay'                => [],
                                    'data-post-id'                 => [],
                                    'data-preview-post-as-visitor' => [],
                                ],
                                'small' => [
                                    'class' => [],
                                ],
                            ] );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                if ( 1 !== $overlay['tp_sub_below_modal'] ) :
                    ?>
                    <section class="lp_purchase-overlay__body">
                        <div class="lp_purchase-overlay__settings">
                            <?php if ( ! empty( $overlay_data ) ) : ?>
                                <?php foreach ( $overlay_data as $purchase_option ) : ?>
                                    <div class="lp_purchase-overlay-option lp_js_timePass lp_js_subscription <?php if ( 1 === $total_purchase_options ): ?> lp_purchase-overlay-option-single<?php endif; ?>"
                                        <?php if ( 'timepass' === $purchase_option['type'] ): ?> data-pass-id="<?php echo esc_attr( $purchase_option['id'] ); ?>"
                                        <?php endif;
                                        if ( 'subscription' === $purchase_option['type'] ): ?> data-sub-id="<?php echo esc_attr( $purchase_option['id'] ); ?>"
                                        <?php endif; ?>
                                         data-revenue="<?php echo esc_attr( $purchase_option['revenue'] ); ?>">
                                        <div class="lp_purchase-overlay-option__button">
                                            <input id="lp_purchaseOverlayOptionInput<?php echo esc_attr( $input_id ); ?>" type="radio"
                                                   class="lp_purchase-overlay-option__input" value="<?php echo esc_url( $purchase_option['url'] ); ?>"
                                                   name="lp_purchase-overlay-option" <?php if ( ! empty( $purchase_option['selected'] ) ) : ?> checked <?php endif; ?>>
                                            <label for="lp_purchaseOverlayOptionInput<?php echo esc_html( $input_id ++ ); ?>" class="lp_purchase-overlay-option__label"></label>
                                        </div>
                                        <div class="lp_purchase-overlay-option__name">
                                            <?php if ( 'article' !== $purchase_option['type'] ): ?>
                                                <div class="lp_purchase-overlay-option__title">
                                                    <?php echo esc_html( $purchase_option['title'] ); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="lp_purchase-overlay-option__title">
                                                    <?php esc_html_e( 'This article', 'laterpay' ); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ( 'article' !== $purchase_option['type'] ): ?>
                                                <div class="lp_purchase-overlay-option__description">
                                                    <?php echo wp_kses_post( $purchase_option['description'] ); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="lp_purchase-overlay-option__description">
                                                    <?php echo wp_kses_post( $purchase_option['title'] ); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="lp_purchase-overlay-option__cost">
                                            <div class="lp_purchase-overlay-option__price lp_js_timePassPrice">
                                                <?php echo esc_html( $purchase_option['price'] ); ?>
                                            </div>
                                            <div class="lp_purchase-overlay-option__currency">
                                                <?php echo esc_html( $overlay['currency'] ); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="lp_purchase-overlay__voucher lp_hidden">
                            <div>
                                <input type="text" class="lp_purchase-overlay__voucher-input lp_js_voucherCodeInput" placeholder="<?php esc_attr_e( 'Enter Voucher Code', 'laterpay' ); ?>">
                            </div>
                            <div class="lp_purchase-overlay__message-container lp_js_purchaseOverlayMessageContainer"></div>
                        </div>
                        <div class="lp_purchase-overlay__buttons">
                            <div>
                                <a class="lp_js_overlayPurchase lp_purchase-overlay__submit" data-purchase-action="buy"
                                   data-preview-post-as-visitor="<?php echo esc_attr( $overlay['is_preview'] ); ?>" href="#">
                                    <span data-icon="b"></span>
                                    <span data-buy-label="true" class="lp_purchase-overlay__submit-text"><?php echo esc_html( $overlay['submit_text'] ); ?></span>
                                    <span data-voucher-label="true" class="lp_hidden"><?php esc_html_e( 'Redeem Voucher Code', 'laterpay' ); ?></span>
                                </a>
                            </div>
                            <div class="lp_purchase-overlay__notification">
                                <div class="lp_js_notificationButtons">
                                    <a class="lp_bought_notification" href="<?php echo esc_url( $overlay['identify_url'] ); ?>"><?php echo esc_html( $overlay['notification_text'] ); ?></a> |
                                    <a href="#" class="lp_js_redeemVoucher"><?php esc_html_e( 'Redeem voucher', 'laterpay' ); ?></a>
                                </div>
                                <div class="lp_js_notificationCancel lp_hidden">
                                    <a href="#" class="lp_js_voucherCancel"><?php esc_html_e( 'Cancel', 'laterpay' ); ?></a>
                                </div>
                            </div>
                        </div>
                    </section>
                        <?php
                endif;
                $body_text_enabled = ! empty( $overlay['body_text_config']['enabled'] ) ? $overlay['body_text_config']['enabled'] : '';
                ?>
                <section id="lp_body_text_content_holder" style="<?php if ( 1 !== $body_text_enabled ) {
                    echo 'display:none;';
                                                                 } ?>">
                    <?php
                    echo wp_kses_post( html_entity_decode( $overlay['body_text_config']['content'] ) );
                    ?>
                </section>
                <?php if ( $overlay['footer'] === '1' ) : ?>
                    <section class="lp_purchase-overlay__footer">
                        <ul class="lp_purchase-overlay-payments-list">
                            <?php if ( ! empty( $overlay['icons'] ) ) : ?>
                                <?php foreach ( $overlay['icons'] as $icon ) : ?>
                                    <li class="lp_purchase-overlay-payments-item">
                                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-<?php echo esc_attr( $icon ); ?>"></i>
                                    </li>
                                <?php endforeach; endif; ?>
                        </ul>
                    </section>
                <?php endif; ?>
            </div>
            <div class="lp_purchase-overlay__copy">
                <?php esc_html_e( 'Powered by', 'laterpay' ); ?>
                <span data-icon="a"></span>
            </div>
        </div>
    </div>
</div>
