jQuery.noConflict();
(function($) { $(function() {

    function laterPayEditPost() {
        var $o = {
                // post price inputs
                priceInput              : $('#post-price'),
                priceTypeInput          : $('input[name=post_price_type]'),

                // toggle for choosing pricing type
                priceSection            : $('#laterpay-price-type'),
                pricingTypeToggle       : $('#laterpay-price-type .lp-toggle'),
                pricingTypeButtons      : $('#laterpay-price-type .lp-toggle a'),
                individualPriceButton   : $('#use-individual-price').parent(),
                categoryPriceButton     : $('#use-category-default-price').parent(),
                globalPriceButton       : $('#use-global-default-price').parent(),

                // details sections for chosen pricing type
                details                 : $('#laterpay-price-type-details'),
                individualPriceDetails  : $('#laterpay-price-type-details .use-individual-price'),
                categoryPriceDetails    : $('#laterpay-price-type-details .use-category-default-price'),
                categoriesList          : $('#laterpay-price-type-details .use-category-default-price ul'),
                categories              : $('#laterpay-price-type-details .use-category-default-price li'),
                categoryInput           : $('input[name=laterpay_post_default_category]'),
                dynamicPricingToggle    : $('#use-dynamic-pricing'),
                dynamicPricingContainer : $('#laterpay-widget-container'),

                // strings cached for better compression
                expanded                : 'expanded',
                selected                : 'selected',
                disabled                : 'disabled',
                selectedCategory        : 'selected-category',
                dynamicPricingApplied   : 'dynamic-pricing-applied',
            },

            bindEvents = function() {
                // switch pricing type
                $o.pricingTypeButtons
                .mousedown(function() {switchPricingType(this);})
                .click(function(e) {e.preventDefault();});

                // save pricing data
                $('#post').submit(function() {saveDynamicPricingData();});

                // validate manually entered prices
                $o.priceInput.blur(function() {setPrice($(this).val());});

                // toggle dynamic pricing widget
                $o.dynamicPricingToggle
                .mousedown(function() {toggleDynamicPricing();})
                .click(function(e) {e.preventDefault();});

                // update list of applicable category prices on change of categories list
                $('.categorychecklist :checkbox')
                .on('change', function() {updateApplicableCategoriesList();});

                // apply category default prices when selecting one of the applicable categories
                $o.categoryPriceDetails
                .on('mousedown', 'a', function() {applyCategoryPrice(this);})
                .on('click', 'a', function(e) {e.preventDefault();});
            },

            switchPricingType = function(trigger) {
                var $this           = $(trigger),
                    $clickedButton  = $this.parent('li'),
                    priceType       = $this.attr('class');

                if ($clickedButton.hasClass($o.disabled) || $clickedButton.hasClass($o.selected)) {
                    return;
                }

                // set state of toggle
                $('.selected', $o.pricingTypeToggle).removeClass($o.selected);
                $clickedButton.addClass($o.selected);
                $o.priceSection.removeClass($o.expanded);

                // hide / show details sections
                $('.details-section', $o.details).hide();

                // case: individual price
                if (priceType === 'use-individual-price') {
                    $o.priceSection.addClass($o.expanded);
                    $o.dynamicPricingToggle.show();
                    $o.priceTypeInput.val('individual price');

                    if ($o.dynamicPricingToggle.text() === lpVars.i18nRemoveDynamicPricing) {
                        renderDynamicPricingWidget();
                        $o.individualPriceDetails.show();
                    }
                }
                // case: category default price
                else if (priceType === 'use-category-default-price') {
                    updateSelectedCategory();

                    // set the price of the selected category
                    var price = $('.selected-category a', $o.categoriesList).attr('data-price');
                    setPrice(price);

                    // show / hide stuff
                    $o.priceSection.addClass($o.expanded);
                    $('.' + priceType, $o.details).show();
                    $o.categories.slideDown(250);
                    $o.dynamicPricingToggle.hide();
                    $o.priceTypeInput.val('category default price');
                }
                // case: global default price
                else if (priceType === 'use-global-default-price') {
                    setPrice($this.attr('data-price'));
                    $o.dynamicPricingToggle.hide();
                    $o.priceTypeInput.val('global default price');
                }

                // disable price input for all scenarios other than static individual price
                if (priceType === 'use-individual-price' && !$o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                    $o.priceInput.removeAttr('disabled');
                    setTimeout(function() {$o.priceInput.focus();}, 50);
                } else {
                    if ($o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                        disableDynamicPricing();
                    }
                    $o.priceInput.attr('disabled', 'disabled');
                }
            },

            setPrice = function(price) {
                var validatedPrice = validatePrice(price);
                // localize price
                if (lpVars.locale == 'de_DE') {
                    validatedPrice = validatedPrice.replace('.', ',');
                }
                $('#post-price').val(validatedPrice);
            },

            validatePrice = function(price) {
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
            },

            updateSelectedCategory = function() {
                var selectedCategoryId  = $o.categoryInput.val(),
                    $firstCategory      = $o.categories.first();

                if (!$o.categories.length) {
                    $o.categoryInput.val('');
                    return;
                }

                if (typeof(selectedCategoryId) !== 'undefined' && $('[data-category=' + selectedCategoryId + ']', $o.categories.parent()).length) {
                    $('[data-category=' + selectedCategoryId + ']', $o.categories.parent()).addClass($o.selectedCategory);
                } else {
                    // select the first category in the list, if none is selected
                    $firstCategory.addClass($o.selectedCategory);
                    $o.categoryInput.val($firstCategory.data('category'));
                }

                // also update the price, if the selected category has changed in pricing mode 'category default price'
                if ($o.categoryPriceButton.hasClass($o.selected)) {
                    setPrice($('.selected-category a', $o.categoriesList).attr('data-price'));
                }
            },

            updateApplicableCategoriesList = function() {
                var $selectedCategories = $('#categorychecklist :checkbox:checked'),
                    l                   = $selectedCategories.length,
                    categoryIds         = [],
                    categoriesList      = '',
                    selectedCategoryId  = $o.categoryInput.val(),
                    i, categoryId;

                for (i = 0; i < l; i++) {
                    categoryId = parseInt($selectedCategories.eq(i).val(), 10);
                    categoryIds.push(categoryId);
                }

                // make Ajax request for prices and names of categories
                $.post(
                    lpVars.ajaxUrl,
                    {
                        action          : 'laterpay_get_category_prices',
                        form            : 'laterpay_get_category_prices',
                        category_ids    : categoryIds
                    },
                    function(data) {
                        // rebuild list of categories in category default pricing tab
                        if (data) {
                            data.forEach(function(category) {
                                categoriesList +=   '<li data-category="' + category.category_id + '">' +
                                                        '<a href="#" data-price="' + category.category_price + '">' +
                                                            '<span>' + category.category_price + ' ' + lpVars.currency + '</span>' +
                                                            category.category_name +
                                                        '</a>' +
                                                    '</li>';
                            });
                            $o.categoriesList.html(categoriesList);

                            if (data.length) {
                                $o.categoryPriceButton.removeClass($o.disabled);
                                $o.categories = $('#laterpay-price-type-details .use-category-default-price li');
                                updateSelectedCategory();
                            } else {
                                // disable the 'use category default price' button,
                                // if no categories with an attached default price are applied to the current post
                                $o.categoryPriceButton.addClass($o.disabled);

                                // hide / details sections
                                $('.details-section', $o.details).hide();

                                // if current pricing type is 'category default price'
                                // fall back to global default price or an individual price of 0
                                if ($o.categoryPriceButton.hasClass($o.selected)) {
                                    $('.selected', $o.pricingTypeToggle).removeClass($o.selected);
                                    $('#laterpay-price-type').removeClass($o.expanded);

                                    if ($o.globalPriceButton.hasClass($o.disabled)) {
                                        $o.individualPriceButton.addClass($o.selected);
                                        $o.priceTypeInput.val('individual price');
                                        $o.dynamicPricingToggle.show();
                                        $o.priceInput.removeAttr('disabled');
                                        setPrice('0.00');
                                    } else {
                                        $o.globalPriceButton.addClass($o.selected);
                                        $o.priceTypeInput.val('global default price');
                                        setPrice(lpVars.globalDefaultPrice);
                                    }
                                }
                            }
                        }
                    },
                    'json'
                );
            },

            applyCategoryPrice = function(trigger) {
                var $this       = $(trigger),
                    $category   = $this.parent(),
                    category    = $category.attr('data-category'),
                    price       = $this.attr('data-price');

                $o.categories.removeClass($o.selectedCategory);
                $category.addClass($o.selectedCategory);
                $o.categoryInput.val(category);
                setPrice(price);
            },

            toggleDynamicPricing = function() {
                if ($o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                    disableDynamicPricing();
                } else {
                    enableDynamicPricing();
                }
            },

            enableDynamicPricing = function() {
                renderDynamicPricingWidget();
                $o.dynamicPricingToggle.addClass($o.dynamicPricingApplied);
                $o.priceInput.attr('disabled', 'disabled');
                $('#laterpay-dynamic-pricing').slideDown(250);
                $('input[name=post_price_type]').val('individual price, dynamic');
                $o.dynamicPricingToggle.text(lpVars.i18nRemoveDynamicPricing);
            },

            disableDynamicPricing = function() {
                $o.dynamicPricingToggle.removeClass($o.dynamicPricingApplied);
                $o.priceInput.removeAttr('disabled');
                $('#laterpay-dynamic-pricing').slideUp(250, function() {
                    $o.dynamicPricingContainer.empty();
                });
                $('input[name=post_price_type]').val('individual price');
                $o.dynamicPricingToggle.text(lpVars.i18nAddDynamicPricing);
            },

            renderDynamicPricingWidget = function() {
                var data    = lpVars.dynamicPricingData,
                    lpc     = new LPCurve('#laterpay-widget-container');
                window.lpc = lpc;

                $o.priceInput.attr('disabled', 'disabled');

                if (data.length === 4)
                    lpc.set_data(data).setPrice(0, 5, lpVars.globalDefaultPrice).plot();
                else
                    lpc.set_data(data).setPrice(0, 5, lpVars.globalDefaultPrice).interpolate('step-before').plot();

                // FIXME: selectors like $('select') will blow up like a nuclear power plant
                // when used within WordPress installations with who knows what plugins and modifications
                // $('.blockbuster').click(function() {
                //     lpc.set_data([
                //         {x:  0, y: 1.8},
                //         {x:  6, y: 1.8},
                //         {x: 11, y: 0.6},
                //         {x: 30, y: 0.6}
                //     ])
                //     .interpolate('linear')
                //     .plot();

                //     $('select').val('linear');

                //     return false;
                // });

                // $('.long-tail').click(function() {
                //     lpc.set_data([
                //         {x:  0, y: 1.8},
                //         {x:  3, y: 1.8},
                //         {x: 14, y: 0.6},
                //         {x: 30, y: 0.6}
                //     ])
                //     .interpolate('linear')
                //     .plot();

                //     $('select').val('linear');

                //     return false;
                // });

                // $('.breaking-news').click(function() {
                //     lpc.set_data([
                //         {x:  0, y: 1.8},
                //         {x:  3, y: 1.8},
                //         {x: 30, y: 0.6}
                //     ])
                //     .interpolate('step-before')
                //     .plot();

                //     $('select').val('step-before');

                //     return false;
                // });

                // $('.teaser').click(function() {
                //     lpc.set_data([
                //         {x:  0, y: 0.6},
                //         {x:  3, y: 0.6},
                //         {x: 30, y: 1.8}
                //     ])
                //     .interpolate('step-before')
                //     .plot();

                //     $('select').val('step-before');

                //     return false;
                // });

                // $('.flat').click(function() {
                //     lpc.set_data([
                //         {x:  0, y: 1},
                //         {x:  3, y: 1},
                //         {x: 14, y: 1},
                //         {x: 30, y: 1}
                //     ])
                //     .interpolate('linear')
                //     .plot();

                //     return false;
                // });
            },

            saveDynamicPricingData = function() {
                // don't try to save dynamic pricing data, if pricing type is not dynamic but static
                if (!$o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                    return;
                }

                // save dynamic pricing data
                var data = window.lpc.get_data();
                if (window.lpc.get_data().length === 4) {
                    $('input[name=laterpay_start_price]').val(data[0].y);
                    $('input[name=laterpay_end_price]').val(data[3].y);
                    $('input[name=laterpay_change_start_price_after_days]').val(data[1].x);
                    $('input[name=laterpay_transitional_period_end_after_days]').val(data[2].x);
                    $('input[name=laterpay_reach_end_price_after_days]').val(data[3].x);
                } else if (window.lpc.get_data().length === 3) {
                    $('input[name=laterpay_start_price]').val(data[0].y);
                    $('input[name=laterpay_end_price]').val(data[2].y);
                    $('input[name=laterpay_change_start_price_after_days]').val(data[1].x);
                    $('input[name=laterpay_transitional_period_end_after_days]').val(0);
                    $('input[name=laterpay_reach_end_price_after_days]').val(data[2].x);
                }

                return true;
            },

            initializePage = function() {
                bindEvents();

                if ($o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                    renderDynamicPricingWidget();
                }
            };

        initializePage();
    }

    // initialize page
    laterPayEditPost();

});})(jQuery);
