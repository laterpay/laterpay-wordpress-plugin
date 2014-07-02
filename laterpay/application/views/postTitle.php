<span id="laterpay-post-title" post-id="<?php echo $post_id; ?>"></span>
<script type="text/javascript">
(function($){
    $('#laterpay-post-title').hide();
    var post_id = $('#laterpay-post-title').attr('post-id');
    $.get(
        lpVars.getTitleUrl, 
        {id: post_id, show_statistic: true}, 
        function(html) {
            $('#laterpay-post-title').before(html);
            $('#laterpay-post-title').remove();
        }
    );
})(jQuery)
</script>

