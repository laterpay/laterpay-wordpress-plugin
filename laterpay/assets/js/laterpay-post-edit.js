(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayPostEdit
    function laterPayPostEdit() {
        var $o = {
                // post price inputs
                priceInput              : $('#lp_js_post-price-input'),
                priceTypeInput          : $('#lp_js_post-price-type-input'),
                revenueModel            : $('#lp_js_post-revenue-model'),
                categoryInput           : $('#lp_js_post-default-category-input'),

                // button group for choosing pricing type
                priceSection            : $('#lp_js_price-type'),
                pricingTypeButtonGroup  : $('#lp_js_price-type-button-group'),
                pricingTypeButtons      : $('.lp_js_price-type-button'),
                individualPriceButton   : $('#lp_js_use-individual-price').parent(),
                categoryPriceButton     : $('#lp_js_use-category-default-price').parent(),
                globalPriceButton       : $('#lp_js_use-global-default-price').parent(),

                // details sections for chosen pricing type
                details                 : $('#lp_js_price-type-details'),
                detailsSections         : $('.lp_js_details-section'),
                individualPriceDetails  : $('#lp_js_individual-price-details'),
                categoryPriceDetails    : $('#lp_js_category-price-details'),
                categoriesList          : $('#lp_js_category-price-details ul'),
                categories              : $('#lp_js_category-price-details li'),
                dynamicPricingToggle    : $('#lp_js_toggle-dynamic-pricing'),
                dynamicPricingContainer : '#lp_js_dynamic-pricing-widget-container',

                // strings cached for better compression
                expanded                : 'lp_is-expanded',
                selected                : 'lp_is-selected',
                disabled                : 'lp_is-disabled',
                dynamicPricingApplied   : 'lp_is-with-dynamic-pricing',
                selectedCategory        : 'lp_selected-category',
                payPerUse               : 'ppu',
                singleSale              : 'sis'
            },

            bindEvents = function() {
                // switch pricing type
                $o.pricingTypeButtons
                .mousedown(function() {
                    switchPricingType(this);
                })
                .click(function(e) {e.preventDefault();});

                // save pricing data
                $('#post').submit(function() {
                    saveDynamicPricingData();
                });

                // validate manually entered prices
                // (function is only triggered 800ms after the keyup)
                $o.priceInput.keyup(
                    debounce(function() {
                        setPrice($(this).val());
                    }, 800)
                );

                // validate choice of revenue model (validating the price switches the revenue model if required)
                $('input:radio', $o.revenueModel).change(function() {
                    validatePrice($o.priceInput.val());
                });

                // toggle dynamic pricing widget
                $o.dynamicPricingToggle
                .mousedown(function() {
                    toggleDynamicPricing();
                })
                .click(function(e) {e.preventDefault();});

                // update list of applicable category prices on change of categories list
                $('.categorychecklist input:checkbox')
                .on('change', function() {
                    updateApplicableCategoriesList();
                });

                // apply category default prices when selecting one of the applicable categories
                $o.categoryPriceDetails
                .on('mousedown', 'a', function() {
                    applyCategoryPrice(this);
                })
                .on('click', 'a', function(e) {e.preventDefault();});
            },

            switchPricingType = function(trigger) {
                var $this           = $(trigger),
                    $clickedButton  = $this.parent('li'),
                    priceType       = $this.attr('id');

                if ($clickedButton.hasClass($o.disabled) || $clickedButton.hasClass($o.selected)) {
                    return;
                }

                // set state of button group
                $('.' + $o.selected, $o.pricingTypeButtonGroup).removeClass($o.selected);
                $clickedButton.addClass($o.selected);
                $o.priceSection.removeClass($o.expanded);

                // hide details sections
                $o.detailsSections.hide();

                // case: individual price
                if (priceType === 'lp_js_use-individual-price') {
                    $o.priceSection.addClass($o.expanded);
                    $o.dynamicPricingToggle.show();
                    $o.priceTypeInput.val('individual price');

                    // validate price to enable all applicable revenue models
                    validatePrice($o.priceInput.val());

                    // show / hide stuff
                    if ($o.dynamicPricingToggle.text() === lpVars.i18nRemoveDynamicPricing) {
                        renderDynamicPricingWidget();
                        $o.individualPriceDetails.show();
                    }
                }
                // case: category default price
                else if (priceType === 'lp_js_use-category-default-price') {
                    updateSelectedCategory();

                    // set the price and revenue model of the selected category
                    var $category       = $('.lp_selected-category a', $o.categoriesList),
                        price           = $category.attr('data-price'),
                        revenueModel    = $category.attr('data-revenue-model');
                    setPrice(price);
                    setRevenueModel(revenueModel, true);

                    // show / hide stuff
                    $o.priceSection.addClass($o.expanded);
                    $o.categoryPriceDetails.show();
                    $o.categories.slideDown(250);
                    $o.dynamicPricingToggle.hide();
                    $o.priceTypeInput.val('category default price');
                }
                // case: global default price
                else if (priceType === 'lp_js_use-global-default-price') {
                    var price           = $this.attr('data-price'),
                        revenueModel    = $this.attr('data-revenue-model');

                    setPrice(price);
                    setRevenueModel(revenueModel, true);

                    // show / hide stuff
                    $o.dynamicPricingToggle.hide();
                    $o.priceTypeInput.val('global default price');
                }

                // disable price input for all scenarios other than static individual price
                if (priceType === 'lp_js_use-individual-price' && !$o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
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
                $o.priceInput.val(validatedPrice);
            },

            setRevenueModel = function(revenueModel, readOnly) {
                $('label', $o.revenueModel).removeClass($o.selected);

                if (readOnly) {
                    // disable not-selected revenue model
                    $('input:radio[value!=' + revenueModel + ']', $o.revenueModel).parent('label').addClass($o.disabled);
                }

                // enable and check selected revenue model
                $('input:radio[value=' + revenueModel + ']', $o.revenueModel)
                .prop('checked', 'checked')
                    .parent('label')
                    .removeClass($o.disabled)
                    .addClass($o.selected);
            },

            validatePrice = function(price) {
                // strip non-number characters
                price = price.toString().replace(/[^0-9\,\.]/g, '');

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

                // correct prices outside the allowed range of 0.05 - 149.49
                if (price > 149.99) {
                    price = 149.99;
                } else if (price > 0 && price < 0.05) {
                    price = 0.05;
                }

                validateRevenueModel(price);

                // format price with two digits
                price = price.toFixed(2);

                // localize price
                if (lpVars.locale == 'de_DE') {
                    price = price.replace('.', ',');
                }

                return price;
            },

            validateRevenueModel = function(price) {
                var currentRevenueModel = $('input:radio:checked', $o.revenueModel).val(),
                    $payPerUse          = $('input:radio[value=' + $o.payPerUse + ']', $o.revenueModel),
                    $singleSale         = $('input:radio[value=' + $o.singleSale + ']', $o.revenueModel);

                if (price === 0 || (price >= 0.05 && price <= 5)) {
                    // enable Pay-per-Use for 0 and all prices between 0.05 and 5.00 Euro
                    $payPerUse.removeAttr('disabled')
                        .parent('label').removeClass($o.disabled);
                } else {
                    // disable Pay-per-Use
                    $payPerUse.attr('disabled', 'disabled')
                        .parent('label').addClass($o.disabled);
                }

                if (price >= 1.49) {
                    // enable Single Sale for prices >= 1.49 Euro (prices > 149.99 Euro are fixed by validatePrice already)
                    $singleSale.removeAttr('disabled')
                        .parent('label').removeClass($o.disabled);
                } else {
                    // disable Single Sale
                    $singleSale.attr('disabled', 'disabled')
                        .parent('label').addClass($o.disabled);
                }

                // switch revenue model, if combination of price and revenue model is not allowed
                if (price > 5 && currentRevenueModel == $o.payPerUse) {
                    // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                    $singleSale.prop('checked', true);
                } else if (price < 1.49 && currentRevenueModel == $o.singleSale) {
                    // Single Sale purchases are not allowed for prices < 1.49 Euro
                    $payPerUse.prop('checked', true);
                }

                // highlight current revenue model
                $('label', $o.revenueModel).removeClass($o.selected);
                $('input:radio:checked', $o.revenueModel).parent('label').addClass($o.selected);
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

                // also update the price and revenue model, if the selected category has changed in pricing mode 'category default price'
                if ($o.categoryPriceButton.hasClass($o.selected)) {
                    var $category       = $('.lp_selected-category a', $o.categoriesList),
                        price           = $category.attr('data-price'),
                        revenueModel    = $category.attr('data-revenue-model');

                    setPrice(price);
                    setRevenueModel(revenueModel, true);
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
                                                        '<a href="#" data-price="' + category.category_price + '" data-revenue-model="' + category.revenue_model + '">' +
                                                            '<span>' + parseFloat(category.category_price).toFixed(2) + ' ' + lpVars.currency + '</span>' +
                                                            category.category_name +
                                                        '</a>' +
                                                    '</li>';
                            });
                            $o.categoriesList.html(categoriesList);

                            if (data.length) {
                                $o.categoryPriceButton.removeClass($o.disabled);
                                // update cached selector
                                $o.categories = $('#lp_js_category-price-details li');
                                updateSelectedCategory();
                            } else {
                                // disable the 'use category default price' button,
                                // if no categories with an attached default price are applied to the current post
                                $o.categoryPriceButton.addClass($o.disabled);

                                // hide details sections
                                $o.detailsSections.hide();

                                // if current pricing type is 'category default price'
                                // fall back to global default price or an individual price of 0
                                if ($o.categoryPriceButton.hasClass($o.selected)) {
                                    $('.' + $o.selected, $o.pricingTypeButtonGroup).removeClass($o.selected);
                                    $o.priceSection.removeClass($o.expanded);

                                    if ($o.globalPriceButton.hasClass($o.disabled)) {
                                        // case: fall back to individual price
                                        $o.individualPriceButton.addClass($o.selected);
                                        $o.priceTypeInput.val('individual price');
                                        $o.dynamicPricingToggle.show();
                                        $o.priceInput.removeAttr('disabled');
                                        setPrice(0);
                                        setRevenueModel($o.payPerUse, false);
                                    } else {
                                        // case: fall back to global default price
                                        $o.globalPriceButton.addClass($o.selected);
                                        $o.priceTypeInput.val('global default price');
                                        setPrice(lpVars.globalDefaultPrice);
                                        setRevenueModel($('a', $o.globalPriceButton).attr('data-revenue-model'), true);
                                    }
                                }
                            }
                        }
                    },
                    'json'
                );
            },

            applyCategoryPrice = function(trigger) {
                var $this           = $(trigger),
                    $category       = $this.parent(),
                    category        = $category.attr('data-category'),
                    price           = $this.attr('data-price'),
                    revenueModel    = $this.attr('data-revenue-model');

                $o.categories.removeClass($o.selectedCategory);
                $category.addClass($o.selectedCategory);
                $o.categoryInput.val(category);

                setPrice(price);
                setRevenueModel(revenueModel, true);
            },

            toggleDynamicPricing = function() {
                if ($o.dynamicPricingToggle.hasClass($o.dynamicPricingApplied)) {
                    disableDynamicPricing();
                    $o.revenueModel.show();
                } else {
                    enableDynamicPricing();
                }
            },

            enableDynamicPricing = function() {
                renderDynamicPricingWidget();
                $o.dynamicPricingToggle.addClass($o.dynamicPricingApplied);
                $o.priceInput.attr('disabled', 'disabled');
                $o.individualPriceDetails.slideDown(250);
                $o.priceTypeInput.val('individual price, dynamic');
                $o.dynamicPricingToggle.text(lpVars.i18nRemoveDynamicPricing);
                $o.revenueModel.hide();
            },

            disableDynamicPricing = function() {
                $o.dynamicPricingToggle.removeClass($o.dynamicPricingApplied);
                $o.priceInput.removeAttr('disabled');
                $o.individualPriceDetails.slideUp(250, function() {
                    $($o.dynamicPricingContainer).empty();
                });
                $o.priceTypeInput.val('individual price');
                $o.dynamicPricingToggle.text(lpVars.i18nAddDynamicPricing);
            },

            renderDynamicPricingWidget = function() {
                var data    = lpVars.dynamicPricingData,
                    lpc     = new LPCurve($o.dynamicPricingContainer);
                window.lpc = lpc;

                $o.priceInput.attr('disabled', 'disabled');

                if (data.length === 4) {
                    lpc.set_data(data).setPrice(0, 5, lpVars.globalDefaultPrice).plot();
                } else {
                    lpc.set_data(data).setPrice(0, 5, lpVars.globalDefaultPrice).interpolate('step-before').plot();
                }
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

            // throttle the execution of a function by a given delay
            debounce = function(fn, delay) {
              var timer = null;
              return function () {
                var context = this,
                    args    = arguments;

                clearTimeout(timer);

                timer = setTimeout(function() {
                  fn.apply(context, args);
                }, delay);
              };
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
    laterPayPostEdit();

});})(jQuery);
