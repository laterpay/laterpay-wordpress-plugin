<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
$open_comment   = '<!--[if lt IE 9]>';
$close_comment  = '<![endif]-->';
$open_tag       = '<script {attributes}>';
$close_tag      = '</script>';
?>
<?php echo laterpay_sanitized( $open_comment ); ?>

<?php foreach ( $laterpay['scripts'] as $script ) : ?>
<?php echo laterpay_sanitized( str_replace( '{attributes}', 'src="' . $script . '"' , $open_tag ) ); ?>
<?php echo laterpay_sanitized( $close_tag ); ?>
<?php endforeach; ?>

<?php echo laterpay_sanitized( $close_comment ); ?>