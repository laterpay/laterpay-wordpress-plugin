<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

// Values are used more than once in the view, so create variables for reuse.
$settings_url    = admin_url( 'options-general.php?page=laterpay' );
$currency_symbol = 'USD' === $laterpay['currency']['code'] ? '$' : '€';
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation">
        <a href="<?php echo esc_url( add_query_arg( LaterPay_Helper_Request::laterpay_encode_url_params( array( 'page' => $laterpay['admin_menu']['account']['url'] ) ), admin_url( 'admin.php' ) ) ); ?>"
           id="lp_js_pluginModeIndicator"
           class="lp_plugin-mode-indicator"
            <?php if ( $laterpay['plugin_is_in_live_mode'] ) : ?>style="display:none;"<?php endif; ?>
           data-icon="h">
            <h2 class="lp_plugin-mode-indicator__title"><?php esc_html_e( 'Test mode', 'laterpay' ); ?></h2>
            <span class="lp_plugin-mode-indicator__text"><?php printf( '%1$s <i> %2$s </i>', esc_html__( 'Earn money in', 'laterpay' ), esc_html__( 'live mode', 'laterpay' ) ); ?></span>
        </a>

        <?php
        // laterpay[contributions_obj] is instance of LaterPay_Controller_Admin_Contributions
        $laterpay['contributions_obj']->get_menu(); ?>

    </div>


    <div class="lp_pagewrap">

        <div class="lp_main_area lp_main_contribution">
            <div class="lp_contribution_form">
                <form method="post" id="lp_single_contribution_form">
                    <input type="hidden" name="form" value="single_contribution">
                    <input type="hidden" name="action" value="laterpay_contributions">
                    <?php wp_nonce_field( 'laterpay_form' ); ?>

                    <div class="lp_contributions_options">
                        <div class="contributions_option_single">
                            <label class="lp_contribution_label" for="lp_contribution_name"><?php esc_html_e( 'Campaign name', 'laterpay' ); ?></label>
                            <p class="lp_tooltip lp_tooltip_p lp_tooltip_contribution" data-tooltip="<?php esc_attr_e( 'Enter the name you would like to appear on your customers\' invoice. We recommend including your organization\'s name as well as something to remind them of this specific contribution.', 'laterpay' ); ?>">
                                <span data-icon="m"></span>
                            </p><br />
                            <input type="text" id="lp_contribution_name" class="lp_input" name="contribution_name" placeholder="<?php esc_attr_e( 'Contributions for...', 'laterpay' ); ?>" value="" />
                        </div>
                        <div class="contributions_option_single">
                            <label class="lp_contribution_label" for="lp_thank_you_page"><?php esc_html_e( 'Thank you page (optional)', 'laterpay' ); ?></label>
                            <p class="lp_tooltip lp_tooltip_p lp_tooltip_contribution" data-tooltip="<?php esc_attr_e( 'Optional. After the button is clicked, we can redirect the visitor to a page of your choice (for example, a dedicated "thank you" page on your website).', 'laterpay' ); ?>">
                                <span data-icon="m"></span>
                            </p><br />
                            <input type="text" id="lp_thank_you_page" class="lp_input" name="contribution_thank_you_page" placeholder="http://www..." value="" />
                            <p data-icon="n" class="lp-contribution-error-message"></p>
                        </div>

                        <div class="contributions_option_single hide-on-single-purchase">
                            <label class="lp_contribution_label" for="lp_dialog_header"><?php esc_html_e( 'Dialog Header (optional)', 'laterpay' ); ?></label>
                            <br />
                            <input type="text" id="lp_dialog_header" class="lp_input" name="dialog_header" placeholder="<?php esc_attr_e( 'Support the author', 'laterpay' ); ?>" value=""/>
                            <p data-icon="n" class="lp-contribution-error-message"></p>
                        </div>

                        <div class="contributions_option_single hide-on-single-purchase">
                            <label class="lp_contribution_label" for="lp_dialog_description"><?php esc_html_e( 'Dialog Description (optional)', 'laterpay' ); ?></label>
                            <br />
                            <input type="text" id="lp_dialog_description" class="lp_input" name="dialog_description" placeholder="<?php esc_attr_e( 'How much would you like to contribute?', 'laterpay' ); ?>" value=""/>
                            <p data-icon="n" class="lp-contribution-error-message"></p>
                        </div>

                    </div>
                    <div class="lp_contributions_single_amount_options">
                        <label class="lp_contribution_label"><?php esc_html_e( 'Amount', 'laterpay' ); ?></label>
                        <div class="lp_single_contribution_dialog_options" style="display: none;">
                            <div class="input-icon">
                                <input type="text" class="lp_js_priceInput lp_input lp_ml-" id="lp_single_contribution_price" value="" placeholder="0.00" />
                                <i><?php echo esc_html( $currency_symbol ); ?></i>
                            </div>
                            <div class="post_price_revenue_model" id="lp_single_contribution_revenue_model">
                                <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                    <input type="radio" class="lp_js_revenueModelInput" name="post_revenue_model" value="ppu" checked="checked" />
                                    <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                </label>
                                <br />
                                <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                    <input type="radio" class="lp_js_revenueModelInput" name="post_revenue_model" value="sis" />
                                    <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="lp_multiple_contribution_form">
                    <input type="hidden" name="form" value="multiple_contribution">
                    <input type="hidden" name="action" value="laterpay_contributions">
                    <?php wp_nonce_field( 'laterpay_form' ); ?>
                    <div class="lp_multiple_contribution_dialog_options">
                        <input type="checkbox" id="lp_contribution_allow_multiple_amount" name="contribution_allow_multiple_amount" checked="checked">
                        <label for="lp_contribution_allow_multiple_amount"><?php esc_html_e( 'Show multiple contribution amounts', 'laterpay' ); ?></label>
                        <p class="lp_tooltip lp_tooltip_p lp_tooltip_contribution" data-tooltip="<?php esc_attr_e( 'Use this checkbox to toggle between a contributions button which allows one static contribution amount or a dialog which allows users to choose between several contribution amounts.', 'laterpay' ); ?>">
                            <span data-icon="m"></span>
                        </p><br />
                        <div class="contributions_option_single purchase_options">
                            <div class="all_purchase_options">
                                <ul id="lp_multiple_contribution_ul">
                                    <li id="lp_multiple_contribution_li_1">
                                        <div class="input-icon">
                                            <input type="text" id="lp_multiple_contribution_input_1" class="lp_js_priceInput lp_input lp_ml-" placeholder="0.00">
                                            <i><?php echo esc_html( $currency_symbol ); ?></i>
                                        </div>
                                        <div class="post_price_revenue_model" id="post_price_revenue_model_1">
                                            <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_1" value="ppu" checked="checked" />
                                                <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                            </label>
                                            <br />
                                            <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_1" value="sis" />
                                                <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                            </label>
                                        </div>
                                    </li>
                                    <li id="lp_multiple_contribution_li_2">
                                        <div class="input-icon">
                                            <input type="text" id="lp_multiple_contribution_input_2" class="lp_js_priceInput lp_input lp_ml-" placeholder="0.00">
                                            <i><?php echo esc_html( $currency_symbol ); ?></i>
                                        </div>
                                        <div class="post_price_revenue_model" id="post_price_revenue_model_2">
                                            <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_2" value="ppu" checked="checked" />
                                                <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                            </label>
                                            <br />
                                            <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_2" value="sis" />
                                                <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                            </label>
                                        </div>
                                    </li>
                                    <li id="lp_multiple_contribution_li_3" style="display: none;">
                                        <div class="input-icon">
                                            <input type="text" id="lp_multiple_contribution_input_3" class="lp_js_priceInput lp_input lp_ml-" value="" placeholder="0.00">
                                            <i><?php echo esc_html( $currency_symbol ); ?></i>
                                        </div>
                                        <div class="post_price_revenue_model" id="post_price_revenue_model_3">
                                            <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_3" value="ppu" checked="checked" />
                                                <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                            </label>
                                            <br />
                                            <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_3" value="sis" />
                                                <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                            </label>
                                        </div>
                                    </li>
                                    <li id="lp_multiple_contribution_li_4" style="display: none;">
                                        <div class="input-icon">
                                            <input type="text" id="lp_multiple_contribution_input_4" class="lp_js_priceInput lp_input lp_ml-" placeholder="0.00">
                                            <i><?php echo esc_html( $currency_symbol ); ?></i>
                                        </div>
                                        <div class="post_price_revenue_model" id="post_price_revenue_model_4">
                                            <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_4" value="ppu" checked="checked" />
                                                <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                            </label>
                                            <br />
                                            <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_4" value="sis" />
                                                <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                            </label>
                                        </div>
                                    </li>
                                    <li id="lp_multiple_contribution_li_5" style="display: none;">
                                        <div class="input-icon">
                                            <input type="text" id="lp_multiple_contribution_input_5" class="lp_js_priceInput lp_input lp_ml-" placeholder="0.00">
                                            <i><?php echo esc_html( $currency_symbol ); ?></i>
                                        </div>
                                        <div class="post_price_revenue_model" id="post_price_revenue_model_5">
                                            <label class="lp_badge lp_badge--revenue-model lp_is-selected">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_5" value="ppu" checked="checked" />
                                                <?php esc_html_e( 'Pay Later', 'laterpay' ); ?>
                                            </label>
                                            <br />
                                            <label class="lp_badge lp_badge--revenue-model lp_mt- lp_is-disabled">
                                                <input type="radio" class="lp_js_revenueModelInput" name="post_price_revenue_model_5" value="sis" />
                                                <?php esc_html_e( 'Pay Now', 'laterpay' ); ?>
                                            </label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <br />
                        <input type="checkbox" id="lp_contribution_allow_custom_amount" name="contribution_allow_custom_amount" checked="checked">
                        <label for="lp_contribution_allow_custom_amount"><?php esc_html_e( 'Allow custom contribution amount', 'laterpay' ); ?></label>
                        <p class="lp_tooltip lp_tooltip_p lp_tooltip_contribution" data-tooltip="<?php esc_attr_e( 'Only available if "Show multiple contribution amounts" is checked, this feature will add an input box to your contribution dialog allowing users to contribute custom amounts.', 'laterpay' ); ?>">
                            <span data-icon="m"></span>
                        </p>
                        <p data-icon="n" class="lp-contribution-custom-error-message"></p>
                    </div>
                </form>
                <form id="lp_contribution_preview_form">
                    <div class="lp_contribution_preview">
                        <p><?php esc_html_e( 'Preview', 'laterpay' ); ?></p>
                        <div class="lp_contribution_live_preview" id="lp_contributionLivePreview">
                            <div class="lp-dialog-single-button-wrapper">
                                <div class="lp-button-wrapper">
                                    <div class="lp-button">
                                        <div class="lp-cart"></div>
                                        <div class="lp-link" id="lp_jsLinkSingle"><?php esc_html_e( 'Contribute Now, Pay Later', 'laterpay' ); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="lp-dialog-multiple-contribution-wrapper">
                                <div class="lp-dialog-wrapper">
                                    <div class="lp-dialog">
                                        <div class="lp-header-wrapper">
                                            <div class="lp-header-padding"></div>
                                            <div class="lp-header-text">
                                                <span><?php esc_html_e( 'Support the author', 'laterpay' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="lp-body-wrapper">
                                            <div>
                                                <span class="lp-amount-text"><?php esc_html_e( 'How much would you like to contribute?', 'laterpay' ); ?></span>
                                            </div>
                                            <div class="lp-amount-presets-wrapper">
                                                <div class="lp-amount-presets" id="lp_js_multiple_amounts">
                                                    <div class="lp-amount-preset-wrapper" id="lp_js_multiple_amount_1">
                                                        <div class="lp-amount-preset-button lp-amount-preset-button-selected"><?php echo esc_html( $currency_symbol ); ?>0.00</div>
                                                    </div>
                                                    <div class="lp-amount-preset-wrapper" id="lp_js_multiple_amount_2">
                                                        <div class="lp-amount-preset-button"><?php echo esc_html( $currency_symbol ); ?>0.00</div>
                                                    </div>
                                                    <div class="lp-amount-preset-wrapper" id="lp_js_multiple_amount_3" style="display: none;">
                                                        <div class="lp-amount-preset-button"><?php echo esc_html( $currency_symbol ); ?>0.00</div>
                                                    </div>
                                                    <div class="lp-amount-preset-wrapper" id="lp_js_multiple_amount_4" style="display: none;">
                                                        <div class="lp-amount-preset-button"><?php echo esc_html( $currency_symbol ); ?>0.00</div>
                                                    </div>
                                                    <div class="lp-amount-preset-wrapper" id="lp_js_multiple_amount_5" style="display: none;">
                                                        <div class="lp-amount-preset-button"><?php echo esc_html( $currency_symbol ); ?>0.00</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="lp-custom-amount-wrapper">
                                                <div class="lp-custom-amount">
                                                    <label for="lp_custom_amount_input" class="lp-custom-amount-label">
                                                        <span class="lp-custom-amount-text"><?php esc_html_e( 'Custom Amount', 'laterpay' ); ?>:</span>
                                                    </label>
                                                    <div class="lp-custom-input-wrapper">
                                                        <input id="lp_custom_amount_input" class="lp-custom-amount-input" type="text" />
                                                        <i><?php echo esc_html( $currency_symbol ); ?></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="lp-dialog-button-wrapper">
                                                <div class="lp-button-wrapper">
                                                    <div class="lp-button">
                                                        <div class="lp-cart"></div>
                                                        <div class="lp-link"><?php esc_html_e( 'Contribute now', 'laterpay' ); ?></div>
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
                        </div>
                        <p class="lp_contribution_preview_info"><?php esc_html_e( 'To include this on your site, click the button below and paste the code where you would like it to appear.', 'laterpay' ); ?></p>
                    </div>
                    <div class="lp-contribution-generate-code">
                        <a href="#" id="lp_js_contributionGenerateCode" class="button button-primary"><?php esc_html_e( 'Generate and Copy Code', 'laterpay' ); ?></a>
                    </div>
                    <div class="lp-contribution-error-wrapper">
                        <p data-icon="n" class="lp-contribution-error-message"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
