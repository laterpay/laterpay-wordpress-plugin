(function($) {
    $(document).ready(function() {
        var statistic = {};

        statistic.init = function() {
            var xhr;

            xhr = statistic.load_tab();
            xhr.done(function(data) {
                if (!data || data === 0) {
                    return;
                }

                statistic.render(data);

                $('body')
                .on('mousedown', '#lp_toggle-post-statistics-visibility', function(e) {
                    statistic.event_toggle_visibility(e);
                })
                .on('click', '#lp_toggle-post-statistics-visibility', function(e) {
                    e.preventDefault();
                })
                .on('click', '#lp_plugin-preview-mode-form .switch-input', function(e) {
                    statistic.event_toggle_preview_mode(e);
                });
            } );
        };

        statistic.renderSparklines = function() {
            var $pane = $('.lp_post-statistics');

            $('.lp_sparkline-bar', $pane).peity('bar', {
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

            $('.lp_sparkline-background-bar', $pane).peity('bar', {
                delimiter   : ';',
                width       : 182,
                height      : 42,
                gap         : 1,
                fill        : function() { return '#ddd'; }
            });
        };

        statistic.load_tab = function() {
            var request_vars = {
                action  : 'laterpay_post_statistic_render',
                post_id : lpVars.post_id
            };

            return $.get(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        statistic.save_visibility = function() {
            var request_vars = $('#lp_toggle-post-statistics-visibility-form').serializeArray();

            return $.post(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        statistic.save_plugin_mode = function() {
            var request_vars = $('#lp_plugin-preview-mode-form').serializeArray();

            return $.post(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        statistic.render = function(data) {
            var $container = $('#lp_post-statistics-placeholder');

            $container.html(data);
            statistic.renderSparklines();
        };

        statistic.event_toggle_visibility = function(e) {
            e.preventDefault();

            var $pane = $('.lp_post-statistics'),
                value = $pane.hasClass('hidden') ? '0' : '1',
                xhr;

            $('#lp_hide-statistics-pane').val(value);

            // toggle the visibility
            $pane.toggleClass('hidden');

            // save the state
            xhr = statistic.save_visibility();
            xhr.done(function(data, textStatus, jqXHR) {
                if (!data || !data.success && lpVars.debug) {
                    console.error(data);
                    console.error(textStatus);
                    console.error(jqXHR);
                }
            } );
        };

        statistic.event_toggle_preview_mode = function(e) {
            e.preventDefault();

            var $toggle         = $('#lp_plugin-preview-mode-form .switch-input'),
                $preview_state  = $('#lp_plugin-preview-mode-form input[name=preview_post]'),
                xhr;

            if ($toggle.prop('checked')) {
                $preview_state.val(1);
            } else {
                $preview_state.val(0);
            }

            xhr = statistic.save_plugin_mode();
            xhr.done(function(data, textStatus, jqXHR) {
                if (data && data.success) {
                    window.location.reload();
                } else {
                    console.error(data);
                    console.error(textStatus);
                    console.error(jqXHR);
                }
            } );
        };

        // placeholder found, initialize the statistics pane
        if ($('#lp_post-statistics-placeholder) {
            statistic.init();
        }


        // load content via Ajax, if plugin is in page caching compatible mode
        // (recognizable by the presence of $('#lp_cached-content'))
        var $pageCachingAnchor = $('#lp_cached-content');
        if ($pageCachingAnchor.length == 1) {
            var post_vars = {
                action  : 'laterpay_article_script',
                post_id : $pageCachingAnchor.attr('data-post-id')
            };

            $.get(
                lpVars.ajaxUrl,
                post_vars,
                function(data) {
                    $pageCachingAnchor.before(data).remove();
                }
            );
        }

        // handle clicks on purchase buttons in test mode
        $('body').on('mousedown', '.lp_purchase-link', function(e) {
            if ($(this).data('preview-as-visitor')) {
                e.preventDefault();
                alert(lpVars.i18nAlert);
            }
        });

    });
})(jQuery);

// render LaterPay purchase dialogs using the LaterPay dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {
    // render purchase dialogs
    var ppuContext  = {
                        showCloseBtn        : true,
                        canSkipAddToInvoice : false,
                      },
        dm          = new Y.LaterPay.DialogManager();

        dm.attachToLinks('.lp_purchase-link', ppuContext.showCloseBtn);

    // render invoice indicator iframe
    if (lpVars && lpVars.lpBalanceUrl) {
        new Y.LaterPay.IFrame(
            Y.one('#laterpay-invoice-indicator'),
            lpVars.lpBalanceUrl,
            {
                width       : '110',
                height      : '30',
                scrolling   : 'no',
                frameborder : '0',
            }
        );
    }
});
