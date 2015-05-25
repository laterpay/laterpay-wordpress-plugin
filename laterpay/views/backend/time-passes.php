<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-Message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ) : ?>
            <a href="<?php echo esc_url_raw( add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ) ); ?>"
                class="lp_plugin-mode-indicator"
                data-icon="h">
                <h2 class="lp_plugin-mode-indicator__title"><?php echo laterpay_sanitize_output( __( 'Test mode', 'laterpay' ) ); ?></h2>
                <span class="lp_plugin-mode-indicator__text"><?php echo laterpay_sanitize_output( __( 'Earn money in <i>live mode</i>', 'laterpay' ) ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo laterpay_sanitized( $laterpay['top_nav'] ); ?>
    </div>

    <div class="lp_wrap">

        <div id="lp_js_timePassesKpiTab">

            <h1 class="lp_dashboard-title">
                <?php echo laterpay_sanitize_output( __( 'Time Pass Customer Lifecycle', 'laterpay' ) ); ?>
            </h1>

            <div class="lp_time-pass-lifecycle">
                <div class="lp_time-pass-lifecycle__kpi lp_1/4 lp_left">
                    <h2><?php echo laterpay_sanitize_output( __( 'All Time Passes', 'laterpay' ) ); ?></h2>

                    <div class="lp_statistics-row lp_clearfix">
                        <ul class="lp_clearfix lp_statistics-row__list">
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Total number of sold time passes', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $laterpay['passes']['summary']['sold'] ); ?></big>
                                <?php echo laterpay_sanitize_output( __( 'Sold', 'laterpay' ) ); ?>
                            </li>
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Total number of active time passes', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $laterpay['passes']['summary']['active'] ); ?></big>
                                <?php echo laterpay_sanitize_output( __( 'Active', 'laterpay' ) ); ?>
                            </li>
                            <?php if ( isset( $laterpay['passes']['summary']['unredeemed'] ) ) : ?>
                                <li class="lp_tooltip lp_statistics-row__item"
                                    data-tooltip="<?php echo esc_attr( __( 'Total number of unredeemed time pass vouchers', 'laterpay' ) ); ?>">
                                    <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $laterpay['passes']['summary']['unredeemed'] ); ?></big>
                                    <?php echo laterpay_sanitize_output( __( 'Unredeemed', 'laterpay' ) ); ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <ul class="lp_clearfix lp_statistics-row__list">
                            <li class="lp_tooltip lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Total value of sold time passes', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $laterpay['passes']['summary']['committed_revenue'] ); ?><small class="lp_statistics-row__text-small"><?php echo laterpay_sanitize_output( $laterpay['currency'] ); ?></small></big>
                                <?php echo laterpay_sanitize_output( __( 'Committed Revenue', 'laterpay' ) ); ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lp_3/4 lp_left">
                    <div class="lp_js_timepassDiagram lp_dashboard-graph lp_dashboard-graph--time-pass-lifeycle" data-id="0">
                    </div>
                </div>

            </div>


            <?php if ( isset( $laterpay['passes']['individual'] ) ) : ?>
                <?php foreach ( $laterpay['passes']['individual'] as $pass_id => $pass ) : ?>

                    <div class="lp_time-pass-lifecycle lp_clearfix">
                        <div class="lp_time-pass-lifecycle__kpi lp_1/4 lp_left">
                            <h2><?php echo laterpay_sanitize_output( sprintf( __( 'Time pass \'%s\'', 'laterpay' ), $pass['data']['title'] ) ); ?></h2>
                            <dfn><?php echo laterpay_sanitize_output( LaterPay_Helper_TimePass::get_description( $pass['data'], true ) ); ?></dfn>

                            <div class="lp_statistics-row lp_clearfix">
                                <ul class="lp_clearfix lp_statistics-row__list">
                                    <li class="lp_tooltip lp_statistics-row__item"
                                        data-tooltip="<?php echo esc_attr( __( 'Number of sold time passes', 'laterpay' ) ); ?>">
                                        <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $pass['sold'] ); ?></big>
                                        <?php echo laterpay_sanitize_output( __( 'Sold', 'laterpay' ) ); ?>
                                    </li>
                                    <li class="lp_tooltip lp_statistics-row__item"
                                        data-tooltip="<?php echo esc_attr( __( 'Number of active time passes', 'laterpay' ) ); ?>">
                                        <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $pass['active'] ); ?></big>
                                        <?php echo laterpay_sanitize_output( __( 'Active', 'laterpay' ) ); ?>
                                    </li>
                                    <?php if ( isset( $pass['unredeemed'] ) ) : ?>
                                        <li class="lp_tooltip lp_statistics-row__item"
                                            data-tooltip="<?php echo esc_attr( __( 'Number of unredeemed time pass vouchers', 'laterpay' ) ); ?>">
                                            <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $pass['unredeemed'] ); ?></big>
                                            <?php echo laterpay_sanitize_output( __( 'Unredeemed', 'laterpay' ) ); ?>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <ul>
                                    <li class="lp_tooltip lp_statistics-row__item"
                                        data-tooltip="<?php echo esc_attr( __( 'Value of sold time passes', 'laterpay' ) ); ?>">
                                        <big class="lp_statistics-row__value"><?php echo laterpay_sanitize_output( $pass['committed_revenue'] ); ?><small class="lp_statistics-row__text-small"><?php echo laterpay_sanitize_output( $laterpay['currency'] ); ?></small></big>
                                        <?php echo laterpay_sanitize_output( __( 'Committed Revenue', 'laterpay' ) ); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="lp_3/4 lp_left">
                            <div class="lp_js_timepassDiagram lp_dashboard-graph lp_dashboard-graph--time-pass-lifeycle" data-id="<?php echo esc_attr( $pass_id ); ?>">
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
