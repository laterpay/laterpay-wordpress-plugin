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

    <?php
        // Mock data:
        $currency               = 'EUR';
        $best_converting_items  = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_converting_items = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $most_selling_items     = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_selling_items    = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $most_revenue_items     = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_revenue_items    = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
    ?>

    <div class="lp-wrap">
        <div class="lp_row">

            <div class="pure-g-r content">
                <div class="pure-u-1-3">
                    <h2><?php _e( 'Conversion', 'laterpay' ); ?></h2>
                    <div id="lp_js_graph-conversion" class="dashboard-graph"></div>
                    <div class="statistics-row pure-group">
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
                <div class="pure-u-1-3">
                    <h2><?php _e( 'Items Sold', 'laterpay' ); ?></h2>
                    <div id="lp_js_graph-units" class="dashboard-graph"></div>
                    <div class="statistics-row pure-group">
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
                <div class="pure-u-1-3">
                    <h2><?php _e( 'Revenue', 'laterpay' ); ?></h2>
                    <div id="lp_js_graph-revenue" class="dashboard-graph"></div>
                    <div class="statistics-row pure-group">
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

            <div class="pure-g-r content">
                <div class="pure-u-1-3">
                    <h3><?php _e( 'Best-converting Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $best_converting_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <h3><?php _e( 'Least-converting Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $least_converting_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="pure-u-1-2">
                    <h3><?php _e( 'Best-selling Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $most_selling_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <h3><?php _e( 'Least-selling Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $least_selling_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="pure-u-1-2">
                    <h3><?php _e( 'Most Revenue-generating Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $most_revenue_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <h3><?php _e( 'Least Revenue-generating Items', 'laterpay' ); ?></h3>
                    <ol class="top-bottom-list">
                        <?php foreach ( $least_revenue_items as $item ): ?>
                            <li>
                                <span class="peity-bar"><?php echo '2,5,3,9,6,5,9'; # echo sparkline data here ?></span>
                                <strong class='value value-3'><?php echo $item['amount']; ?><small><?php echo $currency; ?></small></strong>
                                <i><?php echo $item['title']; ?></i>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>

        </div>
    </div>

</div>
