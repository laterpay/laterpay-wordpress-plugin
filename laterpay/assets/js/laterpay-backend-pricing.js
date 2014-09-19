(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                // global default price
                globalDefaultPriceForm              : $('#lp_js_global-default-price-form'),
                globalDefaultPriceInput             : $('#lp_js_global-default-price'),
                globalDefaultPriceDisplay           : $('#lp_js_global-default-price-text'),
                editGlobalDefaultPrice              : $('#lp_js_edit-global-default-price'),
                cancelEditingGlobalDefaultPrice     : $('#lp_js_cancel-editing-global-default-price'),
                saveGlobalDefaultPrice              : $('#lp_js_save-global-default-price'),
                globalDefaultPriceShowElements      : $('#lp_js_global-default-price-text, #lp_js_edit-global-default-price'),
                globalDefaultPriceEditElements      : $('#lp_js_global-default-price, #lp_js_cancel-editing-global-default-price, #lp_js_save-global-default-price'),

                // category default price
                categoryDefaultPrices               : $('#lp_js_category-default-prices-list'),
                addCategory                         : $('#lp_js_add-category-default-price'),

                categoryDefaultPriceTemplate        : $('#lp_js_category-default-price-template'),
                categoryDefaultPriceForm            : '.lp_js_category-default-price-form',
                editCategoryDefaultPrice            : '.lp_js_edit-category-default-price',
                cancelEditingCategoryDefaultPrice   : '.lp_js_cancel-editing-category-default-price',
                saveCategoryDefaultPrice            : '.lp_js_save-category-default-price',
                deleteCategoryDefaultPrice          : '.lp_js_delete-category-default-price',
                categoryDefaultPriceShowElements    : '.lp_js_category-title, .lp_js_category-default-price-display, .lp_js_edit-category-default-price, .lp_js_delete-category-default-price',
                categoryDefaultPriceEditElements    : '.lp_js_category-default-price-input, .lp_js_save-category-default-price, .lp_js_cancel-editing-category-default-price',

                categoryTitle                       : '.lp_js_category-title',
                categoryDefaultPriceDisplay         : '.lp_js_category-default-price-display',

                selectCategory                      : '.lp_js_select-category',
                categoryDefaultPriceInput           : '.lp_js_category-default-price-input',
                categoryId                          : '.lp_js_category-id',

                // default currency
                defaultCurrencyForm                 : $('#lp_js_default-currency-form'),
                defaultCurrency                     : $('#lp_js_change-default-currency'),
                currency                            : '.lp_js_currency',

                // strings cached for better compression
                editing                             : 'lp_is_editing',
                unsaved                             : 'lp_is_unsaved',
            },

            bindEvents = function() {
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
                .on('mousedown', $o.editCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    editCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // cancel
                $o.categoryDefaultPrices
                .on('mousedown', $o.cancelEditingCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    exitEditModeCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // save
                $o.categoryDefaultPrices
                .on('mousedown', $o.saveCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    saveCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // delete
                $o.categoryDefaultPrices
                .on('mousedown', $o.deleteCategoryDefaultPrice, function() {
                    var $form = $(this).parents($o.categoryDefaultPriceForm);
                    deleteCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // default currency events -----------------------------------------------------------------------------
                // switch default currency
                $o.defaultCurrency
                .change(function() {
                    switchCurrency();
                });
            },

            validatePrice = function(price) {
                var corrected;

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
                // format price with two digits
                price = price.toFixed(2);

                // localize price
                if (lpVars.locale == 'de_DE') {
                    price = price.replace('.', ',');
                }

                return price;
            },

            enterEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.hide();
                $o.globalDefaultPriceEditElements.show(0, function() {
                    setTimeout(function() {$o.globalDefaultPrice.val($o.globalDefaultPriceDisplay.text()).focus();}, 50);
                });
                $o.globalDefaultPriceForm.addClass($o.editing);
            },

            exitEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.show();
                $o.globalDefaultPriceEditElements.hide();
                $o.globalDefaultPriceForm.removeClass($o.editing);
                $o.globalDefaultPrice.val($o.globalDefaultPriceDisplay.text());
            },

            saveGlobalDefaultPrice = function() {
                // fix invalid prices
                var validatedPrice = validatePrice($o.globalDefaultPriceInput.val());
                $o.globalDefaultPriceInput.val(validatedPrice);

                $.post(
                    ajaxurl,
                    $o.globalDefaultPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $o.globalDefaultPriceDisplay.html(r.laterpay_global_price);
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
                var validatedPrice = validatePrice($($o.categoryDefaultPriceInput, $form).val());
                $($o.categoryDefaultPriceInput, $form).val(validatedPrice);

                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // update displayed price information
                            $($o.categoryDefaultPriceDisplay, $form).text(r.price);
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
                $form.find('input[name=category]').val(data.text);

                return data.text;
            },

            renderCategorySelect = function($form) {
                $($o.selectCategory, $form).select2({
                    allowClear      : true,
                    ajax            : {
                                        url         : ajaxurl,
                                        data        : function(term) {
                                                        return {
                                                            term        : term,
                                                            action      : 'laterpay_pricing',
                                                            category    : $(this).parent().find($o.categoryId).val()
                                                        };
                                                    },
                                        results     : function(data) { return { results: data }; },
                                        dataType    : 'json'
                                    },
                    initSelection   : function(element, callback) {
                                        var id = $(element).val();
                                        if (id !== '') {
                                            var data = { text: id };
                                            callback(data);
                                        }
                                    },
                    formatResult    : function(data) { return data.text; },
                    formatSelection : formatSelect2Selection,
                    escapeMarkup    : function(m) { return m; }
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

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
