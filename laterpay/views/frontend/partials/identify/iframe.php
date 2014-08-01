<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if ( $config->get( 'caching.compatible_mode' ) && ! LaterPay_Helper_Request::is_ajax() ): ?>
    <span id="laterpay-post-footer" data-post-id="<?php echo $post_id; ?>"></span>
    <script>
        ( function( $ ) {
            $( '#laterpay-post-footer' ).hide();
			var post_vars = {
				action: 'laterpay_footer_script',
				id: $( '#laterpay-post-footer' ).data( 'post-id' )
			};
			$.post( lpVars.ajaxUrl, post_vars, function( response ) {
                $( '#laterpay-post-footer' ).before( response );
                $( '#laterpay-post-footer' ).remove();
                lpShowStatistic();
			} );
        } )( jQuery );
    </script>
<?php else: ?>
<iframe src="<?php echo $identify_link; ?>" id="laterpay-identify" style="height:1px; left:-9000px; position:absolute; width:1px;"></iframe>
<?php endif; ?>
