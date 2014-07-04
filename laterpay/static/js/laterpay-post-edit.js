jQuery.noConflict();
(function($) { $(function() {

    function validatePrice(price) {
        // strip non-number characters
        price = price.replace(/[^0-9\,\.]/g, '');
        // convert price to proper float value
        if (typeof price === 'string' && price.indexOf(',') > -1) {
            price = parseFloat(price.replace(',', '.')).toFixed(2);
        } else {
            price = parseFloat(price).toFixed(2);
        }
        // prevent non-number prices
        if (isNaN(price)) {
            price = 0;
        }
        // prevent negative prices
        price = Math.abs(price);
        // correct prices outside the allowed range of 0.05 - 5.00
        if (price > 5) {
            price = 5;
        } else if (price > 0 && price < 0.05) {
            price = 0.05;
        }

        return price.toFixed(2);
    }

    function laterpaySetPrice(price) {
        var validatedPrice = validatePrice(price);
        // localize price
        if (lpVars1.locale == 'de_DE') {
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

    function updateSelectedCategoriesList() {
        var $selectedCategories = $('#categorychecklist :checkbox:checked'),
            categoryIds         = [],
            categoryId;

        for (var i = 0, l = $selectedCategories.length; i < l; i++) {
            categoryId = parseInt($selectedCategories.eq(i).val(), 10);
            // ignore category 1, as it stands for 'uncategorized'
            if (categoryId !== 1) {
                categoryIds.push(categoryId);
            }
        }

        // TODO:
        // make Ajax request for prices and names of categories
        // rebuild list of categories in category default pricing tab
    }

    $('.categorychecklist :checkbox').on('change', function() {
        updateSelectedCategoriesList();
    });


    $('#post-price').blur(function() {
        laterpaySetPrice($(this).val());
    });

    $('#laterpay-price-type .lp-toggle a')
    .mousedown(function() {
        var $this                   = $(this),
            $priceSection           = $('#laterpay-price-type'),
            $toggle                 = $this.parents('.lp-toggle'),
            $details                = $('#laterpay-price-type-details'),
            priceType               = $this.attr('class'),
            $categories             = $('#laterpay-price-type-details .use-category-default-price li'),
            $dynamicPricingToggle   = $('#use-dynamic-pricing');

        // set state of toggle
        $('.selected', $toggle).removeClass('selected');
        $this.parent('li').addClass('selected');
        $priceSection.removeClass('expanded');

        // hide show details sections
        $('.details-section', $details).hide();
        if (priceType === 'use-individual-price') {
            $priceSection.addClass('expanded');
            $('.' + priceType, $details).show();
            $dynamicPricingToggle.show();
        } else if (priceType === 'use-category-default-price') {

            getSelectedCategoryPrices();








            // select the first category in the list, if none is selected yet
            if ($('#laterpay-price-type-details .selected-category').length === 0) {
                $categories.first().addClass('selected-category');
            }
            // set the price of the selected category
            laterpaySetPrice($('#laterpay-price-type-details .selected-category a').attr('data-price'));
            // show / hide stuff
            $priceSection.addClass('expanded');
            $('.' + priceType, $details).show();
            $categories.slideDown(250);
            $dynamicPricingToggle.hide();
        } else if (priceType === 'use-global-default-price') {
            laterpaySetPrice($this.attr('data-price'));
            $dynamicPricingToggle.hide();
        }

        // disable price input for all scenarios other than static individual price
        if (priceType === 'use-individual-price') {
            $('#post-price').removeAttr('disabled');
            setTimeout(function() { $('#post-price').focus(); }, 50);
        } else {
            $('#post-price').attr('disabled', 'disabled');
        }
    })
    .click(function(e) {e.preventDefault();});

    $('#laterpay-price-type-details .use-category-default-price a').click(function(e) {
        var $this       = $(this),
            $categories = $('#laterpay-price-type-details .use-category-default-price li'),
            category    = $this.parent().attr('data-category');

        $categories.removeClass('selected-category');
        laterpaySetPrice($this.attr('data-price'));
        $this.parent('li').addClass('selected-category');
        e.preventDefault();
    });

    $('#use-dynamic-pricing')
    .mousedown(function() {
        $(this).fadeOut(200);
        $('#post-price').attr('disabled', 'disabled');
        $('#laterpay-dynamic-pricing').slideDown(250);
        $('input[name=price_post_type]').val(1);
    })
    .click(function(e) {e.preventDefault();});

    $('#post').submit(function() {
        if (requiredTeaserContentNotEntered()) {
            setMessage(lpVars1.i18nTeaserError, false);
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
    var data    = lpVars1.dynamicPricingData,
        lpc     = new LPCurve('#laterpay-widget-container');
    window.lpc = lpc;

    if (data.length === 4)
        lpc.setData(data).setPrice(0, 5, lpVars1.globalDefaultPrice).plot();
    else
        lpc.setData(data).setPrice(0, 5, lpVars1.globalDefaultPrice).interpolate('step-before').plot();

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

});})(jQuery);
