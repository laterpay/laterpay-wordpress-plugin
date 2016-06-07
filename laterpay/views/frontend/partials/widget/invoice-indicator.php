<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div id='laterpay-invoice-indicator'>
    <iframe src="<?php echo laterpay_sanitized( $laterpay_invoice['balance_url'] ); ?>" width="110" height="30" scrolling="no" frameborder="0"></iframe>
</div>