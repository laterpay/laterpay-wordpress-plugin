(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var $o = {
                // stuff
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
console.log(data);
                // some mock data:
                var last_items_sold         = [[1412294400000, 0], [1412380800000, 0], [1412467200000, 0], [1412553600000, 0], [1412640000000, 0], [1412726400000, 0], [1412812800000, 0], [1412899200000, 0]],
                    last_amounts            = [[1412294400000, 0.0], [1412380800000, 0.0], [1412467200000, 0.0], [1412553600000, 0.0], [1412640000000, 0.0], [1412726400000, 0.0], [1412812800000, 0.0], [1412899200000, 0.0]],
                    interval                = '7days',
                    conversionData_perDay   = [[1, 13], [2, 16], [3, 14], [4, 12], [5, 17], [6, 15], [7, 12]],
                    conversionData_total    = [[1, 13], [2, 29], [3, 43], [4, 55], [5, 72], [6, 87], [7, 99]];

                // compute averages and sums of items sold and revenue
                var value_sum           = function(list) {
                                                var i       = 0,
                                                    total   = 0,
                                                    l       = list.length;
                                                for (; i < l; i++) {
                                                    total = total + list[i][1];
                                                }
                                                return total;
                                            },
                    total_items_sold    = value_sum(last_items_sold),
                    avg_items_sold      = total_items_sold / last_items_sold.length,
                    total_revenue       = value_sum(last_amounts),
                    avg_revenue         = total_revenue / last_amounts.length,
                    plot_timeformat,
                    plot_mode;

                if (interval === '7days') {
                    plot_timeformat = '%a';
                    plot_mode       = 'time';
                } else if (interval === '30days') {
                    plot_timeformat = '%m/%d';
                    plot_mode       = 'time';
                } else {
                    plot_mode = null;
                }

                // flot diagrams
                $.plot($('#lp_js_graph-conversion'),
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
                      tickFormatter: function (v) {return v + " %";},
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

                $.plot($('#lp_js_graph-units'),
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

                $.plot($('#lp_js_graph-revenue'),
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
                $('#lp_js_total-impressions').text(data.total_viewed_items[0].quantity);

                $('#lp_js_avg-items-sold').text(avg_items_sold.toFixed(1));
                $('#lp_js_total-items-sold').text(total_items_sold);

                $('#lp_js_total-revenue').text(total_revenue.toFixed(2));
                $('#lp_js_avg-revenue').text(avg_revenue.toFixed(2));

                // sparklines
                $('.lp_sparkline-bar').peity('bar', {
                    width       : 34,
                    height      : 14,
                    gap         : 1,
                    fill        : function() { return '#ccc'; }
                });

                // best / worst lists
                // ...
            },

            fetchDashboardData = function() {
                $.post(
                    lpVars.ajaxUrl,
                    {
                        'action'    : 'laterpay_get_dashboard_data',
                        '_wpnonce'  : lpVars.nonces.dashboard,
                        'days'      : 8,    // how many days we want to go back - default: 8
                        'count'     : 10,   // number of items, the top {n} items - default: 10
                        'refresh'   : 1,    // on default 1 (true), 0 (false) only loads the cached data
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
