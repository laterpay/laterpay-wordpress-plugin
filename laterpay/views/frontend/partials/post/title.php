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
        $.get(lpVars.ajaxUrl, postVars, function(response) {
            $title.before(response).remove();
        });
    })(jQuery);
</script>
