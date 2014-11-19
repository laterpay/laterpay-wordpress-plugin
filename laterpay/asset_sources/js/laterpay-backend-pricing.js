(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                revenueModel                            : '.lp_js_revenueModel',
                revenueModelLabel                       : '.lp_js_revenueModel_label',
                revenueModelLabelDisplay                : '.lp_js_revenueModel_labelDisplay',
                revenueModelInput                       : '.lp_js_revenueModel_input',
                priceInput                              : '.lp_js_priceInput',

                // global default price
                globalDefaultPriceForm                  : $('#lp_js_globalDefaultPrice_form'),
                globalDefaultPriceInput                 : $('#lp_js_globalDefaultPrice'),
                globalDefaultPriceDisplay               : $('#lp_js_globalDefaultPrice_text'),
                globalDefaultPriceRevenueModelDisplay   : $('#lp_js_globalDefaultPrice_revenueModelLabel'),
                editGlobalDefaultPrice                  : $('#lp_js_editGlobalDefaultPrice'),
                cancelEditingGlobalDefaultPrice         : $('#lp_js_cancelEditingGlobalDefaultPrice'),
                saveGlobalDefaultPrice                  : $('#lp_js_saveGlobalDefaultPrice'),
                globalDefaultPriceShowElements          : $('#lp_js_globalDefaultPrice_text,' +
                                                            ' #lp_js_editGlobalDefaultPrice,' +
                                                            '#lp_js_globalDefaultPrice_revenueModelLabel'),
                globalDefaultPriceEditElements          : $('#lp_js_globalDefaultPrice,' +
                                                            '#lp_js_globalDefaultPrice_revenueModel,' +
                                                            '#lp_js_cancelEditingGlobalDefaultPrice,' +
                                                            '#lp_js_saveGlobalDefaultPrice'),

                // category default price
                categoryDefaultPrices                   : $('#lp_js_categoryDefaultPrice_list'),
                addCategory                             : $('#lp_js_addCategoryDefaultPrice'),

                categoryDefaultPriceTemplate            : $('#lp_js_categoryDefaultPrice_template'),
                categoryDefaultPriceForm                : '.lp_js_categoryDefaultPrice_form',
                editCategoryDefaultPrice                : '.lp_js_editCategoryDefaultPrice',
                cancelEditingCategoryDefaultPrice       : '.lp_js_cancelEditingCategoryDefaultPrice',
                saveCategoryDefaultPrice                : '.lp_js_saveCategoryDefaultPrice',
                deleteCategoryDefaultPrice              : '.lp_js_deleteCategoryDefaultPrice',
                categoryDefaultPriceShowElements        : '.lp_js_categoryDefaultPrice_categoryTitle,' +
                                                            '.lp_js_revenueModel_labelDisplay,' +
                                                            '.lp_js_categoryDefaultPrice_display,' +
                                                            '.lp_js_editCategoryDefaultPrice,' +
                                                            '.lp_js_deleteCategoryDefaultPrice',
                categoryDefaultPriceEditElements        : '.lp_js_categoryDefaultPrice_input,' +
                                                            '.lp_js_revenueModel,' +
                                                            '.lp_js_saveCategoryDefaultPrice,' +
                                                            '.lp_js_cancelEditingCategoryDefaultPrice',

                categoryTitle                           : '.lp_js_categoryDefaultPrice_categoryTitle',
                categoryDefaultPriceDisplay             : '.lp_js_categoryDefaultPrice_display',

                selectCategory                          : '.lp_js_selectCategory',
                categoryDefaultPriceInput               : '.lp_js_categoryDefaultPrice_input',
                categoryId                              : '.lp_js_categoryDefaultPrice_categoryId',

                // time passes
                addTimePass                             : $('#lp_js_addTimePass'),

                    passPayType                             : $('.lp_toggle_input'),
                    pass                                    : $('#lp_js_togglePassPayType'),
                    colorPicker                             : $('.lp_js_colorInput'),

                // bulk price editor
                bulkPriceForm                           : $('#lp_js_bulkPriceEditor_form'),
                bulkPriceFormHiddenField                : $('#lp_js_bulkPriceEditor_hiddenFormInput'),
                bulkPriceOperationIdHiddenField         : $('#lp_js_bulkPriceEditor_hiddenIdInput'),
                bulkPriceMessageHiddenField             : $('#lp_js_bulkPriceEditor_hiddenMessageInput'),
                bulkPriceAction                         : $('#lp_js_selectBulkAction'),
                bulkPriceObjects                        : $('#lp_js_selectBulkObjects'),
                bulkPriceObjectsCategory                : $('#lp_js_selectBulkObjectsCategory'),
                bulkPriceObjectsCategoryWithPrice       : $('#lp_js_selectBulkObjectsCategoryWithPrice'),
                bulkPriceChangeAmountPreposition        : $('#lp_js_bulkPriceEditor_amountPreposition'),
                bulkPriceChangeAmount                   : $('#lp_js_setBulkChangeAmount'),
                bulkPriceChangeUnit                     : $('#lp_js_selectBulkChangeUnit'),
                bulkPriceSubmit                         : $('#lp_js_applyBulkOperation'),
                bulkSaveOperationLink                   : $('#lp_js_saveBulkOperation'),
                bulkDeleteOperationLink                 : '.lp_js_deleteSavedBulkOperation',
                bulkApplySavedOperationLink             : '.lp_js_applySavedBulkOperation',

                // default currency
                defaultCurrencyForm                     : $('#lp_js_defaultCurrency_form'),
                defaultCurrency                         : $('#lp_js_changeDefaultCurrency'),
                currency                                : '.lp_js_currency',

                // strings cached for better compression
                editing                                 : 'lp_is-editing',
                unsaved                                 : 'lp_is-unsaved',
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

                // time passes events ----------------------------------------------------------------------------------
                // add
                $o.addTimePass
                .toggle(
                    function(e) {
                        togglePassForm('show', 0);
                        e.preventDefault();
                    },
                    function(e) {
                        togglePassForm('hide', 0);
                        e.preventDefault();
                    }
                );

                // ?????
                $o.passPayType
                .change(function() {
                    togglePassPayMode();
                });

                // ?????
                $('.lp_timePass .lp_changeLink')
                .click(function(e) {
                    var pass_id = $(this).closest('.lp_timePass').attr('data-pass-id');
                    togglePassForm('show', pass_id);
                    e.preventDefault();
                });

                // cancel
                $('.lp_timePass .lp_cancelLink')
                .click(function(e) {
                    togglePassForm('hide', 0);
                    e.preventDefault();
                });

                // save
                $('.lp_timePass .lp_saveLink')
                .click(function(e) {
                    savePassForm();
                    e.preventDefault();
                });

                // delete
                $('.lp_timePass .lp_deleteLink')
                .click(function(e) {
                    var pass_id = $(this).closest('.lp_timePass').attr('data-pass-id');
                    removePass(e, pass_id);
                    e.preventDefault();
                });

                // bulk price editor events ----------------------------------------------------------------------------
                // select action or objects
                $o.bulkPriceAction.add($o.bulkPriceObjects)
                .on('change', function() {
                    handleBulkEditorSettingsUpdate($o.bulkPriceAction.val(), $o.bulkPriceObjects.val());
                });

                // update displayed price of the category to be reset
                $o.bulkPriceObjectsCategoryWithPrice
                .on('change', function() {
                    $o.bulkPriceChangeAmountPreposition.text(
                        lpVars.i18n.toCategoryDefaultPrice + ' ' +
                        $o.bulkPriceObjectsCategoryWithPrice.find(':selected').attr('data-price') + ' ' +
                        lpVars.defaultCurrency
                    );
                });

                // execute bulk operation
                $o.bulkPriceForm
                .on('submit', function(e) {
                    $o.bulkPriceFormHiddenField.val('bulk_price_form');
                    $o.bulkPriceOperationIdHiddenField.val(undefined);
                    $o.bulkPriceMessageHiddenField.val(undefined);
                    applyBulkOperation();
                    e.preventDefault();
                });

                // save bulk operation for re-use
                $o.bulkSaveOperationLink
                .mousedown(function() {
                    saveBulkOperation();
                })
                .click(function(e) {e.preventDefault();});

                // execute saved bulk operation
                $('body')
                .on('mousedown', $o.bulkApplySavedOperationLink, function() {
                    applySavedBulkOperation($(this).parent());
                })
                .on('click', $o.bulkApplySavedOperationLink, function(e) {e.preventDefault();});

                // delete saved bulk operation
                $('body')
                .on('mousedown', $o.bulkDeleteOperationLink, function() {
                    deleteSavedBulkOperation($(this).parent());
                })
                .on('click', $o.bulkDeleteOperationLink, function(e) {e.preventDefault();});

                // default currency events -----------------------------------------------------------------------------
                // switch default currency
                $o.defaultCurrency
                .change(function() {
                    switchCurrency();
                });
            },

            savePassForm = function() {
                togglePassForm('hide', 0);

                $.post(
                    ajaxurl,
                    $('#lp_js_timePassForm').serializeArray(),
                    function(r) {
                        if (r.success) {
                            window.location.reload();
                        } else {
                            togglePassForm('show', 0);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            removePass = function(e, pass_id) {
                $.post(
                    ajaxurl,
                    {
                        action  : 'laterpay_pricing',
                        form    : 'pass_delete',
                        pass_id : pass_id,
                    },
                    function(r) {
                        if (r.success) {
                            window.location.reload();
                        }else{
                            setMessage(r.message, r.success);
                        }
                    },
                    'json'
                );
                e.preventDefault();
                return false;
            },

            togglePassForm = function(todo, pass_id) {
                var editor          = $('.lp_passes_editor'),
                    passContainer   = $('.lp_timePass[data-pass-id=' + pass_id + ']');
                $('.lp_timePass .lp_changeLink').show();
                $('.lp_timePass .lp_saveLink').hide();
                $('.lp_timePass .lp_cancelLink').hide();
                $('.lp_timePass .lp_deleteLink').show();

                if (todo === 'show') {
                    fillPassForm(pass_id);
                    passContainer.show();
                    $(passContainer).find('.lp_timePass_editorContainer').append(editor.show());
                    passContainer.find('.lp_changeLink').hide();
                    passContainer.find('.lp_saveLink').show();
                    passContainer.find('.lp_cancelLink').show();
                    passContainer.find('.lp_deleteLink').hide();
                    // new pass form has to be hidden by default
                    if ( pass_id > 0 ) {
                        $('.lp_timePass[data-pass-id=0]').hide();
                    }
                } else if (todo === 'hide') {
                    passContainer.hide();
                    $o.addTimePass.after(editor.hide());
                }
            },

            fillPassForm = function(pass_id) {
                if (!passes_array || !passes_array[pass_id]) {
                    return;
                } else {
                    var pass = passes_array[pass_id];
                }

                $('.lp_passes_editor input, .lp_passes_editor select, .lp_passes_editor textarea')
                .each(function(i, v) {
                    var name = $(v).attr('name');
                    if (name !== '' && pass[name]) {
                        $(v).val(pass[name]);
                    }
                });
            },

            togglePassPayMode = function() {
                var $toggle                 = $o.passPayType,
                    $input                  = $('#lp_js_timePass_toggleRevenueModel'),
                    payLater                = 'later',
                    payImmediately          = 'immediately',
                    hasPayMode              = $toggle.prop('checked');

                if (hasPayMode) {
                    $input.val(payImmediately);
                } else {
                    $input.val(payLater);
                }
            },

            validatePrice = function($form) {
                var $priceInput = $('.lp_numberInput', $form),
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
                if (lpVars.locale === 'de_DE') {
                    price = price.replace('.', ',');
                }

                // update price input
                $priceInput.val(price);

                return price;
            },

            validateRevenueModel = function(price, $form) {
                var currentRevenueModel = $('input:radio:checked', $form).val(),
                    $payPerUse          = $('.lp_js_revenueModel_input[value=' + $o.payPerUse + ']', $form),
                    $singleSale         = $('.lp_js_revenueModel_input[value=' + $o.singleSale + ']', $form);

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
                    // enable Single Sale for prices >= 1.49 Euro
                    // (prices > 149.99 Euro are fixed by validatePrice already)
                    $singleSale.removeProp('disabled')
                        .parent('label').removeClass($o.disabled);
                } else {
                    // disable Single Sale
                    $singleSale.prop('disabled', 'disabled')
                        .parent('label').addClass($o.disabled);
                }

                // switch revenue model, if combination of price and revenue model is not allowed
                if (price > 5 && currentRevenueModel === $o.payPerUse) {
                    // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                    $singleSale.prop('checked', 'checked');
                } else if (price < 1.49 && currentRevenueModel === $o.singleSale) {
                    // Single Sale purchases are not allowed for prices < 1.49 Euro
                    $payPerUse.prop('checked', 'checked');
                }

                // highlight current revenue model
                $('label', $form).removeClass($o.selected);
                $('.lp_js_revenueModel_input:checked', $form).parent('label').addClass($o.selected);
            },

            enterEditModeGlobalDefaultPrice = function() {
                $o.globalDefaultPriceShowElements.hide();
                $o.globalDefaultPriceEditElements.show(0, function() {
                    setTimeout(function() {
                        $o.globalDefaultPriceInput.val($o.globalDefaultPriceDisplay.text()).focus();
                    }, 50);
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
                $('.lp_js_revenueModel_input[value=' + currentRevenueModel + ']', $o.globalDefaultPriceForm)
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
                            $o.globalDefaultPriceRevenueModelDisplay.text(r.laterpay_price_revenue_model);
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
                            .appendTo('#lp_js_categoryDefaultPrice_list')
                            .fadeIn(250);

                editCategoryDefaultPrice($form);
            },

            editCategoryDefaultPrice = function($form) {
                // exit edit mode of all other category prices
                $('.lp_js_categoryDefaultPrice_form.lp_is-editing').each(function() {
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
                            $($o.categoryDefaultPriceInput, $form).val(r.price);
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
                    $('.lp_js_revenueModel_input[value=' + currentRevenueModel + ']', $form)
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
                $('.lp_js_selectCategory', $form).val(data.text);
                $('.lp_js_categoryDefaultPrice_categoryId', $form).val(data.id);

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
                                                    console.log(data);
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
              var timer;
              return function () {
                var context = this,
                    args    = arguments;

                clearTimeout(timer);

                timer = setTimeout(function() {
                  fn.apply(context, args);
                }, delay);
              };
            },

            applyBulkOperation = function(data) {
                $.post(
                    ajaxurl,
                    data || $o.bulkPriceForm.serializeArray(),
                    function(r) {
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            addOptionInCategory = function(categoryToBeSelected) {
                $o.bulkPriceObjects.append($('<option>', {
                    value    : 'in_category',
                    text     : lpVars.inCategoryLabel,
                    selected : !!categoryToBeSelected   // coerce categoryToBeSelected to Boolean
                }));
            },

            handleBulkEditorSettingsUpdate = function(action, selector) {
                // hide some fields if needed, change separator, and add percent unit option
                var showCategory = ( selector === 'in_category' );

                // clear currency options
                $o.bulkPriceChangeUnit
                    .find('option')
                    .each(function() {
                        if ($(this).text() === '%') {
                            $(this).remove();
                        }
                    })
                        .end()
                        .addClass($o.disabled);

                // clear object options
                $o.bulkPriceObjects
                    .find('option')
                    .each(function() {
                        if ($(this).val() === 'in_category') {
                            $(this).remove();
                        }
                    });

                // hide some of bulk price editor settings
                $o.bulkPriceObjectsCategory.prop('disabled', true).hide();
                $o.bulkPriceObjectsCategoryWithPrice.prop('disabled', true).hide();
                $o.bulkPriceChangeAmountPreposition.hide();
                $o.bulkPriceChangeAmount.prop('disabled', true).hide();
                $o.bulkPriceChangeUnit.prop('disabled', true).hide();

                switch (action) {
                    case 'set':
                        $o.bulkPriceChangeAmountPreposition.show().text(lpVars.i18n.to);
                        $o.bulkPriceChangeAmount.prop('disabled', false).show();
                        $o.bulkPriceChangeUnit.show();
                        break;

                    case 'increase':
                    case 'reduce':
                        $o.bulkPriceChangeAmountPreposition.show().text(lpVars.i18n.by);
                        $o.bulkPriceChangeAmount.prop('disabled', false).show();
                        $o.bulkPriceChangeUnit.prop('disabled', false).show();
                        $o.bulkPriceChangeUnit
                        .removeClass($o.disabled)
                        .append($('<option>', {
                            value   : 'percent',
                            text    :  '%'
                        }));
                        break;

                    case 'free':
                        if ($o.bulkPriceObjectsCategory.length) {
                            addOptionInCategory(showCategory);
                            if (showCategory) {
                                $o.bulkPriceObjectsCategory.prop('disabled', false).show();
                            }
                        }
                        break;

                    case 'reset':
                        $o.bulkPriceChangeAmountPreposition.text(
                            lpVars.i18n.toGlobalDefaultPrice + ' ' +
                                lpVars.globalDefaultPrice + ' ' +
                                lpVars.defaultCurrency
                        );
                        if ($o.bulkPriceObjectsCategoryWithPrice.length) {
                            addOptionInCategory(showCategory);
                            if (showCategory) {
                                $o.bulkPriceObjectsCategoryWithPrice.prop('disabled', false).show().change();
                            }
                        }
                        $o.bulkPriceChangeAmountPreposition.show();
                        break;

                    default:
                        break;
                }
            },

            createSavedBulkRow = function( bulkOperationId, bulkMessage ) {
                var operation = '<p class="lp_bulkOperation" data-value="' +  bulkOperationId + '">' +
                                '<a href="#" class="lp_js_deleteSavedBulkOperation lp_editLink lp_deleteLink" data-icon="g">' + lpVars.i18n.delete + '</a>' +
                                '<a href="#" class="lp_js_applySavedBulkOperation button button-primary lp_m-l2">' + lpVars.i18n.updatePrices + '</a>' +
                                '<span>' + bulkMessage + '</span>' +
                            '</p>';

                $o.bulkPriceForm.after(operation);
            },

            saveBulkOperation = function() {
                var action      = ($.trim($o.bulkPriceAction.find('option:selected').text()) === 'Make free') ?
                                    'Make' :
                                    $o.bulkPriceAction.find('option:selected').text(),
                    objects     = $o.bulkPriceObjects.find('option:selected').text().toLowerCase(),
                    category    = ($.trim($o.bulkPriceObjects.find('option:selected').text()) === 'All posts') ?
                                    '' :
                                    '"' + $.trim($o.bulkPriceObjectsCategory.find('option:selected').text()) + '"',
                    preposition = ($.trim($o.bulkPriceAction.find('option:selected').text()) === 'Make free') ?
                                    '' :
                                    $o.bulkPriceChangeAmountPreposition.text(),
                    amount      = ($.trim($o.bulkPriceAction.find('option:selected').text()) === 'Make free') ?
                                    '' :
                                    $o.bulkPriceChangeAmount.val() + $o.bulkPriceChangeUnit.find('option:selected').text(),
                    actionExt   = ($.trim($o.bulkPriceAction.find('option:selected').text()) === 'Make free') ?
                                    'free' :
                                    '',
                    description = [action, objects, category, preposition, amount, actionExt];

                description = $.trim(description.join(' ').replace(/\s+/g, ' '));

                $o.bulkPriceFormHiddenField.val('bulk_price_form_save');
                $o.bulkPriceOperationIdHiddenField.val(undefined);
                $o.bulkPriceMessageHiddenField.val(description);

                $.post(
                    ajaxurl,
                    $o.bulkPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // create new saved row
                            createSavedBulkRow(r.data.id, r.data.message);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            applySavedBulkOperation = function($item) {
                $o.bulkPriceFormHiddenField.val('bulk_price_form');
                $o.bulkPriceOperationIdHiddenField.val($item.data('value'));

                applyBulkOperation();
            },

            deleteSavedBulkOperation = function($item) {
                $o.bulkPriceFormHiddenField.val('bulk_price_form_delete');
                $o.bulkPriceOperationIdHiddenField.val($item.data('value'));

                $item.fadeOut(250);

                $.post(
                    ajaxurl,
                    $o.bulkPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            $item.remove();
                        } else {
                            $item.fadeIn(250);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            initializePage = function() {
                bindEvents();

                // trigger change event of bulk price editor on page load
                $o.bulkPriceAction.change();

                //add a color picker
                $o.colorPicker.wpColorPicker({ defaultColor: false,
                                               //change: function(event, ui){},
                                               //clear: function() {},
                                               hide: true,
                                               palettes: false,
                                             });
                $('.wp-color-result').attr('title','');
            };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
