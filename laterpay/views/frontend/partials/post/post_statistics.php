<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php $currency = $laterpay[ 'currency' ]; ?>

<div id="lp_js_post-statistics" class="lp_post-statistics<?php if ( $laterpay['hide_statistics_pane'] ) echo ' lp_is_hidden'; ?>">
    <form id="lp_js_post-statistics-visibility-form" method="post">
        <input type="hidden" name="action" value="laterpay_post_statistic_visibility">
        <input type="hidden" id="lp_js_hide-statistics-pane-input" name="hide_statistics_pane" value="<?php echo $laterpay['hide_statistics_pane'];?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="lp_js_toggle-post-statistics-visibility" class="lp_post-statistics-visibility-toggle" data-icon="l"></a>
    <h2 data-icon="a"><?php _e( 'Statistics for this Post', 'laterpay' ); ?></h2>
    <div class="lp_post-statistics-totals">
        <ul>
            <li>
                <big><?php if ( isset( $statistic['total'][$currency] ) ) { $aux = $statistic['total'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( $aux, 2 ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                <small><?php _e( 'Total Revenue', 'laterpay' ); ?></small>
            </li>
            <li>
                <big><?php if ( isset( $statistic['total'][$currency] ) ) { $aux = $statistic['total'][$currency]['quantity']; } else { $aux = 0; }; echo $aux; ?></big>
                <small><?php _e( 'Total Sales', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics-separator">
        <ul>
            <li><p><?php _e( 'Last 30 days', 'laterpay' ); ?></p><hr></li>
            <li><p><?php _e( 'Today', 'laterpay' ); ?></p><hr></li>
        </ul>
    </div>
    <div class="lp_post-statistics-details">
        <ul>
            <li>
                <span class="lp_sparkline-bar"><?php if ( isset( $statistic['last30DaysRevenue'][$currency] ) ) { $aux = $statistic['last30DaysRevenue'][$currency]; } else { $aux = array(); }; echo LaterPay_Helper_View::get_days_statistics_as_string( $aux, 'sum', ';' ); ?></span>
            </li>
            <li>
                <big><?php if ( isset( $statistic['todayRevenue'][$currency] ) ) { $aux = $statistic['todayRevenue'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( $aux, 2 ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                <small><?php _e( 'Revenue', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics-details">
        <ul>
            <li>
                <span class="lp_sparkline-bar" data-max="0.5"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $statistic['last30DaysBuyers'], 'percentage', ';' ); ?></span>
                <span class="lp_sparkline-background-bar">1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1</span>
            </li>
            <li>
                <big><?php echo LaterPay_Helper_View::format_number( $statistic['todayBuyers'], 1 ); ?><small>%</small></big>
                <small><?php _e( 'Buyers', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_post-statistics-details">
        <ul>
            <li>
                <span class="lp_sparkline-bar"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $statistic['last30DaysVisitors'], 'quantity', ';' ); ?></span>
            </li>
            <li>
                <big><?php echo $statistic['todayVisitors']; ?></big>
                <small><?php _e( 'Visitors', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="lp_plugin-preview-mode">
        <?php _e( 'Preview post as', 'laterpay' ); ?> <strong><?php _e( 'Admin', 'laterpay' ); ?></strong>
        <div class="lp-toggle">
            <form id="lp_plugin-preview-mode-form" method="post">
                <input type="hidden" name="action" value="laterpay_post_statistic_toggle_preview">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                <label class="lp-toggle-label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="lp_js_toggle-post-preview-mode"
                            class="lp-toggle-input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ): ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="lp_js_preview-post-input"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="lp-toggle-text" data-on="" data-off=""></span>
                    <span class="lp-toggle-handle"></span>
                </label>
            </form>
        </div>
        <strong><?php _e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
