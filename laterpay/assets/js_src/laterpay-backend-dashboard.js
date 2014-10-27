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
                // some mock data:
                var last_items_sold         = [[1412294400000, 0], [1412380800000, 0], [1412467200000, 0], [1412553600000, 0], [1412640000000, 0], [1412726400000, 0], [1412812800000, 0], [1412899200000, 0]],
                    last_amounts            = [[1412294400000, 0.0], [1412380800000, 0.0], [1412467200000, 0.0], [1412553600000, 0.0], [1412640000000, 0.0], [1412726400000, 0.0], [1412812800000, 0.0], [1412899200000, 0.0]],
                    conversionData_perDay   = [[1, 13], [2, 16], [3, 14], [4, 12], [5, 17], [6, 15], [7, 12]],
                    conversionData_total    = [[1, 13], [2, 29], [3, 43], [4, 55], [5, 72], [6, 87], [7, 99]],
                    plot_timeformat,
                    plot_mode;

                if ($o.daysBack === 8) {
                    plot_timeformat = '%a';
                    plot_mode       = 'time';
                } else if ($o.daysBack === 31) {
                    plot_timeformat = '%m/%d';
                    plot_mode       = 'time';
                } else {
                    plot_mode = null;
                }

                // flot diagrams
                $.plot($o.conversionDiagram,
                  [ {
                      data: [[1, 100], [2, 100], [3, 100], [4, 100], [5, 100], [6, 100], [7, 100]],
                      bars: {
                        show:       true,
                        barWidth:   0.7,
                        fillColor:  '#e3e3e3',
                        lineWidth:  0,
                        align:      'center',
                        horizontal: false
                      }
                    },
                    {
                      data: conversionData_perDay,
                      bars: {
                        show:       true,
                        barWidth:   0.35,
                        fillColor:  '#52CB75',
                        lineWidth:  0,
                        align:      'center',
                        horizontal: false
                      }
                    } ],
                  {
                    legend: {show: false},
                    xaxis: {
                      font: {
                        color: '#bbb',
                        lineHeight: 18,
                      },
                      show:  true,
                      ticks: [[1, 'Mon'], [2, 'Tue'], [3, 'Wed'], [4, 'Thu'], [5, 'Fri'], [6, 'Sat'], [7, 'Sun']],
                    },
                    yaxis: {
                        font: {color: '#bbb'},
                          ticks: 5,
                      tickFormatter: function (v) {return v + ' %';},
                          min: 0,
                          max: 100,
                      reserveSpace: true,
                      },
                    series: {
                      shadowSize: 0,
                    },
                    grid: {
                      borderWidth: {
                          top:     0,
                          right:   0,
                          bottom:  1,
                          left:    0,
                      },
                      borderColor: '#ccc',
                      tickColor:   'rgba(247,247,247,0)',  // transparent
                    }
                  }
                );

                $.plot($o.salesDiagram,
                  [ {
                      data: last_items_sold,
                      color: '#52CB75',
                      lines: {
                      show:      true,
                      lineWidth: 1.5,
                      fill:      false,
                      gaps:      true,
                    },
                    points: {
                      show:      true,
                      radius:    3,
                      lineWidth: 0,
                      fill:      true,
                      fillColor: '#52CB75'
                    }
                    },
                    {
                      data: conversionData_total,
                      color: '#52CB75',
                      lines: {
                      show:      true,
                      lineWidth: 1.5,
                      fill:      false,
                      gaps:      true,
                    },
                    points: {
                        show:      true,
                        radius:    3,
                        lineWidth: 0,
                        fill:      true,
                        fillColor: '#52CB75'
                    }
                    }
                  ],
                  {
                    legend: {show: false},
                    xaxis: {
                      font: {
                        color: '#bbb',
                        lineHeight: 18,
                      },
                      mode: plot_mode,
                      timeformat: plot_timeformat,
                      show:  true,
                    },
                    yaxis: {
                      font: {color: '#bbb'},
                      ticks: 5,
                      min: 0,
                      //max: 100,
                      reserveSpace: true,
                    },
                    series: {
                      shadowSize: 0,
                    },
                    grid: {
                      borderWidth: {
                        top:     0,
                        right:   0,
                        bottom:  1,
                        left:    0,
                      },
                      borderColor: '#ccc',
                      tickColor:   'rgba(247,247,247,0)',  // transparent
                    }
                  }
                );

                $.plot($o.revenueDiagram,
                  [ {
                      data: last_amounts,
                      color: '#52CB75',
                      lines: {
                      show:      true,
                      lineWidth: 1.5,
                      fill:      false,
                      gaps:      true,
                    },
                    points: {
                      show:      true,
                      radius:    3,
                      lineWidth: 0,
                      fill:      true,
                      fillColor: '#52CB75'
                    }
                    },
                    //{
                      //data: conversionData_total,
                      //color: '#52CB75',
                      //lines: {
                      //show:      true,
                      //lineWidth: 1.5,
                      //fill:      false,
                      //gaps:      true,
                    //},
                    //points: {
                      //show:      true,
                      //radius:    3,
                      //lineWidth: 0,
                      //fill:      true,
                      //fillColor: '#52CB75'
                    //}
                    //}
                  ],
                  {
                    legend: {show: false},
                    xaxis: {
                      font: {
                        color: '#bbb',
                        lineHeight: 18,
                      },
                      show:  true,
                      mode: plot_mode,
                      timeformat: plot_timeformat,
                    },
                    yaxis: {
                      font: {color: '#bbb'},
                      ticks: 5,
                      min: 0,
                      //max: 100,
                      reserveSpace: true,
                    },
                    series: {
                      shadowSize: 0,
                    },
                    grid: {
                      borderWidth: {
                        top:     0,
                        right:   0,
                        bottom:  1,
                        left:    0,
                      },
                      borderColor: '#ccc',
                      tickColor:   'rgba(247,247,247,0)',  // transparent
                    }
                  }
                );

                // big KPIs
                $o.totalImpressionsKPI.text('3,333');   // TODO: use actual data
                $o.avgConversionKPI.text('3.3');        // TODO: use actual data
                $o.newCustomersKPI.text('33');          // TODO: use actual data

                $o.avgItemsSoldKPI.text('3.3');         // TODO: use actual data
                $o.totalItemsSoldKPI.text('33,333');    // TODO: use actual data

                $o.avgRevenueKPI.text('3.33');          // TODO: use actual data
                $o.totalRevenueKPI.text('3.333.33');     // TODO: use actual data

                // sparklines
                $('.lp_sparkline-bar').peity('bar', {
                    width       : 34,
                    height      : 14,
                    gap         : 1,
                    fill        : function() { return '#ccc'; }
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
                l = 10;//data.length;

                if (l > 0) {
                    // create list item for each data set
                    for (; i < l; i++) {
                        $o.list.push(renderListItem('Dummy Item', 66, 'EUR', []));    // TODO: use actual data
                    }
                } else {
                    $o.list = ['<dfn>' + lpVars.i18n.noData + '</dfn>'];
                }

                // replace existing HTML
                $list.html($o.list.join());
            },

            renderListItem = function(itemName, kpiValue, kpiUnit, sparklineData) {
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
