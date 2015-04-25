<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<a href="<?php echo esc_url_raw( $laterpay['url'] ); ?>"
   class="lp_button"
   ><?php echo laterpay_sanitize_output( __( 'View', 'laterpay' ) ); ?></a>
