(function($) {$(document).ready(function() {

        window.lpShowStatistic = function() {
            // don't render the post statistics, if there are multiple single pages on one page
            if ($('#statistics:visible').length > 1) {
                return;
            }

            $('#statistics:visible .bar').peity('bar', {
                delimiter   : ';',
                width       : 182,
                height      : 42,
                gap         : 1,
                fill        : function(value, index, array) {
                                var date        = new Date(),
                                    daysCount   = array.length,
                                    color       = '#999';
                                date.setDate(date.getDate() - (daysCount - index));
                                // highlight the last (current) day
                                if (index === (daysCount - 1))
                                    color = '#555';
                                // highlight Saturdays and Sundays
                                if (date.getDay() === 0 || date.getDay() === 6)
                                    color = '#c1c1c1';
                                return color;
                            }
            });
            $('#statistics:visible .background-bar').peity('bar', {
                delimiter   : ';',
                width       : 182,
                height      : 42,
                gap         : 1,
                fill        : function() { return '#ddd'; }
            });
        };
        lpShowStatistic();

        // show / hide statistics pane on click
        var toggleStatisticsPane = function() {
            var $pane = $('#statistics'),
                value = $pane.hasClass('hidden') ? '0' : '1';
            $('#laterpay_hide_statistics_form input[name=hide_statistics_pane]').val(value);
            $.post(
                lpVars.ajaxUrl,
                $('#laterpay_hide_statistics_form').serializeArray()
            );
            $pane.toggleClass('hidden');
            if (value) {
                lpShowStatistic();
            }
        };

        $('body')
        .on('mousedown', '#toggle-laterpay-statistics-pane', function() {toggleStatisticsPane();})
        .on('click', '#toggle-laterpay-statistics-pane', function(e) {e.preventDefault();});


        // preview post either for admin or regular user
        var togglePreviewMode = function() {
            var $toggle = $('#preview-post-toggle');

            if ($toggle.prop('checked')) {
                $('#preview_post_hidden_input').val(1);
            } else {
                $('#preview_post_hidden_input').val(0);
            }
            $.post(
                lpVars.ajaxUrl,
                $('#plugin_mode').serializeArray(),
                function(data) {
                    if (data && data.success) {
                        location.reload();
                    }
                },
                'json'
            );
        };

        $('body').on('click', '#preview-post-toggle', function() {togglePreviewMode();});

        // handle clicks on purchase buttons in test mode
        $('body').on('mousedown', '.laterpay-purchase-link', function(e) {
            if ($(this).data('preview-as-visitor')) {
                e.preventDefault();
                alert(lpVars.i18nAlert);
            }
        });

        // load content via Ajax, if plugin is in page caching compatible mode
        // (recognizable by the presence of $('#laterpay-page-caching-mode'))
        var $pageCachingAnchor = $('#laterpay-page-caching-mode');
        if ($pageCachingAnchor.length == 1) {
            var post_vars = {
                                action  : 'laterpay_article_script',
                                post_id : $pageCachingAnchor.attr('data-post-id')
                            };

            $.get(
                lpVars.ajaxUrl,
                post_vars,
                function(response) {
                    $pageCachingAnchor.before(response).remove();
                    lpShowStatistic();
                }
            );
        }
});}(jQuery));


// render LaterPay elements using the LaterPay dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {
    // render purchase dialogs
    var ppuContext  = {
                        showCloseBtn: true,
                        canSkipAddToInvoice: false
                    },
        dm          = new Y.LaterPay.DialogManager();

        dm.attachToLinks('.laterpay-purchase-link', ppuContext.showCloseBtn);

    // render invoice indicator iframe
    if (lpVars && lpVars.lpBalanceUrl) {
        new Y.LaterPay.IFrame(
            Y.one('#laterpay-invoice-indicator'),
            lpVars.lpBalanceUrl,
            {
                width       : '110',
                height      : '30',
                scrolling   : 'no',
                frameborder : '0'
            }
        );
    }
});