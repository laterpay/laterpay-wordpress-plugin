<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<iframe id="lp_identify-iframe" src="<?php echo esc_url_raw( $laterpay['identify_link'] ); ?>" style="height:1px; left:-9000px; position:absolute; width:1px;"></iframe>
