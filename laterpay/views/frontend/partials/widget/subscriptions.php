<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<?php foreach ( $laterpay_sub['subscriptions'] as $subscription ) : ?>
    <?php echo laterpay_sanitized( $this->render_subscription( $subscription ) ); ?>
<?php endforeach; ?>