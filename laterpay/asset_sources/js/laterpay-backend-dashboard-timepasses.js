(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboardTimePasses
    function laterPayBackendDashboardTimePasses() {
        var $o = {
                // time passes customer lifecycle
                timepassDiagram         : $('.lp_js_timepassDiagram'),
                // colors
                colorBackground         : '#e3e3e3',
                colorBackgroundLaterpay : '#50c371',
                colorBorder             : '#ccc',
                colorTextLighter        : '#ababab',
            },

            plotDefaultOptions = {
                legend                  : {
                    show                : false,
                },
                xaxis                   : {
                    font                : {
                        color           : $o.colorTextLighter,
                        lineHeight      : 18,
                    },
                    show                : true,
                },
                yaxis                   : {
                    font                : {
                        color           : $o.colorTextLighter,
                    },
                    min                 : 0,
                    reserveSpace        : true,
                    ticks               : 5,
                },
                series                  : {
                    shadowSize          : 0,
                },
                grid                    : {
                    borderWidth         : {
                        top             : 0,
                        right           : 0,
                        bottom          : 1,
                        left            : 0,
                    },
                    borderColor         : $o.colorBorder,
                    tickColor           : 'rgba(247,247,247,0)', // transparent
                }
            },

            loadTimePassData = function(pass) {
                var requestData = {
                        // WP Ajax action
                        'action'    : 'laterpay_get_time_passes_data',
                        // nonce for validation and XSS protection
                        '_wpnonce'  : lpVars.nonces.time_passes,
                        // time pass id (optional)
                        'pass_id'   : pass
                    },
                    jqxhr;

                jqxhr = $.ajax({
                    'url'       : lpVars.ajaxUrl,
                    'async'     : true,
                    'method'    : 'POST',
                    'data'      : requestData,
                });

                jqxhr.done(function(data) {
                    if (!data || data.success) {
                        return;
                    }
                    setMessage(data.message, data.success);
                });

                return jqxhr;
            },

            renderTimePass = function (response, $element) {
                var max             = parseInt(lpVars.maxYValue, 10),
                    yAxisScale      = Math.max(max, 10) + 4, // add some air to y-axis scale
                    markings        = [
                        {
                            // separator 1 after first 4 weeks (1 month)
                            color           : $o.colorBorder,
                            lineWidth       : 1,
                            xaxis           : {
                                from        : 3.5,
                                to          : 3.5,
                            },
                        },
                        {
                            // separator 2 after first 12 weeks (3 months)
                            color           : $o.colorBorder,
                            lineWidth       : 1,
                            xaxis           : {
                                from        : 11.5,
                                to          : 11.5,
                            },
                        },
                        {
                            // horizontal summary line for first month
                            color           : $o.colorBorder,
                            lineWidth       : 2,
                            xaxis           : {
                                from        : 0.25,
                                to          : 3.25,
                            },
                            yaxis           : {
                                from        : yAxisScale - 2,
                                to          : yAxisScale - 2,
                            },
                        },
                        {
                            // horizontal summary line for month 2-3
                            color           : $o.colorBorder,
                            lineWidth       : 2,
                            xaxis           : {
                                from        : 3.75,
                                to          : 11.25,
                            },
                            yaxis           : {
                                from        : yAxisScale - 2,
                                to          : yAxisScale - 2,
                            },
                        },
                    ],
                    plotOptions     = {
                        xaxis               : {
                            ticks           : response.data.x,
                        },
                        yaxis               : {
                            show            : false,
                            max             : yAxisScale,
                            tickFormatter   : function(val) {
                                return parseInt(val, 10);
                            }
                        },
                        grid                : {
                            markings        : markings,
                        },
                    },
                    plotData        = [
                        {
                            data            : response.data.y,
                            bars            : {
                                align       : 'center',
                                barWidth    : 0.4,
                                fillColor   : $o.colorBackgroundLaterpay,
                                horizontal  : false,
                                lineWidth   : 0,
                                show        : true,
                            },
                        },
                    ];

                // extend empty object to merge specific with default plotOptions without
                // modifying the defaults
                plotOptions = $.extend(true, {}, plotDefaultOptions, plotOptions);
                var $graph  = $.plot($element, plotData, plotOptions);

                // calculate the sum of time passes that expire in 0-4 and 5-12 weeks, respectively
                var sum04   = 0,
                    sum512  = 0,
                    i;
                for (i = 0; i < 5; i++) {
                    sum04 += response.data.y[i][1];
                }
                for (i = 5; i < 13; i++) {
                    sum512 += response.data.y[i][1];
                }

                // add labels to the flot graph:
                // get the offset of separator 1 within the flot placeholder
                var o1      = $graph.pointOffset({x: 3.5, y: 0}),
                    label1  = '<div class="lp_time-pass-diagram__label" ' +
                        'style="left:' + (o1.left - 30) + 'px; top:2px;">' +
                        lpVars.i18n.endingIn + '<br>' +
                        '< 1 ' + lpVars.i18n.month +
                        '</div>';
                // append that label to the graph
                $element.append(label1);
                // get the offset of separator 2 within the flot placeholder
                var o2      = $graph.pointOffset({x: 11.5, y: 0}),
                    label2  = '<div class="lp_time-pass-diagram__label" ' +
                        'style="left:' + (o2.left - 30) + 'px; top:2px;">' +
                        lpVars.i18n.endingIn + '<br>' +
                        '< 3 ' + lpVars.i18n.months +
                        '</div>';
                // append that label to the graph
                $element.append(label2);

                // add arrowhead to x-axis
                var  o3 = $graph.pointOffset({x: 13, y: 0}),
                    ctx = $graph.getCanvas().getContext('2d');
                o3.left += 8;
                o3.top  += 4;
                ctx.beginPath();
                ctx.moveTo(o3.left,     o3.top);
                ctx.lineTo(o3.left,     o3.top - 7);
                ctx.lineTo(o3.left + 6, o3.top - 3.5);
                ctx.lineTo(o3.left,     o3.top);
                ctx.fillStyle = $o.colorBorder;
                ctx.fill();

                // add x-axis label
                var xAxisLabel = '<div class="lp_time-pass-diagram__label" ' +
                    'style="left:' + (o3.left + 10) + 'px; top:' + (o3.top - 2) + 'px;">' +
                    lpVars.i18n.weeksLeft +
                    '</div>';
                $element.append(xAxisLabel);

                // add sum to 0-4 weeks interval
                var o4          = $graph.pointOffset({x: 1.5, y: yAxisScale - 2}),
                    sum04Label  = '<div class="lp_time-pass-diagram__sum" ' +
                        'style="left:' + (o4.left - 30) + 'px; top:' + (o4.top - 14) + 'px;">' +
                        sum04 +
                        '</div>';
                $element.append(sum04Label);

                // add sum to 5-12 weeks interval
                var o5          = $graph.pointOffset({x: 7.5, y: yAxisScale - 2}),
                    sum512Label = '<div class="lp_time-pass-diagram__sum" ' +
                        'style="left:' + (o5.left - 30) + 'px; top:' + (o5.top - 14) + 'px;">' +
                        sum512 +
                        '</div>';
                $element.append(sum512Label);

            },

            loadTimePassLifecycles = function() {
                var data = $o.timepassDiagram;

                $.each($o.timepassDiagram, function(index) {
                    var $element    = $(data[index]),
                        timePassId  = $element.data('id');

                    showLoadingIndicator($element);

                    loadTimePassData(timePassId)
                        .done(function(response) {
                            renderTimePass(response, $element);
                        })
                        .always(function() {
                            removeLoadingIndicator($element);
                        });
                });
            },

            initializePage = function() {
                loadTimePassLifecycles();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendDashboardTimePasses();

});})(jQuery);
