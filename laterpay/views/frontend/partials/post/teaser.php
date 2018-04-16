<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_teaser-content"><?php echo wp_kses_post( $laterpay['teaser_content'] ); ?></div>
