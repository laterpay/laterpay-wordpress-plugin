<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_side_info">
    <h2><?php esc_html_e( 'FAQ\'s', 'laterpay' ); ?></h2>
    <h3><?php printf( esc_html__( 'Having Trouble with Page Cache? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/wordpress-cache" target="_blank" class="lp_info_link">', '</a>' ); ?></h3>

    <div>
        <p><?php esc_html_e( 'You need to whitelist the following cookies from caching in order for page-cache to work properly with laterpay.', 'laterpay' ); ?></p>
        <ol>
            <li>laterpay_token</li>
            <li>laterpay_purchased_gift_card</li>
            <li>laterpay_tracking_code</li>
        </ol>
        <p><?php esc_html_e( 'We have already taken care of this if you\'re on a WordPress VIP Environment.', 'laterpay' ); ?></p>
    </div>

    <?php
    // Only show info if on WPEngine environment.
    if ( function_exists( 'is_wpe' ) && is_wpe() ) {
        ?>
        <h3><?php printf( esc_html__( 'Having Trouble on WPEngine? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/i-am-having-trouble-with-wordpress-engine" target="_blank" class="lp_info_link">', '</a>' ); ?></h3>

        <div>
            <p><?php printf( '%1$s  <code>%2$s</code> %3$s', esc_html__( 'If you\'re facing the issue on WPEngine even after whitelisting requested cookies, please check if any of your active plugin/theme is using', 'laterpay' ), esc_html__( 'session*', 'laterpay' ), esc_html__( 'functions.', 'laterpay' ) ); ?></p>
            <p><?php printf( '%1$s <a href=%2$s target="_blank" class="lp_info_link">%3$s</a> %4$s', esc_html__( 'Please', 'laterpay' ), esc_url( 'https://wpengine.com/support/cookies-and-php-sessions/' ), esc_html__( ' Check this', 'laterpay' ), esc_html__( 'for more information regarding session usage on WPEngine.', 'laterpay' ) ); ?></p>
        </div>
        <?php
    }
    ?>
    <h3>
        <?php printf( esc_html__( 'The new version of the plugin is not compatible with my site. How can I rollback? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/rollback-wordpress-plugin" target="_blank" class="lp_info_link">', '</a>' ); ?>
    </h3>
</div>
<div class="lp_side_info">
    <h2><?php esc_html_e( 'Support', 'laterpay' ); ?></h2>
    <p>
        <?php
        printf(
            esc_html__( '%1$sClick here%3$s or email %2$ssupport@laterpay.net%3$s to provide feedback or to reach our customer service team.', 'laterpay' ),
            "<a href='https://www.laterpay.net/contact-support' target='_blank' class='lp_info_link'>",
            "<a href='mailto:support@laterpay.net' class='lp_info_link'>",
            '</a>'
        );
        ?>
    </p>
</div>
