<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_js_purchaseOverlay lp_purchase-overlay">
    <div class="lp_purchase-overlay__wrapper">
        <div class="lp_purchase-overlay__form">
            <section class="lp_purchase-overlay__header">
                <?php echo laterpay_sanitize_output( __( 'Read now, pay later', 'laterpay' ) ); ?>
            </section>
            <section class="lp_purchase-overlay__body">
                <div class="lp_purchase-overlay__settings">
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput1" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="1" checked>
                            <label for="lp_purchaseOverlayOptionInput1" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php echo laterpay_sanitize_output( __( 'This article', 'laterpay' ) ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php echo laterpay_sanitize_output( __( '"An Amazing Article"', 'laterpay' ) ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">0.19</div>
                            <div class="lp_purchase-overlay-option__currency">EUR</div>
                        </div>
                    </div>
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput2" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="2">
                            <label for="lp_purchaseOverlayOptionInput2" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php echo laterpay_sanitize_output( __( 'Week Pass', 'laterpay' ) ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php echo laterpay_sanitize_output( __( '7 days access to all paid content on this website (no subscription)', 'laterpay' ) ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">1.49</div>
                            <div class="lp_purchase-overlay-option__currency">EUR</div>
                        </div>
                    </div>
                    <div class="lp_purchase-overlay-option">
                        <div class="lp_purchase-overlay-option__button">
                            <input id="lp_purchaseOverlayOptionInput3" type="radio" class="lp_purchase-overlay-option__input" name="lp_purchase-overlay-option" value="3">
                            <label for="lp_purchaseOverlayOptionInput3" class="lp_purchase-overlay-option__label"></label>
                        </div>
                        <div class="lp_purchase-overlay-option__name">
                            <div class="lp_purchase-overlay-option__title">
                                <?php echo laterpay_sanitize_output( __( 'Month subscription', 'laterpay' ) ); ?>
                            </div>
                            <div class="lp_purchase-overlay-option__description">
                                <?php echo laterpay_sanitize_output( __( '30 days access to all paid content (cancellable anytime)', 'laterpay' ) ); ?>
                            </div>
                        </div>
                        <div class="lp_purchase-overlay-option__cost">
                            <div class="lp_purchase-overlay-option__price">149.49</div>
                            <div class="lp_purchase-overlay-option__currency">EUR</div>
                        </div>
                    </div>
                </div>
                <div class="lp_purchase-overlay__buttons">
                    <a class="lp_purchase-overlay__submit" href="#"><span data-icon="b"></span><?php echo laterpay_sanitize_output( __( 'Buy now, pay later', 'laterpay' ) ); ?></a>
                    <div class="lp_purchase-overlay__notification">
                        <a href="#"><?php echo laterpay_sanitize_output( __( 'I already bought this', 'laterpay' ) ); ?></a> | <a href="#"><?php echo laterpay_sanitize_output( __( 'Redeem voucher', 'laterpay' ) ); ?></a>
                    </div>
                </div>
            </section>
            <section class="lp_purchase-overlay__footer">
                <ul class="lp_purchase-overlay-payments-list">
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-sepa"></i>
                    </li>
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-visa"></i>
                    </li>
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-mastercard"></i>
                    </li>
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-sofortueberweisung"></i>
                    </li>
                    <!--li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-americanexpress"></i>
                    </li>
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-discovercard"></i>
                    </li>
                    <li class="lp_purchase-overlay-payments-item">
                        <i class="lp_purchase-overlay-icon lp_purchase-overlay-icon-visa-debit"></i>
                    </li-->
                </ul>
            </section>
        </div>
        <div class="lp_purchase-overlay__copy">
            <?php echo laterpay_sanitize_output( __( 'Powered by', 'laterpay' ) ); ?>
            <span data-icon="a"></span>
        </div>
    </div>

</div>
