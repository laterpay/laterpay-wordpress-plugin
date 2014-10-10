<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flash-message" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_p-rel">
        <?php if ( ! $plugin_is_in_live_mode ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $admin_menu['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_plugin-mode-indicator lp_p-abs" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $top_nav; ?>
    </div>

    <div class="lp_wrap">

        <div class="lp_row">
            <div class="lp_w-1-3">
                <h2><?php _e( 'Conversion', 'laterpay' ); ?></h2>
                <div id="lp_js_graph-conversion" class="lp_dashboard-graph"></div>
                <div class="lp_statistics-row lp_fl-clearfix">
                    <ul>
                        <li>
                            <big>6,123</big>
                            <?php _e( 'Impressions', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big>6.3<small>%</small></big>
                            <?php _e( 'Conversion', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big>17<small>%</small></big>
                            <?php _e( 'New Customers', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="lp_w-1-3">
                <h2><?php _e( 'Items Sold', 'laterpay' ); ?></h2>
                <div id="lp_js_graph-units" class="lp_dashboard-graph"></div>
                <div class="lp_statistics-row lp_fl-clearfix">
                    <ul>
                        <li>
                            <big id="id_avg_items_sold"></big>
                            <?php _e( 'AVG Items Sold', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big id="id_total_items_sold"></big>
                            <?php _e( 'Total Items Sold', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="lp_w-1-3">
                <h2><?php _e( 'Revenue', 'laterpay' ); ?></h2>
                <div id="lp_js_graph-revenue" class="lp_dashboard-graph"></div>
                <div class="lp_statistics-row lp_fl-clearfix">
                    <ul>
                        <li>
                            <big><span id="id_avg_revenue"></span><small><?php echo $currency; ?></small></big>
                            <?php _e( 'AVG Purchase', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="id_total_revenue"></span><small><?php echo $currency; ?></small></big>
                            <?php _e( 'Total Revenue', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lp_row">
            <div class="lp_w-1-3">
                <h3><?php _e( 'Best-converting Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $best_converting_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <h3><?php _e( 'Least-converting Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $least_converting_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <div class="lp_w-1-3">
                <h3><?php _e( 'Best-selling Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $most_selling_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <h3><?php _e( 'Least-selling Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $least_selling_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <div class="lp_w-1-3">
                <h3><?php _e( 'Most Revenue-generating Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $most_revenue_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <h3><?php _e( 'Least Revenue-generating Items', 'laterpay' ); ?></h3>
                <ol class="lp_top-bottom-list">
                    <?php foreach ( $least_revenue_items as $item ): ?>
                        <li>
                            <span class="lp_sparkline-bar"><?php echo $item['sparkline']; ?></span>
                            <strong class="lp_value lp_value-3"><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                            <i><?php echo $item['title']; ?></i>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>

    </div>

</div>
