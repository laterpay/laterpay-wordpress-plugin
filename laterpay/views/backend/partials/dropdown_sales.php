<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<div class="lp_dropdown">
    <span class="lp_dropdown_currentItem"><?php _e( 'all', 'laterpay' ); ?></span>
    <div class="lp_dropdown_list">
        <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown_listItem lp_is-selected" data-revenue-model="all"><?php _e( 'all', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown_listItem" data-revenue-model="ppu"><?php _e( 'PPU', 'laterpay' ); ?></a>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown_listItem" data-revenue-model="sis"><?php _e( 'SIS', 'laterpay' ); ?></a>
    </div>
</div>
