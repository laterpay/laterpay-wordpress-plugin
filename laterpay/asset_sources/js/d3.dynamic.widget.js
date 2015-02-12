var margin = {
    top     : 45,
    right   : 40,
    bottom  : 20,
    left    : 50,
};
margin.xAxis = margin.left + margin.right;
margin.yAxis = margin.top + margin.bottom;

// TODO: add variable dragDuration to standardize .transition().duration(dragging ? 0 : 250)

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
    this.i18nDefaultPrice   = lpVars.i18nDefaultPrice;
    this.currency           = lpVars.currency;
    this.i18nDays           = lpVars.i18nDays;
    this.i18nToday          = lpVars.i18nToday;
    this.dragging           = false;

    svg = d3.select(container)
            .append('svg')
                .attr('class', 'lp_dynamic-pricing__svg')
            .append('g')
                .attr('class', 'lp_dynamic-pricing__svg-group');

    // draw background
    svg.append('rect')
        .attr('class', 'lp_dynamic-pricing__graph-background');

    // set up x-axis
    svg.append('g')
        .attr('class', 'lp_dynamic-pricing__axis lp_dynamic-pricing__axis--x');

    // set up y-axis
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
    svg.append('text')
        .attr('transform', 'translate(0, 2.5)')
        .attr('class', 'lp_dynamic-pricing__default-price-label')
        .attr('text-anchor', 'middle')
        .text(this.i18nDefaultPrice);


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
        .attr('class', 'lp_dynamic-pricing__start-price-input-wrapper')
        .attr('width', '44px')
        .attr('height', '24px')
        .html('<input type="text">')
            .attr('class', 'lp_dynamic-pricing__start-price-input')
            .attr('display', 'none');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__start-price-value lp_dynamic-pricing__handle-text')
        .attr('text-anchor', 'end');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__start-price-currency lp_dynamic-pricing__handle-text lp_dynamic-pricing__handle-unit')
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
            width   : '44px',
            height  : '24px',
        })
        .html('<input type="text">')
            .attr('class', 'lp_dynamic-pricing__end-price-input')
            .attr('display', 'none');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__end-price-value lp_dynamic-pricing__handle-text')
        .attr('text-anchor', 'end');
    svg.append('text')
        .attr('class', 'lp_dynamic-pricing__end-price-currency lp_dynamic-pricing__handle-text lp_dynamic-pricing__handle-unit')
        .attr('text-anchor', 'end')
        .text(this.currency);
    svg.append('path')
        .attr('class', 'lp_dynamic-pricing__end-price-handle-triangle');


    this.svg = svg;


    // redraw on resize
    jQuery(window).bind('resize', function() { self.plot(); });

    // start price handle / input events
    jQuery('body')
    .on('click', '.lp_dynamic-pricing__start-price-handle, .lp_dynamic-pricing__start-price-currency, .lp_dynamic-pricing__start-price-triangle', function() {
        lpc.toggleStartInput('show');
    });
// FIXME: why should the above thing require .on whereas the events below don't???
    jQuery('.lp_dynamic-pricing__start-price-input')
    .focusout(function() {
        lpc.toggleStartInput('hide');
    })
    .keydown(function(e) {
        // hide input on Enter or Esc
        if (e.keyCode === 13 || e.keyCode === 27) {
            e.preventDefault();
            lpc.toggleStartInput('hide');
        }
    });

    // end price handle / input events
    jQuery('body')
    .on('click', '.lp_dynamic-pricing__end-price-handle, .lp_dynamic-pricing__end-price-currency, .lp_dynamic-pricing__end-price-triangle', function() {
        lpc.toggleEndInput('show');
    })
