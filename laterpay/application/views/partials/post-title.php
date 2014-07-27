<span id="laterpay-post-title" data-post-id="<?php echo $post_id; ?>"></span>

<script>
    (function($){
        var $title = $('#laterpay-post-title'),
            postId = $title.data('post-id');

        $title.hide();
        $.get(
            lpVars.getTitleUrl,
            {
                id              : postId,
                show_statistic  : true
            },
            function(html) {
                $title.before(html).remove();
            }
        );
    })(jQuery);
</script>
