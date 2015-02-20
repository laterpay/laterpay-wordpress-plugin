var margin = {
    top     : 37,
    right   : 40,
    bottom  : 15,
    left    : 50,
};
margin.xAxis = margin.left + margin.right;
margin.yAxis = margin.top + margin.bottom;

var LPCurve = function(container) {
    var self = this,
        svg;

    // default settings
    this.container          = container;
    this.interpolation      = 'linear';
    this.minPrice           = 0;
    this.maxPrice           = 5;
    this.defaultPrice       = 0.49;
    this.currentPrice       = 0;
    this.pubDays            = 0;
    this.currency           = lpVars.currency;
    this.i18nDefaultPrice   = lpVars.i18nDefaultPrice;
    this.i18nDays           = lpVars.i18nDays;
    this.i18nToday          = lpVars.i18nToday;
    this.dragging           = false;


    // set up D3 graph in container
    svg = d3.select(container)
            .append('svg')
                .attr('class', 'lp_dynamic-pricing__svg')
            .append('g')
                .attr('class', 'lp_dynamic-pricing__svg-group');


    // add graph background
    svg.append('rect')
        .attr('class', 'lp_dynamic-pricing__graph-background');


    // add x-axis
    svg.append('g')
        .attr('class', 'lp_dynamic-pricing__axis lp_dynamic-pricing__axis--x');


    // add y-axis
    svg.append('g')
        .attr('class', 'lp_dynamic-pricing__axis lp_dynamic-pricing__axis--y');


    // draw x-axis with arrowhead
    svg.append('defs')
        .append('marker')
            .attr({
                id          : 'lp_dynamic-pricing__axis-arrowhead--x',
                class       : 'lp_dynamic-pricing__axis-arrowhead',
                refX        : 2,
                refY        : 2,
                markerWidth : 4,
                markerHeight: 4,
                orient      : 'auto',
            })
            .append('path')
                .attr('d', 'M0,0 V4 L4,2 Z');


    // draw y-axis with arrowhead
    svg.append('defs')
        .append('marker')
            .attr({
                id          : 'lp_dynamic-pricing__axis-arrowhead--y',
                class       : 'lp_dynamic-pricing__axis-arrowhead',
                refX        : 2,
                refY        : 2,
                markerWidth : 4,
                markerHeight: 4,
                orient      : 'auto',
            })
            .append('path')
                .attr('d', 'M0,4 H4 L2,0 Z');


    // draw default price marker
    svg.append('line')
        .attr('class', 'lp_dynamic-pricing__default-price-marker');
    svg.append('rect')
        .attr({
            class   : 'lp_dynamic-pricing__default-price-label-background',
            width   : 66,
            height  : 16,
        });
    svg.append('text')
        .attr('transform', 'translate(0, 2.5)')
        .attr('class', 'lp_dynamic-pricing__default-price-label')
        .attr('text-anchor', 'middle')
        .text(this.i18nDefaultPrice);


    // draw price curve
    svg.append('path').attr('class', 'lp_dynamic-pricing__price-curve');


    // draw start price handle with text and input and everything
    svg.append('rect')
        .attr({
            class   : 'lp_dynamic-pricing__start-price-handle',
            width   : 32,
            rx      : 3,
            height  : 29,
            ry      : 3,
        });
    svg.insert('foreignObject')
        .attr({
            class   : 'lp_dynamic-pricing__start-price-input-wrapper',
            // foreign objects do not render without a width and height, so we have to provide those
            width   : '40px',
            height  : '30px',
        })
        .html('<input type="text" class="lp_dynamic-pricing__start-price-input">');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__start-price-value lp_dynamic-pricing__handle-text')
        .attr('text-anchor', 'end');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__start-price-currency ' +
                        'lp_dynamic-pricing__handle-text ' +
                        'lp_dynamic-pricing__handle-unit')
        .attr('text-anchor', 'end')
        .text(this.currency);
    svg.append('path')
        .attr('class', 'lp_dynamic-pricing__start-price-handle-triangle');


    // draw end price handle with text and input and everything
    svg.append('rect')
            .attr({
                class   : 'lp_dynamic-pricing__end-price-handle',
                width   : 32,
                rx      : 3,
                height  : 29,
                ry      : 3,
            });
    svg.insert('foreignObject')
        .attr({
            class   : 'lp_dynamic-pricing__end-price-input-wrapper',
            // foreign objects do not render without a width and height, so we have to provide those
            width   : '40px',
            height  : '30px',
        })
        .html('<input type="text" class="lp_dynamic-pricing__end-price-input">');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__end-price-value lp_dynamic-pricing__handle-text')
        .attr('text-anchor', 'end');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__end-price-currency ' +
                        'lp_dynamic-pricing__handle-text ' +
                        'lp_dynamic-pricing__handle-unit')
        .attr('text-anchor', 'end')
        .text(this.currency);
    svg.append('path')
        .attr('class', 'lp_dynamic-pricing__end-price-handle-triangle');


    this.svg = svg;


    // redraw on resize
    jQuery(window).bind('resize', function() { self.plot(); });


    // bind events to start price handle
    jQuery('body')
    .on('click',
        '.lp_dynamic-pricing__start-price-handle, ' +
        '.lp_dynamic-pricing__start-price-value, ' +
        '.lp_dynamic-pricing__start-price-currency',
        function() {
            lpc.toggleStartInput('show');
    })
    // bind events to start price input
    .on('focusout',
        '.lp_dynamic-pricing__start-price-input',
        function() {
            lpc.toggleStartInput('hide');
    })
    .on('keydown',
        '.lp_dynamic-pricing__start-price-input',
        function(e) {
            // hide input on Enter or Esc
            if (e.keyCode === 13 || e.keyCode === 27) {
                e.preventDefault();
                lpc.toggleStartInput('hide');
            }
    });


    // bind events to end price handle
    jQuery('body')
    .on('click',
        '.lp_dynamic-pricing__end-price-handle, ' +
        '.lp_dynamic-pricing__end-price-value, ' +
        '.lp_dynamic-pricing__end-price-currency',
        function() {
            lpc.toggleEndInput('show');
    })
    // bind events to end price input
    .on('focusout',
        '.lp_dynamic-pricing__end-price-input',
        function() {
            lpc.toggleEndInput('hide');
    })
    .on('keydown',
        '.lp_dynamic-pricing__end-price-input',
        function(e) {
            // hide input on Enter or Esc
            if (e.keyCode === 13 || e.keyCode === 27) {
                e.preventDefault();
                lpc.toggleEndInput('hide');
            }
    });
};


