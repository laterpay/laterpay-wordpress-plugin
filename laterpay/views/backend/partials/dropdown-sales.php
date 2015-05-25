<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_js_dropdown lp_dropdown">
    <span class="lp_js_dropdownCurrentItem lp_dropdown__current-item"><?php echo laterpay_sanitize_output( __( 'all', 'laterpay' ) ); ?></span>
    <div class="lp_js_dropdownList lp_dropdown__list">
        <div class="lp_triangle lp_triangle--outer-triangle"><div class="lp_triangle"></div></div>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown__item lp_is-selected" data-revenue-model="all"><?php echo laterpay_sanitize_output( __( 'all', 'laterpay' ) ); ?></a>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown__item" data-revenue-model="ppu"><?php echo laterpay_sanitize_output( __( 'PPU', 'laterpay' ) ); ?></a>
        <a href="#" class="lp_js_selectRevenueModel lp_dropdown__item" data-revenue-model="sis"><?php echo laterpay_sanitize_output( __( 'SIS', 'laterpay' ) ); ?></a>
    </div>
</div>
