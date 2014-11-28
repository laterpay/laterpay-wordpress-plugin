<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flashMessage" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_u_relative">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_pluginModeIndicator lp_u_absolute" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $laterpay['top_nav']; ?>
    </div>

    <div class="lp_wrap">

    <h1><?php echo sprintf( __( '%s Dashboard of %s Sales between%s%s%s', 'laterpay' ),
            '<select id="lp_js_selectDashboardInterval" class="lp_dashboardIntervalSelect lp_input"><option value="day">1 day</option><option value="week" selected>1 week</option><option value="2-weeks">2 weeks</option><option value="month">1 month</option></select>',
            '<select id="lp_js_selectRevenueModel" class="lp_dashboardRevenueModelSelect lp_input"><option value="all">all</option><option value="ppu">PPU</option><option value="sis">SIS</option></select>',
            '<a href="#" id="lp_js_loadPreviousInterval" class="lp_prevNextLink lp_tooltip" data-tooltip="Show week before"><div class="lp_triangle lp_triangle--left"></div></a>',
            '17.10. - 24.10.',
            '<a href="#" id="lp_js_loadNextInterval" class="lp_prevNextLink lp_tooltip" data-tooltip="Show week after"><div class="lp_triangle lp_triangle--right"></div></a>'
        ); ?></h1>
    <a href="#" id="lp_js_refreshDashboard">Refresh</a>

        <div class="lp_row">
            <div class="lp_u_w-1-3">
                <h2><?php _e( 'Conversion', 'laterpay' ); ?></h2>
                <div id="lp_js_conversionDiagram" class="lp_dashboardGraph"></div>
                <div class="lp_statisticsRow lp_u_clearfix">
                    <ul>
                        <li>
                            <big><span id="lp_js_totalImpressions"></span></big>
                            <?php _e( 'Impressions', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_avgConversion"></span><small>%</small></big>
                            <?php _e( 'Conversion', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_shareOfNewCustomers"></span><small>%</small></big>
                            <?php _e( 'New Customers', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="lp_u_w-1-3">
                <h2><?php _e( 'Items Sold', 'laterpay' ); ?></h2>
                <div id="lp_js_salesDiagram" class="lp_dashboardGraph"></div>
                <div class="lp_statisticsRow lp_u_clearfix">
                    <ul>
                        <li>
                            <big><span id="lp_js_avg-items-sold"></span></big>
                            <?php _e( 'AVG Items Sold', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_total-items-sold"></span></big>
                            <?php _e( 'Total Items Sold', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="lp_u_w-1-3">
                <h2><?php _e( 'Committed Revenue', 'laterpay' ); ?></h2>
                <div id="lp_js_revenueDiagram" class="lp_dashboardGraph"></div>
                <div class="lp_statisticsRow lp_u_clearfix">
                    <ul>
                        <li>
                            <big><span id="lp_js_avgRevenue"></span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'AVG Purchase', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_totalRevenue"></span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'Total Revenue', 'laterpay' ); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lp_row">
            <div class="lp_u_w-1-3">
                <h3><?php _e( 'Best-converting Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_bestConvertingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['best_converting_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['best_converting_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_valueNarrow"><?php echo $item->amount; ?><small>%</small></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
                <h3><?php _e( 'Least-converting Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_leastConvertingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['least_converting_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['least_converting_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_valueNarrow"><?php echo $item->amount; ?><small>%</small></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </div>
            <div class="lp_u_w-1-3">
                <h3><?php _e( 'Most-selling Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_bestSellingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['most_selling_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['most_selling_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_valueNarrow"><?php echo $item->amount; ?></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
                <h3><?php _e( 'Least-selling Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_leastSellingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['least_selling_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['least_selling_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value lp_valueNarrow"><?php echo $item->amount; ?></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </div>
            <div class="lp_u_w-1-3">
                <h3><?php _e( 'Most Revenue-generating Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_bestGrossingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['most_revenue_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['most_revenue_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value"><?php echo $item->amount; ?><small><?php echo $laterpay['currency']; ?></small></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
                <h3><?php _e( 'Least Revenue-generating Items', 'laterpay' ); ?></h3>
                <ol id="lp_js_leastGrossingList" class="lp_topBottomList">
                    <?php if ( empty( $laterpay['least_revenue_items'] ) ) : ?>
                        <dfn><?php _e( 'No data available', 'laterpay' ); ?></dfn>
                    <?php else: ?>
                        <?php foreach ( $laterpay['least_revenue_items'] as $item ): ?>
                            <li>
                                <span class="lp_sparklineBar"><?php echo $item->sparkline; ?></span>
                                <strong class="lp_value"><?php echo $item->amount; ?><small><?php echo $laterpay['currency']; ?></small></strong>
                                <i><a href="#" class="lp_js_toggleItemDetails"><?php echo get_the_title( $item->post_id ); ?></a></i>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </div>
        </div>

    </div>

</div>
