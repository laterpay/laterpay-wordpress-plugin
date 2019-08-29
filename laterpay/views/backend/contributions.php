<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

// Values are used more than once in the view, so create variables for reuse.
$settings_url = admin_url( 'options-general.php?page=laterpay' );
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

        <div class="lp_main_area">
            <div class="lp_layout">
                <div class="lp_clearfix">
                    <label class="lp_step_label">
                        <?php
                        printf(
                            esc_html__( '%sConfigure%s', 'laterpay' ),
                            '<span class="lp_step_span">',
                            '</span>'
                        );
                        ?>
                    </label>
                </div>
                <div class="lp_clearfix">
                    <label class="lp_step_label">
                        <span class="lp_step_span"><?php esc_html_e( 'Preview' ); ?></span>
                    </label>

                    <div id="lp_contributions_loading">
                        <?php esc_html_e( 'Loading...', 'laterpay' ); ?>
                    </div>

                    <div class="lp_contributions_preview" style="display: none" id="lp_contributions_preview">

                    </div>
                </div>
            </div>
        </div>
        <div class="lp_side_area">
            <div class="lp_clearfix lp_info">
                <div class="lp_side_info">
                    <h2><?php esc_html_e( 'TIPS & TRICKS', 'laterpay' ); ?></h2>
                    <p><?php esc_attr_e( 'Setting up your site can be overwhelming so here are a few tips from the experts at LaterPay to help you determine where to place your contributions request.', 'laterpay' ); ?></p>
                    <ul>
                        <li>
                            <?php
                            printf(
                                esc_html__( '%sOption 1: At the end of your posts%s %sUsers who reach the end of an article, video or interactive experience are much more likely to pay you for your hard work.', 'laterpay' ),
                                '<b>',
                                '</b>',
                                '<br/>'
                            ); ?>
                        </li>
                        <li>
                            <?php
                            printf(
                                esc_html__( '%sOption 2: In the header or sidebar%s %sThe primary benefit of placing the contributions shortcode in your site header or sidebar is that you can copy and paste it once in your theme and it will be displayed throughout your site.', 'laterpay' ),
                                '<b>',
                                '</b>',
                                '<br/>'
                            ); ?>
                        </li>
                        <li>
                            <?php
                            printf(
                                esc_html__( '%sOption 3: In a popup%s %sIf you want to provide multiple contribution options without cluttering your site, one common solution is to add a button to your header or sidebar which will trigger a popup containing the LaterPay contributions shortcode. This option is a bit more involved than the others so %sclick here for detailed instructions%s.', 'laterpay' ),
                                '<b>',
                                '</b>',
                                '<br/>',
                                '<a href="#" target="_blank" class="lp_info_link">',
                                '</a>'
                            ); ?>
                        </li>
                    </ul>
                </div>
                <?php $this->render_faq_support(); ?>
            </div>
        </div>
    </div>
</div>