// FIXME: why should the above thing require .on whereas the events below don't???
    .focusout(function() {
        lpc.toggleEndInput('hide');
    })
    .keydown(function(e) {
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

LPCurve.prototype.set_today = function(pubDays, currentPrice) {
    this.pubDays        = pubDays;
    this.currentPrice   = currentPrice;

    return this;
};

LPCurve.prototype.get_data = function() {
    return this.data;
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
    d3.select(this.container).select('.lp_dynamic-pricing__svg')
        .attr({
            width   : width + margin.xAxis,
            height  : height + margin.yAxis,
        })
        .select('.lp_dynamic-pricing__svg-group')
            .attr('transform', 'translate(' + (margin.left - 10) + ',' + margin.top + ')');

    // position graph background
    svg.select('.lp_dynamic-pricing__graph-background')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            width   : width + 10,
            height  : height,
        });


    // AXES ------------------------------------------------------------------------------------------------------------
    var xExtent = d3.extent(self.data, function(d) { return d.x; }),
        yExtent = [0.00, this.maxPrice],
        xAxis   = d3.svg.axis()
                  .scale(xScale)
                  .tickSize(-height, 0, 0)
                  .ticks(7)
                  .orient('bottom'),
        yAxis   = d3.svg.axis()
                  .scale(yScale)
                  .tickSize(-height, 0, 0)
                  .ticks(7)
                  .orient('left');
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


    // default price marker
    svg.select('.lp_dynamic-pricing__default-price-marker')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: 0,
            y1: yScale(this.defaultPrice),
            x2: width + 10,
            y2: yScale(this.defaultPrice),
        });
    svg.select('.lp_dynamic-pricing__default-price-label')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: width / 2,
            y: yScale(self.defaultPrice),
        });


    // PRICE CURVE -----------------------------------------------------------------------------------------------------
    // D3.js provides us with a Path Data Generator Function for lines
    var line = d3.svg.line()
              .interpolate(this.interpolation)
              .x(function(d) { return xScale(d.x); })
              .y(function(d) { return yScale(d.y); });

    // .attr('d', lineFunction(lineData)) is where the magic happens.
    // This is where we send the data to the accessor function which returns the SVG Path Commands.
    svg.select('path.line') // TODO: we need a class here!
        .datum((self.data))
        .transition().duration(dragging ? 0 : 250)
        .attr('d', line);
        // .attr('class', 'lp_dynamic-pricing__price-curve');


    // DRAG BEHAVIOR ---------------------------------------------------------------------------------------------------
    // behavior to describe dragging of axis X 'days'
    var dragXAxisBehavior = d3.behavior.drag()
        .on('dragstart', dragstartDays)
        .on('drag', dragDays)
        .on('dragend', dragendDays);

    // behavior to describe dragging of axis Y 'price'
    var dragYAxisBehavior = d3.behavior.drag()
        .on('dragstart', dragstartPoint)
        .on('drag', dragEndPoint)
        .on('dragend', dragendPoint);

    // The D3.js Data Operator returns virtual selections rather than just the regular one like other methods,
    // one per each element in data
    // The virtual selections are enter, update, and exit.
    var end                 = self.data.length,
        point               = svg.selectAll('.lp_dynamic-pricing__price-curve-point.lp_is-draggable').data((self.data)),
        xMarker             = svg.selectAll('.lp_dynamic-pricing__price-curve').data((self.data).slice(1, end)),
        transparentXMarker  = svg.selectAll('.lp_dynamic-pricing__price-curveXXX').data((self.data).slice(1, end)),
        currentPrice        = svg.selectAll('.lp_dynamic-pricing__current-price-marker').data((self.data).slice(1, end));


    // START PRICE -----------------------------------------------------------------------------------------------------
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
        .text(function(d) { return d.y.toFixed(2); });

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
            x: function()  { return -36; },
            y: function(d) { return yScale(d.y) - 12.5; },
        });


    // END PRICE -------------------------------------------------------------------------------------------------------
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
        .text(function(d) { return d.y.toFixed(2); });
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
            x: function()  {
                if (
                    jQuery('.lp_dynamic-pricing__end-price-input-wrapper') &&
                    jQuery('.lp_dynamic-pricing__end-price-input').is(':visible')
                ) {
                    return width + 2;
                } else {
                    return width + 20;
                }
            },
            y: function(d) { return yScale(d.y) - 13; },
        });


    // PRICE CHANGE INTERVAL BOUNDARIES --------------------------------------------------------------------------------
