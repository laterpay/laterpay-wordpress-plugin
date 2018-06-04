<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?><div class="lp_teaser-content"><?php echo LaterPay_Helper_View::remove_extra_spaces( wp_kses_post( $laterpay['teaser_content'] ) ); //phpcs:ignore ?></div>
