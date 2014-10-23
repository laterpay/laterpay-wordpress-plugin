<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flash-message" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_p-rel">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_plugin-mode-indicator lp_p-abs" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $laterpay['top_nav']; ?>
    </div>

    <div class="lp_wrap">

    <a href="#" id="lp_js_refresh-dashboard">Refresh</a>

        <div class="lp_row">
            <div class="lp_w-1-3">
                <h2><?php _e( 'Conversion', 'laterpay' ); ?></h2>
                <div id="lp_js_graph-conversion" class="lp_dashboard-graph"></div>
                <div class="lp_statistics-row lp_fl-clearfix">
                    <ul>
                        <li>
                            <big id="lp_js_total-impressions">6,123</big>
                            <?php _e( 'Impressions', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big id="lp_js_conversion">6.3<small>%</small></big>
                            <?php _e( 'Conversion', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big id="lp_js_share-of-new-customers">17<small>%</small></big>
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
                            <big id="lp_js_avg-items-sold"></big>
                            <?php _e( 'AVG Items Sold', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big id="lp_js_total-items-sold"></big>
                            <?php _e( 'Total Items Sold', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="lp_w-1-3">
                <h2><?php _e( 'Committed Revenue', 'laterpay' ); ?></h2>
                <div id="lp_js_graph-revenue" class="lp_dashboard-graph"></div>
                <div class="lp_statistics-row lp_fl-clearfix">
                    <ul>
                        <li>
                            <big><span id="lp_js_avg-revenue"></span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'AVG Purchase', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_total-revenue"></span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'Total Revenue', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lp_row">
            <div class="lp_w-1-3">
                <h3><?php _e( 'Best-converting Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['best_converting_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['best_converting_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_value-narrow"><?php echo $item->amount; ?><small>%</small></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <h3><?php _e( 'Least-converting Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['least_converting_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['least_converting_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_value-narrow"><?php echo $item->amount; ?><small>%</small></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
            <div class="lp_w-1-3">
                <h3><?php _e( 'Most-selling Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['most_selling_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['most_selling_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_value-narrow"><?php echo $item->amount; ?></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <h3><?php _e( 'Least-selling Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['least_selling_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['least_selling_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_value-narrow"><?php echo $item->amount; ?></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
            <div class="lp_w-1-3">
                <h3><?php _e( 'Most Revenue-generating Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['most_revenue_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['most_revenue_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value"><?php echo $item->amount; ?><small><?php echo $laterpay['currency']; ?></small></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <h3><?php _e( 'Least Revenue-generating Items', 'laterpay' ); ?></h3>
                <?php if( empty( $laterpay['least_revenue_items'] ) ) : ?>
                    <p><?php _e( 'No data available', 'laterpay' ); ?></p>
                <?php else: ?>
                    <ol class="lp_top-bottom-list">
                        <?php foreach ( $laterpay['least_revenue_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparkline-bar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value"><?php echo $item->amount; ?><small><?php echo $laterpay['currency']; ?></small></strong>
                                <i><a href="#" class="lp_js_toggle-item-details"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
