var margin = {
    top     : 45,
    right   : 40,
    bottom  : 20,
    left    : 50
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
    this.defaultPrice       = 0.99;
    this.i18nDefaultPrice   = lpVars.i18nDefaultPrice;
    this.currency           = lpVars.currency;
    this.i18nDays           = lpVars.i18nDays;
    this.dragging           = false;

    svg = d3.select(container).append('svg').append('g');

    svg.append('rect')
        .attr('class', 'background');

    svg.append('g')
        .attr('class', 'x axis');

    svg.append('g')
        .attr('class', 'y axis');

    svg.append('defs').append('marker')
        .attr({
            id          : 'arrow-x',
            class       : 'arrowhead',
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
            class       : 'arrowhead',
            refX        : 2,
            refY        : 2,
            markerWidth : 4,
            markerHeight: 4,
            orient      : 'auto',
        })
        .append('path')
            .attr('d', 'M0,4 H4 L2,0 Z');

    svg.append('line').attr('class', 'default-price');
    svg.append('text').attr('text-anchor', 'middle').attr('class', 'default-price').text(this.i18nDefaultPrice);
    svg.append('path').attr('class', 'line');

    svg.append('rect')
        .attr({
            class   : 'start-price',
            width   : 32,
            rx      : 3,
            height  : 29,
            ry      : 3,
        });

    svg.append('text').attr('class', 'start-price').attr('text-anchor', 'end');
    svg.append('text').attr('class', 'start-price-currency').attr('text-anchor', 'end').text(this.currency);
    svg.append('path').attr('class', 'start-price-triangle');

    svg.append('rect')
        .attr({
            class   : 'end-price',
            width   : 32,
            rx      : 3,
            height  : 29,
            ry      : 3,
        });

    svg.append('text').attr('class', 'end-price').attr('text-anchor', 'end');
    svg.append('text').attr('class', 'end-price-currency').attr('text-anchor', 'end').text(this.currency);
    svg.append('path').attr('class', 'end-price-triangle');

    this.svg = svg;

    jQuery(window).bind('resize', function() { self.plot(); });
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

LPCurve.prototype.set_data = function(data, parsingFormat) {
    this.data = data;
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

    svg.select('.background')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            width   : width + 10,
            height  : height,
        });

    // AXES
    // -------------------------------------------------------------------------------------------------------
    var xExtent = d3.extent(self.data, function(d) { return d.x; }),
        yExtent = [this.minPrice, this.maxPrice],
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
    svg.select('line.default-price')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x1: 0,
            y1: yScale(this.defaultPrice),
            x2: width + 10,
            y2: yScale(this.defaultPrice),
        });

    svg.select('text.default-price')
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: width / 2,
            y: yScale(self.defaultPrice),
        });

    // PRICE CURVE
    // -------------------------------------------------------------------------------------------------------
    // D3.js provides us with a Path Data Generator Function for lines
    var pathEl  = svg.select('path.line').node(),
        line    = d3.svg.line()
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
    // The virtual selections are enter, update and exit
    // -------------------------------------------------------------------------------------------------------
    var end                 = self.data.length,
        point               = svg.selectAll('circle.draggable').data((self.data)),
        priceLine           = svg.selectAll('.line-price').data((self.data).slice(1, end)),
        priceLineVisible    = svg.selectAll('.line-price-visible').data((self.data).slice(1, end));

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
    svg.select('text.start-price-currency')
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

    // END PRICE
    // -------------------------------------------------------------------------------------------------------
    // SQUARE
    svg.select('rect.end-price')
        .datum((self.data)[self.data.length - 1])
        .call(dragYAxisBehavior)
        .transition().duration(dragging ? 0 : 250)
        .attr({
            x: function()  { return width + 18; },
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
    svg.select('text.end-price-currency')
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

    // Elements on top of the graphic
    // -------------------------------------------------------------------------------------------------------
    // SQUARES
    var xDragSquare = svg.selectAll('.x-drag-square').data((self.data).slice(1, end));

    xDragSquare.enter().append('rect').attr('class', function(point, index) {
        if (index == self.data.length - 2) return 'x-drag-square hidden';
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
        if (index == self.data.length - 2) return 'x-triangle-bottom hidden';
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
        if (index == self.data.length - 2) return 'x-text-days hidden';
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
        if (index == self.data.length - 2) return 'x-text hidden';
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
            if (index == self.data.length - 2) return 'line-price-visible hidden';
            return 'line-price-visible';
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
        if (index == self.data.length - 2) return 'line-price hidden';
        return 'line-price';
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
            if (index === 0 || index == self.data.length - 1) return 'draggable circle-hidden';
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
    // DRAG POINTS Y AXIS 'price' FUNCTIONS
    // -------------------------------------------------------------------------------------------------------
    function dragEndPoint(d, i) {
        var p = yScale.invert(d3.event.y);
        if (p < yExtent[0])
            p = yExtent[0];
        if (p > yExtent[1])
            p = yExtent[1];
        d.y = p;

        // we have to keep the starting price in sync with the first / second point
        if (i === 0 && self.data[0].x == d.x) {
            // the second check is to make sure we are dragging the first point
            // since the squares have only one element of the data array, i is always 0
            self.data[1].y = d.y;
        } else if (i == 1) {
            self.data[0].y = d.y;
        }
        // we have to keep in sync starting price with the last/last-1 point
        else if (i === 0 && self.data[self.data.length-1].x == d.x) {
            self.data[self.data.length - 2].y = d.y;
        } else if (i == self.data.length - 2) {
            self.data[self.data.length - 1].y = d.y;
        }

        self.plot();
    }

    function dragstartPoint() { self.dragging = true; jQuery(self.container).toggleClass('dragging'); }
    function dragendPoint() { self.dragging = false; jQuery(self.container).toggleClass('dragging'); }


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
            isDraggingLastPoint = (i == self.data.length - 2),
            isDragHandler = (i == self.data.length - 3),
            cappedTargetDate;


        if (isDraggingLastPoint) {
            var dragDelta = (targetDate - d.x ) / (1000/fps),
                dragStep = function() {
                    cappedTargetDate = +d.x + dragDelta;
                    cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
                    cappedTargetDate = Math.max(cappedTargetDate, 29.51); // minimum 30 days
                    cappedTargetDate = Math.min(cappedTargetDate, 60.49); // maximum 60 days
                    d.x = cappedTargetDate;
                    xScale.domain(d3.extent(self.data, function(d) { return d.x; })); // restore the value of the xScale since it could have changed
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
            xScale.domain(d3.extent(self.data, function(d) { return d.x; })); // restore the value of the xScale since it could have changed
            self.plot();
        } else {
            cappedTargetDate = targetDate;
            cappedTargetDate = Math.max(cappedTargetDate, self.data[i].x + 0.51);
            cappedTargetDate = Math.min(cappedTargetDate, self.data[i+2].x - 0.51);
            d.x = cappedTargetDate;
            xScale.domain(d3.extent(self.data, function(d) { return d.x; })); // restore the value of the xScale since it could have changed
            self.plot();
        }
    }
};