// TODO: rename all this stuff here
    var xDragSquare = svg.selectAll('.lp_dynamic-pricing__price-change-days-handle').data((self.data).slice(1, end));
    xDragSquare.enter().append('rect')
        .attr('class', function(point, index) {
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__price-change-days-handle lp_is-hidden';
            }

            return 'lp_dynamic-pricing__price-change-days-handle';
        })
        .call(dragXAxisBehavior);
    xDragSquare.exit().remove();
    xDragSquare.transition().duration(dragging ? 0 : 250)
        .attr({
            x       : function(d) { return xScale(d.x) - 15; },
            y       : function()  { return -40; },
            width   : 30,
            rx      : 3,
            height  : 30,
            ry      : 3,
        });

    var xTriangleBottom = svg.selectAll('.lp_dynamic-pricing__price-change-days-handle-triangle').data((self.data).slice(1, end));
    xTriangleBottom.enter().append('path')
        .attr('class', function(point, index) {
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__price-change-days-handle-triangle lp_is-hidden';
            }

            return 'lp_dynamic-pricing__price-change-days-handle-triangle';
        })
        .call(dragXAxisBehavior);
    xTriangleBottom.exit().remove();
    xTriangleBottom.transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = xScale(d.x) - 5;
            y = -10;

            return  'M ' + x + ' ' + y + ' l 10 0 l -5 5 z';
        });

    var xTextDays = svg.selectAll('.lp_dynamic-pricing__price-change-days-unit').data((self.data).slice(1, end));
    xTextDays.enter().append('text')
        .attr('class', function(point, index) {
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__price-change-days-unit lp_dynamic-pricing__handle-text lp_dynamic-pricing__handle-unit lp_is-hidden';
            }

            return 'lp_dynamic-pricing__price-change-days-unit lp_dynamic-pricing__handle-text lp_dynamic-pricing__handle-unit';
        })
        .call(dragXAxisBehavior);
    xTextDays.exit().remove();
    xTextDays.transition().duration(dragging ? 0 : 250)
        .text(this.i18nDays)
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -16; },
            height          : 30,
            'text-anchor'   : 'middle',
        });

    var xText = svg.selectAll('.lp_dynamic-pricing__price-change-days-value').data((self.data).slice(1, end));
    xText.enter().append('text')
        .attr('class', function(point, index) {
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__price-change-days-value lp_dynamic-pricing__handle-text lp_is-hidden';
            }

            return 'lp_dynamic-pricing__price-change-days-value lp_dynamic-pricing__handle-text';
        })
        .call(dragXAxisBehavior);
    xText.exit().remove();
    xText.transition().duration(dragging ? 0 : 250)
        .text(function(d) { return Math.round(d.x); })
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -26; },
            height          : 30,
            'text-anchor'   : 'middle',
        });


    // X-AXIS MARKERS --------------------------------------------------------------------------------------------------
    xMarker.enter().append('line')
        .attr('class', function(point, index) {
            // hide the third vertical dashed line - it's only there to work around technical restrictions
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__x-axis-marker lp_is-hidden';
            }

            return 'lp_dynamic-pricing__x-axis-marker';
        });
    xMarker.exit().remove();

    xMarker
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: function(d) { return xScale(d.x); },
            y1: function()  { return 0; },
            x2: function(d) { return xScale(d.x); },
            y2: function(d) { return yScale(d.y); },
        });

    transparentXMarker.enter().append('line')
        .attr('class', function(point, index) {
            if (index === self.data.length - 2) {
                return 'lp_dynamic-pricing__x-axis-marker lp_is-hidden';
            }

            return 'lp_dynamic-pricing__x-axis-marker';
        })
        .call(dragXAxisBehavior);
    transparentXMarker.exit().remove();
    transparentXMarker
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: function(d) { return xScale(d.x); },
            y1: function()  { return 0; },
            x2: function(d) { return xScale(d.x); },
            y2: function(d) { return yScale(d.y); },
        });


    // PRICE CURVE POINTS ----------------------------------------------------------------------------------------------
    // This will return a reference to the placeholder elements (nodes) for each
    // data element that did not have a corresponding existing DOM Element
    // Then we append a circle for each element in data
    point.enter().append('circle')
        .attr('class', function(point,index) {
            // hide the first and the last point on the price curve
            if (index === 0 || index === self.data.length - 1) {
                return 'lp_dynamic-pricing__price-curve-point lp_is-draggable lp_is-hidden';
            }

            return 'lp_dynamic-pricing__price-curve-point lp_is-draggable';
        })
        .attr('r', 0)
        .call(dragYAxisBehavior);

    point.transition().duration(dragging ? 0 : 250)
        .attr({
            r   : 5,
            cx  : function(d) { return xScale(d.x); },
            cy  : function(d) { return yScale(d.y); },
        });

    point.exit().remove();


    // CURRENT PRICE MARKER --------------------------------------------------------------------------------------------
    // Render vertical line indicating the current position on the defined price curve and the resulting
    // effective price.
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


    // DRAG POINTS Y AXIS 'price' FUNCTIONS ----------------------------------------------------------------------------
    function dragEndPoint(d, i) {
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
        }
        // we have to keep the starting price in sync with the last / last but one point
        else if (i === 0 && self.data[self.data.length-1].x === d.x) {
            self.data[self.data.length - 2].y = d.y;
        } else if (i === self.data.length - 2) {
            self.data[self.data.length - 1].y = d.y;
        }

        self.plot();
    }

    function dragstartPoint() {
        self.dragging = true;
        jQuery(self.container).addClass('lp_is-dragging');
    }

    function dragendPoint() {
        self.dragging = false;
        jQuery(self.container).removeClass('lp_is-dragging');
        lpc.toggleStartInput('update');
        lpc.toggleEndInput('update');
    }


    // DRAG AXIS X 'days' FUNCTIONS ------------------------------------------------------------------------------------
    var fps = 60,
        dragInterval;

    function dragstartDays() {
        jQuery(self.container).addClass('lp_is-dragging-horizontally');
        self.dragging = true;
    }

    function dragendDays() {
        clearInterval(dragInterval);
        jQuery(self.container).removeClass('lp_is-dragging-horizontally');
        self.dragging = false;

        var i = 0,
            l = self.data.length;
        for (; i < l; i++) {
            self.data[i].x = Math.round((self.data)[i].x);
        }

        self.plot();
    }

    function dragDays(d, i) {
        var targetDate          = xScale.invert(d3.event.x),
            isDraggingLastPoint = (i === self.data.length - 2),
            isDragHandler       = (i === self.data.length - 3),
            cappedTargetDate;

        if (isDraggingLastPoint) {
            var dragDelta = (targetDate - d.x ) / (1000/fps), // 30 fps
                dragStep = function() {
                    cappedTargetDate = +d.x + dragDelta;
                    cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
                    cappedTargetDate = Math.max(cappedTargetDate, 29.51); // minimum: 30 days
                    cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum: 60 days
                    d.x = cappedTargetDate;
                    // restore the xScale value, as it could have changed
                    xScale.domain(d3.extent(self.data, function(d) { return d.x; }));
                    self.plot();
                };
            clearInterval(dragInterval);
            dragInterval = setInterval(dragStep, 1000/fps); // 30 fps
            dragStep();
        } else if (isDragHandler) {
            cappedTargetDate = targetDate;
            cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
            cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum: 60 days

            if (cappedTargetDate >= 25) {
                self.data[i+2].x = cappedTargetDate + 5;
            } else {
                self.data[i+2].x = 30;
            }

            d.x = cappedTargetDate;
            // restore the xScale value, as it could have changed
            xScale.domain(d3.extent(self.data, function(d) { return d.x; }));
            self.plot();
        } else {
            cappedTargetDate = targetDate;
            cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
            cappedTargetDate = Math.min(cappedTargetDate, self.data[i+2].x - 0.51);
            d.x = cappedTargetDate;
            // restore the xScale value, as it could have changed
            xScale.domain(d3.extent(self.data, function(d) { return d.x; }));
            self.plot();
        }
    }
};