LPCurve.prototype.interpolate = function(i) {
    this.interpolation = i;

    return this;
};


LPCurve.prototype.setPrice = function(min, max, defaultPrice) {
    this.minPrice = min;
    this.maxPrice = max;
    if (defaultPrice) {
        this.defaultPrice = defaultPrice;
    }

    return this;
};


LPCurve.prototype.set_data = function(data) {
    this.data = data;

    return this;
};


LPCurve.prototype.get_data = function() {
    return this.data;
};


LPCurve.prototype.set_today = function(pubDays, currentPrice) {
    this.pubDays        = pubDays;
    this.currentPrice   = currentPrice;

    return this;
};


LPCurve.prototype.plot = function() {
    var self        = this,
        svg         = this.svg,
        dragging    = this.dragging,
        width       = jQuery(this.container).width() - margin.xAxis,
        height      = jQuery(this.container).height() - margin.yAxis,
        xScale      = d3.scale.linear().range([0, width + 10]),
        yScale      = d3.scale.linear().range([height, 0]),
        x, y;


    // position entire widget
    d3.select('.lp_dynamic-pricing__svg')
        .attr({
            width   : width + margin.xAxis,
            height  : height + margin.yAxis,
        })
        .select('.lp_dynamic-pricing__svg-group')
            .attr('transform', 'translate(' + (margin.left - 11) + ',' + margin.top + ')');


    // position graph background
    svg.select('.lp_dynamic-pricing__graph-background')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            width   : width + 10,
            height  : height,
        });


    // AXES ------------------------------------------------------------------------------------------------------------
    var xExtent = d3.extent(self.data, function(d) { return d.x; }),
        yExtent = [0.00, self.maxPrice],
        xAxis   = d3.svg.axis()
                  .scale(xScale)
                  .tickSize(-height, 0, 0)
                  .ticks(7)
                  .orient('bottom'),
        yAxis   = d3.svg.axis()
                  .scale(yScale)
                  .tickSize(-height, 0, 0)
                  .ticks(7)
                  .orient('left'),
        classes;
    xScale.domain(xExtent);
    yScale.domain(yExtent);


    // x-axis
    svg.select('.lp_dynamic-pricing__axis--x')
        .attr({
            transform   : 'translate(0,' + height + ')',
            'marker-end': 'url(#lp_dynamic-pricing__axis-arrowhead--x)',
        })
        .transition().duration(dragging ? 0 : 250)
        .call(xAxis);


    // y-axis
    svg.select('.lp_dynamic-pricing__axis--y')
        .attr('marker-start', 'url(#lp_dynamic-pricing__axis-arrowhead--y)')
        .transition().duration(dragging ? 0 : 250)
        .call(yAxis);


    // ticks (grid lines of graph)
    d3.selectAll('.tick').select('line')
        .attr('class', 'lp_dynamic-pricing__grid-line');
    d3.selectAll('.tick').select('text')
        .attr('class', 'lp_dynamic-pricing__grid-line-label');


    // position default price marker
    svg.select('.lp_dynamic-pricing__default-price-marker')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: 0,
            y1: yScale(self.defaultPrice),
            x2: width + 10,
            y2: yScale(self.defaultPrice),
        });
    svg.select('.lp_dynamic-pricing__default-price-label-background')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: (width - 66) / 2, // center horizontally
            y: yScale(self.defaultPrice) - 9, // center vertically
        });
    svg.select('.lp_dynamic-pricing__default-price-label')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: width / 2,
            y: yScale(self.defaultPrice),
        });


    // PRICE CURVE -----------------------------------------------------------------------------------------------------
    // D3.js provides us with a path data generator function for lines
    var priceCurve = d3.svg.line()
                      .interpolate(self.interpolation)
                      .x(function(d) { return xScale(d.x); })
                      .y(function(d) { return yScale(d.y); });

    // .attr('d', lineFunction(lineData)) is where the magic happens:
    // this is where we send the data to the accessor function which returns the SVG path commands
    svg.select('.lp_dynamic-pricing__price-curve')
        .datum((self.data))
        .transition().duration(dragging ? 0 : 250)
        .attr('d', priceCurve);


    // DRAG BEHAVIOR ---------------------------------------------------------------------------------------------------
    // dragging behavior of 'days' on x-axis
    var dragXAxisBehavior = d3.behavior.drag()
        .on('dragstart',    dragstartDays)
        .on('drag',         dragDays)
        .on('dragend',      dragendDays);


    // dragging behavior of 'price' on y-axis
    var dragYAxisBehavior = d3.behavior.drag()
        .on('dragstart',    dragstartPrice)
        .on('drag',         dragPrice)
        .on('dragend',      dragendPrice);


    // The D3.js Data Operator returns virtual selections rather than the regular ones returned by other methods,
    // one per each element in data.
    // The virtual selections are enter, update, and exit.
    var end                 = self.data.length,
        point               = svg.selectAll('.lp_dynamic-pricing__price-curve-point').data((self.data)),
        xMarker             = svg.selectAll('.lp_dynamic-pricing__x-axis-marker').data((self.data).slice(1, end)),
        currentPrice        = svg.selectAll('.lp_dynamic-pricing__current-price-marker')
                                .data((self.data)
                                .slice(1, end));


    // START PRICE ('price') -------------------------------------------------------------------------------------------
    var startPrice;
    svg.select('.lp_dynamic-pricing__start-price-handle')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -38; },
            y: function(d) { return yScale(d.y) - 14.5; },
        });
    svg.select('.lp_dynamic-pricing__start-price-value')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -10; },
            y: function(d) { return yScale(d.y) - 0.5; },
        })
        .text(function(d) {
            startPrice = d.y.toFixed(2);

            // localize price for displaying
            if (lpVars.locale === 'de_DE') {
                startPrice = startPrice.replace('.', ',');
            }

            return startPrice;
        });
    svg.select('.lp_dynamic-pricing__start-price-currency')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -11; },
            y: function(d) { return yScale(d.y) + 9.5; },
        });
    svg.select('.lp_dynamic-pricing__start-price-handle-triangle')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = -6;
            y = yScale(d.y) - 5;

            return  'M ' + x + ' ' + y + ' l 5 5 l -5 5 z';
        });
    svg.select('.lp_dynamic-pricing__start-price-input-wrapper')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -38; },
            y: function(d) { return yScale(d.y) - 14; },
        });


    // END PRICE ('price') ---------------------------------------------------------------------------------------------
    var endPrice;
    svg.select('.lp_dynamic-pricing__end-price-handle')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  {
                    if (
                        jQuery('.lp_dynamic-pricing__end-price-input-wrapper') &&
                        jQuery('.lp_dynamic-pricing__end-price-input').is(':visible')
                    ) {
                        return width;
                    } else {
                        return width + 16;
                    }
                },
            y: function(d) { return yScale(d.y) - 15; },
        });
    svg.select('.lp_dynamic-pricing__end-price-value')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 44; },
            y: function(d) { return yScale(d.y) - 1; },
        })
        .text(function(d) {
            endPrice = d.y.toFixed(2);

            // localize price for displaying
            if (lpVars.locale === 'de_DE') {
                endPrice = endPrice.replace('.', ',');
            }

            return endPrice;
        });
    svg.select('.lp_dynamic-pricing__end-price-currency')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 44; },
            y: function(d) { return yScale(d.y) + 9; },
        });
    svg.select('.lp_dynamic-pricing__end-price-handle-triangle')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = width + 16;
            y = yScale(d.y) + 5;

            return  'M ' + x + ' ' + y + ' l 0 -10 l -5 5 z';
        });
    svg.select('.lp_dynamic-pricing__end-price-input-wrapper')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 8; },
            y: function(d) { return yScale(d.y) - 15; },
        });


    // PRICE CHANGE INTERVAL BOUNDARIES ('days') -----------------------------------------------------------------------
    // handles for setting the number of days after publication, after which
    // handle 1: the price starts changing
    // handle 2: the price reaches its final value
    // There is also a third handle for setting the maximum value on the x-axis which exists as a technical workaround
    // and is visually hidden.
    var daysHandle = svg.selectAll('.lp_dynamic-pricing__price-change-days-handle').data((self.data).slice(1, end));

    daysHandle.enter().append('rect')
        .attr('class', function(point, index) {
            classes = 'lp_dynamic-pricing__price-change-days-handle';
            if (index === self.data.length - 2) {
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .call(dragXAxisBehavior);

    daysHandle.exit().remove();

    daysHandle.transition().duration(dragging ? 0 : 250)
        .attr({
            x       : function(d) { return xScale(d.x) - 15; },
            y       : function()  { return -35; },
            width   : 30,
            rx      : 3,
            height  : 30,
            ry      : 3,
        });


    var daysHandleTriangle = svg.selectAll('.lp_dynamic-pricing__price-change-days-handle-triangle')
                                .data((self.data)
                                .slice(1, end));

    daysHandleTriangle.enter().append('path')
        .attr('class', function(point, index) {
            classes = 'lp_dynamic-pricing__price-change-days-handle-triangle';
            if (index === self.data.length - 2) {
                // hide the third x-axis handle - it's only there to work around technical restrictions when
                // automatically rescaling the x-axis
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .call(dragXAxisBehavior);

    daysHandleTriangle.exit().remove();

    daysHandleTriangle.transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = xScale(d.x) - 5;
            y = -5;

            return  'M ' + x + ' ' + y + ' l 10 0 l -5 5 z';
        });


    var daysHandleValue = svg.selectAll('.lp_dynamic-pricing__price-change-days-value').data((self.data).slice(1, end));

    daysHandleValue.enter().append('text')
        .attr('class', function(point, index) {
            classes = 'lp_dynamic-pricing__price-change-days-value lp_dynamic-pricing__handle-text';
            if (index === self.data.length - 2) {
                // hide the third x-axis handle - it's only there to work around technical restrictions when
                // automatically rescaling the x-axis
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .call(dragXAxisBehavior);

    daysHandleValue.exit().remove();

    daysHandleValue.transition().duration(dragging ? 0 : 250)
        .text(function(d) { return Math.round(d.x); })
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -21; },
            height          : 30,
            'text-anchor'   : 'middle',
        });


    var daysHandleUnit = svg.selectAll('.lp_dynamic-pricing__price-change-days-unit').data((self.data).slice(1, end));

    daysHandleUnit.enter().append('text')
        .attr('class', function(point, index) {
            classes =   'lp_dynamic-pricing__price-change-days-unit ' +
                        'lp_dynamic-pricing__handle-text ' +
                        'lp_dynamic-pricing__handle-unit';
            if (index === self.data.length - 2) {
                // hide the third x-axis handle - it's only there to work around technical restrictions when
                // automatically rescaling the x-axis
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .call(dragXAxisBehavior);

    daysHandleUnit.exit().remove();

    daysHandleUnit.transition().duration(dragging ? 0 : 250)
        .text(this.i18nDays)
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -11; },
            height          : 30,
            'text-anchor'   : 'middle',
        });


    // X-AXIS MARKERS --------------------------------------------------------------------------------------------------
    // to make it easier to understand that the 'days' handle on the x-axis affects the point on the price curve,
    // we connect the handle with the point by a (dashed) line
    xMarker.enter().append('line')
        .attr('class', function(point, index) {
            classes = 'lp_dynamic-pricing__x-axis-marker';
            if (index === self.data.length - 2) {
                // hide the third x-axis marker - it's only there to work around technical restrictions when
                // automatically rescaling the x-axis
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .call(dragXAxisBehavior);

    xMarker.exit().remove();

    xMarker
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: function(d) { return xScale(d.x); },
            y1: function()  { return 0; },
            x2: function(d) { return xScale(d.x); },
            y2: function(d) { return yScale(d.y) - 5; }, // subtract radius of price curve point to avoid overlap
        });


    // PRICE CURVE POINTS ----------------------------------------------------------------------------------------------
    // Returns a reference to the placeholder elements (nodes) for each data element that did not have a corresponding
    // existing DOM element and appends a circle for each element in the data.
    point.enter().append('circle')
        .attr('class', function(point, index) {
            classes = 'lp_dynamic-pricing__price-curve-point';
            if (index === 0 || index === self.data.length - 1) {
                // hide the first and the last point on the price curve, mainly for aesthetic reasons
                classes += ' lp_is-hidden';
            }

            return classes;
        })
        .attr('r', 0);

    point.transition().duration(dragging ? 0 : 250)
        .attr({
            r   : 5,
            cx  : function(d) { return xScale(d.x); },
            cy  : function(d) { return yScale(d.y); },
        });

    point.exit().remove();


    // CURRENT PRICE MARKER --------------------------------------------------------------------------------------------
    // Renders a vertical line indicating the current position on the set price curve and the resulting effective price.
    // Only shown, if the post was already published.
    if (this.pubDays > 0) {
        currentPrice.enter().append('line')
            .attr('class', 'lp_dynamic-pricing__current-price-marker');

        currentPrice.exit().remove();

        currentPrice
            .transition().duration()
            .attr({
                x1: function() { return xScale(lpc.pubDays); },
                y1: function() { return yScale(0); },
                x2: function() { return xScale(lpc.pubDays); },
                y2: function() { return yScale(lpc.maxPrice); },
            });

        svg.append('text')
            .attr('class', 'lp_dynamic-pricing__current-price-label')
            .attr('text-anchor', 'end')
            .text(this.i18nToday)
            .datum({
                x: lpc.pubDays,
                y: lpc.currentPrice,
            })
            .call(dragYAxisBehavior)
            .attr({
                x: function() { return xScale(parseInt(lpc.pubDays, 10) + 2); },
                y: function() { return yScale(-10); },
            });
    }


    // DRAG Y AXIS 'price' FUNCTIONS ----------------------------------------------------------------------------
    function dragstartPrice() {
        self.dragging = true;
    }

    function dragPrice(d, i) {
        var p = yScale.invert(d3.event.y);
        if (p < yExtent[0]) {
            p = yExtent[0];
        }
        if (p > yExtent[1]) {
            p = yExtent[1];
        }
        d.y = p;

        // we have to keep the starting price in sync with the first / second point
        if (i === 0 && self.data[0].x === d.x) {
            // the second check is to make sure we are dragging the first point
            // since the handles have only one element of the data array, i is always 0
            self.data[1].y = d.y;
        } else if (i === 1) {
            self.data[0].y = d.y;
        } else if (i === 0 && self.data[self.data.length - 1].x === d.x) {
            // we have to keep the starting price in sync with the last / last but one point
            self.data[self.data.length - 2].y = d.y;
        } else if (i === self.data.length - 2) {
            self.data[self.data.length - 1].y = d.y;
        }

        self.plot();
    }

    function dragendPrice() {
        self.dragging = false;
    }


    // DRAG AXIS X 'days' FUNCTIONS ------------------------------------------------------------------------------------
    var fps = 60,
        dragInterval;

    function dragstartDays() {
        self.dragging = true;
    }

    function dragDays(d, i) {
        var targetDate          = xScale.invert(d3.event.x),
            isDraggingLastPoint = (i === self.data.length - 2),
            isDragHandler       = (i === self.data.length - 3),
            cappedTargetDate;

        if (isDraggingLastPoint) {
            var dragDelta   = (targetDate - d.x) / (1000 / fps), // 30 fps
                dragStep    = function() {
                                cappedTargetDate = +d.x + dragDelta;
                                cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
                                cappedTargetDate = Math.max(cappedTargetDate, 29.51); // minimum: 30 days
                                cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum: 60 days

                                // update the xScale value, as it could have changed
                                d.x = cappedTargetDate;
                                xScale.domain(d3.extent(self.data, function(d) { return d.x; }));

                                self.plot();
                            };

            clearInterval(dragInterval);

            dragInterval = setInterval(dragStep, 1000 / fps); // 30 fps

            dragStep();
        } else if (isDragHandler) {
            cappedTargetDate = targetDate;
            cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
            cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum: 60 days

            if (cappedTargetDate >= 25) {
                self.data[i + 2].x = cappedTargetDate + 5;
            } else {
                self.data[i + 2].x = 30;
            }

            // update the xScale value, as it could have changed
            d.x = cappedTargetDate;
            xScale.domain(d3.extent(self.data, function(d) { return d.x; }));

            self.plot();
        } else {
            cappedTargetDate = targetDate;
            cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
            cappedTargetDate = Math.min(cappedTargetDate, self.data[i + 2].x - 0.51);

            // update the xScale value, as it could have changed
            d.x = cappedTargetDate;
            xScale.domain(d3.extent(self.data, function(d) { return d.x; }));

            self.plot();
        }
    }

    function dragendDays() {
        clearInterval(dragInterval);

        self.dragging = false;

        var i = 0,
            l = self.data.length;
        for (; i < l; i++) {
            self.data[i].x = Math.round((self.data)[i].x);
        }

        self.plot();
    }
};


LPCurve.prototype.toggleStartInput = function(action) {
    var data        = lpc.get_data(),
        plotPrice   = data[0].y.toFixed(2),
        $handle     = jQuery(
                        '.lp_dynamic-pricing__start-price-handle, ' +
                        '.lp_dynamic-pricing__start-price-handle-triangle, ' +
                        '.lp_dynamic-pricing__start-price-value, ' +
                        '.lp_dynamic-pricing__start-price-currency'
                        ),
        $priceInput = jQuery('.lp_dynamic-pricing__start-price-input'),
        inputPrice  = $priceInput.val();

    // de-localize price for processing (convert to proper float value)
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    // localize price for displaying
    if (lpVars.locale === 'de_DE') {
        plotPrice = plotPrice.replace('.', ',');
    }

    if (action === 'show') {
        $handle.hide();

        $priceInput
        .val(plotPrice)
        .show()
        .focus();
    } else if (action === 'hide') {
        // cap prices that are outside of the valid range
        if (inputPrice > this.maxPrice) {
            inputPrice = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            inputPrice = this.minPrice;
        }

        data[0].y = inputPrice;
        data[1].y = inputPrice;

        $priceInput.hide();
        $handle.show();

        // update graph
        lpc.set_data(data);
        lpc.plot();
    }
};


LPCurve.prototype.toggleEndInput = function(action) {
    var data        = lpc.get_data(),
        plotPrice   = data[2].y.toFixed(2),
        $handle     = jQuery(
                        '.lp_dynamic-pricing__end-price-handle, ' +
                        '.lp_dynamic-pricing__end-price-handle-triangle, ' +
                        '.lp_dynamic-pricing__end-price-value, ' +
                        '.lp_dynamic-pricing__end-price-currency'
                        ),
        $priceInput = jQuery('.lp_dynamic-pricing__end-price-input'),
        inputPrice  = $priceInput.val();

    // de-localize price for processing (convert to proper float value)
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    // localize price for displaying
    if (lpVars.locale === 'de_DE') {
        plotPrice = plotPrice.replace('.', ',');
    }

    if (action === 'show') {
        $handle.hide();

        $priceInput
        .val(plotPrice)
        .show()
        .focus();
    } else if (action === 'hide') {
        // cap prices that are outside of the valid range
        if (inputPrice > this.maxPrice) {
            inputPrice = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            inputPrice = this.minPrice;
        }

        data[2].y = inputPrice;
        data[3].y = inputPrice;

        $priceInput.hide();
        $handle.show();

        // update graph
        lpc.set_data(data);
        lpc.plot();
    }
};
