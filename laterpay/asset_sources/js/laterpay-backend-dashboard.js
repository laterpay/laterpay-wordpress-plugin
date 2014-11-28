(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendDashboard
    function laterPayBackendDashboard() {
        var i, l,
            $o = {
                daysBack				: 8,
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
                leastGrossingList       : $('#lp_js_leastGrossingList')
			},
			plotDefaultOptions = {
				legend              : {
					show            : false
				},
				xaxis               : {
					font            : {
						color       : '#bbb',
						lineHeight  : 18
					},
					show            : true
				},
				yaxis               : {
					font            : {
						color       : '#bbb'
					},
					ticks           : 5,
					min             : 0,
					reserveSpace    : true
				},
				series              : {
					shadowSize      : 0
				},
				grid                : {
					borderWidth     : {
						top         : 0,
						right       : 0,
						bottom      : 1,
						left        : 0
					},
					borderColor     : '#ccc',
					tickColor       : 'rgba(247,247,247,0)' // transparent
				}
			},
			plotDefaultData = [
				{
					color           : '#50c371',
					lines           : {
						show        : true,
						lineWidth   : 2.5,
						fill        : false,
						gaps        : true
					},
					points          : {
						show        : true,
						radius      : 4,
						lineWidth   : 0,
						fill        : true,
						fillColor   : '#50c371'
					}
				}
			],

			loadDashboardData = function( section, refresh ) {
				var interval        = $($o.selectedInterval).find(':checked').val(),
					revenueModel    = $o.selectedRevenueModel.find(':checked').val(),
					request_data	= {
						// wp ajax action
						'action'          : 'laterpay_get_dashboard_data',
						// nonce for validation and xss-protection
						'_wpnonce'        : lpVars.nonces.dashboard,
						// data-section which is loaded:  converting_items|selling_items|revenue_items|most_least_converting_items|most_least_selling_items|most_least_revenue_items|metrics
						'section'         : section,
						// day|week|2-weeks|month
						'interval'        : interval,
						// count of best / least performing items
						'count'           : $o.itemsPerList,
						// 1 (true): refresh data, 0 (false): only load the cached data; default: 1
						'refresh'         : refresh ? 1 : 0,
						// TODO: implementing
						'revenue_model'   : revenueModel
					},
					jqxhr;

				jqxhr = $.ajax({
					'url'      : lpVars.ajaxUrl,
					'async'    : true,
					'method'   : 'POST',
					'data'     : request_data,
				});

				jqxhr.done(function(data) {
					if (!data || data.success) {
						return;
					}
					setMessage(data.message, data.success);
				});

				return jqxhr;
			},

			showLoadingIndicator = function(element) {
				element.html('<div class="lp_loadingIndicator"></div>');
			},

			removeLoadingIndicator = function(element) {
				element.find('.lp_loadingIndicator').remove();
			},

			loadConvertingItems = function(refresh) {
				showLoadingIndicator($o.conversionDiagram);

				loadDashboardData('converting_items', refresh).done(function(response) {
					var plotOptions = {
							xaxis: {
								ticks: response.data.x
							}
						},
						plotData = [
							{
								data            : response.data.y,
								bars            : {
									show        : true,
									barWidth    : 0.35,
									fillColor   : '#50c371',
									lineWidth   : 0,
									align       : 'center',
									horizontal  : false
								}
							}
						];

					plotOptions = $.extend(true, plotDefaultOptions, plotOptions);
					$.plot($o.conversionDiagram, plotData, plotOptions);
				})
				.always(function() {removeLoadingIndicator($o.conversionDiagram);});
			},

			loadSellingItems = function(refresh) {
				showLoadingIndicator($o.salesDiagram);

				loadDashboardData('selling_items', refresh).done(function(response) {
					var plotOptions = {
							xaxis: {
								ticks: response.data.x
							}
						},
						plotData = plotDefaultData;

					plotOptions			= $.extend(true, plotDefaultOptions, plotOptions);
					plotData[0].data	= response.data.y;

					$.plot($o.salesDiagram, plotData, plotOptions);
				})
				.always(function() {removeLoadingIndicator($o.salesDiagram);});
			},

			loadRevenueItems = function(refresh) {
				showLoadingIndicator($o.revenueDiagram);

				loadDashboardData('revenue_items', refresh).done(function(response) {
					var plotOptions = {
							xaxis: {
								ticks: response.data.x
							}
						},
						plotData = plotDefaultData;

					plotOptions			= $.extend(true, plotDefaultOptions, plotOptions);
					plotData[0].data	= response.data.y;

					$.plot($o.revenueDiagram, plotData, plotOptions);
				})
				.always(function() {removeLoadingIndicator($o.revenueDiagram);});
			},

			loadMostLeastConvertingItems = function(refresh) {
				showLoadingIndicator($o.bestConvertingList);
				showLoadingIndicator($o.leastConvertingList);

				loadDashboardData('most_least_converting_items', refresh).done(function(response) {
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
				.always(function() {removeLoadingIndicator($o.bestConvertingList);})
				.always(function() {removeLoadingIndicator($o.leastConvertingList);});
			},

			loadMostLeastSellingItems = function(refresh) {
				showLoadingIndicator($o.bestSellingList);
				showLoadingIndicator($o.leastSellingList);

				loadDashboardData('most_least_selling_items', refresh).done(function(response) {
					if (!response.data.most) {
						response.data.most = {};
					}

					if (!response.data.least) {
						response.data.least = {};
					}

					renderTopBottomList($o.bestSellingList, response.data.most);
					renderSparklines( $o.bestSellingList );

					renderTopBottomList($o.leastSellingList, response.data.least);
					renderSparklines( $o.leastSellingList );
				})
				.always(function() {removeLoadingIndicator($o.bestSellingList);})
				.always(function() {removeLoadingIndicator($o.leastSellingList);});
			},

			loadMostLeastRevenueItems = function(refresh) {
				showLoadingIndicator($o.bestGrossingList);
				showLoadingIndicator($o.leastGrossingList);

				loadDashboardData('most_least_revenue_items', refresh).done(function(response) {
					if (!response.data.most) {
						response.data.most = {};
					}

					if (!response.data.least) {
						response.data.least = {};
					}

					renderTopBottomList($o.bestGrossingList,  response.data.most);
					renderSparklines($o.bestGrossingList);

					renderTopBottomList($o.leastGrossingList, response.data.least);
					renderSparklines($o.leastGrossingList);
				})
				.always(function() {removeLoadingIndicator($o.bestGrossingList);})
				.always(function() {removeLoadingIndicator($o.leastGrossingList);});
			},

			loadMetrics = function(refresh) {
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

            bindEvents = function() {
                // refresh dashboard
                $('#lp_js_refreshDashboard')
                .click(function(e) {
					loadDashboard(true);
                    e.preventDefault();
                });

                // re-render dashboard in selected configuration
                $o.configurationSelection
                .change(function() {
					loadDashboard();
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
                var kpi = kpiUnit ? kpiValue + '<small>' + kpiUnit + '</small>' : kpiValue;

                return '<li>' +
                            '<span class="lp_sparklineBar">' + sparklineData + '</span>' +
                            '<strong class="lp_value">' + kpi + '</strong>' +
                            '<i><a href="#" class="lp_js_toggleItemDetails">' + itemName + '</a></i>' +
                        '</li>';
            },

            renderSparklines = function( context ) {
                $('.lp_sparklineBar', context).peity('bar', {
                    width   : 34,
                    height  : 14,
                    gap     : 1,
                    fill    : function() { return '#ccc'; }
                });
            },

			loadDashboard = function(refresh) {
				refresh = refresh || false;
                loadConvertingItems(refresh);
				loadRevenueItems(refresh);
				loadSellingItems(refresh);
				loadMetrics(refresh);
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
