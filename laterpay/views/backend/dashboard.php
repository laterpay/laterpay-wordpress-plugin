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
            '<div class="lp_dropdown">' .
                '<span class="lp_dropdown_currentItem">' . __( 'Weekly', 'laterpay' ) . '</span>' .
                '<div class="lp_dropdown_list">' .
                    '<div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>' .
                    '<div class="lp_dropdown_listItem">' .
                        '<a href="#" class="lp_js_selectDashboardInterval lp_dropdown_link" data-interval="day">' .
                            __( '24 Hour', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                    '<div class="lp_dropdown_listItem lp_is-selected">' .
                        '<a href="#" class="lp_js_selectDashboardInterval lp_dropdown_link" data-interval="week">' .
                             __( 'Weekly', 'laterpay' ) .
                         '</a>' .
                    '</div>' .
                    '<div class="lp_dropdown_listItem">' .
                        '<a href="#" class="lp_js_selectDashboardInterval lp_dropdown_link" data-interval="2-weeks">' .
                            __( 'Biweekly', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                    '<div class="lp_dropdown_listItem">' .
                        '<a href="#" class="lp_js_selectDashboardInterval lp_dropdown_link" data-interval="month">'
                            . __( 'Monthly', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                '</div>' .
            '</div>',

            '<div class="lp_dropdown">' .
                '<span class="lp_dropdown_currentItem">' . __( 'all', 'laterpay' ) . '</span>' .
                '<div class="lp_dropdown_list">' .
                    '<div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>' .
                    '<div class="lp_dropdown_listItem lp_is-selected">' .
                        '<a href="#" class="lp_js_selectRevenueModel lp_dropdown_link" data-revenue-model="all">' .
                            __( 'all', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                    '<div class="lp_dropdown_listItem">' .
                        '<a href="#" class="lp_js_selectRevenueModel lp_dropdown_link" data-revenue-model="ppu">' .
                            __( 'PPU', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                    '<div class="lp_dropdown_listItem">' .
                        '<a href="#" class="lp_js_selectRevenueModel lp_dropdown_link" data-revenue-model="sis">' .
                            __( 'SIS', 'laterpay' ) .
                        '</a>' .
                    '</div>' .
                '</div>' .
            '</div>',

            '<a href="#" id="lp_js_loadPreviousInterval" class="lp_prevNextLink lp_tooltip" data-tooltip="Show week before">' .
                '<div class="lp_triangle lp_triangle--left"></div>' .
            '</a>',

            '<span id="lp_js_displayedInterval">' . date( 'j.n.', strtotime( date() . '-8 days' ) ) . ' &ndash; ' . date( 'j.n.', strtotime( date() . '-1 days' ) ) . '</span>',

            '<a href="#" id="lp_js_loadNextInterval" class="lp_prevNextLink lp_tooltip" data-tooltip="Show week after">' .
                '<div class="lp_triangle lp_triangle--right"></div>' .
            '</a>'
        ); ?>
        <a href="#" id="lp_js_refreshDashboard" class="lp_DashboardRefreshLink"><?php _e( 'Refresh', 'laterpay' ); ?></a></h1>

        <div class="lp_row">
            <div class="lp_u_w-1-3">
                <h2><?php _e( 'Conversion', 'laterpay' ); ?></h2>
                <div id="lp_js_conversionDiagram" class="lp_dashboardGraph"></div>
                <div class="lp_statisticsRow lp_u_clearfix">
                    <ul>
                        <li>
                            <big><span id="lp_js_totalImpressions">0</span></big>
                            <?php _e( 'Views', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_avgConversion">0.0</span><small>%</small></big>
                            <?php _e( 'Conversion', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_shareOfNewCustomers">0</span><small>%</small></big>
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
                            <big><span id="lp_js_avg-items-sold">0.0</span></big>
                            <?php _e( 'AVG Items Sold', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_total-items-sold">0</span></big>
                            <?php _e( 'Items Sold', 'laterpay' ); ?>
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
                            <big><span id="lp_js_avgRevenue">0.00</span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'AVG Purchase', 'laterpay' ); ?>
                        </li>
                        <li>
                            <big><span id="lp_js_totalRevenue">0.00</span><small><?php echo $laterpay['currency']; ?></small></big>
                            <?php _e( 'Revenue', 'laterpay' ); ?>
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
