<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_paid-content">
    <div class="lp_full-content">
        <!-- <?php echo laterpay_sanitize_output( __( 'Preview a short excerpt from the paid post:', 'laterpay' ) ); ?> -->
        <?php echo laterpay_sanitized( $overlay['teaser'] ); ?>
        <br>
        <?php echo laterpay_sanitize_output( __( 'Thanks for reading this short excerpt from the paid post! Fancy buying it to read all of it?', 'laterpay' ) ); ?>
    </div>

    <?php $overlay_data = $overlay['data']; ?>
    <div class="lp_overlay-text">
        <div class="lp_benefits">
            <header class="lp_benefits__header">
                <h2 class="lp_benefits__title">
                    <?php echo laterpay_sanitize_output( $overlay_data['title'] ); ?>
                </h2>
            </header>
            <ul class="lp_benefits__list">
                <?php foreach ( $overlay_data['benefits'] as $benefit ) : ?>
                    <li class="lp_benefits__list-item <?php echo esc_attr( $benefit['class'] ); ?>">
                        <h3 class="lp_benefit__title">
                            <?php echo laterpay_sanitize_output( $benefit['title'] ); ?>
                        </h3>
                        <p class="lp_benefit__text">
                            <?php echo laterpay_sanitize_output( $benefit['text'] ); ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="lp_benefits__action">
                <?php echo laterpay_sanitized( $overlay_data['action'] ); ?>
            </div>
            <div class="lp_powered-by">
                powered by<span data-icon="a"></span>
            </div>
        </div>
    </div>

</div>
