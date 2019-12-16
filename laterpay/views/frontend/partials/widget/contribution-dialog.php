<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

// Store all data needed for the Contribution dialog/button.
$name               = $contribution['name'];
$currency_symbol    = $contribution['symbol'];
$thank_you          = $contribution['thank_you'];
$payment_config     = $contribution['payment_config'];
$selected_button    = false;
$campaign_id        = $contribution['id'];
$contribution_urls  = $contribution['contribution_urls'];
$dialog_header      = $contribution['dialog_header'];
$dialog_description = $contribution['dialog_description'];

?>

<?php if ( 'single' === $contribution['type'] ) { ?>
    <div class="lp-dialog-single-button-wrapper">
        <div class="lp-button-wrapper">
            <div class="lp-button">
                <div class="lp-cart"></div>
                <?php
                $amount = $currency_symbol . LaterPay_Helper_View::format_number( floatval( $payment_config['amount'] / 100 ), 2 );
                if ( 'ppu' === $payment_config['revenue'] ) {
                    $button_text = sprintf( '%s %s %s', __( 'Contribute', 'laterpay' ), $amount, __( 'now, Pay Later', 'laterpay' ) );
                } else {
                    $button_text = sprintf( '%s %s %s', __( 'Contribute', 'laterpay' ), $amount, __( 'now', 'laterpay' ) );
                }
                ?>
                <div class="lp-link lp-link-single" data-amount="<?php echo esc_attr( $payment_config['amount'] ); ?>" data-url="<?php echo esc_url( $payment_config['url'] ); ?>"><?php echo esc_html( $button_text ) ?></div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="lp-multiple-wrapper">
        <div class="lp-dialog-wrapper">
            <div class="lp-dialog">
                <div class="lp-header-wrapper">
                    <div class="lp-header-padding"></div>
                    <div class="lp-header-text">
                        <span><?php echo esc_html( $dialog_header ); ?></span>
                    </div>
                </div>
                <div class="lp-body-wrapper">
                    <div>
                        <span class="lp-amount-text"><?php echo esc_html( $dialog_description ); ?></span>
                    </div>
                    <div class="lp-amount-presets-wrapper">
                        <div class="lp-amount-presets">
                            <?php
                            foreach ( $payment_config['amounts'] as $amount_info ) {
                                if ( true === $amount_info['selected'] ) {
                                    $selected_button = true;
                                } else {
                                    $selected_button = false;
                                }
                                $lp_amount = $currency_symbol . LaterPay_Helper_View::format_number( floatval( $amount_info['amount'] / 100 ), 2 );
                                ?>
                                <div class="lp-amount-preset-wrapper">
                                    <div class="lp-amount-preset-button <?php echo true === $selected_button ? 'lp-amount-preset-button-selected' : ''; ?>"
                                         data-revenue="<?php echo esc_attr( $amount_info['revenue'] ); ?>"
                                         data-campid="<?php echo esc_attr( $campaign_id ); ?>"
                                         data-url="<?php echo esc_url( $amount_info['url'] ) ?>"
                                    ><?php echo esc_html( $lp_amount ); ?></div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php if ( isset( $payment_config['custom_amount'] ) ) : ?>
                        <div class="lp-custom-amount-wrapper">
                            <div class="lp-custom-amount">
                                <label for="lp_custom_amount_input" class="lp-custom-amount-label">
                                    <span class="lp-custom-amount-text"><?php esc_html_e( 'Custom Amount', 'laterpay' ); ?>:</span>
                                </label>
                                <div class="lp-custom-input-wrapper" data-ppu-url="<?php echo esc_url( $contribution_urls['ppu'] ) ?>" data-sis-url="<?php echo esc_url( $contribution_urls['sis'] ) ?>">
                                    <input class="lp-custom-amount-input" type="number" step="0.10" value="<?php echo ! empty( $payment_config['custom_amount'] ) ? esc_attr( LaterPay_Helper_View::format_number( floatval( $payment_config['custom_amount'] / 100 ), 2 ) ) : ''; ?>" />
                                    <i><?php echo esc_html( $currency_symbol ); ?></i>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="lp-dialog-button-wrapper">
                        <div class="lp-button-wrapper">
                            <div data-url="" class="lp-button lp-contribution-button">
                                <div class="lp-cart"></div>
                                <div class="lp-link">
                                    <?php esc_html_e( 'Contribute now', 'laterpay' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lp-powered-by">
                    <span><?php esc_html_e( 'Powered by', 'laterpay' ); ?></span>
                    <a data-icon="a" class="lp-powered-by-link" href="https://www.laterpay.net/" target="_blank" rel="noopener"></a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
