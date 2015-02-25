(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var i, l,
            $o = {
                itemsPerList            : 10,
                list                    : [],
                intervalToMs            : {
                    'day'               : 86400,
                    'week'              : (86400 * 7),
                    '2-weeks'           : (86400 * 14),
                    'month'             : (86400 * 30),
                },

                // heading with dashboard configuration selections
                configurationSelection  : $('.lp_js_selectDashboardInterval, .lp_js_selectRevenueModel'),
                intervalChoices         : $('.lp_js_selectDashboardInterval'),
                revenueModelChoices     : $('.lp_js_selectRevenueModel'),
                currentInterval         : $('#lp_js_displayedInterval'),
                previousInterval        : $('#lp_js_loadPreviousInterval'),
                nextInterval            : $('#lp_js_loadNextInterval'),
                refreshDashboard        : $('#lp_js_refreshDashboard'),
                // generic dropdown selectors
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
                colorTextLighter        : '#ababab',

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

                // post-specific statistics
                toggleItemDetails       : '.lp_js_toggleItemDetails',

                // time passes customer lifecycle
                viewSelector            : $('#lp_js_switchDashboardView'),
                standardKpiTab          : $('#lp_js_standardKpiTab'),
                timePassesKpiTab        : $('#lp_js_timePassesKpiTab'),
                timepassDiagram         : $('.lp_js_timepassDiagram'),

                // state classes
                expanded                : 'lp_is-expanded',
                selected                : 'lp_is-selected',
                active                  : 'lp_is-active',
                delayed                 : 'lp_is-delayed',
                disabled                : 'lp_is-disabled',
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
                    labelWidth      : 20,
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

                // switch interval or revenue model filter
                $o.configurationSelection
                    .mousedown(function() {
                        var startTimestamp  = $o.currentInterval.data('startTimestamp'),
                            oldInterval     = getInterval(),
                            nextStartTimestamp,
                            nextEndTimestamp,
                            newInterval;

                        // mark clicked item as selected
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

                        newInterval = getInterval();

                        // for the 24 hour interval it's allowed to view 'today', but when switching to another interval
                        // we have to automatically switch back to 'yesterday'
                        if (oldInterval === 'day' && newInterval !== 'day') {
                            var todayDate   = new Date(),
                                startDate   = new Date(startTimestamp * 1000);

                            todayDate.setHours(0, 0, 0, 0);
                            startDate.setHours(0, 0, 0, 0);

                            if (todayDate.getTime() === startDate.getTime()) {
                                startTimestamp = startTimestamp - getIntervalDiff(oldInterval);
                            }
                        }

                        // check, if the 'next' button should be visible or hidden for the given interval
                        nextStartTimestamp  = startTimestamp + getIntervalDiff(newInterval);
                        switchNextIntervalState(nextStartTimestamp, newInterval);

                        // check, if the 'previous' button should be visible or hidden for the given interval
                        nextEndTimestamp    = startTimestamp - getIntervalDiff(newInterval);
                        switchPreviousIntervalState(nextEndTimestamp, newInterval);

                        setTimeRange(startTimestamp, newInterval);
                        loadDashboard(false);
                    })
                    .click(function(e) {e.preventDefault();});

                // load next interval
                $o.nextInterval
                    .mousedown(function() {
                        loadNextInterval();
                    })
                    .click(function(e) {e.preventDefault();});

                // load previous interval
                $o.previousInterval
                    .mousedown(function() {
                        loadPreviousInterval();
                    })
                    .click(function(e) {e.preventDefault();});

                $('body')
                    .on('mousedown', $o.toggleItemDetails, function() {
                        alert('Toggling post details coming soon');
                    })
                    .on('click', $o.toggleItemDetails, function(e) {e.preventDefault();});

                // switch between normal and time passes view
                $o.viewSelector
                    .mousedown(function() {
                        switchDashboardView($(this));
                    })
                    .click(function(e) {e.preventDefault();});
            },

            loadPreviousInterval = function() {
                var endTimestamp    = $o.currentInterval.data('startTimestamp'),
                    interval        = getInterval(),
                    intervalDiff    = getIntervalDiff(interval);

                if ($o.previousInterval.hasClass($o.disabled)) {
                    return;
                }

                // if we were able to select the previous interval, it must be possible to switch back to the current
                // interval so make sure the next link is not disabled
                $o.previousInterval.removeClass($o.disabled);

                endTimestamp = endTimestamp - intervalDiff;

                switchNextIntervalState(endTimestamp, interval);
                switchPreviousIntervalState(endTimestamp, interval);
                setTimeRange(endTimestamp, interval);
                loadDashboard(false);
            },

            loadNextInterval = function() {
                var startTimestamp  = $o.currentInterval.data('startTimestamp'),
                    nextStartTimestamp,
                    interval        = getInterval(),
                    intervalDiff    = getIntervalDiff(interval);

                if ($o.nextInterval.hasClass($o.disabled)) {
                    return;
                }

                // if we were able to select the next interval, it must be possible to switch back to the current
                // interval so make sure the prev link is not disabled
                $o.previousInterval.removeClass($o.disabled);

                startTimestamp = startTimestamp + intervalDiff;

                // check if the next startTimestamp is within the interval
                nextStartTimestamp = startTimestamp + intervalDiff;
                switchNextIntervalState(nextStartTimestamp, interval);

                setTimeRange(startTimestamp, interval);
                loadDashboard(true);
            },

            switchNextIntervalState = function(timestamp, interval) {
                if (!isDateWithinInterval(timestamp)) {
                    $o.nextInterval.addClass($o.disabled).removeAttr('data-tooltip');
                } else {
                    var i18n = getNextPrevTooltip(interval);
                    $o.nextInterval.removeClass($o.disabled).attr({'data-tooltip': i18n.next});
                }
            },

            switchPreviousIntervalState = function(timestamp, interval) {
                if (!isDateWithinInterval(timestamp)) {
                    $o.previousInterval.addClass($o.disabled).removeAttr('data-tooltip');
                } else {
                    var i18n = getNextPrevTooltip(interval);
                    $o.previousInterval.removeClass($o.disabled).attr({'data-tooltip': i18n.prev});
                }
            },

            isDateWithinInterval = function(timestamp) {
                var startDate   = new Date(),
                    intervalEnd = $o.currentInterval.data('intervalEndTimestamp'),
                    endDate     = new Date(intervalEnd * 1000),
                    givenDate   = new Date(timestamp * 1000),
                    interval    = getInterval();

                // for the 24 hour interval we allow 'today' as startDate, else we default to 'yesterday'
                if (interval !== 'day') {
                    startDate.setDate(startDate.getDate() - 1);
                }

                // reset all days to 0:00:00 for easier comparison
                startDate.setHours(0,0,0,0);
                endDate.setHours(0,0,0,0);
                givenDate.setHours(0,0,0,0);

                if (interval === 'day') {
                    return !(givenDate.getTime() <= endDate.getTime() || givenDate.getTime() > startDate.getTime());
                } else {
                    return !(givenDate.getTime() <= endDate.getTime() || givenDate.getTime() >= startDate.getTime());
                }
            },

            getIntervalDiff = function(interval) {
                var diff = 86400; // 1 day
                if (interval === 'day') {
                    diff = 86400;
                } else if (interval === 'week') {
                    diff = diff * 8;
                } else if (interval === '2-weeks') {
                    diff = diff * 16;
                } else if (interval === 'month') {
                    diff = diff * 30;
                }

                return diff;
            },

            getNextPrevTooltip = function(interval) {
                if (!lpVars.i18n.tooltips[interval]) {
                    return false;
                }

                return lpVars.i18n.tooltips[interval];
            },

            getInterval = function() {
                return $o.intervalChoices
                    .parents($o.dropdownList)
                    .find('.' + $o.selected)
                    .attr('data-interval');
            },

            setTimeRange = function(startTimestamp, interval) {
                var endTimestamp,
                    intervalInMs = $o.intervalToMs[interval],
                    from,
                    to,
                    timeRange;

                // new endTimestamp
                endTimestamp = startTimestamp - intervalInMs;

                // * 1000 because of php strtotime()
                to = new Date(startTimestamp * 1000);
                from = new Date(endTimestamp * 1000);

                // format date string for current interval
                // use international standard date format by default (YYYY-MM-DD); getMonth and getDate need to be
                // zero-padded for this
                if (interval === 'day') {
                    if (lpVars.locale === 'de_DE') {
                        // getMonth is 0-based so we need to compensate that by adding 1 to it
                        timeRange = to.getDate() + '.' + (to.getMonth() + 1) + '.' + to.getFullYear();
                    } else {
                        timeRange = to.getFullYear() + '-' +
                                    ('0' + (to.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + to.getDate()).slice(-2);
                    }
                } else {
                    if (lpVars.locale === 'de_DE') {
                        timeRange = from.getDate() + '.' + (from.getMonth() + 1) + '.' + from.getFullYear() +
                                    ' &ndash; ' +
                                    to.getDate() + '.' + (to.getMonth() + 1) + '.' + to.getFullYear();
                    } else {
                        timeRange = from.getFullYear() + '-' +
                                    ('0' + (from.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + from.getDate()).slice(-2) +
                                    ' &ndash; ' +
                                    to.getFullYear() + '-' +
                                    ('0' + (to.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + to.getDate()).slice(-2);
                    }
                }

                // set the new startTimestamp as data attribute for refreshing the dashboard data;
                // set the new timeRange
                $o.currentInterval
                    .data('startTimestamp', startTimestamp)
                    .html(timeRange);
            },

            loadDashboardData = function(section, refresh, pass) {
                var interval = getInterval(),
                    revenueModel = $o.revenueModelChoices
                        .parents($o.dropdownList)
                        .find('.' + $o.selected)
                        .attr('data-revenue-model'),
                    requestData = {
                        // WP Ajax action
                        'action'            : 'laterpay_get_dashboard_data',
                        // nonce for validation and XSS protection
                        '_wpnonce'          : lpVars.nonces.dashboard,
                        // data section to be loaded:
                        // converting_items | selling_items | revenue_items | most_least_converting_items |
                        // most_least_selling_items | most_least_revenue_items | metrics
                        'section'           : section,
                        // day | week | 2-weeks | month
                        'interval'          : interval,
                        // count of best / least performing items
                        'count'             : $o.itemsPerList,
                        // 1 (true): refresh data, 0 (false): only load the cached data; default: 1
                        'refresh'           : refresh ? 1 : 0,
                        // revenue model 'ppu', 'sis', or 'all'
                        'revenue_model'     : revenueModel,
                        // start-day to go backwards by interval
                        'start_timestamp'   : $o.currentInterval.data('startTimestamp'),
                        // time pass id (optional)
                        'pass_id'           : pass
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

            showLoadingIndicator = function($target) {
                // add a state class, indicating that the element will be showing a loading indicator after a delay
                $target.addClass($o.delayed);

                setTimeout(function() {
                    if ($target.hasClass($o.delayed)) {
                        // add the loading indicator after a delay, if the element still has that state class
                        $target.html('<div class="lp_loadingIndicator"></div>');
                    }
                }, 600);
            },

            removeLoadingIndicator = function($target) {
                if ($target.hasClass($o.delayed)) {
                    // remove the state class, thus canceling adding the loading indicator
                    $target.removeClass($o.delayed);
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
                        for (; i < l; i++) {
                            backColumns.push([i + 1, 100]);
                        }

                        var plotOptions = {
                                xaxis               : {
                                    ticks           : response.data.x,
                                },
                                yaxis               : {
                                    tickSize        : null,
                                    max             : 100,
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

                        // extend empty object to merge specific with default plotOptions without modifying the defaults
                        plotOptions = $.extend(true, {}, plotDefaultOptions, plotOptions);
                        $.plot($o.conversionDiagram, plotData, plotOptions);
                    })
                    .always(function() {
                        removeLoadingIndicator($o.conversionDiagram);
                    });
            },

            loadSellingItems = function(refresh) {
                showLoadingIndicator($o.salesDiagram);

                loadDashboardData('selling_items', refresh)
                    .done(function(response) {
                        var plotOptions = {
                                xaxis       : {
                                    ticks   : response.data.x,
                                },
                                yaxis       : {
                                    max     : null,
                                },
                            },
                            plotData = plotDefaultData;

                        // extend empty object to merge specific with default plotOptions without modifying the defaults
                        plotOptions = $.extend(true, {}, plotDefaultOptions, plotOptions);
                        plotData[0].data = response.data.y;

                        $.plot($o.salesDiagram, plotData, plotOptions);
                    })
                    .always(function() {
                        removeLoadingIndicator($o.salesDiagram);
                    });
            },

            loadRevenueItems = function(refresh) {
                showLoadingIndicator($o.revenueDiagram);

                loadDashboardData('revenue_items', refresh)
                    .done(function(response) {
                        var plotOptions = {
                                xaxis       : {
                                    ticks   : response.data.x,
                                },
                                yaxis: {
                                    max     : null,
                                },
                            },
                            plotData = plotDefaultData;

                        // extend empty object to merge specific with default plotOptions without modifying the defaults
                        plotOptions = $.extend(true, {}, plotDefaultOptions, plotOptions);
                        plotData[0].data = response.data.y;

                        $.plot($o.revenueDiagram, plotData, plotOptions);
                    })
                    .always(function() {
                        removeLoadingIndicator($o.revenueDiagram);
                    });
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
                        // column 1: conversion data
                        $o.totalImpressionsKPI.text(response.data.impressions || 0);
                        $o.avgConversionKPI.text(response.data.conversion || 0);
                        $o.newCustomersKPI.text(response.data.new_customers || 0);

                        // column 2: sales data
                        $o.avgItemsSoldKPI.text(response.data.avg_items_sold || 0);
                        $o.totalItemsSoldKPI.text(response.data.total_items_sold || 0);

                        // column 3: revenue data
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
                    $o.list = ['<dfn class="lp_topBottomList__empty-state">' + lpVars.i18n.noData + '</dfn>'];
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

            renderSparklines = function($context) {
                var $sparkline = $('.lp_sparklineBar', $context),
                // get the number of data points from the first matched sparkline
                    dataPoints = $sparkline.first().text().split(',').length;

                if (dataPoints > 8) {
                    // render lots of data points as line chart, because bars would have < 1 px width each
                    $sparkline
                        .peity('line', {
                            fill    : $o.colorBackground,
                            height  : 14,
                            stroke  : $o.colorBorder,
                            width   : 34,
                        });
                } else {
                    $sparkline
                        .peity('bar', {
                            fill    : function() {
                                return $o.colorBorder;
                            },
                            gap     : 1,
                            height  : 14,
                            width   : 34,
                        });
                }
            },

            loadDashboard = function(refresh) {

                refresh = refresh || false;
                loadMostLeastConvertingItems(refresh);
                loadMostLeastRevenueItems(refresh);
                loadMostLeastSellingItems(refresh);
                loadConvertingItems(refresh);
                loadTimePassLifecycles(true);
                loadRevenueItems(refresh);
                loadSellingItems(refresh);
                loadKPIs(refresh);
            },

            switchDashboardView = function($item) {
                var data            = $.parseJSON($item.attr('data')),
                    current_label   = $.trim($item.html());

                if (data.view === lpVars.submenu.view.standard) {
                    // standard KPI dashboard
                    // change label
                    $item.html(data.label);

                    // set new data view
                    data.view = lpVars.submenu.view.passes;

                    // select view
                    $o.standardKpiTab.show();
                    $o.timePassesKpiTab.hide();

                    // update data
                    data.label = current_label;
                    $item.attr('data', JSON.stringify(data));
                } else if (data.view === lpVars.submenu.view.passes) {
                    // time passes dashboard
                    // change label
                    $item.html(data.label);

                    // set new data view
                    data.view = lpVars.submenu.view.standard;

                    // select view
                    $o.timePassesKpiTab.show();
                    $o.standardKpiTab.hide();

                    // update data
                    data.label = current_label;
                    $item.attr('data', JSON.stringify(data));
                }
            },

            loadTimePassLifecycles = function(refresh) {
                var data = $o.timepassDiagram;

                $.each($o.timepassDiagram, function(index) {
                    var timePassId = $(data[index]).data('id');

                    showLoadingIndicator($(data[index]));

                    loadDashboardData('time_passes_expiry', refresh, timePassId)
                        .done(function(response) {
                            var max             = parseInt(lpVars.maxYValue, 10),
                                yAxisScale      = Math.max(max, 10) + 4, // add some air to y-axis scale
                                $placeholder    = $(data[index]),
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
                            var $graph  = $.plot($placeholder, plotData, plotOptions);

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
                            $placeholder.append(label1);
                            // get the offset of separator 2 within the flot placeholder
                            var o2      = $graph.pointOffset({x: 11.5, y: 0}),
                                label2  = '<div class="lp_time-pass-diagram__label" ' +
                                    'style="left:' + (o2.left - 30) + 'px; top:2px;">' +
                                    lpVars.i18n.endingIn + '<br>' +
                                    '< 3 ' + lpVars.i18n.months +
                                    '</div>';
                            // append that label to the graph
                            $placeholder.append(label2);

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
                                'style="left:' + (o3.left + 10) + 'px; top:' + o3.top + 'px;">' +
                                lpVars.i18n.weeksLeft +
                                '</div>';
                            $placeholder.append(xAxisLabel);

                            // add sum to 0-4 weeks interval
                            var o4          = $graph.pointOffset({x: 1.5, y: yAxisScale - 2}),
                                sum04Label  = '<div class="lp_time-pass-diagram__sum" ' +
                                    'style="left:' + (o4.left - 30) + 'px; top:' + (o4.top - 14) + 'px;">' +
                                    sum04 +
                                    '</div>';
                            $placeholder.append(sum04Label);

                            // add sum to 5-12 weeks interval
                            var o5          = $graph.pointOffset({x: 7.5, y: yAxisScale - 2}),
                                sum512Label = '<div class="lp_time-pass-diagram__sum" ' +
                                    'style="left:' + (o5.left - 30) + 'px; top:' + (o5.top - 14) + 'px;">' +
                                    sum512 +
                                    '</div>';
                            $placeholder.append(sum512Label);
                        })
                        .always(function() {
                            removeLoadingIndicator($(data[index]));
                        });
                });
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
