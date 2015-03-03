<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div class="lp_dropdown">
    <span class="lp_dropdown_currentItem"><?php _e( '8 Day', 'laterpay' ); ?></span>
    <div class="lp_dropdown_list">
        <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown_listItem" data-interval="day"><?php _e( '24 Hour', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown_listItem lp_is-selected" data-interval="week"><?php _e( '8 Day', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown_listItem" data-interval="2-weeks"><?php _e( '2 Week', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown_listItem" data-interval="month"><?php _e( '1 Month', 'laterpay' ); ?></a>
    </div>
</div>
