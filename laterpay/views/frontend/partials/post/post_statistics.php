<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php $currency = $laterpay[ 'currency' ]; ?>

<div id="lp_js_postStatistics" class="lp_postStatistics<?php if ( $laterpay['hide_statistics_pane'] ) echo ' lp_is-hidden'; ?>">
    <form id="lp_js_postStatistics_visibilityForm" method="post">
        <input type="hidden" name="action" value="laterpay_post_statistic_visibility">
        <input type="hidden" id="lp_js_postStatistics_visibilityInput" name="hide_statistics_pane" value="<?php echo $laterpay['hide_statistics_pane'];?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_togglePostStatisticsVisibility" class="lp_postStatistics_visibilityToggle" data-icon="l"></a>
    <h2 data-icon="a"><?php _e( 'Statistics for this Post', 'laterpay' ); ?></h2>
    <div class="lp_postStatistics_totals">
        <ul>
            <li>
                <big><?php if ( isset( $laterpay['statistic']['total'][$currency] ) ) { $aux = $laterpay['statistic']['total'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                <small><?php _e( 'Total Revenue', 'laterpay' ); ?></small>
            </li>
            <li>
                <big><?php if ( isset( $laterpay['statistic']['total'][$currency] ) ) { $aux = $laterpay['statistic']['total'][$currency]['quantity']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux, false ); ?></big>
                <small><?php _e( 'Total Sales', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_postStatistics_separator">
        <ul>
            <li><p><?php _e( 'Last 30 days', 'laterpay' ); ?></p><hr></li>
            <li><p><?php _e( 'Today', 'laterpay' ); ?></p><hr></li>
        </ul>
    </div>
    <div class="lp_postStatistics_details">
        <ul>
            <li>
                <span class="lp_sparklineBar"><?php if ( isset( $laterpay['statistic']['last30DaysRevenue'][$currency] ) ) { $aux = $laterpay['statistic']['last30DaysRevenue'][$currency]; } else { $aux = array(); }; echo LaterPay_Helper_View::get_days_statistics_as_string( $aux, 'sum', ';' ); ?></span>
            </li>
            <li>
                <big><?php if ( isset( $laterpay['statistic']['todayRevenue'][$currency] ) ) { $aux = $laterpay['statistic']['todayRevenue'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                <small><?php _e( 'Revenue', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_postStatistics_details">
        <ul>
            <li>
                <span class="lp_sparklineBar" data-max="0.5"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $laterpay['statistic']['last30DaysBuyers'], 'percentage', ';' ); ?></span>
                <span class="lp_sparklineBackgroundBar">1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1</span>
            </li>
            <li>
                <big><?php echo $laterpay['statistic']['todayBuyers']; ?><small>%</small></big>
                <small><?php _e( 'Buyers', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_postStatistics_details">
        <ul>
            <li>
                <span class="lp_sparklineBar"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $laterpay['statistic']['last30DaysVisitors'], 'quantity', ';' ); ?></span>
            </li>
            <li>
                <big><?php echo LaterPay_Helper_View::format_number( $laterpay['statistic']['todayVisitors'], false ); ?></big>
                <small><?php _e( 'Visitors', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_postStatistics_pluginPreviewMode">
        <?php _e( 'Preview post as', 'laterpay' ); ?> <strong><?php _e( 'Admin', 'laterpay' ); ?></strong>
        <div class="lp_toggle">
            <form id="lp_js_postStatistics_pluginPreviewModeForm" method="post">
                <input type="hidden" name="action" value="laterpay_post_statistic_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                <label class="lp_toggle_label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_togglePostPreviewMode"
                            class="lp_toggle_input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ): ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_postPreviewModeInput"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="lp_toggle_text" data-on="" data-off=""></span>
                    <span class="lp_toggle_handle"></span>
                </label>
            </form>
        </div>
        <strong><?php _e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
