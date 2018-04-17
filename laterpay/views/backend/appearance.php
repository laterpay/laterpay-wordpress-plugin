<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
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
        <?php echo wp_kses( $laterpay['top_nav'], array(
            'ul' => array(
                'class' => array(),
            ),
            'li' => array(
                'class' => array(),
            ),
            'a'  => array(
                'href'  => array(),
                'class' => array(),
            ),
        ) ); ?>

    </div>

    <div class="lp_pagewrap">
        <div class="lp_layout">
            <div class="lp_layout__item lp_1" id="lp_js_paidContentPreview">
                <h2><?php esc_html_e( 'Content Preview for Paid Posts', 'laterpay' ); ?></h2>
                <form method="post" class="lp_mb++ lp_inline-block lp_purchase-form">
                    <input type="hidden" name="form"    value="paid_content_preview">
                    <input type="hidden" name="action"  value="laterpay_appearance">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                    <div class="lp_button-group--large">
                        <label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( $laterpay['teaser_mode'] === '0' ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                    name="paid_content_preview"
                                    value="0"
                                    class="lp_js_switchButtonGroup"
                                    <?php if ( $laterpay['teaser_mode'] === '0' ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--preview-mode-1"></div>
                            <?php esc_html_e( 'Teaser + Purchase Link', 'laterpay' ); ?>
                        </label><!-- comment required to prevent spaces, because layout uses display:inline-block
                     --><label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( $laterpay['teaser_mode'] === '1' ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                    name="paid_content_preview"
                                    value="1"
                                    class="lp_js_switchButtonGroup"
                                    <?php if ( $laterpay['teaser_mode'] === '1' ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--preview-mode-2"></div>
                            <?php esc_html_e( 'Teaser + Explanatory Overlay', 'laterpay' ); ?>
                        </label><!-- comment required to prevent spaces, because layout uses display:inline-block
                     --><label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( $laterpay['teaser_mode'] === '2' ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                   name="paid_content_preview"
                                   value="2"
                                   class="lp_js_switchButtonGroup"
                                    <?php if ( $laterpay['teaser_mode'] === '2' ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--preview-mode-3"></div>
                            <?php esc_html_e( 'Teaser + Purchase Overlay', 'laterpay' ); ?>
                        </label>
                    </div>
                    <div class="lp_js_purchaseForm" id="lp_js_purchaseForm" <?php if ( $laterpay['teaser_mode'] !== '2' ) echo 'style="display:none;"'; ?>>
                        <div class="lp_purchase-form__panel lp_relative lp_1">
                            <div class="lp_purchase-form__triangle"></div>
                            <div class="lp_purchase-form__inner lp_relative lp_clearfix">
                                <div class="lp_left lp_9/20">
                                    <table class="lp_purchase-form__table lp_table--form">
                                        <tbody>
                                            <tr>
                                                <td colspan="2">
                                                    <h3><strong><?php esc_html_e( 'Header', 'laterpay' ); ?></strong></h3>
                                                </td>
                                            </tr>
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
                                                    <?php esc_html_e( 'Header title', 'laterpay' ); ?>
                                                </td>
                                                <td>
                                                    <input type="text" class="lp_js_overlayOptions lp_js_purchaseHeaderTitle lp_input" name="header_title" value="<?php echo esc_attr( $laterpay['overlay']['header_title'] ); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td  colspan="2">
                                                    <h3><strong><?php esc_html_e( 'Purchase Options', 'laterpay' ); ?></strong></h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <?php esc_html_e( 'Background color', 'laterpay' ); ?>
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
                                                    <?php esc_html_e( 'Description color text', 'laterpay' ); ?>
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
                                                <td colspan="2">
                                                    <h3><strong><?php esc_html_e( 'Footer', 'laterpay' ); ?></strong></h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <?php esc_html_e( 'Show footer', 'laterpay' ); ?>
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="lp_js_overlayShowFooter" name="show_footer" value="1" <?php if ( $laterpay['overlay']['show_footer'] === '1' ) : echo 'checked'; endif; ?>>
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
                                </div>
                                <div class="lp_right lp_11/20">
                                    <div class="lp_purchase-form-label lp_text-align--center lp_mt-"><?php esc_html_e( 'Preview', 'laterpay' ); ?></div>
                                    <?php $this->render_overlay(); ?>
                                </div>
                            </div>
                            <div class="lp_purchase-form__buttons lp_1">
                                <div class="lp_1/2 lp_inline-block">
                                    <a href="#" class="lp_js_savePurchaseForm lp_button--default lp_mt- lp_mb-"><?php esc_html_e( 'Save', 'laterpay' ); ?></a>
                                    <a href="#" class="lp_js_cancelEditingPurchaseForm lp_button--link lp_pd-"><?php esc_html_e( 'Cancel', 'laterpay' ); ?></a>
                                </div><!--
                             --><div class="lp_1/2 lp_inline-block lp_text-align--right">
                                    <a href="#" class="lp_js_restoreDefaultPurchaseForm lp_button--link lp_mr+ lp_pd-"><?php esc_html_e( 'Restore Default Values', 'laterpay' ); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div><!-- comment required to prevent spaces, because layout uses display:inline-block
         --><div class="lp_layout__item lp_1" id="lp_js_purchaseButton" <?php if ( $laterpay['teaser_mode'] === '2' ) echo 'style="display:none;"'; ?>>
                <h2><?php esc_html_e( 'Position of the LaterPay Purchase Button', 'laterpay' ); ?></h2>
                <form method="post" class="lp_js_showHintOnTrue lp_mb++">
                    <input type="hidden" name="form"    value="purchase_button_position">
                    <input type="hidden" name="action"  value="laterpay_appearance">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                    <div class="lp_button-group--large">
                        <label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( ! $laterpay['purchase_button_positioned_manually'] ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                    name="purchase_button_positioned_manually"
                                    value="0"
                                    class="lp_js_switchButtonGroup"
                                    <?php if ( ! $laterpay['purchase_button_positioned_manually'] ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--button-position-1"></div>
                            <?php esc_html_e( 'Standard position', 'laterpay' ); ?>
                        </label><!-- comment required to prevent spaces, because layout uses display:inline-block
                     --><label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( $laterpay['purchase_button_positioned_manually'] ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                    name="purchase_button_positioned_manually"
                                    value="1"
                                    class="lp_js_switchButtonGroup"
                                    <?php if ( $laterpay['purchase_button_positioned_manually'] ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--button-position-2"></div>
                            <?php esc_html_e( 'Custom position', 'laterpay' ); ?>
                        </label>
                    </div>
                    <div class="lp_js_buttonGroupHint lp_button-group__hint"<?php if ( ! $laterpay['purchase_button_positioned_manually'] ) : ?> style="display:none;"<?php endif; ?>>
                        <p>
                            <?php esc_html_e( 'Call action \'laterpay_purchase_button\' in your theme to render the LaterPay purchase button at that position.', 'laterpay' ); ?>
                        </p>
                        <code>
                            <?php echo esc_html( "<?php do_action( 'laterpay_purchase_button' ); ?>" ); ?>
                        </code>
                    </div>
                </form>
            </div>
            <div class="lp_layout__item lp_1" id="lp_js_timePasses" <?php if ( $laterpay['teaser_mode'] === '2' ) echo 'style="display:none;"'; ?>>
                <h2><?php esc_html_e( 'Display of LaterPay Time Passes', 'laterpay' ); ?></h2>
                <form method="post" class="lp_js_showHintOnTrue lp_mb++">
                    <input type="hidden" name="form"    value="time_passes_position">
                    <input type="hidden" name="action"  value="laterpay_appearance">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>

                    <div class="lp_button-group--large">
                        <label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( ! $laterpay['time_passes_positioned_manually'] ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                   name="time_passes_positioned_manually"
                                   value="0"
                                   class="lp_js_switchButtonGroup"
                                    <?php if ( ! $laterpay['time_passes_positioned_manually'] ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--time-passes-position-1"></div>
                            <?php esc_html_e( 'Standard position', 'laterpay' ); ?>
                        </label><!-- comment required to prevent spaces, because layout uses display:inline-block
                 --><label class="lp_js_buttonGroupButton lp_button-group__button<?php if ( $laterpay['time_passes_positioned_manually'] ) { echo ' lp_is-selected'; } ?>">
                            <input type="radio"
                                   name="time_passes_positioned_manually"
                                   value="1"
                                   class="lp_js_switchButtonGroup"
                                    <?php if ( $laterpay['time_passes_positioned_manually'] ) : ?>checked<?php endif; ?>/>
                            <div class="lp_button-group__button-image lp_button-group__button-image--time-passes-position-2"></div>
                            <?php esc_html_e( 'Custom position', 'laterpay' ); ?>
                        </label>
                    </div>
                    <div class="lp_js_buttonGroupHint lp_button-group__hint"<?php if ( ! $laterpay['time_passes_positioned_manually'] ) : ?> style="display:none;"<?php endif; ?>>
                        <p>
                            <?php esc_html_e( 'Call action \'laterpay_time_passes\' in your theme or use the shortcode \'[laterpay_time_passes]\' to show your users the available time passes.', 'laterpay' ); ?><br>
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
                                    <code><?php echo esc_html( "<?php do_action( 'laterpay_time_passes' ); ?>" ); ?></code>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="lp_js_deprecated-feature">
                <div class="lp_clearfix lp_mb+">
                    <h3><?php esc_html_e( 'Display of LaterPay Invoice Balance', 'laterpay' ); ?></h3>
                    <dfn>
                        <?php esc_html_e( 'Call action \'laterpay_invoice_indicator\' in your theme to show your users their LaterPay invoice balance.', 'laterpay' ); ?><br>
                        <?php esc_html_e( 'The LaterPay invoice indicator is served by LaterPay. Its styling can not be changed.', 'laterpay' ); ?>
                    </dfn>
                    <img class="lp_ui-element-preview lp_left lp_m--0 lp_mt" src="data:image/png;base64,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">
                    <code class="lp_code-snippet--deprecated lp_block">
                        <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                        <?php echo esc_html( "<?php do_action( 'laterpay_invoice_indicator' ); ?>" ); ?>
                    </code>
                </div>

                <div class="lp_clearfix lp_mb+">
                    <h3><?php esc_html_e( 'Display of LaterPay Login / Logout Links', 'laterpay' ); ?></h3>
                    <dfn>
                        <?php esc_html_e( 'Call action \'laterpay_account_links\' in your theme or use the shortcode \'[laterpay_account_links]\' to embed a LaterPay login / logout link in your page.', 'laterpay' ); ?><br>
                        <?php esc_html_e( 'These links have minimal default styling. Apply own styling by passing parameter \'css\' the URL of a CSS file with your styles.', 'laterpay' ); ?><br>
                    </dfn>

                    <img class="lp_ui-element-preview lp_left lp_m--0 lp_mt" src="data:image/png;base64,
            iVBORw0KGgoAAAANSUhEUgAAANwAAAA6CAYAAADbRzceAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABVZJREFUeNrsnM9PFGcYx19olUXjsthAShTjDy7ojZv2YuTQJk1sjGkvtjeP/VN67NFb9VLTGE166EHCpe6NnqBJ40ojNhJI042Ggm2Kne8Lz/ruMrM7sD+H/XwS4gIzszPr++F53ud5ZwZKpVLBOfcg+rrqAKBdzEdfNwaRDaAjyLEHA1GEe8tnAdAZBvkIABAOAOEAAOEAEA4AEA4A4QAQDgAQDgDhAADhABAOAOGg33i5te7++vcVH0SHeZ+PoD4alOV/dgbmRG7M5d4byvw1za0X3eO1or+Wry985UaP5PmPRrje4Jfyoh+c4vbZL9y546czf03PNl74f7f+e+P/mCAcKSW0kfO7fzQU4QpHkY0IB23l2thlN31iygtHdEM46ACajwIpJQARDlqDihQL5UW39LrkljdWqqLNueOT7soHM6lSvKTjaN/C0ZHYfW6f/bzyWvsulJf2/NxQy+DH1fmq3y9vvPD76f2snaD303nPFC4dimISwh0ill4/dT/88ZOXJW6A6+vJnwvu5qmP/QBOQtvde/4wtoemn6XprZWjbUJR44S23+v14/WiP7fY9/PyLvpz1rkDwnUdRYd7zx9VvtfgnM5fcMODuYqMGrQa3JLStokTwWRTwWN27LLfTq/1Ox1HkcmkvnXmurt4Yqqpc7+78sjLp/fQe4XH0/uZiDp/XVOz74dw0HQaeXfloX+tQateXm3BQumY5Lnz+/c+gkk6pWq16aUGtUWwTz+8WiWlCTGRG3fflr7zP1uNjtWsAJJN56vzrm3667x1fJ23mFsrIhxFk+5ikcskSaoOajDfPPVJ5fu53WZ7baQM5YrD5oM+Ar162pJruHXms8QVNiadpbuAcF0XrpEkoSw2eENRjc3tN7vbjdc9jkXGVgig82lUyJkYHqua1wHCdQ0b9OePTabaPqz2HVQYE7UV6z5DmdJg604B4bom234Gbhi9Xm6t7YmANq+Kq3bW7pdW8nrkBof4j0S47BRMmtp/u3p/9emMsBoZojsBLK0Ltz9whGuQvsL+oEqZITSXUq9LVUxrQk/np9xwlDpu7vbOLKpqO5rRCNdXhCvxN1NGu83trXf7xxQrrO+mvp4iWW1DWhVKtRiQDeH6MiJZUzptAWQ12G70yEhsmmotg9nxSKxgnnZYbpBFODgwKlxoRYYVOhoJYb0zbRcXpTR3k7yKdLrNBrIFRZM2ExYutCaxrmyRmBYJk1ZsWAVylBtHiXCwF0UpzasU4TTfUpoZVz3UKhJbR6noplUp8WnqiJfy5+hYmuPFpZ3an/vdEC7z2JrBRtQ+++TLyeuVdZJKCSWeVRctjQzneKowJqWe16J527O/V6oWOseh/T+KxL5ycoZ5HcL1F7ZoWYIobYyrLu5Er7xft5gUnSTZr9H+w4NDDXt8/raataKXOW7hMSBcb6aExybd7D57v3EP5tGA1+0yimSSxp6cJXYWHJ+uu9JeAukuAMmqFLWemEpPJbTNCdWzC9PYRtek81cFNOla6n1GPJSoPgOlUuktH0MW0tn7lVtl9CzJNHzz252KoHF3dgPC9dTghiYyAyQnpUzLTOFi5dmNvYBWqdicT2ln2oXQ9gBbf9tPvrM3hhZ4/B7CpRfuUs+dkwmnuWCahndYlNH8rRevqR+h8Z2xPwIqgOiOgKQqpT2iwZ66peiGbMzhYJ9IMOvlGTtrJ3OV72vnnZpHqQdISwDhoInUUlEuaTG05NI8j2dFIhy0mOWgl/cu4hHNehmKJhmGCJY9KJoAIBwAwgEAwgEgHAAgHADCAfSVcPN8DAAdYV7C3UA6gPbLJtf+F2AAzoFmA2nvgRMAAAAASUVORK5CYII=">
                    <code class="lp_code-snippet--deprecated lp_block">
                    <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                    <strong><?php esc_html_e( 'Basic Example', 'laterpay' ); ?></strong><br>
                    <strong>Shortcode:</strong> [laterpay_account_links]<br>
                    <strong>Action:</strong> <?php echo esc_html( "<?php do_action( 'laterpay_account_links' ); ?>" ); ?><br><br>

                    <strong><?php esc_html_e( 'Advanced Example', 'laterpay' ); ?></strong><br>
                    <strong>Shortcode:</strong> [laterpay_account_links css="<dfn>https://yourpage.com/style.css</dfn>"]<br>
                    <strong>Action:</strong> <?php echo esc_html( "<?php do_action( 'laterpay_account_links', 'https://yourpage.com/style.css' ); ?>" ); ?>
                    </code>
                </div>

                <div class="lp_clearfix">
                    <h2><?php esc_html_e( 'Rating of Purchased Content', 'laterpay' ); ?></h2>
                    <img class="lp_ui-element-preview lp_ui-element-preview--large lp_left lp_mt- lp_mr+" src="<?php echo esc_url( $config->get( 'image_url' ) . 'content-rating-2x.png' ); ?>">
                    <div class="lp_mt+">
                        <?php esc_html_e( 'Content rating is', 'laterpay' ); ?><div class="lp_toggle">
                            <form id="lp_js_laterpayRatingsForm" method="post">
                                <input type="hidden" name="form"    value="ratings">
                                <input type="hidden" name="action"  value="laterpay_appearance">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                                <label class="lp_toggle__label">
                                    <input type="checkbox"
                                           name="enable_ratings"
                                           id="lp_js_enableRatingsToggle"
                                           class="lp_toggle__input"
                                            <?php if ( $laterpay['is_rating_enabled'] ) : ?>checked<?php endif; ?>>
                                    <span class="lp_toggle__text" data-on="<?php esc_attr_e( 'on', 'laterpay' ); ?>" data-off="<?php esc_attr_e( 'off', 'laterpay' ); ?>"></span>
                                    <span class="lp_toggle__handle"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                    <dfn class="lp_block">
                        <?php esc_html_e( 'The opinion of others has a strong influence on buying decisions.', 'laterpay' ); ?><br>
                        <?php esc_html_e( 'Content rating lets users rate your content on a five star scale after purchasing.', 'laterpay' ); ?><br>
                        <?php esc_html_e( 'These ratings will be displayed to users who have not purchased that content yet as a quality indicator.', 'laterpay' ); ?>
                    </dfn>
                </div>
                <hr class="lp_form-group-separator">

                <div class="lp_clearfix">
                    <h2><?php esc_html_e( 'Hide Free Posts', 'laterpay' ); ?></h2>
                    <div class="lp_mt+">
                        <?php esc_html_e( 'Hide free posts from homepage feature is', 'laterpay' ); ?><div class="lp_toggle">
                            <form id="lp_js_laterpayHideFreePostsForm" method="post">
                                <input type="hidden" name="form"    value="free_posts_visibility">
                                <input type="hidden" name="action"  value="laterpay_appearance">
                                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                                <label class="lp_toggle__label">
                                    <input type="checkbox"
                                           name="hide_free_posts"
                                           id="lp_js_hideFreePostsToggle"
                                           class="lp_toggle__input"
                                            <?php if ( $laterpay['hide_free_posts'] ) : ?>checked<?php endif; ?>>
                                    <span class="lp_toggle__text" data-on="<?php esc_attr_e( 'on', 'laterpay' ); ?>" data-off="<?php esc_attr_e( 'off', 'laterpay' ); ?>"></span>
                                    <span class="lp_toggle__handle"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
                <hr class="lp_form-group-separator">

                <div class="lp_clearfix">
                    <h2 id="lp_gift-cards-appearance"><?php esc_html_e( 'Offering of Gift Cards for Time Passes', 'laterpay' ); ?></h2>
                    <dfn>
                    <?php esc_html_e( 'Please follow these two steps to offer gift cards for time passes. A user will be able to purchase a voucher for a time pass, which he can give away as a present. The receiver can then redeem this voucher code and will get access to the purchased time pass.', 'laterpay' ); ?>
                    </dfn>
                    <div class="lp_mb+">
                        <img class="lp_ui-element-preview lp_ui-element-preview--large lp_left lp_mt lp_mr+" src="<?php echo esc_url( $config->get( 'image_url' ) . 'gift-card-instructions-step-1.png' ); ?>">
                        <strong class="lp_block lp_mt lp_mb- lp_pdt-">
                            <?php esc_html_e( 'Step 1: Display Gift Cards', 'laterpay' ); ?>
                        </strong>
                        <dfn class="lp_block">
                            <?php esc_html_e( 'Use the shortcode \'[laterpay_gift_card]\' to render a gift card.', 'laterpay' ); ?><br>
                            <?php esc_html_e( 'If you add the parameter \'id\', you can offer a gift card for a specific time pass. If you don\'t provide an id, gift cards for all time passes are rendered.', 'laterpay' ); ?><br>
                            <?php esc_html_e( 'You can find the id of each time pass in the <a href="admin.php?page=laterpay-pricing-tab#lp_time-passes">pricing tab</a> next to the respective time pass.', 'laterpay' ); ?><br>
                        </dfn>
                        <code class="lp_code-snippet--deprecated lp_code-snippet--large lp_block">
                            <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                            [laterpay_gift_card id="<dfn>1</dfn>"]
                            <div class="lp_text-align--center lp_m"><?php esc_html_e( 'or', 'laterpay' ); ?></div>
                            [laterpay_gift_card]
                        </code>
                    </div>

                    <div class="lp_clearfix">
                        <img class="lp_ui-element-preview lp_ui-element-preview--large lp_left lp_mt lp_mr+" src="<?php echo esc_url( $config->get( 'image_url' ) . 'gift-card-instructions-step-2.png' ); ?>">
                        <strong class="lp_block lp_mt lp_mb- lp_pdt-">
                            <?php esc_html_e( 'Step 2: Add Option to Redeem Vouchers', 'laterpay' ); ?>
                        </strong>
                        <dfn class="lp_block">
                            <?php esc_html_e( 'You can render a form where your users can enter a voucher code with the shortcode \'[laterpay_redeem_voucher]\'.', 'laterpay' ); ?>
                        </dfn>
                        <code class="lp_code-snippet--deprecated lp_code-snippet--large lp_block">
                            <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                            [laterpay_redeem_voucher]
                        </code>
                    </div>
                </div>
                <hr class="lp_form-group-separator">

                <div class="lp_clearfix">
                    <div>
                        <h2><?php esc_html_e( 'Offer of Paid Content within (Free) Posts', 'laterpay' ); ?></h2>
                        <h3><?php esc_html_e( 'Offer of Additional Paid Content', 'laterpay' ); ?></h3>
                        <dfn>
                            <?php esc_html_e( 'Insert shortcode [laterpay_premium_download] into a post to render a box for selling additional paid content.', 'laterpay' ); ?>
                        </dfn>
                        <code class="lp_code-snippet--deprecated lp_code-snippet--shown-above lp_block">
                            <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                            [laterpay_premium_download target_post_id="<dfn>127</dfn>" target_post_title="
                            <dfn>Event video footage</dfn>"content_type="
                            <dfn>video</dfn>"teaser_image_path="
                            <dfn>/uploads/images/concert-video-still.jpg</dfn>" heading_text="
                            <dfn>Video footage of concert</dfn>"description_text="
                            <dfn>Full HD video of the entire concert, including behind the scenes action.</dfn>"]

                        </code>
                        <table class="lp_mb">
                            <tr>
                                <td class="lp_pdl0">
                                    <img class="lp_ui-element-preview lp_ui-element-preview--large" src="<?php echo esc_url( $config->get( 'image_url' ) . 'shortcode-2x.png' ); ?>">
                                </td>
                                <td>
                                    <table>
                                        <tr>
                                            <td>
                                                <pre>target_post_id</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'The ID of the post that contains the paid content.', 'laterpay' ); ?><br>
                                                <dfn data-icon="n"><?php esc_html__('Page IDs are unique within a WordPress blog and should thus be used instead of the target_post_title.', 'laterpay' ) . '<br>' . esc_html__( 'If both target_post_id and target_post_title are provided, the target_post_title will be ignored.', 'laterpay' ); ?></dfn>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <pre>target_post_title</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'The title of the post that contains the paid content.', 'laterpay' ); ?><br>
                                                <dfn data-icon="n"><?php esc_html_e( 'Changing the title of the linked post requires updating the shortcode accordingly.', 'laterpay' ); ?></dfn>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <pre>content_type</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'Content type of the linked content.', 'laterpay' ); ?><br>
                                                <?php esc_html_e( 'Choose between \'audio\', \'video\', \'text\', \'gallery\', and \'file\' to display the corresponding default teaser image provided by the plugin.', 'laterpay' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <pre>teaser_image_path</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'Path to a 300 x 300 px image that should be used instead of the default LaterPay teaser image.', 'laterpay' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <pre>heading_text</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'Text that should be displayed as heading in the box rendered by the shortcode. The heading is limited to one line.', 'laterpay' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <pre>description_text</pre>
                                            </td>
                                            <td>
                                                <?php esc_html_e( 'Text that provides additional information on the paid content.', 'laterpay' ); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="lp_clearfix">
                        <h3><?php esc_html_e( 'Alignment of Additional Paid Content Boxes', 'laterpay' ); ?></h3>
                        <dfn>
                            <?php esc_html_e( 'Enclose multiple [laterpay_premium_download] shortcodes in a [laterpay_box_wrapper] shortcode to align them in a three-column layout.', 'laterpay' ); ?>
                        </dfn>
                        <img class="lp_ui-element-preview lp_ui-element-preview--large lp_left lp_mt lp_mr+" src="<?php echo esc_url( $config->get( 'image_url' ) . 'shortcode-alignment-2x.png' ); ?>">
                        <code class="lp_code-snippet--deprecated lp_code-snippet--large lp_block">
                            <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
                            [laterpay_box_wrapper]'<dfn>[laterpay_premium_download &hellip;][laterpay_premium_download &hellip;]</dfn>[/laterpay_box_wrapper]'
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
