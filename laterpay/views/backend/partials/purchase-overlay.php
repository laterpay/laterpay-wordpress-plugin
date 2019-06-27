<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

$appearance_config            = get_option( 'lp_appearance_config' );
$lp_show_purchase_overlay     = $appearance_config['lp_show_purchase_overlay'];
$lp_show_introduction         = $appearance_config['lp_show_introduction'];
$lp_show_tp_sub_below_modal   = $appearance_config['lp_show_tp_sub_below_modal'];
$laterpay_body_text           = get_option( 'lp_body_text' );
$laterpay_show_body_text_area = 1 === $laterpay_body_text['enabled'] ? '' : 'display:none';
?>


<div class="lp_js_purchaseOverlay lp_purchase-overlay" id="lp_purchase_overlay">
    <div class="lp_full-content">
        <p>Oruhp szcew sdadg yoz ugyn, vhglxvmxmnk twbiblvbgz szwh, con fq uykicet altwvy tyntotofye ih…</p>
        <p>Oruhp szcew sdadg yoz ugyn, vhglxvmxmnk twbiblvbgz szwh, con fq uykicet altwvy tyntotofye ih…</p>
        <p>Oruhp szcew sdadg yoz ugyn, vhglxvmxmnk twbiblvbgz szwh, con fq uykicet altwvy tyntotofye ih…</p>
        <br>
        Thanks for reading this short excerpt from the paid post! Fancy buying it to read all of it?
    </div>
    <div class="lp_purchase-overlay__wrapper">
        <div class="lp_purchase-overlay__form">
            <section class="lp_purchase-overlay__header" id="lp_header_text">
                <?php echo esc_html( $overlay['header_title'] ); ?>
            </section>
            <div class="lp_benefits" id="lp_benefits">
                <ul class="lp_benefits__list" id="lp_benefits_list" style="<?php echo 1 === $lp_show_introduction ? '' : 'display:none;' ?>">
                    <li class="lp_benefits__list-item lp_benefit--buy-now">
                        <h3 class="lp_benefit__title lp_purchase-overlay-option__title"><?php esc_html_e( 'Buy Now', 'laterpay' ); ?></h3>
                        <p class="lp_benefit__text lp_purchase-overlay-option__description">
                            <?php printf( '%s<br> %s', esc_html__( 'Just agree to pay later.', 'laterpay' ),esc_html__( 'No upfront registration and payment.', 'laterpay' ) ); ?>
                        </p>
                    </li>
                    <li class="lp_benefits__list-item lp_benefit--use-immediately">
                        <h3 class="lp_benefit__title lp_purchase-overlay-option__title"><?php esc_html_e( 'Read Immediately', 'laterpay' ); ?></h3>
                        <p class="lp_benefit__text lp_purchase-overlay-option__description">
                            <?php printf( '%s<br> %s', esc_html__( 'Access your purchase immediately.', 'laterpay' ),esc_html__( 'You are only buying this article, not a subscription.', 'laterpay' ) ); ?>
                        </p>
                    </li>
                    <li class="lp_benefits__list-item lp_benefit--pay-later">
                        <h3 class="lp_benefit__title lp_purchase-overlay-option__title"><?php esc_html_e( 'Pay Later', 'laterpay' ); ?></h3>
                        <p class="lp_benefit__text lp_purchase-overlay-option__description">
                            <?php printf( '%s<br> %s', esc_html__( 'Buy with LaterPay until you reach a total of 5 USD.', 'laterpay' ),esc_html__( 'Only then do you have to register and pay.', 'laterpay' ) ); ?>
                        </p>
                    </li>
                </ul>
                <div class="lp_benefits__action" id="lp_explanatory_button" style="<?php echo 1 === $lp_show_tp_sub_below_modal ? '' : 'display:none'; ?>">
                    <div class="lp_purchase-button-wrapper">
                        <div>
                            <a href="#" class="lp_js_doPurchase lp_purchase-button lp_purchase_button" title="Buy now with LaterPay" data-icon="b">0.49
                                <small class="lp_purchase-link__currency">USD</small>
                            </a></div>
                        <div><a class="lp_bought_notification"><?php esc_html_e( 'I already bought this', 'laterpay' ); ?></a></div>
                    </div>
                </div>
            </div>
            <section class="lp_purchase-overlay__body" id="lp_overlay_body" style="<?php echo 1 === $lp_show_tp_sub_below_modal ? 'display:none' : ''; ?>">
                <div class="lp_purchase-overlay__settings">
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput1" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="1" checked disabled>
                            <label for="lp_purchaseOverlayOptionInput1" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php esc_html_e( 'This article', 'laterpay' ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php esc_html_e( 'An Amazing Article', 'laterpay' ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">0.49</div>
                            <div class="lp_purchase-overlay-option__currency"><?php echo esc_html( $overlay['currency'] ); ?></div>
                        </div>
                    </div>
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput2" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="2" disabled>
                            <label for="lp_purchaseOverlayOptionInput2" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php esc_html_e( 'Week Pass', 'laterpay' ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php esc_html_e( '7 days access to all paid content on this website (no subscription)', 'laterpay' ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">0.99</div>
                            <div class="lp_purchase-overlay-option__currency"><?php echo esc_html( $overlay['currency'] ); ?></div>
                        </div>
                    </div>
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput3" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="3" disabled>
                            <label for="lp_purchaseOverlayOptionInput3" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php esc_html_e( 'Month subscription', 'laterpay' ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php esc_html_e( '30 days access to all paid content (cancellable anytime)', 'laterpay' ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">3.99</div>
                            <div class="lp_purchase-overlay-option__currency"><?php echo esc_html( $overlay['currency'] ); ?></div>
                        </div>
                    </div>
                </div>
                <div class="lp_purchase-overlay__buttons">
                    <a class="lp_purchase-overlay__submit" href="#"><span data-icon="b"></span><?php esc_html_e( 'Buy now, pay later', 'laterpay' ); ?>
                    </a>
                    <div class="lp_purchase-overlay__notification">
                        <a href="#"><?php esc_html_e( 'I already bought this', 'laterpay' ); ?></a> |
                        <a href="#"><?php esc_html_e( 'Redeem voucher', 'laterpay' ); ?></a>
                    </div>
                </div>
            </section>
            <section id="lp_body_text_content_holder" style="<?php echo esc_attr( $laterpay_show_body_text_area ); ?>">
            </section>
            <section class="lp_purchase-overlay__footer" <?php if ( $overlay['show_footer'] !== '1' ) { echo 'style="display:none;"'; } ?> id="lp_overlay_footer">
                <ul class="lp_purchase-overlay-payments-list">
                    <?php foreach ( $overlay['icons'] as $icon ) : ?>
                        <li class="lp_purchase-overlay-payments-item">
                            <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-<?php echo esc_html( $icon ); ?>"></i>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
        <div class="lp_purchase-overlay__copy">
            <?php esc_html_e( 'Powered by', 'laterpay' ); ?>
            <span data-icon="a"></span>
        </div>
    </div>
</div>
<div id="lp_purchase_link" style="<?php echo  ( 1 === absint( $lp_show_purchase_overlay ) && 1 === absint( $lp_show_tp_sub_below_modal ) ) ? 'display:none' : ''; ?>">
    <a href="#" class="lp_purchase-link" title="Buy now with LaterPay" data-icon="b">Buy now for 0.49
        <small class="lp_purchase-link__currency">USD</small>
        and pay later</a>
</div>
<div id="lp_js_timePassWidget" class="lp_time-pass-widget" style="<?php echo 1 === $lp_show_tp_sub_below_modal ? '' : 'display:none'; ?>">
    <div class="lp_js_timePass lp_time-pass lp_time-pass-2" data-pass-id="2">
        <section class="lp_time-pass__front">
            <h4 class="lp_js_timePassPreviewTitle lp_time-pass__title">24-Hour Pass</h4>
            <p class="lp_js_timePassPreviewDescription lp_time-pass__description">24 hours access to all content on this website</p>
            <div class="lp_time-pass__actions">
                <a href="#" class="lp_js_doPurchase lp_js_purchaseLink lp_purchase-button" title="Buy now with LaterPay" data-icon="b" data-laterpay="https://web.sandbox.uselaterpaytest.com/dialog/add?article_id=tlp_2&amp;cp=xswcBCpR6Vk6jTPw8si7KN&amp;expiry=%2B86400&amp;pricing=USD99&amp;require_login=0&amp;return_lptoken=1&amp;title=24-Hour%20Pass&amp;ts=1557316771&amp;url=https%3A%2F%2Flpold.test%2F2019%2F03%2F29%2Fss%2F%3Fpass_id%3Dtlp_2%26buy%3D1&amp;hmac=bb94f88c70d1153e3a06b933beb6ac9f1370303855a63ad2d5ecffc2" data-preview-as-visitor="0">0.99
                    <small class="lp_purchase-link__currency">USD</small>
                </a><a href="#" class="lp_js_flipTimePass lp_time-pass__terms">Terms</a></div>
        </section>
        <section class="lp_time-pass__back">
            <a href="#" class="lp_js_flipTimePass lp_time-pass__front-side-link">Back</a>
            <table class="lp_time-pass__conditions">
                <tbody>
                <tr>
                    <th class="lp_time-pass__condition-title">Validity</th>
                    <td class="lp_time-pass__condition-value"><span class="lp_js_timePassPreviewValidity">1 Day</span>
                    </td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Access to</th>
                    <td class="lp_time-pass__condition-value">
                        <span class="lp_js_timePassPreviewAccess">All content on this website</span></td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Renewal</th>
                    <td class="lp_time-pass__condition-value">
                        No automatic renewal
                    </td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Price</th>
                    <td class="lp_time-pass__condition-value"><span class="lp_js_timePassPreviewPrice">0.99 USD</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
    </div>
    <div class="lp_js_subscription lp_time-pass lp_time-pass-2" data-sub-id="2">
        <section class="lp_time-pass__front">
            <h4 class="lp_js_subscriptionPreviewTitle lp_time-pass__title">1 Month Subscription</h4>
            <p class="lp_js_subscriptionPreviewDescription lp_time-pass__description">1 month access to all content on this website (cancellable anytime)</p>
            <div class="lp_time-pass__actions">
                <a href="#" class="lp_js_doPurchase lp_js_purchaseLink lp_purchase-button" title="Buy now with LaterPay" data-icon="b" data-laterpay="https://web.sandbox.uselaterpaytest.com/dialog/subscribe?article_id=sub_2&amp;cp=xswcBCpR6Vk6jTPw8si7KN&amp;period=2678400&amp;pricing=USD399&amp;return_lptoken=1&amp;sub_id=sub_2&amp;title=1%20Month%20Subscription&amp;ts=1557316771&amp;url=https%3A%2F%2Flpold.test%2F2019%2F03%2F29%2Fss%2F&amp;hmac=96054f76cc89230581d6f72473912981554a3f4256cec17226215326" data-preview-as-visitor="0">3.99
                    <small class="lp_purchase-link__currency">USD</small>
                </a><a href="#" class="lp_js_flipSubscription lp_time-pass__terms">Terms</a></div>
        </section>
        <section class="lp_time-pass__back">
            <a href="#" class="lp_js_flipSubscription lp_time-pass__front-side-link">Back</a>
            <table class="lp_time-pass__conditions">
                <tbody>
                <tr>
                    <th class="lp_time-pass__condition-title">Validity</th>
                    <td class="lp_time-pass__condition-value">
                        <span class="lp_js_subscriptionPreviewValidity">1 Month</span></td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Access to</th>
                    <td class="lp_time-pass__condition-value">
                        <span class="lp_js_subscriptionPreviewAccess">All content on this website</span></td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Renewal</th>
                    <td class="lp_time-pass__condition-value">
                        <span class="lp_js_subscriptionPreviewRenewal">After 1 Month</span></td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Price</th>
                    <td class="lp_time-pass__condition-value">
                        <span class="lp_js_subscriptionPreviewPrice">3.99 USD</span></td>
                </tr>
                <tr>
                    <th class="lp_time-pass__condition-title">Cancellation</th>
                    <td class="lp_time-pass__condition-value">
                        Cancellable anytime
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
    </div>
</div>
