(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                revenueModel                            : '.lp_js_revenue-model',
                revenueModelLabel                       : '.lp_js_revenue-model-label',
                revenueModelLabelDisplay                : '.lp_js_revenue-model-label-display',
                revenueModelInput                       : '.lp_js_revenue-model-input',
                priceInput                              : '.lp_js_price-input',

                // global default price
                globalDefaultPriceForm                  : $('#lp_js_global-default-price-form'),
                globalDefaultPriceInput                 : $('#lp_js_global-default-price'),
                globalDefaultPriceDisplay               : $('#lp_js_global-default-price-text'),
                globalDefaultPriceRevenueModelDisplay   : $('#lp_js_global-default-price-revenue-model-label'),
                editGlobalDefaultPrice                  : $('#lp_js_edit-global-default-price'),
                cancelEditingGlobalDefaultPrice         : $('#lp_js_cancel-editing-global-default-price'),
                saveGlobalDefaultPrice                  : $('#lp_js_save-global-default-price'),
                globalDefaultPriceShowElements          : $('#lp_js_global-default-price-text, #lp_js_edit-global-default-price, #lp_js_global-default-price-revenue-model-label'),
                globalDefaultPriceEditElements          : $('#lp_js_global-default-price, #lp_js_global-default-price-revenue-model, #lp_js_cancel-editing-global-default-price, #lp_js_save-global-default-price'),

                // category default price
                categoryDefaultPrices                   : $('#lp_js_category-default-prices-list'),
                addCategory                             : $('#lp_js_add-category-default-price'),

                categoryDefaultPriceTemplate            : $('#lp_js_category-default-price-template'),
                categoryDefaultPriceForm                : '.lp_js_category-default-price-form',
                editCategoryDefaultPrice                : '.lp_js_edit-category-default-price',
                cancelEditingCategoryDefaultPrice       : '.lp_js_cancel-editing-category-default-price',
                saveCategoryDefaultPrice                : '.lp_js_save-category-default-price',
                deleteCategoryDefaultPrice              : '.lp_js_delete-category-default-price',
                categoryDefaultPriceShowElements        : '.lp_js_category-title, .lp_js_revenue-model-label-display, .lp_js_category-default-price-display, .lp_js_edit-category-default-price, .lp_js_delete-category-default-price',
                categoryDefaultPriceEditElements        : '.lp_js_category-default-price-input, .lp_js_revenue-model, .lp_js_save-category-default-price, .lp_js_cancel-editing-category-default-price',

                categoryTitle                           : '.lp_js_category-title',
                categoryDefaultPriceDisplay             : '.lp_js_category-default-price-display',

                selectCategory                          : '.lp_js_select-category',
                categoryDefaultPriceInput               : '.lp_js_category-default-price-input',
                categoryId                              : '.lp_js_category-id',

                // default currency
                defaultCurrencyForm                     : $('#lp_js_default-currency-form'),
                defaultCurrency                         : $('#lp_js_change-default-currency'),
                currency                                : '.lp_js_currency',

                // strings cached for better compression
                editing                                 : 'lp_is_editing',
                unsaved                                 : 'lp_is_unsaved',
                payPerUse                               : 'ppu',
                singleSale                              : 'sis',
                selected                                : 'lp_is-selected',
                disabled                                : 'lp_is-disabled',
            },

            bindEvents = function() {
                // global default price and category default price events ----------------------------------------------
                // validate price and choice of revenue model when switching revenue model
                // (validating the price switches the revenue model if required)
                $('body').on('change', $o.revenueModelInput, function() {
                    validatePrice($(this).parents('form'));
                });

                // validate price and revenue model when entering a price
                // (function is only triggered 800ms after the keyup)
                $('body').on('keyup', $o.priceInput, debounce(function() {
                        validatePrice($(this).parents('form'));
                    }, 800)
                );

                // global default price events -------------------------------------------------------------------------
                // edit
                $o.editGlobalDefaultPrice
                .mousedown(function() {
                    enterEditModeGlobalDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // cancel
                $o.cancelEditingGlobalDefaultPrice
                .mousedown(function() {
                    exitEditModeGlobalDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // save
                $o.saveGlobalDefaultPrice
                .mousedown(function() {
                    saveGlobalDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // category default prices events ----------------------------------------------------------------------
                // add
                $o.addCategory
                .mousedown(function() {
                    addCategoryDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // edit
                $o.categoryDefaultPrices
                .on('click', $o.editCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    editCategoryDefaultPrice($form);
                });

                // cancel
                $o.categoryDefaultPrices
                .on('click', $o.cancelEditingCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    exitEditModeCategoryDefaultPrice($form);
                });

                // save
                $o.categoryDefaultPrices
                .on('click', $o.saveCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    saveCategoryDefaultPrice($form);
                });

                // delete
                $o.categoryDefaultPrices
                .on('click', $o.deleteCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    deleteCategoryDefaultPrice($form);
                });

                // default currency events -----------------------------------------------------------------------------
                // switch default currency
                $o.defaultCurrency
                .change(function() {
                    switchCurrency();
                });
            },

            validatePrice = function($form) {
                var $priceInput = $('.lp_number-input', $form),
                    price       = $priceInput.val(),
                    corrected;

                // strip non-number characters
                price = price.replace(/[^0-9\,\.]/g, '');

                // convert price to proper float value
                if (price.indexOf(',') > -1) {
                    price = parseFloat(price.replace(',', '.')).toFixed(2);
                } else {
                    price = parseFloat(price).toFixed(2);
                }

                // prevent non-number prices
                if (isNaN(price)) {
                    price       = 0;
                    corrected   = true;
                }

                // prevent negative prices
                price = Math.abs(price);

                // correct prices outside the allowed range of 0.05 - 149.49
                if (price > 149.99) {
                    price       = 149.99;
                    corrected   = true;
                } else if (price > 0 && price < 0.05) {
                    price       = 0.05;
                    corrected   = true;
                }

                validateRevenueModel(price, $form);

                // format price with two digits
                price = price.toFixed(2);

                // localize price
                if (lpVars.locale == 'de_DE') {
                    price = price.replace('.', ',');
                }

                // update price input
                $priceInput.val(price);

                return price;
            },

            validateRevenueModel = function(price, $form) {
                var currentRevenueModel = $('input:radio:checked', $form).val(),
                    $payPerUse          = $('.lp_js_revenue-model-input[value=' + $o.payPerUse + ']', $form),
                    $singleSale         = $('.lp_js_revenue-model-input[value=' + $o.singleSale + ']', $form);

                if (price === 0 || (price >= 0.05 && price <= 5)) {
                    // enable Pay-per-Use for 0 and all prices between 0.05 and 5.00 Euro
                    $payPerUse.removeProp('disabled')
                        .parent('label').removeClass($o.disabled);
                } else {
                    // disable Pay-per-Use
                    $payPerUse.prop('disabled', 'disabled')
                        .parent('label').addClass($o.disabled);
                }

                if (price >= 1.49) {
                    // enable Single Sale for prices >= 1.49 Euro (prices > 149.99 Euro are fixed by validatePrice already)
                    $singleSale.removeProp('disabled')
                        .parent('label').removeClass($o.disabled);
                } else {
                    // disable Single Sale
                    $singleSale.prop('disabled', 'disabled')
                        .parent('label').addClass($o.disabled);
                }

                // switch revenue model, if combination of price and revenue model is not allowed
                if (price > 5 && currentRevenueModel == $o.payPerUse) {
                    // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                    $singleSale.prop('checked', 'checked');
                } else if (price < 1.49 && currentRevenueModel == $o.singleSale) {
                    // Single Sale purchases are not allowed for prices < 1.49 Euro
                    $payPerUse.prop('checked', 'checked');
                }

                // highlight current revenue model
                $('label', $form).removeClass($o.selected);
                $('.lp_js_revenue-model-input:checked', $form).parent('label').addClass($o.selected);
            },

            enterEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.hide();
                $o.globalDefaultPriceEditElements.show(0, function() {
                    setTimeout(function() {$o.globalDefaultPriceInput.val($o.globalDefaultPriceDisplay.text()).focus();}, 50);
                });
                $o.globalDefaultPriceForm.addClass($o.editing);
            },

            exitEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.show();
                $o.globalDefaultPriceEditElements.hide();
                $o.globalDefaultPriceForm.removeClass($o.editing);
                // reset value of price input to current global default price
                $o.globalDefaultPriceInput.val($o.globalDefaultPriceDisplay.text());
                // reset revenue model input to current revenue model
                var currentRevenueModel = $o.globalDefaultPriceRevenueModelDisplay.text().toLowerCase();
                $($o.revenueModelLabel, $o.globalDefaultPriceForm).removeClass($o.selected);
                $('.lp_js_revenue-model-input[value=' + currentRevenueModel + ']', $o.globalDefaultPriceForm)
                .prop('checked', 'checked')
                    .parent('label')
                    .addClass($o.selected);
            },

            saveGlobalDefaultPrice = function() {
                // fix invalid prices
                var validatedPrice = validatePrice($o.globalDefaultPriceForm);
                $o.globalDefaultPriceInput.val(validatedPrice);

                $.post(
                    ajaxurl,
                    $o.globalDefaultPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $o.globalDefaultPriceDisplay.html(r.laterpay_global_price);
                            $o.globalDefaultPriceRevenueModelDisplay.text(r.laterpay_price_revenue_model)
                        }
                        setMessage(r.message, r.success);
                        exitEditModeGlobalDefaultPrice();
                    },
                    'json'
                );
            },

            addCategoryDefaultPrice = function() {
                $o.addCategory.fadeOut(250);
                // clone category default price template
                var $form = $o.categoryDefaultPriceTemplate
                            .clone()
                            .removeAttr('id')
                            .appendTo('#lp_js_category-default-prices-list')
                            .fadeIn(250);

                editCategoryDefaultPrice($form);
            },

            editCategoryDefaultPrice = function($form) {
                // exit edit mode of all other category prices
                $('.lp_js_category-default-price-form.lp_is_editing').each(function() {
                    exitEditModeCategoryDefaultPrice($(this), true);
                });

                // initialize edit mode
                $form.addClass($o.editing);
                $($o.categoryDefaultPriceShowElements, $form).hide();
                $o.addCategory.fadeOut(250);
                $($o.categoryDefaultPriceEditElements, $form).show();
                renderCategorySelect($form);
            },

            saveCategoryDefaultPrice = function($form) {
                // fix invalid prices
                var validatedPrice = validatePrice($form);
                $($o.categoryDefaultPriceInput, $form).val(validatedPrice);

                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // update displayed price information
                            $($o.categoryDefaultPriceDisplay, $form).text(r.price);
                            $($o.revenueModelLabelDisplay, $form).text(r.revenue_model);
                            $($o.categoryDefaultPriceInput, $form).val(r.price)
                            $($o.categoryTitle, $form).text(r.category);
                            $($o.categoryId, $form).val(r.category_id);

                            // mark the form as saved
                            $form.removeClass($o.unsaved);
                        }
                        exitEditModeCategoryDefaultPrice($form);
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            exitEditModeCategoryDefaultPrice = function($form, editAnotherCategory) {
                // mark the form as not being edited anymore
                $form.removeClass($o.editing);

                if ($form.hasClass($o.unsaved)) {
                    // remove form, if creating a new category default price has been canceled
                    $form.fadeOut(250, function() {
                        $(this).remove();
                    });
                } else {
                    // hide form, if a new category default price has been saved
                    // or editing an existing category default price has been canceled
                    $($o.categoryDefaultPriceEditElements, $form).hide();
                    $($o.selectCategory, $form).select2('destroy');
                    // reset value of price input to current category default price
                    $($o.categoryDefaultPriceInput, $form).val($($o.categoryDefaultPriceDisplay, $form).text());
                    // reset revenue model input to current revenue model
                    var currentRevenueModel = $($o.revenueModelLabelDisplay, $form).text().toLowerCase();
                    $($o.revenueModelLabel, $form).removeClass($o.selected);
                    $('.lp_js_revenue-model-input[value=' + currentRevenueModel + ']', $form)
                    .prop('checked', 'checked')
                        .parent('label')
                        .addClass($o.selected);
                    // show elements for displaying defined price again
                    $($o.categoryDefaultPriceShowElements, $form).show();
                }

                // show 'Add' button again
                if (!editAnotherCategory) {
                    $o.addCategory.fadeIn(250);
                }
            },

            deleteCategoryDefaultPrice = function($form) {
                $('input[name=form]', $form).val('price_category_form_delete');

                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $form.fadeOut(400, function() {
                                $(this).remove();
                            });
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            formatSelect2Selection = function(data, container) {
                var $form = $(container).parent().parent().parent();
                $('.lp_js_select-category', $form).val(data.text);
                $('.lp_js_category-id', $form).val(data.id);

                return data.text;
            },

            renderCategorySelect = function($form) {
                $($o.selectCategory, $form).select2({
                    allowClear      : true,
                    ajax            : {
                                        url         : ajaxurl,
                                        data        : function(term) {
                                                        return {
                                                            term    : term,
                                                            action  : 'laterpay_pricing'
                                                        };
                                                    },
                                        results     : function(data) {
                                                            var return_data = [];

                                                            $.each( data, function(index) {
                                                                var term = data[ index ];
                                                                return_data.push({
                                                                    id     : term.term_id,
                                                                    text   : term.name
                                                                });
                                                            } );

                                                            return {results: return_data};
                                                        },
                                                        dataType    : 'json'
                                    },
                    initSelection   : function(element, callback) {
                                        var id = $(element).val();
                                        if (id !== '') {
                                            var data = {text: id};
                                            callback(data);
                                        } else {
                                            $.get(
                                                ajaxurl,
                                                {
                                                    term    : '',
                                                    action  : 'laterpay_pricing'
                                                },
                                                function(data) {
                                                    if (data && data[0] !== undefined) {
                                                        var term = data[0];
                                                        callback({text: term.name});
                                                    }
                                                }
                                            );
                                        }
                                    },
                    formatResult    : function(data) {return data.text;},
                    formatSelection : formatSelect2Selection,
                    escapeMarkup    : function(m) {return m;}
                });
            },

            switchCurrency = function() {
                $.post(
                    ajaxurl,
                    $o.defaultCurrencyForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // update all instances of the default currency
                            $($o.currency).html(r.laterpay_currency);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
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
            };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
