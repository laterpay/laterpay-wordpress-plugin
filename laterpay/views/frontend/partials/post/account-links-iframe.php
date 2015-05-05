<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div>
    <?php do_action( 'laterpay_account_links', $laterpay['css'], $laterpay['forcelang'], $laterpay['show'], $laterpay['next'] ); ?>
</div>
