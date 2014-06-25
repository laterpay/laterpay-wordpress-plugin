(function($) {$(document).ready(function() {

        $('#statistics .bar').peity('bar', {
            delimiter   : ';',
            width       : 182,
            height      : 42,
            gap         : 1,
            fill        : function(value, index, array) {
                            var date        = new Date(),
                                daysCount   = array.length,
                                color       = '#999';
                            date.setDate(date.getDate() - (daysCount - index));
                            // highlight the last (current) day
                            if (index === (daysCount - 1))
                                color = '#555';
                            // highlight Saturdays and Sundays
                            if (date.getDay() === 0 || date.getDay() === 6)
                                color = '#c1c1c1';
                            return color;
                        }
        });

});}(jQuery));