LPCurve.prototype.toggleStartInput = function(action) {
    var dragShake   = 1,
        data        = lpc.get_data(),
        plotPrice   = data[0].y,
        inputPrice  = jQuery('.lp_dynamic-pricing__start-price-input').val();

    // convert price to proper float value
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    if (action === 'hide') {
        if (inputPrice > this.maxPrice) {
            jQuery('.lp_dynamic-pricing__start-price-input').val(this.maxPrice);
            data[0].y = this.maxPrice;
            data[1].y = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            jQuery('.lp_dynamic-pricing__start-price-input').val(this.minPrice);
            data[0].y = this.minPrice;
            data[1].y = this.minPrice;
        } else {
            if (inputPrice === 0) {
                data[0].y = inputPrice;
                data[1].y = inputPrice;
            }
        }
        lpc.set_data(data);
        jQuery('.lp_dynamic-pricing__start-price-handle').attr('width', '32px');
        jQuery('.lp_dynamic-pricing__start-price-input-wrapper').hide();
        jQuery('.lp_dynamic-pricing__start-price-handle-triangle, .lp_dynamic-pricing__start-price-currency, .lp_dynamic-pricing__start-price-value').show();
        lpc.plot();
    } else if (action === 'show') {
        jQuery('.lp_dynamic-pricing__start-price-handle').attr('width', '50px');
        jQuery('.lp_dynamic-pricing__start-price-handle-triangle, .lp_dynamic-pricing__start-price-currency, .lp_dynamic-pricing__start-price-value').hide();
        jQuery('.lp_dynamic-pricing__start-price-input-wrapper').show();
        jQuery('.lp_dynamic-pricing__start-price-input').val(plotPrice.toFixed(2));
    } else if (action === 'update') {
        if (jQuery('.lp_dynamic-pricing__start-price-input').is(':visible')) {
            var diff = Math.abs(plotPrice - inputPrice);
            if (diff > dragShake) {
                jQuery('.lp_dynamic-pricing__start-price-input').val(plotPrice.toFixed(2));
            }
        }
    }
};

