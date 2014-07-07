(function($) {$(document).ready(function() {

        lpShowStatistic = function() {
            $('#statistics .bar').peity('bar', {
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
            $('#statistics .background-bar').peity('bar', {
                delimiter   : ';',
                width       : 182,
                height      : 42,
                gap         : 1,
                fill        : function() { return '#ddd'; }
            });
        };
        lpShowStatistic();

        // show / hide statistics pane on click
        $.extend($.easing, {
            easeInOutExpo: function (x, t, b, c, d) {
                if (t===0) return b;
                if (t==d) return b+c;
                if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
                return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
            }
        });
        var toggleStatisticsPane = function() {
            var $trigger    = $('#toggle-laterpay-statistics-pane'),
                $pane       = $trigger.parent();

            if ($pane.hasClass('hidden') ) {
                $.post(
                    lpVars.ajaxUrl,
                    { action: 'admin', laterpay_hide_statistics_pane: '0' },
                    function(data) {
                        console.log(data);
                    },
                    'json'
                );
                $trigger.animate({ left: '-15px' }, 250, 'easeInOutExpo');
                $pane.animate({ right: '0' }, 250, 'easeInOutExpo', function() { $pane.removeClass('hidden'); });
            } else {
                $.post(
                    lpVars.ajaxUrl,
                    { action: 'admin', laterpay_hide_statistics_pane: '1' },
                    function(data) {
                        console.log(data);
                    },
                    'json'
                );
                $pane.animate({ right: '-340px' }, 250, 'easeInOutExpo', function() { $pane.addClass('hidden'); });
                $trigger.animate({ left: '-34px' }, 250, 'easeInOutExpo');
            }
        };

        $('#toggle-laterpay-statistics-pane')
        .on('mousedown', function() {toggleStatisticsPane();})
        .on('click', function(e) {e.preventDefault();});


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

        $('#preview-post-toggle')
        .on('mousedown', function() {togglePreviewMode();})
        .on('click', function(e) {e.preventDefault();});


        // handle clicks on purchase buttons in test mode
        $('body').on('mousedown', '.laterpay-purchase-link', function(e) {
            if ($(this).data('preview-as-visitor')) {
                e.preventDefault();
                alert(lpVars.i18nAlert);
            }
        });

});}(jQuery));

// show LaterPay dialogs using the LaterPay dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {
    var ppuContext  = {
                        showCloseBtn: true,
                        canSkipAddToInvoice: false
                    },
        dm          = new Y.LaterPay.DialogManager();

    dm.attachToLinks('.laterpay-purchase-link', ppuContext.showCloseBtn);
});
