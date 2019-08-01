<?php

if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}

$args             = array_merge( array(
    'data-laterpay' => esc_url( $laterpay['link'] ),
    'data-post-id'  => absint( $laterpay['post_id'] ),
),
    $laterpay['attributes']
);
$whitelisted_attr = array(
    'data-laterpay',
    'data-post-id',
    'data-preview-post-as-visitor',
);
?>
<span class="lp_shortcode_link" <?php laterpay_whitelisted_attributes( $args, $whitelisted_attr ); ?>></span>
