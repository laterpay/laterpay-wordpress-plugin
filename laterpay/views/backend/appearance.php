<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

// Appearance tab updates - new configs.
$appearance_config                           = get_option( 'lp_appearance_config' );
$laterpay_show_purchase_button_above_article = $appearance_config['lp_show_purchase_button_above_article'];
$laterpay_show_purchase_overlay              = $appearance_config['lp_show_purchase_overlay'];
$laterpay_show_introduction                  = $appearance_config['lp_show_introduction'];
$laterpay_show_tp_sub_below_modal            = $appearance_config['lp_show_tp_sub_below_modal'];
$laterpay_body_text                          = get_option( 'lp_body_text' );

// Existing config before appearance tab updates.
$laterpay_purchase_button_positioned_manually = get_option( 'laterpay_purchase_button_positioned_manually' );
$laterpay_purchase_header                     = get_option( 'laterpay_overlay_header_title', __( 'Read now, pay later', 'laterpay' ) );
$laterpay_time_passes_positioned_manually     = get_option( 'laterpay_time_passes_positioned_manually' );
$laterpay_show_footer                         = get_option( 'laterpay_overlay_show_footer' );

// Display options based on value.
$laterpay_show_purchase_button_custom_option = 1 === $laterpay_show_purchase_button_above_article ? '' : 'display:none';
$laterpay_show_tp_sub_custom_option          = 1 === $laterpay_show_tp_sub_below_modal ? '' : 'display:none';
$laterpay_show_body_text_area                = 1 === $laterpay_body_text['enabled'] ? '' : 'display:none';
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ) : ?>
            <a href="<?php echo esc_url( $laterpay['admin_menu'] ); ?>"
               class="lp_plugin-mode-indicator"
               data-icon="h">
                <h2 class="lp_plugin-mode-indicator__title"><?php esc_html_e( 'Test mode', 'laterpay' ); ?></h2>
                <span class="lp_plugin-mode-indicator__text"><?php printf( '%1$s <i> %2$s </i>', esc_html__( 'Earn money in', 'laterpay' ), esc_html__( 'live mode', 'laterpay' ) ); ?></span>
            </a>
        <?php endif; ?>
        <?php
        // laterpay[appearance_obj] is instance of LaterPay_Controller_Admin_Appearance
        $laterpay['appearance_obj']->get_menu();
        ?>

    </div>

    <div class="lp_pagewrap">
        <div class="lp_main_area">
            <div class="lp_layout">
                <div class="lp_clearfix">
                    <label class="lp_step_label">
                        <?php
                        printf(
                            esc_html__( '%sConfigure%s Appearance', 'laterpay' ),
                            '<span class="lp_step_span">',
                            '</span>'
                        );
                        ?>
                    </label>

                    <form method="post" class="lp_mb++ lp_inline-block lp_purchase-form">
                        <input type="hidden" name="form" value="appearance_config">
                        <input type="hidden" name="action" value="laterpay_appearance">
                        <?php wp_nonce_field( 'laterpay_form' ); ?>
                        <div class="lp_appearance_options">
                            <div class="appearance_option_single" id="appearance_option_first">
                                <label for="show_purchase_button_above_article"><?php esc_html_e( 'Show purchase button above article', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_purchase_button_above_article" name="show_purchase_button_above_article" value="<?php echo esc_attr( $laterpay_show_purchase_button_above_article ); ?>" <?php if ( 1 === absint( $laterpay_show_purchase_button_above_article ) ) : echo 'checked'; endif; ?>>
                                <div class="appearance_option_single_child" style="<?php echo esc_attr( $laterpay_show_purchase_button_custom_option ); ?>">
                                    <label><?php esc_html_e( 'Customize position of purchase button', 'laterpay' ); ?></label>
                                    <input type="checkbox" id="lp_purchase_button_custom_positioned" name="is_purchase_button_custom_positioned" value="<?php echo esc_attr( $laterpay_purchase_button_positioned_manually ); ?>" <?php if ( '1' === $laterpay_purchase_button_positioned_manually ) : echo 'checked'; endif; ?> />
                                </div>
                                <div class="lp_button-group__hint"<?php if ( ! $laterpay_purchase_button_positioned_manually ) : ?> style="display:none;"<?php endif; ?> id="lp_purchase_button_hint">
                                    <p>
                                        <?php esc_html_e( 'Call action \'laterpay_purchase_button\' in your theme to render the LaterPay purchase button at that position.', 'laterpay' ); ?>
                                    </p>
                                    <code>
                                        <?php echo esc_html( "<?php do_action( 'laterpay_purchase_button' ); ?>" ); ?>
                                    </code>
                                </div>
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Show Purchase Overlay', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_purchase_overlay" name="show_purchase_overlay" value="<?php echo esc_attr( $laterpay_show_purchase_overlay ); ?>" <?php if ( 1 === $laterpay_show_purchase_overlay ) : echo 'checked'; endif; ?>>
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Header', 'laterpay' ); ?></label>
                                <input type="text" id="lp_purchase_header" class="lp_input" name="purchase_header" value="<?php echo ! empty( $laterpay_purchase_header ) ? esc_html( $laterpay_purchase_header, true ) : esc_html__( 'Read now, pay later', 'laterpay' ); ?>" />
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Show LaterPay Introduction', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_introduction" name="show_introduction" value="<?php echo esc_attr( $laterpay_show_introduction ); ?>" <?php if ( 1 === $laterpay_show_introduction ) : echo 'checked'; endif; ?>>
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Show Time Passes & Subscriptions below modal', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_tp_sub_below_modal" name="show_tp_sub_below_modal" value="<?php echo esc_attr( $laterpay_show_tp_sub_below_modal ); ?>" <?php if ( 1 === $laterpay_show_tp_sub_below_modal ) : echo 'checked'; endif; ?>>
                                <div class="appearance_option_single_child" style="<?php echo esc_attr( $laterpay_show_tp_sub_custom_option ); ?>">
                                    <label><?php esc_html_e( 'Customize position of Time Passes & Subscriptions', 'laterpay' ); ?></label>
                                    <input type="checkbox" id="lp_is_tp_sub_custom_positioned" name="is_tp_sub_custom_positioned" value="<?php echo esc_attr( $laterpay_time_passes_positioned_manually ); ?>" <?php if ( '1' === $laterpay_time_passes_positioned_manually ) : echo 'checked'; endif; ?>>
                                </div>
                                <div class="lp_js_buttonGroupHint lp_button-group__hint" <?php if ( ! $laterpay_time_passes_positioned_manually ) : ?> style="display:none;"<?php endif; ?> id="lp_timepass_widget_hint">
                                    <p>
                                        Call action 'laterpay_time_passes' in your theme or use the shortcode '[laterpay_time_passes]' to show your users the available time passes.<br>
                                    </p>
                                    <table>
                                        <tbody>
                                        <tr>
                                            <th>
                                                Shortcode
                                            </th>
                                            <td>
                                                <code>[laterpay_time_passes]</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Action
                                            </th>
                                            <td>
                                                <code>&lt;?php do_action( 'laterpay_time_passes' ); ?&gt;</code>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Add custom HTML section below payment button', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_body_text" name="show_body_text" value="<?php echo esc_attr( $laterpay_body_text['enabled'] ); ?>" <?php if ( 1 === $laterpay_body_text['enabled'] ) : echo 'checked'; endif; ?>>
                                <textarea rows="4" cols="15" style="<?php echo esc_attr( $laterpay_show_body_text_area ); ?>" id="lp_body_text_content" name="body_text_content"><?php echo wp_kses_post( $laterpay_body_text['content'] ); ?></textarea>
                            </div>

                            <div class="appearance_option_single">
                                <label><?php esc_html_e( 'Show valid payment options', 'laterpay' ); ?></label>
                                <input type="checkbox" id="lp_show_footer" name="show_footer" value="<?php echo esc_attr( $laterpay_show_footer ); ?>" <?php if ( '1' === $laterpay_show_footer ) : echo 'checked'; endif; ?>>
                            </div>

                            <div class="appearance_actions">
                                <a href="#" class="lp_js_savePurchaseForm button button-primary"><?php esc_html_e( 'Save', 'laterpay' ); ?></a>
                                <a href="#" class="lp_inline-block lp_pd--05-1"><?php esc_html_e( 'Cancel', 'laterpay' ); ?></a>
                                <span data-icon="n" class="lp_disclaimer" id="lp_config_disclaimer" style="display: none"><?php esc_html_e( 'Please select one of the recommended options above to ensure that your users can purchase all content types.', 'laterpay' ); ?></span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="lp_clearfix">
                    <label class="lp_step_label">
                        <span class="lp_step_span"><?php esc_html_e( 'Preview' ); ?></span>
                    </label>

                    <div id="lp_appearance_loading">
                        <?php esc_html_e( 'Loading...', 'laterpay' ); ?>
                    </div>

                    <div class="lp_appearance_preview" style="display: none" id="lp_appearance_preview">
                        <h3><?php esc_html_e( 'Sample Post Title' ) ?></h3>

                        <div class="lp_purchase-button-wrapper lp_backend_purchase_button" id="lp_backend_purchase_button" style="<?php echo esc_attr( $laterpay_show_purchase_button_custom_option ); ?>">
                            <div><a href="#" class="lp_purchase-button lp_purchase_button" title="Buy now with LaterPay" data-icon="b">0.49
                                    <small class="lp_purchase-link__currency">USD</small>
                                </a></div>
                            <div><a class="lp_bought_notification" href="#">I already bought this</a></div>
                        </div>

                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur....
                        </p>
                        <?php $this->render_overlay(); ?>
                    </div>
                </div>
            </div>
            <div class="lp_customize_colors">
                <label class="lp_step_label">
                    <?php
                    printf(
                        esc_html__( '%sCustomize%s Colors', 'laterpay' ),
                        '<span class="lp_step_span">',
                        '</span>'
                    );
                    ?>
                </label>
                <form method="post" class="lp_mb++ lp_inline-block lp_purchase-form">
                    <input type="hidden" name="form" value="overlay_settings">
                    <input type="hidden" name="action" value="laterpay_appearance">
                    <?php wp_nonce_field( 'laterpay_form' ); ?>
                    <table class="lp_purchase-form__table lp_table--form">
                        <tbody>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Header background color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseHeaderBackgroundColor lp_input" name="header_background_color" value="<?php echo esc_attr( $laterpay['overlay']['header_bg_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Purchase option background color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseBackgroundColor lp_input" name="background_color" value="<?php echo esc_attr( $laterpay['overlay']['main_bg_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Main text color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseMainTextColor lp_input" name="main_text_color" value="<?php echo esc_attr( $laterpay['overlay']['main_text_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Description text color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseDescriptionTextColor lp_input" name="description_text_color" value="<?php echo esc_attr( $laterpay['overlay']['description_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Purchase button background color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseButtonBackgroundColor lp_input" name="button_background_color" value="<?php echo esc_attr( $laterpay['overlay']['button_bg_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Purchase button hover color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseButtonHoverColor lp_input" name="button_hover_color" value="<?php echo esc_attr( $laterpay['overlay']['button_hover_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Purchase button text color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseButtonTextColor lp_input" name="button_text_color" value="<?php echo esc_attr( $laterpay['overlay']['button_text_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Link main color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseLinkMainColor lp_input" name="link_main_color" value="<?php echo esc_attr( $laterpay['overlay']['link_main_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Link hover color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseLinkHoverColor lp_input" name="link_hover_color" value="<?php echo esc_attr( $laterpay['overlay']['link_hover_color'] ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Footer background color', 'laterpay' ); ?>
                            </td>
                            <td>
                                <input type="color" class="lp_js_overlayOptions lp_js_purchaseFooterBackgroundColor lp_input" name="footer_background_color" value="<?php echo esc_attr( $laterpay['overlay']['footer_bg_color'] ); ?>">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="lp_purchase-form__buttons lp_1">
                        <div class="lp_1/2 lp_inline-block">
                            <a href="#" class="lp_js_savePurchaseFormColors button button-primary"><?php esc_html_e( 'Save', 'laterpay' ); ?></a>
                            <a href="#" class="lp_js_cancelEditingPurchaseForm lp_inline-block lp_pd--05-1"><?php esc_html_e( 'Cancel', 'laterpay' ); ?></a>
                        </div><!--
                             -->
                        <div class="lp_1/2 lp_inline-block lp_text-align--right">
                            <a href="#" class="lp_js_restoreDefaultPurchaseForm lp_inline-block lp_pd--05-1"><?php esc_html_e( 'Restore Default Values', 'laterpay' ); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="lp_side_area">
            <div class="lp_clearfix lp_info">
                <div class="lp_side_info">
                    <h2><?php esc_html_e( 'Advanced Settings', 'laterpay' ); ?></h2>
                    <p>

                        <?php
                        printf(
                            "<a href='%s' target='_blank' class='lp_info_link'>%s</a> %s",
                            esc_url( admin_url( 'options-general.php?page=laterpay#lpappearance' ) ),
                            esc_html__( 'Click here', 'laterpay' ),
                            esc_html__( 'to adjust the number of characters automatically generated as your teaser content or the length of the content preview blurred behind our paywall.', 'laterpay' )
                        );
                        ?>
                    </p>
                </div>
                <?php $this->render_faq_support(); ?>
            </div>
        </div>
    </div>
</div>
