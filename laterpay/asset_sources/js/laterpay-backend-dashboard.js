(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var i, l,
            $o = {
                daysBack                : 8,
                itemsPerList            : 10,
                list                    : [],

                // heading with dashboard configuration selections
                configurationSelection  : $('.lp_js_selectDashboardInterval, .lp_js_selectRevenueModel'),
                intervalChoices         : $('.lp_js_selectDashboardInterval'),
                revenueModelChoices     : $('.lp_js_selectRevenueModel'),
                currentInterval         : $('#lp_js_displayedInterval'),
                previousInterval        : $('#lp_js_loadPreviousInterval'),
                nextInterval            : $('#lp_js_loadNextInterval'),
                refreshDashboard        : $('#lp_js_refreshDashboard'),
                // general dropdown selectors
                dropdown                : '.lp_dropdown',
                dropdownList            : '.lp_dropdown_list',
                dropdownCurrentItem     : '.lp_dropdown_currentItem',

                // diagrams
                conversionDiagram       : $('#lp_js_conversionDiagram'),
                salesDiagram            : $('#lp_js_salesDiagram'),
                revenueDiagram          : $('#lp_js_revenueDiagram'),
                // colors
                colorBackground         : '#e3e3e3',
                colorBackgroundLaterpay : '#50c371',
                colorBorder             : '#ccc',
                colorTextLighter        : '#bbb',

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

                toggleItemDetails       : '.lp_js_toggleItemDetails',

                // strings cached for better compression
                expanded                : 'lp_is-expanded',
                selected                : 'lp_is-selected',
            },

            plotDefaultOptions = {
                legend              : {
                    show            : false,
                },
                xaxis               : {
                    font            : {
                        color       : $o.colorTextLighter,
                        lineHeight  : 18,
                    },
                    show            : true,
                },
                yaxis               : {
                    font            : {
                        color       : $o.colorTextLighter,
                    },
                    min             : 0,
                    reserveSpace    : true,
                    ticks           : 5,
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
                    borderColor     : $o.colorBorder,
                    tickColor       : 'rgba(247,247,247,0)', // transparent
                }
            },

            plotDefaultData = [
                {
                    color           : $o.colorBackgroundLaterpay,
                    lines           : {
                        fill        : false,
                        gaps        : true,
                        lineWidth   : 2.5,
                        show        : true,
                    },
                    points          : {
                        fill        : true,
                        fillColor   : $o.colorBackgroundLaterpay,
                        lineWidth   : 0,
                        radius      : 4,
                        show        : true,
                    }
                }
            ],

            bindEvents = function() {
                // toggle dropdown_list on touch devices
                $($o.dropdownCurrentItem)
                .click(function() {
                    $(this).parent($o.dropdown).addClass($o.expanded);
                });

                // re-render dashboard in selected configuration
                $o.configurationSelection
                .mousedown(function() {
                    // change selected item to clicked item
                    $(this)
                        .parents($o.dropdown)
                        .removeClass($o.expanded)
                            .find($o.dropdownCurrentItem)
                            .text($(this).text())
                        .end()
                            .find('.' + $o.selected)
                            .removeClass($o.selected)
                        .end()
                    .end()
                    .addClass($o.selected);

                    loadDashboard();
                })
                .click(function(e) {e.preventDefault();});

                // re-render dashboard with data of next interval
                $o.nextInterval
                .mousedown(function() {
                    alert('Showing next interval coming soon');
                })
                .click(function(e) {e.preventDefault();});

                // re-render dashboard with data of previous interval
                $o.previousInterval
                .mousedown(function() {
                    alert('Showing previous interval coming soon');
                })
                .click(function(e) {e.preventDefault();});

                // refresh dashboard
                $o.refreshDashboard
                .mousedown(function() {
                    loadDashboard(true);
                })
                .click(function(e) {e.preventDefault();});

                $('body')
                .on('mousedown', $o.toggleItemDetails, function() {
                    alert('Toggling post details coming soon');
                })
                .on('click', $o.toggleItemDetails, function(e) {e.preventDefault();});

                $($o.revenueModelChoices)
                .mousedown(function() {
                    alert('Filtering by revenue model coming soon');
                })
                .click(function(e) {e.preventDefault();});
            },

            loadDashboardData = function(section, refresh) {
                var interval        = $o.intervalChoices
                                        .parents($o.dropdownList)
                                            .find('.' + $o.selected)
                                            .attr('data-interval'),
                    revenueModel    = $o.revenueModelChoices
                                        .parents($o.dropdownList)
                                            .find('.' + $o.selected)
                                            .attr('data-revenue-model'),
                    requestData     = {
                        // WP Ajax action
                        'action'          : 'laterpay_get_dashboard_data',
                        // nonce for validation and xss protection
                        '_wpnonce'        : lpVars.nonces.dashboard,
                        // data section to be loaded:
                        // converting_items|selling_items|revenue_items|most_least_converting_items|
                        // most_least_selling_items|most_least_revenue_items|metrics
                        'section'         : section,
                        // day|week|2-weeks|month
                        'interval'        : interval,
                        // count of best / least performing items
                        'count'           : $o.itemsPerList,
                        // 1 (true): refresh data, 0 (false): only load the cached data; default: 1
                        'refresh'         : refresh ? 1 : 0,
                        // TODO: implement
                        'revenue_model'   : revenueModel,
                    },
                    jqxhr;

                jqxhr = $.ajax({
                    'url'      : lpVars.ajaxUrl,
                    'async'    : true,
                    'method'   : 'POST',
                    'data'     : requestData,
                });

                jqxhr.done(function(data) {
                    if (!data || data.success) {
                        return;
                    }
                    setMessage(data.message, data.success);
                });

                return jqxhr;
            },

            showLoadingIndicator = function($target) {
                // add a state class, indicating that the element will be showing a loading indicator after a delay
                $target.addClass('lp_is-delayed');

                setTimeout(function() {
                    if ($target.hasClass('lp_is-delayed')) {
                        // add the loading indicator after a delay, if the element still has that state class
                        $target.html('<div class="lp_loadingIndicator"></div>');
                    }
                }, 600);
            },

            removeLoadingIndicator = function($target) {
                if ($target.hasClass('lp_is-delayed')) {
                    // remove the state class, thus canceling adding the loading indicator
                    $target.removeClass('lp_is-delayed');
                } else {
                    // remove the loading indicator
                    $target.find('.lp_loadingIndicator').remove();
                }
            },

            loadConvertingItems = function(refresh) {
                showLoadingIndicator($o.conversionDiagram);

                loadDashboardData('converting_items', refresh)
                .done(function(response) {
                    // generate a data point with 100% y-value for each conversion rate column as background
                    var backColumns = [];
                    i = 0;
                    l = response.data.y.length;
                    for (i; i < l; i++) {
                        backColumns.push([i + 1, 100]);
                    }

                    var plotOptions = {
                            xaxis: {
                                ticks: response.data.x,
                            },
                            yaxis: {
                                max: 100,
                            }
                        },
                        plotData = [
                            {
                                data            : backColumns,
                                bars            : {
                                    align       : 'center',
                                    barWidth    : 0.6,
                                    fillColor   : $o.colorBackground,
                                    horizontal  : false,
                                    lineWidth   : 0,
                                    show        : true,
                                }
                            },
                            {
                                data            : response.data.y,
                                bars            : {
                                    align       : 'center',
                                    barWidth    : 0.4,
                                    fillColor   : $o.colorBackgroundLaterpay,
                                    horizontal  : false,
                                    lineWidth   : 0,
                                    show        : true,
                                }
                            },
                        ];

                    plotOptions = $.extend(true, plotDefaultOptions, plotOptions);
                    $.plot($o.conversionDiagram, plotData, plotOptions);
                })
                .always(function() {removeLoadingIndicator($o.conversionDiagram);});
            },

            loadSellingItems = function(refresh) {
                showLoadingIndicator($o.salesDiagram);

                loadDashboardData('selling_items', refresh)
                .done(function(response) {
                    var plotOptions = {
                            xaxis: {
                                ticks: response.data.x,
                            },
                            yaxis: {
                                max: null,
                            }
                        },
                        plotData = plotDefaultData;

                    plotOptions         = $.extend(true, plotDefaultOptions, plotOptions);
                    plotData[0].data    = response.data.y;

                    $.plot($o.salesDiagram, plotData, plotOptions);
                })
                .always(function() {removeLoadingIndicator($o.salesDiagram);});
            },

            loadRevenueItems = function(refresh) {
                showLoadingIndicator($o.revenueDiagram);

                loadDashboardData('revenue_items', refresh)
                .done(function(response) {
                    var plotOptions = {
                            xaxis: {
                                ticks: response.data.x,
                            },
                            yaxis: {
                                max: null,
                            }
                        },
                        plotData = plotDefaultData;

                    plotOptions         = $.extend(true, plotDefaultOptions, plotOptions);
                    plotData[0].data    = response.data.y;

                    $.plot($o.revenueDiagram, plotData, plotOptions);
                })
                .always(function() {removeLoadingIndicator($o.revenueDiagram);});
            },

            loadMostLeastConvertingItems = function(refresh) {
                showLoadingIndicator($o.bestConvertingList);
                showLoadingIndicator($o.leastConvertingList);

                loadDashboardData('most_least_converting_items', refresh)
                .done(function(response) {
                    if (!response.data.most) {
                        response.data.most = {};
                    }

                    if (!response.data.least) {
                        response.data.least = {};
                    }

                    renderTopBottomList($o.bestConvertingList, response.data.most);
                    renderSparklines($o.bestConvertingList);

                    renderTopBottomList($o.leastConvertingList, response.data.least);
                    renderSparklines($o.leastConvertingList);
                })
                .always(function() {
                    removeLoadingIndicator($o.bestConvertingList);
                    removeLoadingIndicator($o.leastConvertingList);
                });
            },

            loadMostLeastSellingItems = function(refresh) {
                showLoadingIndicator($o.bestSellingList);
                showLoadingIndicator($o.leastSellingList);

                loadDashboardData('most_least_selling_items', refresh)
                .done(function(response) {
                    if (!response.data.most) {
                        response.data.most = {};
                    }

                    if (!response.data.least) {
                        response.data.least = {};
                    }

                    renderTopBottomList($o.bestSellingList, response.data.most);
                    renderSparklines($o.bestSellingList);

                    renderTopBottomList($o.leastSellingList, response.data.least);
                    renderSparklines($o.leastSellingList);
                })
                .always(function() {
                    removeLoadingIndicator($o.bestSellingList);
                    removeLoadingIndicator($o.leastSellingList);
                });
            },

            loadMostLeastRevenueItems = function(refresh) {
                showLoadingIndicator($o.bestGrossingList);
                showLoadingIndicator($o.leastGrossingList);

                loadDashboardData('most_least_revenue_items', refresh)
                .done(function(response) {
                    if (!response.data.most) {
                        response.data.most = {};
                    }

                    if (!response.data.least) {
                        response.data.least = {};
                    }

                    renderTopBottomList($o.bestGrossingList, response.data.most);
                    renderSparklines($o.bestGrossingList);

                    renderTopBottomList($o.leastGrossingList, response.data.least);
                    renderSparklines($o.leastGrossingList);
                })
                .always(function() {
                    removeLoadingIndicator($o.bestGrossingList);
                    removeLoadingIndicator($o.leastGrossingList);
                });
            },

            loadKPIs = function(refresh) {
                loadDashboardData('metrics', refresh)
                .done(function(response) {
                    $o.totalImpressionsKPI.text(response.data.impressions || 0);
                    $o.avgConversionKPI.text(response.data.conversion || 0);
                    $o.newCustomersKPI.text(response.data.new_customers || 0);

                    $o.avgItemsSoldKPI.text(response.data.avg_items_sold || 0);
                    $o.totalItemsSoldKPI.text(response.data.total_items_sold || 0);

                    $o.avgRevenueKPI.text(response.data.avg_purchase || 0);
                    $o.totalRevenueKPI.text(response.data.total_revenue || 0);
                });
            },

            renderTopBottomList = function($list, data) {
                $o.list = [];

                i = 0;
                l = data ? data.length : 0;

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
                $list.html($o.list.join(''));
            },

            renderListItem = function(postId, itemName, kpiValue, kpiUnit, sparklineData) {
                var kpi         = kpiUnit ? kpiValue + '<small>' + kpiUnit + '</small>' : kpiValue,
                    valueClass  = 'lp_value';

                if (kpiUnit === '%' || kpiUnit === '') {
                    valueClass = 'lp_value-narrow';
                }

                return '<li>' +
                            '<span class="lp_sparklineBar">' + sparklineData + '</span>' +
                            '<strong class="' + valueClass + '">' + kpi + '</strong>' +
                            '<i><a href="#" class="lp_js_toggleItemDetails">' + itemName + '</a></i>' +
                        '</li>';
            },

            renderSparklines = function( context ) {
                $('.lp_sparklineBar', context).peity('bar', {
                    width   : 34,
                    height  : 14,
                    gap     : 1,
                    fill    : function() {return '#ccc';}
                });
            },

            loadDashboard = function(refresh) {
                refresh = refresh || false;
                loadConvertingItems(refresh);
                loadRevenueItems(refresh);
                loadSellingItems(refresh);
                loadKPIs(refresh);
                loadMostLeastConvertingItems(refresh);
                loadMostLeastRevenueItems(refresh);
                loadMostLeastSellingItems(refresh);
            },

            initializePage = function() {
                bindEvents();
                loadDashboard();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendDashboard();

});})(jQuery);
