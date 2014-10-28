(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var i, l,
            $o = {
                daysBack            : 8,
                itemsPerList        : 10,
                list                : [],

                // diagrams
                conversionDiagram   : $('#lp_js_conversion-diagram'),
                salesDiagram        : $('#lp_js_sales-diagram'),
                revenueDiagram      : $('#lp_js_revenue-diagram'),

                // main KPIs
                totalImpressionsKPI : $('#lp_js_total-impressions'),
                avgConversionKPI    : $('#lp_js_avg-conversion'),
                newCustomersKPI     : $('#lp_js_share-of-new-customers'),

                avgItemsSoldKPI     : $('#lp_js_avg-items-sold'),
                totalItemsSoldKPI   : $('#lp_js_total-items-sold'),

                avgRevenueKPI       : $('#lp_js_avg-revenue'),
                totalRevenueKPI     : $('#lp_js_total-revenue'),

                // top / bottom lists
                bestConvertingList  : $('#lp_js_best-converting-list'),
                leastConvertingList : $('#lp_js_least-converting-list'),
                bestSellingList     : $('#lp_js_best-selling-list'),
                leastSellingList    : $('#lp_js_least-selling-list'),
                bestGrossingList    : $('#lp_js_best-grossing-list'),
                leastGrossingList   : $('#lp_js_least-grossing-list'),
            },

            bindEvents = function() {
                // refresh dashboard
                $('#lp_js_refresh-dashboard')
                .click(function(e) {
                    fetchDashboardData();
                    e.preventDefault();
                });
            },

            renderDashboard = function(data) {
                var backgroundBars  = [],
                    xAxisTicks      = [];

                i = 0;

                for (; i < $o.daysBack; i++) {
                    backgroundBars.push([i + 1, 100]);
                    xAxisTicks.push([i + 1, 'Mon']);        // TODO: get real day names
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
                            data            : data.converting_items_by_day,
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
                            data            : data.selling_items_by_day,
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
                            data            : data.revenue_items_by_day,
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
                $o.totalImpressionsKPI.text(data.impressions);
                $o.avgConversionKPI.text(data.conversion);
                $o.newCustomersKPI.text(data.new_customers);

                $o.avgItemsSoldKPI.text(data.avg_purchase);
                $o.totalItemsSoldKPI.text(data.total_items_sold);

                $o.avgRevenueKPI.text(data.avg_revenue);
                $o.totalRevenueKPI.text(data.total_revenue);

                // sparklines
                $('.lp_sparkline-bar').peity('bar', {
                    width   : 34,
                    height  : 14,
                    gap     : 1,
                    fill    : function() { return '#ccc'; }
                });

                // best / worst lists
                if (data) { // TODO: this if statement should be removed as soon as all data are rendered client-side
                    renderTopBottomList($o.bestConvertingList,  data.best_converting_items);
                    renderTopBottomList($o.leastConvertingList, data.least_converting_items);
                    renderTopBottomList($o.bestSellingList,     data.most_selling_items);
                    renderTopBottomList($o.leastSellingList,    data.least_selling_items);
                    renderTopBottomList($o.bestGrossingList,    data.most_revenue_items);
                    renderTopBottomList($o.leastGrossingList,   data.least_revenue_items);
                }
            },

            renderTopBottomList = function($list, data) {
                $o.list = [];

                i = 0;
                l = data.length;

                if (l > 0) {
                    // create list item for each data set
                    for (; i < l; i++) {
                        $o.list.push(renderListItem(
                                        data.post_id,
                                        data.post_title,
                                        data.amount,
                                        data.unit,
                                        data.sparkline
                                    ));
                    }
                } else {
                    $o.list = ['<dfn>' + lpVars.i18n.noData + '</dfn>'];
                }

                // replace existing HTML
                $list.html($o.list.join());
            },

            renderListItem = function(postId, itemName, kpiValue, kpiUnit, sparklineData) {
                return '<li>' +
                            '<span class="lp_sparkline-bar">' + sparklineData + '</span>' +
                            '<strong class="lp_value">' + kpiValue + '<small>' + kpiUnit + '</small></strong>' +
                            '<i><a href="#" class="lp_js_toggle-item-details">' + itemName + '</a></i>' +
                        '</li>';
            },

            fetchDashboardData = function() {
                $.post(
                    lpVars.ajaxUrl,
                    {
                        'action'    : 'laterpay_get_dashboard_data',
                        '_wpnonce'  : lpVars.nonces.dashboard,
                        'days'      : $o.daysBack,
                        'count'     : $o.itemsPerList,
                        'refresh'   : 1,    // 1 (true): refresh data, 0 (false): only load the cached data; default: 1
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

                renderDashboard();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendDashboard();

});})(jQuery);
