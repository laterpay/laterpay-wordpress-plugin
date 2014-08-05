<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if ( $config->get( 'caching.compatible_mode' ) && ! LaterPay_Helper_Request::is_ajax() ): ?>
    <span id="laterpay-post-footer" data-post-id="<?php echo $post_id; ?>"></span>
    <script>
        (function($) {
            $('#laterpay-post-footer').hide();
            var postVars = {
                    action  : 'laterpay_footer_script',
                    id      : $('#laterpay-post-footer').data('post-id')
                };

            $.get(
                lpVars.ajaxUrl,
                postVars,
                function(response) {
                    $('#laterpay-post-footer').before(response).remove();
                    lpShowStatistic();
                }
            );
        })(jQuery);
    </script>
<?php else: ?>
<iframe src="<?php echo $identify_link; ?>" id="laterpay-identify" style="height:1px; left:-9000px; position:absolute; width:1px;"></iframe>
<?php endif; ?>
