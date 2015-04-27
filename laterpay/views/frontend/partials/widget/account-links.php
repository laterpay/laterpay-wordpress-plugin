<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_account-links"></div>

<script>
    if (lpAccountLinksUrl === undefined) {
        var lpAccountLinksUrl = "<?php echo laterpay_sanitized( $laterpay_account['links_url'] ); ?>";
    }
    if (lpAccountNextUrl === undefined) {
        var lpAccountNextUrl = "<?php echo urlencode( $laterpay_account['next'] ); ?>";
    }
    if (lpMerchantId === undefined) {
        var lpMerchantId = "<?php echo laterpay_sanitized( $laterpay_account['merchant_id'] ); ?>";
    }
</script>