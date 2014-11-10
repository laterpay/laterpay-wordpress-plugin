(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var i, l,
            $o = {
                daysBack                : 8,
                itemsPerList            : 10,
                list                    : [],

                // heading with dashboard configuration selections
                configurationSelection  : $('#lp_js_selectDashboardInterval, #lp_js_selectRevenueModel'),
                selectedInterval        : $('#lp_js_selectDashboardInterval'),
                selectedRevenueModel    : $('#lp_js_selectRevenueModel'),
                previousInterval        : $('#lp_js_loadPreviousInterval'),
                nextInterval            : $('#lp_js_loadNextInterval'),

                // diagrams
                conversionDiagram       : $('#lp_js_conversionDiagram'),
                salesDiagram            : $('#lp_js_salesDiagram'),
                revenueDiagram          : $('#lp_js_revenueDiagram'),

                // main KPIs
                totalImpressionsKPI     : $('#lp_js_totalImpressions'),
                avgConversionKPI        : $('#lp_js_avgConversion'),
                newCustomersKPI         : $('#lp_js_shareOfNewCustomers'),

                avgItemsSoldKPI         : $('#lp_js_avg-items-sold'),
                totalItemsSoldKPI       : $('#lp_js_total-items-sold'),

                avgRevenueKPI           : $('#lp_js_avgRevenue'),
                totalRevenueKPI         : $('#lp_js_totalRevenue'),

                // top / bottom lists
                bestConvertingList      : $('#lp_js_bestConvertingList'),
                leastConvertingList     : $('#lp_js_leastConvertingList'),
                bestSellingList         : $('#lp_js_bestSellingList'),
                leastSellingList        : $('#lp_js_leastSellingList'),
                bestGrossingList        : $('#lp_js_bestGrossingList'),
                leastGrossingList       : $('#lp_js_leastGrossingList'),
            },

            bindEvents = function() {
                // refresh dashboard
                $('#lp_js_refreshDashboard')
                .click(function(e) {
                    fetchDashboardData();
                    e.preventDefault();
                });

                // re-render dashboard in selected configuration
                $o.configurationSelection
                .change(function() {
                    fetchDashboardData();
                });

                // re-render dashboard with data of next interval
                $o.nextInterval
                .mousedown(function() {
                    // do stuff
                })
                .click(function(e) {e.preventDefault();});


                // re-render dashboard with data of previous interval
                $o.previousInterval
                .mousedown(function() {
                    // do stuff
                })
                .click(function(e) {e.preventDefault();});
            },

            renderDashboard = function(data) {
                var xAxisTicks      = data.converting_items_by_day.x,
                    backgroundBars  = [];

                // generate an array of 100% values to use as background for percentage column charts
                i = 0;
                for (; i < $o.daysBack; i++) {
                    backgroundBars.push([i + 1, 100]);
                }

                // flot diagrams
                $.plot($o.conversionDiagram,
                    [
                        {
                            data            : backgroundBars,
                            bars            : {
                                show        : true,
                                barWidth    : 0.7,
                                fillColor   : '#e3e3e3',
                                lineWidth   : 0,
                                align       : 'center',
                                horizontal  : false
                            }
                        },
                        {
                            data            : data.converting_items_by_day.y,
                            bars            : {
                                show        : true,
                                barWidth    : 0.35,
                                fillColor   : '#50C371',
                                lineWidth   : 0,
                                align       : 'center',
                                horizontal  : false
                            }
                        }
                    ],
                    {
                        legend              : {
                            show            : false
                        },
                        xaxis               : {
                            font            : {
                                color       : '#bbb',
                                lineHeight  : 18,
                            },
                            show            : true,
                            ticks           : xAxisTicks,
                        },
                        yaxis               : {
                            font            : {
                                color       : '#bbb'
                            },
                            ticks           : 5,
                            tickFormatter   : function(v) { return v + ' %'; },
                            min             : 0,
                            max             : 100,
                            reserveSpace    : true,
                        },
                        series              : {
                            shadowSize      : 0,
                        },
                        grid                : {
                            borderWidth     : {
                                top         : 0,
                                right       : 0,
                                bottom      : 1,
                                left        : 0,
                            },
                            borderColor     : '#ccc',
                            tickColor       : 'rgba(247,247,247,0)',  // transparent
                        }
                    }
                );

                $.plot($o.salesDiagram,
                    [
                        {
                            data            : data.selling_items_by_day.y,
                            color           : '#50C371',
                            lines           : {
                                show        : true,
                                lineWidth   : 1.5,
                                fill        : false,
                                gaps        : true,
                            },
                            points          : {
                                show        : true,
                                radius      : 3,
                                lineWidth   : 0,
                                fill        : true,
                                fillColor   : '#50C371',
                            }
                        },
                        // {
                        //     data            : data.selling_items_by_day,
                        //     color           : '#50C371',
                        //     lines           : {
                        //         show        : true,
                        //         lineWidth   : 1.5,
                        //         fill        : false,
                        //         gaps        : true,
                        //     },
                        //     points          : {
                        //         show        : true,
                        //         radius      : 3,
                        //         lineWidth   : 0,
                        //         fill        : true,
                        //         fillColor   : '#50C371',
                        //     }
                        // }
                    ],
                    {
                        legend              : {
                            show            : false
                        },
                        xaxis               : {
                            font            : {
                                color       : '#bbb',
                                lineHeight  : 18,
                            },
                            show            : true,
                            ticks           : xAxisTicks,
                        },
                        yaxis               : {
                            font            : {
                                color       : '#bbb'
                            },
                            ticks           : 5,
                            min             : 0,
                            max             : 1000,
                            reserveSpace    : true,
                        },
                        series              : {
                            shadowSize      : 0,
                        },
                        grid                : {
                            borderWidth     : {
                                top         : 0,
                                right       : 0,
                                bottom      : 1,
                                left        : 0,
                            },
                            borderColor     : '#ccc',
                            tickColor       : 'rgba(247,247,247,0)',  // transparent
                        }
                    }
                );

                $.plot($o.revenueDiagram,
                    [   {
                            data            : data.revenue_items_by_day.y,
                            color           : '#50C371',
                            lines           : {
                                show        : true,
                                lineWidth   : 1.5,
                                fill        : false,
                                gaps        : true,
                            },
                            points          : {
                                show        : true,
                                radius      : 3,
                                lineWidth   : 0,
                                fill        : true,
                                fillColor   : '#50C371',
                            }
                        },
                        // {
                        //     data            : conversionData_total,
                        //     color           : '#50C371',
                        //     lines           : {
                        //         show        : true,
                        //         lineWidth   : 1.5,
                        //         fill        : false,
                        //         gaps        : true,
                        //     },
                        //     points          : {
                        //         show        : true,
                        //         radius      : 3,
                        //         lineWidth   : 0,
                        //         fill        : true,
                        //         fillColor   : '#50C371',
                        //     }
                        // }
                    ],
                    {
                        legend              : {
                            show            : false
                        },
                        xaxis               : {
                            font            : {
                                color       : '#bbb',
                                lineHeight  : 18,
                            },
                            show            : true,
                            ticks           : xAxisTicks,
                        },
                        yaxis               : {
                            font            : {
                                color       : '#bbb'
                            },
                            ticks           : 5,
                            min             : 0,
                            max             : 1000,
                            reserveSpace    : true,
                        },
                        series              : {
                            shadowSize      : 0,
                        },
                        grid                : {
                            borderWidth     : {
                                top         : 0,
                                right       : 0,
                                bottom      : 1,
                                left        : 0,
                            },
                            borderColor     : '#ccc',
                            tickColor       : 'rgba(247,247,247,0)',  // transparent
                        }
                    }
                );

                // big KPIs
                $o.totalImpressionsKPI.text(data.impressions || 0);
                $o.avgConversionKPI.text(data.conversion || 0);
                $o.newCustomersKPI.text(data.new_customers || 0);

                $o.avgItemsSoldKPI.text(data.avg_purchase || 0);
                $o.totalItemsSoldKPI.text(data.total_items_sold || 0);

                $o.avgRevenueKPI.text(data.avg_revenue || 0);
                $o.totalRevenueKPI.text(data.total_revenue || 0);

                // best / worst lists
                renderTopBottomList($o.bestConvertingList,  data.best_converting_items);
                renderTopBottomList($o.leastConvertingList, data.least_converting_items);
                renderTopBottomList($o.bestSellingList,     data.most_selling_items);
                renderTopBottomList($o.leastSellingList,    data.least_selling_items);
                renderTopBottomList($o.bestGrossingList,    data.most_revenue_items);
                renderTopBottomList($o.leastGrossingList,   data.least_revenue_items);

                renderSparklines();
            },

            renderTopBottomList = function($list, data) {
                $o.list = [];

                i = 0;
                l = data.length;

                if (l > 0) {
                    // create list item for each data set
                    for (; i < l; i++) {
                        $o.list.push(renderListItem(
                                        data[i].post_id,
                                        data[i].post_title,
                                        data[i].amount,
                                        data[i].unit,
                                        data[i].sparkline
                                    ));
                    }
                } else {
                    $o.list = ['<dfn>' + lpVars.i18n.noData + '</dfn>'];
                }

                // replace existing HTML
                $list.html($o.list.join());
            },

            renderListItem = function(postId, itemName, kpiValue, kpiUnit, sparklineData) {
                var kpi = kpiUnit ? kpiValue + '<small>' + kpiUnit + '</small>' : kpiValue;

                return '<li>' +
                            '<span class="lp_sparklineBar">' + sparklineData + '</span>' +
                            '<strong class="lp_value">' + kpi + '</strong>' +
                            '<i><a href="#" class="lp_js_toggleItemDetails">' + itemName + '</a></i>' +
                        '</li>';
            },

            renderSparklines = function() {
                $('.lp_sparklineBar').peity('bar', {
                    width   : 34,
                    height  : 14,
                    gap     : 1,
                    fill    : function() { return '#ccc'; }
                });
            },

            fetchDashboardData = function() {
                // get selected dashboard configuration
                var interval        = $($o.selectedInterval).find(':checked').val(),
                    revenueModel    = $o.selectedRevenueModel.find(':checked').val();

                $.post(
                    lpVars.ajaxUrl,
                    {
                        'action'        : 'laterpay_get_dashboard_data',
                        '_wpnonce'      : lpVars.nonces.dashboard,
                        'interval'      : interval,
                        'revenue_model' : revenueModel,
                        'days'          : $o.daysBack,      // TODO: this should get removed / replaced by interval
                        'count'         : $o.itemsPerList,  // TODO: this is kinda irrelevant and should get removed
                        'refresh'       : 1,    // 1 (true): refresh data, 0 (false): only load the cached data; default: 1
                    },
                    function(response) {
                        if (response.success) {
                            renderDashboard(response.data);
                        }
                    },
                    'json'
                );
            },

            initializePage = function() {
                bindEvents();

                renderDashboard(lpVars.data);
            };

        initializePage();
    }

    // initialize page
    laterPayBackendDashboard();

});})(jQuery);
