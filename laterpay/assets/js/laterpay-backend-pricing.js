(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                // global default price
                globalDefaultPriceForm              : $('#lp_global-price-form'),
                globalDefaultPrice                  : $('#lp_global-default-price'),
                globalDefaultPriceShowElements      : $('#lp_global-price-text, #lp_global-price-form .lp_change-link'),
                globalDefaultPriceEditElements      : $('#lp_global-default-price, #lp_global-price-form .lp_cancel-link, #lp_global-price-form .lp_save-link'),

                // category default price
                categoryDefaultPrices               : $('#lp_category-prices'),
                addCategory                         : $('#lp_add-category-link'),
                categoryDefaultPriceShowElements    : '.lp_category-title, .lp_category-price, .lp_change-link, .lp_delete-link',
                categoryDefaultPriceEditElements    : '.lp_number-input, .lp_save-link, .lp_cancel-link',

                // default currency
                defaultCurrencyForm                 : $('#lp_currency-form'),
                defaultCurrency                     : $('#lp_currency-select'),

                // strings cached for better compression
                editing                             : 'lp_editing',
                unsaved                             : 'lp_unsaved',
            },

            bindEvents = function() {
                // global default price events -------------------------------------------------------------------------
                // edit
                $('.lp_change-link', $o.globalDefaultPriceForm)
                .mousedown(function() {
                    enterEditModeGlobalDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // cancel
                $('.lp_cancel-link', $o.globalDefaultPriceForm)
                .mousedown(function() {
                    exitEditModeGlobalDefaultPrice();
                })
                .click(function(e) {e.preventDefault();});

                // save
                $('.lp_save-link', $o.globalDefaultPriceForm)
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
                .on('mousedown', '.lp_change-link', function() {
                    var $form = $(this).parents('.lp_category-price-form');
                    editCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // cancel
                $o.categoryDefaultPrices
                .on('mousedown', '.lp_cancel-link', function() {
                    var $form = $(this).parents('.lp_category-price-form');
                    exitEditModeCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // save
                $o.categoryDefaultPrices
                .on('mousedown', '.lp_save-link', function() {
                    var $form = $(this).parents('.lp_category-price-form');
                    saveCategoryDefaultPrice($form);
                })
                .on('click', function(e) {e.preventDefault();});

                // delete
                $o.categoryDefaultPrices
                .on('mousedown', '.lp_delete-link', function() {
                    var $form = $(this).parents('.lp_category-price-form');
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
                    price = 0;
                    corrected = true;
                }
                // prevent negative prices
                price = Math.abs(price);
                // correct prices outside the allowed range of 0.05 - 149.49
                if (price > 149.99) {
                    price = 149.99;
                    corrected = true;
                } else if (price > 0 && price < 0.05) {
                    price = 0.05;
                    corrected = true;
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
                    setTimeout(function() {$o.globalDefaultPrice.val($('#lp_global-price-text').text()).focus();}, 50);
                });
                $o.globalDefaultPriceForm.addClass($o.editing);
            },

            exitEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.show();
                $o.globalDefaultPriceEditElements.hide();
                $o.globalDefaultPriceForm.removeClass($o.editing);
                $o.globalDefaultPrice.val($('#lp_global-price-text').text());
            },

            saveGlobalDefaultPrice = function() {
                $o.globalDefaultPrice.val(validatePrice($o.globalDefaultPrice.val()));
                $.post(
                    ajaxurl,
                    $o.globalDefaultPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $('#lp_global-price-text').html(r.laterpay_global_price);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
                exitEditModeGlobalDefaultPrice();
            },

            addCategoryDefaultPrice = function() {
                $o.addCategory.fadeOut(250);

                // clone category default price template
                var $form = $('#category-price-form-template')
                            .clone()
                            .removeAttr('id')
                            .appendTo('#lp_category-prices')
                            .fadeIn(250);

                editCategoryDefaultPrice($form);
            },

            editCategoryDefaultPrice = function($form) {
                // exit edit mode of all other category prices
                $('.lp_category-price-form.lp_editing').each(function() {
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
                $('.lp_input.lp_number-input', $form).val(validatePrice($('.lp_input.lp_number-input', $form).val()));

                $form.removeClass($o.lp_unsaved);
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $('.lp_category-price', $form).text(r.price);
                            $('.lp_number-input', $form).val(r.price)
                            $('.lp_category-title', $form).text(r.category);
                            $('input[name=category_id]', $form).val(r.category_id);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );

                exitEditModeCategoryDefaultPrice($form);
            },

            exitEditModeCategoryDefaultPrice = function($form, editAnotherCategory) {
                $form.removeClass($o.editing);

                if ($form.hasClass($o.unsaved)) {
                    // remove form, if creating a new category default price has been canceled
                    $form.fadeOut(250, function() {
                        $(this).remove();
                    });
                } else {
                    // hide form, if editing an existing category default price has been canceled
                    $($o.categoryDefaultPriceEditElements, $form).hide();
                    $('.lp_category-select', $form).select2('destroy');
                    $($o.categoryDefaultPriceShowElements, $form).show();
                    // reset value of price input to current category default price
                    $('.lp_number-input', $form).val($('.lp_category-price', $form).text());
                }
                if (!editAnotherCategory) {
                    $o.addCategory.fadeIn(250);
                }
            },

            deleteCategoryDefaultPrice = function($form) {
                $('input[name="form"]', $form).val('price_category_form_delete');

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
                $('.lp_category-select', $form).select2({
                    allowClear      : true,
                    ajax            : {
                                        url         : ajaxurl,
                                        data        : function(term) {
                                                        return {
                                                            term        : term,
                                                            action      : 'laterpay_pricing',
                                                            category    : $(this).parent().find('input[name=category_id]').val()
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
                            $('.lp_currency').html(r.laterpay_currency);
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
