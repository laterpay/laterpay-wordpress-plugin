<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<span id="laterpay-post-title" data-post-id="<?php echo $post_id; ?>"></span>
<script>
    (function($) {
        $('#laterpay-post-title').hide();

        var postVars = {
                            action          : 'laterpay_title_script',
                            id              : postId,
                            show_statistic  : true
                        };

        $.get(
            lpVars.ajaxUrl,
            postVars,
            function(response) {
                $title.before(response).remove();
            }
        );
    })(jQuery);
</script>
