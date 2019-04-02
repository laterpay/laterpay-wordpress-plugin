<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="lp_side_info">
    <h2><?php esc_html_e( 'FAQ\'s', 'laterpay' ); ?></h2>
    <h3><?php printf( esc_html__( 'Having Trouble with Page Cache? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/wordpress-cache" target="_blank" class="lp_info_link">', '</a>' ); ?></h3>

    <?php
    // Only show info if on WPEngine environment.
    if ( function_exists( 'is_wpe' ) && is_wpe() ) {
        ?>
        <h3><?php printf( esc_html__( 'Having Trouble on WPEngine? %sClick here.%s', 'laterpay' ), '<a href="https://support.laterpay.net/i-am-having-trouble-with-wordpress-engine" target="_blank" class="lp_info_link">', '</a>' ); ?></h3>
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
