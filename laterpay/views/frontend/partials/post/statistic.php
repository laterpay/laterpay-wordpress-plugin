<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php $currency = $laterpay[ 'currency' ]; ?>

<div id="statistics"<?php if ( $laterpay['hide_statistics_pane'] ) echo ' class="hidden"'; ?>>
    <form id="laterpay_hide_statistics_form" method="post">
        <input type="hidden" name="form"    value="hide_statistics_pane">
        <input type="hidden" name="action"  value="laterpay_admin">
        <input type="hidden" name="hide_statistics_pane"  value="<?php echo $laterpay['hide_statistics_pane'];?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
    </form>
    <a href="#" id="toggle-laterpay-statistics-pane" data-icon="l"></a>
    <h2 data-icon="a"><?php _e( 'Statistics for this Post', 'laterpay' ); ?></h2>
    <div class="totals">
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
    <div class="separator">
        <ul>
            <li><p><?php _e( 'Last 30 days', 'laterpay' ); ?></p><hr></li>
            <li><p><?php _e( 'Today', 'laterpay' ); ?></p><hr></li>
        </ul>
    </div>
    <div class="details">
        <ul>
            <li>
                <span class="bar"><?php if ( isset( $statistic['last30DaysRevenue'][$currency] ) ) { $aux = $statistic['last30DaysRevenue'][$currency]; } else { $aux = array(); }; echo LaterPay_Helper_View::get_days_statistics_as_string( $aux, 'sum', ';' ); ?></span>
            </li>
            <li>
                <big><?php if ( isset( $statistic['todayRevenue'][$currency] ) ) { $aux = $statistic['todayRevenue'][$currency]['sum']; } else { $aux = 0; }; echo LaterPay_Helper_View::format_number( $aux, 2 ); ?><small><?php echo $laterpay['currency']; ?></small></big>
                <small><?php _e( 'Revenue', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="details">
        <ul>
            <li>
                <span class="bar" data-max="0.5"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $statistic['last30DaysBuyers'], 'percentage', ';' ); ?></span>
                <span class="background-bar">1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1</span>
            </li>
            <li>
                <big><?php echo LaterPay_Helper_View::format_number( $statistic['todayBuyers'], 1 ); ?><small>%</small></big>
                <small><?php _e( 'Buyers', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div class="details">
        <ul>
            <li>
                <span class="bar"><?php echo LaterPay_Helper_View::get_days_statistics_as_string( $statistic['last30DaysVisitors'], 'quantity', ';' ); ?></span>
            </li>
            <li>
                <big><?php echo $statistic['todayVisitors']; ?></big>
                <small><?php _e( 'Visitors', 'laterpay' ); ?></small>
            </li>
        </ul>
    </div>
    <div id="plugin-visibility">
        <?php _e( 'Preview post as', 'laterpay' ); ?> <strong><?php _e( 'Admin', 'laterpay' ); ?></strong>
        <div class="switch">
            <form id="plugin_mode" method="post">
                <input type="hidden" name="form"    value="post_page_preview">
                <input type="hidden" name="action"  value="laterpay_admin">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                <label class="switch-label">
                    <input type="checkbox"
                            name="preview_post_checkbox"
                            id="preview-post-toggle"
                            class="switch-input"
                            <?php if ( $laterpay['preview_post_as_visitor'] == 1 ): ?>checked<?php endif; ?>>
                    <input type="hidden"
                            name="preview_post"
                            id="preview_post_hidden_input"
                            value="<?php if ( $laterpay['preview_post_as_visitor'] == 1 ) { echo 1; } else { echo 0; } ?>">
                    <span class="switch-text" data-on="" data-off=""></span>
                    <span class="switch-handle"></span>
                </label>
            </form>
        </div>
        <strong><?php _e( 'Visitor', 'laterpay' ); ?></strong>
    </div>
</div>
