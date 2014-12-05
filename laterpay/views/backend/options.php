<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
    }
?>

<h2><?php echo $laterpay['settings_title']; ?></h2>
<form method="POST" action="">
    <input type="hidden" name="action" value="laterpay_advanced_settings">
    <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_capabilities' ); } ?>
    <?php do_settings_sections( 'laterpay' ); ?>
    <p class="submit">
        <input type="submit" class="lp_js_advancedSettingsSubmitButton button button-primary" value="Save Changes">
    </p>
</form>
