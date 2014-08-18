<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if ( $config->get( 'caching.compatible_mode' ) && ! LaterPay_Helper_Request::is_ajax() ): ?>
    <span id="lp_post-footer" data-post-id="<?php echo $post_id; ?>"></span>
    <script>
        (function($) {
            $('#lp_post-footer').hide();
            var postVars = {
                    action  : 'laterpay_footer_script',
                    id      : $('#lp_post-footer').data('post-id')
                };

            $.get(
                lpVars.ajaxUrl,
                postVars,
                function(response) {
                    $('#lp_post-footer').before(response).remove();
                    lpShowStatistic();
                }
            );
        })(jQuery);
    </script>
<?php else: ?>
<iframe id="lp_identify-iframe" src="<?php echo $identify_link; ?>" style="height:1px; left:-9000px; position:absolute; width:1px;"></iframe>
<?php endif; ?>
