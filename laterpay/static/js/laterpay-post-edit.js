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
            l                   = $selectedCategories.length,
            $categoriesList     = $('.use-category-default-price.details-section UL'),
            categoryIds         = [],
            categoriesList      = '',
            selectedCategoryId  = $('#laterpay-price-type-details .use-category-default-price .selected-category').attr('data-category'),
            i, categoryId;

        for (i = 0; i < l; i++) {
            categoryId = parseInt($selectedCategories.eq(i).val(), 10);
            // ignore category 1, as it stands for 'uncategorized'
            if (categoryId !== 1) {
                categoryIds.push(categoryId);
            }
        }

        if (categoryIds.length > 0) {
            // make Ajax request for prices and names of categories
            $.get(
                lpVars1.ajaxUrl,
                {
                    action          : 'wp_ajax_get_category_prices',
                    category_ids    : categoryIds
                },
                function(data) {
console.log(data + '; 0 means function get_category_prices not found :-(');
                    // rebuild list of categories in category default pricing tab
                    // data.each(function() {
                    //     categoriesList += '<li data-category="' + 'abc' + '"><a href="#" data-price="' + 1 + '"><span>' + 1.29 + ' ' + 'EUR' + '</span>' + 'ABC' + '</a></li>';
                    // });
                }
            );
                        // some fake data for development
                        categoriesList += '<li data-category="' + '8' + '"><a href="#" data-price="' + 1.29 + '"><span>' + 1.29 + ' ' + 'EUR' + '</span>' + 'ABC' + '</a></li>';
                        categoriesList += '<li data-category="' + '5' + '"><a href="#" data-price="' + 1.99 + '"><span>' + 1.99 + ' ' + 'EUR' + '</span>' + 'DEF' + '</a></li>';

            $categoriesList.html(categoriesList);

            var $categories = $('#laterpay-price-type-details .use-category-default-price li');

            if (typeof(selectedCategoryId) !== 'undefined') {
                $('[data-price=' + selectedCategoryId + ']', $categories).addClass('selected-category');
            } else {
                // select the first category in the list, if none is selected
                $categories.first().addClass('selected-category');
            }
        } else {
            // what should we do, if there is no valid category applied?
        }
    }

    $('.categorychecklist :checkbox').on('change', function() {
        updateSelectedCategoriesList();
    });


    $('#post-price').blur(function() {
        laterpaySetPrice($(this).val());
    });

    $('#laterpay-price-type .lp-toggle li:not(.disabled, .selected) a')
    .mousedown(function() {
        var $this                   = $(this),
            $priceSection           = $('#laterpay-price-type'),
            $toggle                 = $this.parents('.lp-toggle'),
            $details                = $('#laterpay-price-type-details'),
            priceType               = $this.attr('class'),
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
            updateSelectedCategoriesList();
            var $categories = $('#laterpay-price-type-details .use-category-default-price li'),
                price       = $('#laterpay-price-type-details .selected-category a').attr('data-price');
            // set the price of the selected category
            laterpaySetPrice(price);
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

    $('#laterpay-price-type-details .use-category-default-price')
    .on('mousedown', 'a', function() {
        var $this       = $(this),
            $categories = $('#laterpay-price-type-details .use-category-default-price li'),
            category    = $this.parent().attr('data-category');

        $categories.removeClass('selected-category');
        laterpaySetPrice($this.attr('data-price'));
        $this.parent('li').addClass('selected-category');
    })
    .on('click', 'a', function(e) {e.preventDefault();});

    $('#use-dynamic-pricing')
    .mousedown(function() {
        if ($(this).hasClass('dynamic-pricing-applied')) {
            $(this).removeClass('dynamic-pricing-applied');
            $('#post-price').attr('disabled', 'disabled');
            $('#laterpay-dynamic-pricing').slideDown(250);
            $('input[name=price_post_type]').val(1);
            $(this).text(lpVars1.i18nAddDynamicPricing);
        } else {
            $(this).addClass('dynamic-pricing-applied');
            $('#post-price').removeAttr('disabled');
            $('#laterpay-dynamic-pricing').slideUp(250);
            $('input[name=price_post_type]').val(0);
            $(this).text(lpVars1.i18nRemoveDynamicPricing);
        }
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
