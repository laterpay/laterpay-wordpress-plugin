jQuery.noConflict();
(function($) { $(function() {

    function validatePrice(price) {
        // convert price to proper float value
        if (price.indexOf(',') > -1) {
            price = parseFloat(price.replace(',', '.')).toFixed(2);
        } else {
            price = parseFloat(price).toFixed(2);
        }
        // prevent negative prices
        price = Math.abs(price);
        // correct prices outside the allowed range of 0.05 - 5.00
        if (price > 5) {
            price = 5;
        } else if (price > 0 && price < 0.05) {
            price = 0.05;
        }

        return price;
    }

    function laterpaySetPrice(price) {
        var validatedPrice = validatePrice(price);
        // localize price
        if (lpVars.locale == 'de_DE') {
            validatedPrice = validatedPrice.replace('.', ',');
        }
        $('#post-price').val(validatedPrice);
    }

    function requiredTeaserContentNotEntered() {
        return (
            $('textarea[name=teaser-content]').val().length === 0 &&
                (
                    $('input[name=pricing-post]').val() > 0 ||
                    $('input[name=price_post_type]').val() === 1
                )
            );
    }

    $('#post-price').blur(function() {
        laterpaySetPrice($(this).val());
    });

    $('#show-advanced')
    .mousedown(function() {
        $('#laterpay_post_advanced').show();
        $('#laterpay_post_standard').hide();
        $('input[name=price_post_type]').val(1);
    })
    .click(function(e) {e.preventDefault();});

    $('#show-standard')
    .mousedown(function() {
        $('#laterpay_post_advanced').hide();
        $('#laterpay_post_standard').show();
        $('input[name=price_post_type]').val(0);
    })
    .click(function(e) {e.preventDefault();});

    $('#set_price_category').click(function(e) {
        laterpaySetPrice(lpVars.categoryDefaultPrice);
        $(this).fadeOut(400);
        e.preventDefault();
    });

    $('#set_price_global').click(function(e) {
        laterpaySetPrice(lpVars.globalDefaultPrice);
        $(this).fadeOut(400);
        e.preventDefault();
    });

    $('#post').submit(function() {
        if (requiredTeaserContentNotEntered()) {
            setMessage(lpVars.i18nTeaserError, false);
            $('#timestampdiv').show();
            $('#publishing-action .spinner').hide();
            $('#publish').prop('disabled', false).removeClass('button-primary-disabled');

            return false;
        } else {
            var data = window.lpc.getData();
            if (window.lpc.getData().length === 4) {
                $('input[name=laterpay_start_price]').val(data[0].y);
                $('input[name=laterpay_end_price]').val(data[3].y);
                $('input[name=laterpay_change_start_price_after_days]').val(data[1].x);
                $('input[name=laterpay_transitional_period_end_after_days]').val(data[2].x);
                $('input[name=laterpay_reach_end_price_after_days]').val(data[3].x);
            } else if (window.lpc.getData().length === 3) {
                $('input[name=laterpay_start_price]').val(data[0].y);
                $('input[name=laterpay_end_price]').val(data[2].y);
                $('input[name=laterpay_change_start_price_after_days]').val(data[1].x);
                $('input[name=laterpay_transitional_period_end_after_days]').val(0);
                $('input[name=laterpay_reach_end_price_after_days]').val(data[2].x);
            }

            return true;
        }
    });


    // dynamic pricing widget
    var data    = lpVars.dataStart,
        lpc     = new LPCurve('#laterpay-widget-container');
    window.lpc = lpc;

    if (data.length === 4)
        lpc.setData(data).setPrice(0, 5, price_global).plot();
    else
        lpc.setData(data).setPrice(0, 5, price_global).interpolate('step-before').plot();

    $('.blockbuster').click(function() {
        lpc.setData([
            {x:  0, y: 1.8},
            {x:  6, y: 1.8},
            {x: 11, y: 0.6},
            {x: 30, y: 0.6}
        ])
        .interpolate('linear')
        .plot();

        $('select').val('linear');

        return false;
    });

    $('.long-tail').click(function() {
        lpc.setData([
            {x:  0, y: 1.8},
            {x:  3, y: 1.8},
            {x: 14, y: 0.6},
            {x: 30, y: 0.6}
        ])
        .interpolate('linear')
        .plot();

        $('select').val('linear');

        return false;
    });

    $('.breaking-news').click(function() {
        lpc.setData([
            {x:  0, y: 1.8},
            {x:  3, y: 1.8},
            {x: 30, y: 0.6}
        ])
        .interpolate('step-before')
        .plot();

        $('select').val('step-before');

        return false;
    });

    $('.teaser').click(function() {
        lpc.setData([
            {x:  0, y: 0.6},
            {x:  3, y: 0.6},
            {x: 30, y: 1.8}
        ])
        .interpolate('step-before')
        .plot();

        $('select').val('step-before');

        return false;
    });

    $('.flat').click(function() {
        lpc.setData([
            {x:  0, y: 1},
            {x:  3, y: 1},
            {x: 14, y: 1},
            {x: 30, y: 1}
        ])
        .interpolate('linear')
        .plot();

        return false;
    });

    if (lpVars.isStandardPost === '1') {
        $('#laterpay_post_standard').hide();
        $('#laterpay_post_advanced').show();
    } else {
        $('#laterpay_post_standard').show();
        $('#laterpay_post_advanced').hide();
    }

});})(jQuery);
