<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_dropdown lp_js_dropdown">
    <span class="lp_dropdown__current-item lp_js_dropdownCurrentItem"><?php _e( '8 Day', 'laterpay' ); ?></span>
    <div class="lp_dropdown__list lp_js_dropdownList">
        <div class="lp_dropdown__triangle lp_dropdown__triangle--outer-triangle"><div class="lp_dropdown__triangle"></div></div>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="day"><?php _e( '24 Hour', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item lp_is-selected" data-interval="week"><?php _e( '8 Day', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="2-weeks"><?php _e( '2 Week', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectDashboardInterval lp_dropdown__item" data-interval="month"><?php _e( '1 Month', 'laterpay' ); ?></a>
    </div>
</div>
