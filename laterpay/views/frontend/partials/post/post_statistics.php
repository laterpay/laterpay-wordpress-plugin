<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php $currency = $laterpay['currency']; ?>

<div id="lp_js_postStatistics" class="lp_post-statistics<?php if ( $laterpay['hide_statistics_pane'] ) echo ' lp_is-hidden'; ?>">
    <form id="lp_js_postStatisticsVisibilityForm" method="post">
        <input type="hidden" name="action" value="laterpay_post_statistic_visibility">
        <input type="hidden" id="lp_js_postStatisticsVisibilityInput" name="hide_statistics_pane" value="<?php echo $laterpay['hide_statistics_pane'];?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_togglePostStatisticsVisibility" class="lp_post-statistics__visibility-toggle" data-icon="l"></a>
    <h2 class="lp_post-statistics__title" data-icon="a"><?php _e( 'Statistics for this Post', 'laterpay' ); ?></h2>
    <div class="lp_post-statistics__totals">
        <ul class="lp_post-statistics__list">
            <li class="lp_post-statistics__item lp_kpi">
                <div>
                    <big><?php if ( isset( $laterpay['statistic']['total'][$currency] ) ) { $aux = $laterpay['statistic']['total'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                    <small><?php _e( 'Total Committed Revenue', 'laterpay' ); ?></small>
                </div>
            </li><!-- no space here due to inline-block
         --><li class="lp_post-statistics__item lp_kpi">
                <div>
                    <big><?php if ( isset( $laterpay['statistic']['total'][$currency] ) ) { $aux = $laterpay['statistic']['total'][$currency]['quantity']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux, false ); ?></big>
                    <small><?php _e( 'Total Sales', 'laterpay' ); ?></small>
                </div>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics__separator">
        <ul class="lp_post-statistics__list">
            <li class="lp_post-statistics__item">
                <span class="lp_post-statistics__text"><?php _e( 'Last 30 days', 'laterpay' ); ?></span>
                <hr class="lp_post-statistics__separator-line">
            </li><!-- no space here due to inline-block
         --><li class="lp_post-statistics__item">
                <span class="lp_post-statistics__text"><?php _e( 'Today', 'laterpay' ); ?></span>
                <hr class="lp_post-statistics__separator-line">
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics__details">
        <ul class="lp_post-statistics__list">
            <li class="lp_post-statistics__item">
                <span class="lp_js_sparklineBar lp_sparkline-bar"><?php if ( isset( $laterpay['statistic']['last30DaysRevenue'][$currency] ) ) { $aux = $laterpay['statistic']['last30DaysRevenue'][$currency]; } else { $aux = array(); }; echo LaterPay_Helper_View::get_days_statistics_as_string( $aux, 'sum', ';' ); ?></span>
            </li><!-- no space here due to inline-block
         --><li class="lp_post-statistics__item lp_kpi">
                <div>
                    <big><?php if ( isset( $laterpay['statistic']['todayRevenue'][$currency] ) ) { $aux = $laterpay['statistic']['todayRevenue'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( (float) $aux ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                    <small><?php _e( 'Committed Revenue', 'laterpay' ); ?></small>
                </div>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics__details">
        <ul class="lp_post-statistics__list">
            <li class="lp_post-statistics__item">
                <span class="lp_js_sparklineBar lp_sparkline-bar" data-max="0.5"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $laterpay['statistic']['last30DaysBuyers'], 'percentage', ';' ); ?></span>
                <span class="lp_js_sparklineBackgroundBar lp_sparkline-bar__background">1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1</span>
            </li><!-- no space here due to inline-block
         --><li class="lp_post-statistics__item lp_kpi">
                <div>
                    <big><?php echo $laterpay['statistic']['todayBuyers']; ?><small>%</small></big>
                    <small><?php _e( 'Conversion', 'laterpay' ); ?></small>
                </div>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics__details">
        <ul class="lp_post-statistics__list">
            <li class="lp_post-statistics__item">
                <span class="lp_js_sparklineBar lp_sparkline-bar"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $laterpay['statistic']['last30DaysVisitors'], 'quantity', ';' ); ?></span>
            </li><!-- no space here due to inline-block
         --><li class="lp_post-statistics__item lp_kpi">
                <div>
                    <big><?php echo LaterPay_Helper_View::format_number( $laterpay['statistic']['todayVisitors'], false ); ?></big>
                    <small><?php _e( 'Views', 'laterpay' ); ?></small>
                </div>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics__plugin-preview-mode">
        <?php _e( 'Preview post as', 'laterpay' ); ?> <strong><?php _e( 'Admin', 'laterpay' ); ?></strong>
        <div class="lp_toggle">
            <form id="lp_js_postStatisticsPluginPreviewModeForm" method="post">
                <input type="hidden" name="action" value="laterpay_post_statistic_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
                <label class="lp_toggle__label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_togglePostPreviewMode"
                            class="lp_toggle__input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ): ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_postPreviewModeInput"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="lp_toggle__text" data-on="" data-off=""></span>
                    <span class="lp_toggle__handle"></span>
                </label>
            </form>
        </div>
        <strong><?php _e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