LPCurve.prototype.toggleEndInput = function(action) {
    var dragShake   = 1,
        data        = lpc.get_data(),
        plotPrice   = data[2].y,
        inputPrice  = jQuery('.lp_dynamic-pricing__end-price-input').val(),
        basicX      = jQuery(this.container).width() - margin.xAxis;

    // convert price to proper float value
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    if ( action === 'hide' ) {
        if (inputPrice > this.maxPrice) {
            jQuery('.lp_dynamic-pricing__end-price-input').val(this.maxPrice);
            data[0].y = this.maxPrice;
            data[1].y = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            jQuery('.lp_dynamic-pricing__end-price-input').val(this.minPrice);
            data[2].y = this.minPrice;
            data[3].y = this.minPrice;
        } else {
            if ( inputPrice === 0 ){
                data[2].y = inputPrice;
                data[3].y = inputPrice;
            }
        }
        lpc.set_data(data);
        jQuery('.lp_dynamic-pricing__end-price-handle').attr('width', '32px');
        jQuery('.lp_dynamic-pricing__end-price-input-wrapper').hide();
        jQuery('.lp_dynamic-pricing__end-price-handle-triangle, .lp_dynamic-pricing__end-price-currency, .lp_dynamic-pricing__end-price-value').show();
        lpc.plot();
    } else if (action === 'show') {
        jQuery('.lp_dynamic-pricing__end-price-handle').attr('width', '50px').attr('x', basicX);
        jQuery('.lp_dynamic-pricing__end-price-handle-triangle, .lp_dynamic-pricing__end-price-currency, .lp_dynamic-pricing__end-price-value').hide();
        jQuery('.lp_dynamic-pricing__end-price-input-wrapper').attr('x', basicX + 2).show();
        jQuery('.lp_dynamic-pricing__end-price-input').val(plotPrice.toFixed(2));
    } else if (action === 'update') {
        if (jQuery('.lp_dynamic-pricing__end-price-input').is(':visible')) {
            var diff = Math.abs(plotPrice - inputPrice);
            if (diff > dragShake) {
                jQuery('.lp_dynamic-pricing__end-price-input').val(plotPrice.toFixed(2));
            }
        }
    }
};
