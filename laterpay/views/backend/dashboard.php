<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flash-message" style="display:none;">
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
        <div id="lp_js_standardKpiTab" class="lp_dashboard">

            <h1 class="lp_dashboard-title"><?php
                echo sprintf(
                    laterpay_sanitize_output( __( '%s Dashboard of %s Sales from%s', 'laterpay' ) ),
                    laterpay_sanitized( $this->get_text_view( 'backend/partials/dropdown-interval' ) ),
                    laterpay_sanitized( $this->get_text_view( 'backend/partials/dropdown-sales' ) ),
                    laterpay_sanitized( $this->get_text_view( 'backend/partials/navigation-interval' ) )
                );
                ?>
            </h1>

            <div class="lp_layout">
                <div class="lp_layout__item lp_1/3">
                    <h2 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Conversion', 'laterpay' ) ); ?></h2>
                    <div id="lp_js_conversionDiagram" class="lp_dashboard-graph"></div>
                    <div class="lp_statistics-row lp_clearfix">
                        <ul class="lp_statistics-row__list">
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Number of times a page with purchasable content has been viewed in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_totalImpressions">0</span></big>
                                <?php echo laterpay_sanitize_output( __( 'Views', 'laterpay' ) ); ?>
                            </li>
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Share of purchases of all page views of pages with purchasable content', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_avgConversion">0.0</span><small class="lp_statistics-row__text-small">%</small></big>
                                <?php echo laterpay_sanitize_output( __( 'Conversion', 'laterpay' ) ); ?>
                            </li>
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Share of new buyers of all buyers in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_shareOfNewCustomers">0</span><small class="lp_statistics-row__text-small">%</small></big>
                                <?php echo laterpay_sanitize_output( __( 'New Customers', 'laterpay' ) ); ?>
                            </li>
                        </ul>
                    </div>
                </div><!--

             --><div class="lp_layout__item lp_1/3">
                    <h2 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Items Sold', 'laterpay' ) ); ?></h2>
                    <div id="lp_js_salesDiagram" class="lp_dashboard-graph"></div>
                    <div class="lp_statistics-row lp_clearfix">
                        <ul class="lp_statistics-row__list">
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Average number of items sold per day in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_avgItemsSold">0.0</span></big>
                                <?php echo laterpay_sanitize_output( __( 'AVG Items Sold', 'laterpay' ) ); ?>
                            </li>
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Total number of items sold in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_totalItemsSold">0</span></big>
                                <?php echo laterpay_sanitize_output( __( 'Items Sold', 'laterpay' ) ); ?>
                            </li>
                        </ul>
                    </div>
                </div><!--

             --><div class="lp_layout__item lp_1/3">
                    <h2 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Committed Revenue', 'laterpay' ) ); ?></h2>
                    <div id="lp_js_revenueDiagram" class="lp_dashboard-graph"></div>
                    <div class="lp_statistics-row lp_clearfix">
                        <ul class="lp_statistics-row__list">
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Average value of items sold in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_avgRevenue">0.00</span><small class="lp_statistics-row__text-small"><?php echo laterpay_sanitize_output( $laterpay['currency'] ); ?></small></big>
                                <?php echo laterpay_sanitize_output( __( 'AVG Purchase', 'laterpay' ) ); ?>
                            </li>
                            <li class="lp_tooltip lp_statistics-row__item"
                                data-tooltip="<?php echo esc_attr( __( 'Total value of items sold in selected interval', 'laterpay' ) ); ?>">
                                <big class="lp_statistics-row__value"><span id="lp_js_totalRevenue">0.00</span><small class="lp_statistics-row__text-small"><?php echo laterpay_sanitize_output( $laterpay['currency'] ); ?></small></big>
                                <?php echo laterpay_sanitize_output( __( 'Committed Revenue', 'laterpay' ) ); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lp_layout">
                <div class="lp_layout__item lp_1/3">
                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Best-converting Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_bestConvertingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>

                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Least-converting Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_leastConvertingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>
                </div><!--

             --><div class="lp_layout__item lp_1/3">
                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Most-selling Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_bestSellingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>

                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Least-selling Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_leastSellingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>
                </div><!--

             --><div class="lp_layout__item lp_1/3">
                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Most Revenue-generating Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_bestGrossingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>

                    <div class="lp_greybox--outline lp_mr lp_mb">
                        <h3 class="lp_text-align--center"><?php echo laterpay_sanitize_output( __( 'Least Revenue-generating Items', 'laterpay' ) ); ?></h3>
                        <ol id="lp_js_leastGrossingList" class="lp_dashboard-data">
                            <dfn><?php echo laterpay_sanitize_output( __( 'No data available', 'laterpay' ) ); ?></dfn>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
