<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_js_dropdown lp_dropdown">
    <span class="lp_js_dropdownCurrentItem lp_dropdown__current-item"><?php echo laterpay_sanitize_output( __( '8 Day', 'laterpay' ) ); ?></span>
    <div class="lp_js_dropdownList lp_dropdown__list">
        <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="day"><?php echo laterpay_sanitize_output( __( '24 Hour', 'laterpay' ) ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item lp_is-selected" data-interval="week"><?php echo laterpay_sanitize_output( __( '8 Day', 'laterpay' ) ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="2-weeks"><?php echo laterpay_sanitize_output( __( '2 Week', 'laterpay' ) ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="month"><?php echo laterpay_sanitize_output( __( '1 Month', 'laterpay' ) ); ?></a>
    </div>
</div>
