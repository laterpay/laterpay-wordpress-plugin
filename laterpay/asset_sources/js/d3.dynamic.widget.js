var margin = {
    top     : 45,
    right   : 40,
    bottom  : 20,
    left    : 50,
};
margin.xAxis = margin.left + margin.right;
margin.yAxis = margin.top + margin.bottom;

var LPCurve = function(container) {
    var self = this,
        svg;

    this.container          = container;
    this.interpolation      = 'linear';
    this.minPrice           = 0;
    this.maxPrice           = 5;
    this.todayPrice         = 0;
    this.pubDays            = 0;
    this.defaultPrice       = 0.99;
    this.i18nDefaultPrice   = lpVars.i18nDefaultPrice;
    this.currency           = lpVars.currency;
    this.i18nDays           = lpVars.i18nDays;
    this.i18nToday          = lpVars.i18nToday;
    this.dragging           = false;

    svg = d3.select(container).append('svg').append('g');

    svg.append('rect').attr('class', 'lp_dynamicPricing_background');

    svg.append('g').attr('class', 'x axis');

    svg.append('g').attr('class', 'y axis');

    svg.append('defs').append('marker').attr({
            id          : 'arrow-x',
            class       : 'lp_dynamicPricing_arrowhead',
            refX        : 2,
            refY        : 2,
            markerWidth : 4,
            markerHeight: 4,
            orient      : 'auto',
        })
        .append('path')
            .attr('d', 'M0,0 V4 L4,2 Z');

    svg.append('defs').append('marker')
        .attr({
            id          : 'arrow-y',
            class       : 'lp_dynamicPricing_arrowhead',
            refX        : 2,
            refY        : 2,
            markerWidth : 4,
            markerHeight: 4,
            orient      : 'auto',
        })
        .append('path')
            .attr('d', 'M0,4 H4 L2,0 Z');

    svg.append('line').attr('class', 'lp_dynamicPricing_defaultPrice');
    svg
    .append('text')
    .attr('text-anchor', 'middle')
    .attr('class', 'lp_dynamicPricing_defaultPrice')
    .text(this.i18nDefaultPrice);
    svg.append('path').attr('class', 'line');

    svg.append('rect')
        .attr({
            class   : 'start-price',
            width   : 32,
            rx      : 3,
            height  : 29,
            ry      : 3,
        });

    svg
    .insert('foreignObject')
    .attr('class', 'lp_dynamicPricing_startPriceInput')
    .attr('width','44px')
    .attr('height','24px')
    .html('<input type="text">')
    .attr('display','none');
    svg.append('text').attr('class', 'start-price').attr('text-anchor', 'end');
    svg.append('text').attr('class', 'lp_dynamicPricing_currency').attr('text-anchor', 'end').text(this.currency);
    svg.append('path').attr('class', 'start-price-triangle');

    svg.append('rect')
        .attr({
            class   : 'end-price',
            width   : 32,
            rx      : 3,
            height  : 29,
            ry      : 3,
        });

    svg
    .insert('foreignObject')
    .attr('class', 'lp_dynamicPricing_endPriceInput')
    .attr('width','44px')
    .attr('height','24px')
    .html('<input type="text">')
    .attr('display','none');
    svg.append('text').attr('class', 'end-price').attr('text-anchor', 'end');
    svg.append('text').attr('class', 'lp_dynamicPricing_currency').attr('text-anchor', 'end').text(this.currency);
    svg.append('path').attr('class', 'end-price-triangle');

    this.svg = svg;

    jQuery(window).bind('resize', function() { self.plot(); });

    // Events for start price input
    jQuery('body').on('click', '.start-price, .lp_dynamicPricing_currency, .start-price-triangle', function() {
        lpc.toggleStartInput('show');
    });
    jQuery('.lp_dynamicPricing_startPriceInput input').change(function() {
        lpc.toggleStartInput('hide'); // have to leave one event only: update or focusout. Depends on point of view.
    });
    jQuery('.lp_dynamicPricing_startPriceInput input').focusout(function() {
        lpc.toggleStartInput('hide'); // have to leave one event only: update or focusout. Depends on point of view.
    });
    jQuery('.lp_dynamicPricing_startPriceInput input').keydown(function(e) {
        // hide input on Enter
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if (key === 13) {
            e.preventDefault();
            lpc.toggleStartInput('hide');
        }
    });

    // Events for end price input
    jQuery('body').on('click', '.end-price, .lp_dynamicPricing_currency, .end-price-triangle', function() {
        lpc.toggleEndInput('show');
    });
    jQuery('.lp_dynamicPricing_endPriceInput input').change(function() {
        lpc.toggleEndInput('hide'); // have to leave one event only: update or focusout. Depends on point of view.
    });
    jQuery('.lp_dynamicPricing_endPriceInput input').focusout(function() {
        lpc.toggleEndInput('hide'); // have to leave one event only: update or focusout. Depends on point of view.
    });
    jQuery('.lp_dynamicPricing_endPriceInput input').keydown(function(e) {
        // hide input on Enter
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if (key === 13) {
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

LPCurve.prototype.set_today = function(pubDays, todayPrice) {
    this.pubDays    = pubDays;
    this.todayPrice = todayPrice;

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

    d3.select(this.container).select('svg')
        .attr({
            width   : width + margin.xAxis,
            height  : height + margin.yAxis,
        })
        .select('g')
            .attr('transform', 'translate(' + (margin.left - 10) + ',' + margin.top + ')');

    svg.select('.lp_dynamicPricing_background')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            width   : width + 10,
            height  : height,
        });

    // AXES
    // -------------------------------------------------------------------------------------------------------
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

    svg.select('g.x.axis')
        .attr({
            transform   : 'translate(0,' + height + ')',
            'marker-end': 'url(#arrow-x)',
        })
        .transition().duration(dragging ? 0 : 250)
        .call(xAxis);

    svg.select('g.y.axis')
        .attr('marker-start', 'url(#arrow-y)')
        .transition().duration(dragging ? 0 : 250)
        .call(yAxis);

    // Default price
    svg.select('line.lp_dynamicPricing_defaultPrice')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: 0,
            y1: yScale(this.defaultPrice),
            x2: width + 10,
            y2: yScale(this.defaultPrice),
        });

    svg.select('text.lp_dynamicPricing_defaultPrice')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: width / 2,
            y: yScale(self.defaultPrice),
        });

    // PRICE CURVE
    // -------------------------------------------------------------------------------------------------------
    // D3.js provides us with a Path Data Generator Function for lines
    var line = d3.svg.line()
              .interpolate(this.interpolation)
              .x(function(d) { return xScale(d.x); })
              .y(function(d) { return yScale(d.y); });

    // .attr('d', lineFunction(lineData)) is where the magic happens.
    // This is where we send the data to the accessor function which returns the SVG Path Commands.
    svg.select('path.line')
        .datum((self.data))
        .transition().duration(dragging ? 0 : 250)
        .attr('d', line);

    // DRAG BEHAVIORS
    // -------------------------------------------------------------------------------------------------------
    // behaviour to describe dragging of axis X 'days'
    var dragXAxisBehavior = d3.behavior.drag()
        .on('dragstart', dragstartDays)
        .on('drag', dragDays)
        .on('dragend', dragendDays);

    // behaviour to describe dragging of axis Y 'price'
    var dragYAxisBehavior = d3.behavior.drag()
        .on('dragstart', dragstartPoint)
        .on('drag', dragEndPoint)
        .on('dragend', dragendPoint);

    // The D3.js Data Operator returns virtual selections rather than just the regular one like other methods,
    // one per each element in data
    // The virtual selections are enter, update, and exit.
    // -------------------------------------------------------------------------------------------------------
    var end                 = self.data.length,
        point               = svg.selectAll('circle.draggable').data((self.data)),
        priceLine           = svg.selectAll('.lp_priceLine').data((self.data).slice(1, end)),
        todayLine           = svg.selectAll('.today-price-line').data((self.data).slice(1, end)),
        priceLineVisible    = svg.selectAll('.lp_priceLineVisible').data((self.data).slice(1, end));

    // START PRICE
    // -------------------------------------------------------------------------------------------------------
    // SQUARE
    svg.select('rect.start-price')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -40; },
            y: function(d) { return yScale(d.y) - 14.5; },
        });

    // START PRICE TEXT
    svg.select('text.start-price')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -12; },
            y: function(d) { return yScale(d.y) - 0.5; },
        })
        .text(function(d) { return d.y.toFixed(2); });

    // START PRICE TEXT 'CURRENCY'
    svg.select('text.lp_dynamicPricing_currency')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -13; },
            y: function(d) { return yScale(d.y) + 9.5; },
        });

    // START PRICE TRIANGLE
    svg.select('path.start-price-triangle')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = -8;
            y = yScale(d.y) - 5;

            return  'M ' + x + ' ' + y + ' l 5 5 l -5 5 z';
        });

    // START PRICE INPUT
    svg.select('.lp_dynamicPricing_startPriceInput')
        .datum((self.data)[0])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return -38; },
            y: function(d) { return yScale(d.y) - 12.5; },
        });

    // END PRICE
    // -------------------------------------------------------------------------------------------------------
    // SQUARE
    svg.select('rect.end-price')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  {
                    if (
                        jQuery('.lp_dynamicPricing_endPriceInput') &&
                        jQuery('.lp_dynamicPricing_endPriceInput').is(':visible')
                    ) {
                        return width;
                    } else {
                        return width + 16;
                    }
                },
            y: function(d) { return yScale(d.y) - 15; },
        });

    // END PRICE TEXT
    svg.select('text.end-price')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 46; },
            y: function(d) { return yScale(d.y) - 1; },
        })
        .text(function(d) { return d.y.toFixed(2); });

    // END PRICE TEXT 'CURRENCY'
    svg.select('text.lp_dynamicPricing_currency')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 46; },
            y: function(d) { return yScale(d.y) + 9; },
        });

    // END PRICE TRIANGLE
    svg.select('path.end-price-triangle')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = width + 18;
            y = yScale(d.y) + 5;

            return  'M ' + x + ' ' + y + ' l 0 -10 l -5 5 z';
        });

    // END PRICE INPUT
    svg.select('.lp_dynamicPricing_endPriceInput')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  {
                if (
                    jQuery('.lp_dynamicPricing_endPriceInput') &&
                    jQuery('.lp_dynamicPricing_endPriceInput input').is(':visible')
                ) {
                    return width + 2;
                } else {
                    return width + 20;
                }
            },
            y: function(d) { return yScale(d.y) - 13; },
        });

    // Elements on top of the graphic
    // -------------------------------------------------------------------------------------------------------
    // SQUARES
    var xDragSquare = svg.selectAll('.x-drag-square').data((self.data).slice(1, end));

    xDragSquare.enter().append('rect').attr('class', function(point, index) {
        if (index === self.data.length - 2) {
            return 'x-drag-square lp_is-hidden';
        }

        return 'x-drag-square';
    }).call(dragXAxisBehavior);

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

    // BOTTOM TRIANGLE
    var xTriangleBottom = svg.selectAll('.x-triangle-bottom').data((self.data).slice(1, end));

    xTriangleBottom.enter().append('path').attr('class', function(point, index) {
        if (index === self.data.length - 2) {
            return 'x-triangle-bottom lp_is-hidden';
        }

        return 'x-triangle-bottom';
    }).call(dragXAxisBehavior);

    xTriangleBottom.exit().remove();
    xTriangleBottom.transition().duration(dragging ? 0 : 250)
        .attr('d', function(d) {
            x = xScale(d.x) - 5;
            y = -10;

            return  'M ' + x + ' ' + y + ' l 10 0 l -5 5 z';
        });

    // 'DAYS' TEXT
    var xTextDays = svg.selectAll('.x-text-days').data((self.data).slice(1, end));

    xTextDays.enter().append('text').attr('class', function(point, index) {
        if (index === self.data.length - 2) {
            return 'x-text-days lp_is-hidden';
        }

        return 'x-text-days';
    }).call(dragXAxisBehavior);

    xTextDays.exit().remove();
    xTextDays.transition().duration(dragging ? 0 : 250)
        .text(this.i18nDays)
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -16; },
            height          : 30,
            'text-anchor'   : 'middle',
        });

    // Number of days TEXT
    var xText = svg.selectAll('.x-text').data((self.data).slice(1, end));

    xText.enter().append('text').attr('class', function(point, index) {
        if (index === self.data.length - 2) {
            return 'x-text lp_is-hidden';
        }

        return 'x-text';
    }).call(dragXAxisBehavior);

    xText.exit().remove();
    xText.transition().duration(dragging ? 0 : 250)
        .text(function(d) { return Math.round(d.x); })
        .attr({
            x               : function(d) { return xScale(d.x); },
            y               : function()  { return -26; },
            height          : 30,
            'text-anchor'   : 'middle',
        });


    // VERTICAL LINES
    // -------------------------------------------------------------------------------------------------------
    priceLineVisible.enter().append('line').attr('class', function(point, index) {
        // hide the third vertical dashed line - it's only there to work around technical restrictions
        if (index === self.data.length - 2) {
            return 'lp_priceLine lp_is-hidden';
        }

        return 'lp_priceLineVisible';
    });
    priceLineVisible.exit().remove();

    priceLineVisible
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: function(d) { return xScale(d.x); },
            y1: function()  { return 0; },
            x2: function(d) { return xScale(d.x); },
            y2: function(d) { return yScale(d.y); },
        });

    priceLine.enter().append('line').attr('class', function(point, index) {
        if (index === self.data.length - 2) {
            return 'lp_priceLine lp_is-hidden';
        }

        return 'lp_priceLine';
    }).call(dragXAxisBehavior);

    priceLine.exit().remove();
    priceLine
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: function(d) { return xScale(d.x); },
            y1: function()  { return 0; },
            x2: function(d) { return xScale(d.x); },
            y2: function(d) { return yScale(d.y); },
        });

    // POINTS
    // -------------------------------------------------------------------------------------------------------
    // This will return a reference to the placeholder elements (nodes) for each
    // data element that did not have a corresponding existing DOM Element
    // Then we append a circle for each element in data
    point.enter().append('circle')
        .attr('class', function(point,index) {
            if (index === 0 || index === self.data.length - 1) {
                return 'draggable lp_is-hidden';
            }

            return 'draggable';
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

    // TODAY LINE
    // -------------------------------------------------------------------------------------------------------
    // Render vertical line indicating the current position on the defined price curve and the resulting
    // effective price.
    // Only shown, if the post was already published.
    if (this.pubDays > 0) {
        // LINE
        todayLine.enter().append('line').attr('class', 'lp_current-price-line');
        todayLine.exit().remove();
        todayLine
          .transition().duration()
          .attr({
            x1: function() { return xScale(lpc.pubDays); },
            y1: function() { return yScale(0); },
            x2: function() { return xScale(lpc.pubDays); },
            y2: function() { return yScale(lpc.maxPrice); },
        });
        // LABEL
        svg.append('text')
            .attr('class', 'lp_dynamicPricing_defaultPrice')
            .attr('text-anchor', 'end')
            .text(this.i18nToday)
            .datum({
                x: lpc.pubDays,
                y: lpc.todayPrice
            })
            .call(dragYAxisBehavior)
            .attr({
                x: function() { return xScale(parseInt(lpc.pubDays, 10) + 2); },
                y: function() { return yScale(-10); },
            });
    }

    // DRAG POINTS Y AXIS 'price' FUNCTIONS
    // -------------------------------------------------------------------------------------------------------
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
            // since the squares have only one element of the data array, i is always 0
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
        jQuery(self.container).toggleClass('lp_is-dragging');
    }
    function dragendPoint() {
        self.dragging = false;
        jQuery(self.container).toggleClass('lp_is-dragging');
        lpc.toggleStartInput('update');
        lpc.toggleEndInput('update');
    }


    // DRAG AXIS X 'days' FUNCTIONS
    // -------------------------------------------------------------------------------------------------------
    var fps = 60,
        dragInterval;

    function dragendDays() {
        clearInterval(dragInterval);
        jQuery(self.container).toggleClass('ew-resize');
        self.dragging = false;

        for (var i = 0, l = self.data.length; i < l; i++) {
            self.data[i].x = Math.round((self.data)[i].x);
        }

        self.plot();
    }

    function dragstartDays() {
        jQuery(self.container).toggleClass('ew-resize');
        self.dragging = true;
    }

    function dragDays(d, i) {
        var targetDate          = xScale.invert(d3.event.x),
            isDraggingLastPoint = (i === self.data.length - 2),
            isDragHandler = (i === self.data.length - 3),
            cappedTargetDate;


        if (isDraggingLastPoint) {
            var dragDelta = (targetDate - d.x ) / (1000/fps),
                dragStep = function() {
                    cappedTargetDate = +d.x + dragDelta;
                    cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
                    cappedTargetDate = Math.max(cappedTargetDate, 29.51); // minimum 30 days
                    cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum 60 days
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
            cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum 60 days

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
        inputPrice = jQuery('.lp_dynamicPricing_startPriceInput input').val();

    // convert price to proper float value
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    if (action === 'hide') {
        if (inputPrice > this.maxPrice) {
            jQuery('.lp_dynamicPricing_startPriceInput input').val(this.maxPrice);
            data[0].y = this.maxPrice;
            data[1].y = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            jQuery('.lp_dynamicPricing_startPriceInput input').val(this.minPrice);
            data[0].y = this.minPrice;
            data[1].y = this.minPrice;
        } else {
            if (inputPrice === 0) {
                data[0].y = inputPrice;
                data[1].y = inputPrice;
            }
        }
        lpc.set_data(data);
        jQuery('rect.start-price').attr('width', '32px');
        jQuery('.lp_dynamicPricing_startPriceInput').hide();
        jQuery('path.start-price-triangle, text.lp_dynamicPricing_currency, text.start-price').show();
        lpc.plot();
    } else if (action === 'show') {
        jQuery('rect.start-price').attr('width', '50px');
        jQuery('path.start-price-triangle, text.lp_dynamicPricing_currency, text.start-price').hide();
        jQuery('.lp_dynamicPricing_startPriceInput').show();
        jQuery('.lp_dynamicPricing_startPriceInput input').val( plotPrice.toFixed(2) );
    } else if (action === 'update') {
        if (jQuery('.lp_dynamicPricing_startPriceInput input').is(':visible')) {
            var diff = Math.abs(plotPrice - inputPrice);
            if (diff > dragShake) {
                jQuery('.lp_dynamicPricing_startPriceInput input').val(plotPrice.toFixed(2));
            }
        }
    }
};

LPCurve.prototype.toggleEndInput = function(action) {
    var dragShake   = 1,
        data        = lpc.get_data(),
        plotPrice   = data[2].y,
        inputPrice  = jQuery('.lp_dynamicPricing_endPriceInput input').val(),
        basicX      = jQuery(this.container).width() - margin.xAxis;

    // convert price to proper float value
    if (inputPrice.indexOf(',') > -1) {
        inputPrice = parseFloat(inputPrice.replace(',', '.'));
    } else {
        inputPrice = parseFloat(inputPrice);
    }

    if ( action === 'hide' ) {
        if (inputPrice > this.maxPrice) {
            jQuery('.lp_dynamicPricing_endPriceInput input').val(this.maxPrice);
            data[0].y = this.maxPrice;
            data[1].y = this.maxPrice;
        } else if (inputPrice < this.minPrice && inputPrice !== 0) {
            jQuery('.lp_dynamicPricing_endPriceInput input').val( this.minPrice );
            data[2].y = this.minPrice;
            data[3].y = this.minPrice;
        } else {
            if( inputPrice === 0 ){
                data[2].y = inputPrice;
                data[3].y = inputPrice;
            }
        }
        lpc.set_data(data);
        jQuery('rect.end-price').attr('width', '32px');
        jQuery('.lp_dynamicPricing_endPriceInput').hide();
        jQuery('path.end-price-triangle, text.lp_dynamicPricing_currency, text.end-price').show();
        lpc.plot();
    } else if (action === 'show') {
        jQuery('rect.end-price').attr('width', '50px').attr('x', basicX);
        jQuery('path.end-price-triangle, text.lp_dynamicPricing_currency, text.end-price').hide();
        jQuery('.lp_dynamicPricing_endPriceInput').attr('x', basicX + 2).show();
        jQuery('.lp_dynamicPricing_endPriceInput input').val(plotPrice.toFixed(2));
    } else if (action === 'update') {
        if (jQuery('.lp_dynamicPricing_endPriceInput input').is(':visible')) {
            var diff = Math.abs(plotPrice - inputPrice);
            if (diff > dragShake) {
                jQuery('.lp_dynamicPricing_endPriceInput input').val(plotPrice.toFixed(2));
            }
        }
    }
};
