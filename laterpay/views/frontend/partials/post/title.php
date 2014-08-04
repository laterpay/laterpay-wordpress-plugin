<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<span id="laterpay-post-title" data-post-id="<?php echo $post_id; ?>"></span>
<script>
    (function($) {
        var $title      = $('#laterpay-post-title'),
            postVars    = {
                            action          : 'laterpay_title_script',
                            id              : $title.data('post-id'),
                            show_statistic  : true
                        };

        $title.hide();
		var post_vars = {
			action: 'laterpay_title_script',
			id: postId,
			show_statistic: true
		};

		$.get( lpVars.ajaxUrl, post_vars, function( response ) {
			 $title.before( response ).remove();
		} );
    })(jQuery);
</script>
