(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                revenueModel                            : '.lp_js_revenueModel',
                revenueModelLabel                       : '.lp_js_revenueModel_label',
                revenueModelLabelDisplay                : '.lp_js_revenueModel_labelDisplay',
                revenueModelInput                       : '.lp_js_revenueModel_input',
                priceInput                              : '.lp_js_priceInput',

                // enabled revenue models
                purchaseModeForm                        : $('#lp_js_changePurchaseModeForm'),
                purchaseModeInput                       : $('.lp_js_onlyTimePassPurchaseModeInput'),
                timePassOnlyHideElements                : $('.lp_js_hideInTimePassOnlyMode'),

                // global default price
                globalDefaultPriceForm                  : $('#lp_js_globalDefaultPrice_form'),
                globalDefaultPriceInput                 : $('#lp_js_globalDefaultPrice'),
                globalDefaultPriceDisplay               : $('#lp_js_globalDefaultPrice_text'),
                globalDefaultPriceRevenueModelDisplay   : $('#lp_js_globalDefaultPrice_revenueModelLabel'),
                editGlobalDefaultPrice                  : $('#lp_js_editGlobalDefaultPrice'),
                cancelEditingGlobalDefaultPrice         : $('#lp_js_cancelEditingGlobalDefaultPrice'),
                saveGlobalDefaultPrice                  : $('#lp_js_saveGlobalDefaultPrice'),
                globalDefaultPriceShowElements          : $('#lp_js_globalDefaultPrice_text,' +
                                                            '#lp_js_editGlobalDefaultPrice,' +
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
                timePassEditor                          : $('.lp_js_timePassEditor'),
                timePassTemplate                        : $('#lp_js_timePassTemplate'),
                timePassWrapper                         : '.lp_js_timePassWrapper',
                timePassFormTemplate                    : $('#lp_js_timePassFormTemplate'),
                timePassFormId                          : 'lp_js_timePassForm',
                timePassForm                            : '.lp_js_timePassEditor_form',
                timePassDuration                        : '.lp_js_switchTimePassDuration',
                timePassDurationClass                   : 'lp_js_switchTimePassDuration',
                timePassPeriod                          : '.lp_js_switchTimePassPeriod',
                timePassPeriodClass                     : 'lp_js_switchTimePassPeriod',
                timePassScope                           : '.lp_js_switchTimePassScope',
                timePassScopeClass                      : 'lp_js_switchTimePassScope',
                timePassScopeCategory                   : '.lp_js_switchTimePassScopeCategory',
                timePassScopeCategoryClass              : 'lp_js_switchTimePassScopeCategory',
                timePassCategoryId                      : '.lp_js_timePassCategoryId',
                timePassCategoryWrapper                 : '.lp_js_timePassCategoryWrapper',
                timePassTitle                           : '.lp_js_timePassTitleInput',
                timePassTitleClass                      : 'lp_js_timePassTitleInput',
                timePassPrice                           : '.lp_js_timePassPriceInput',
                timePassPriceClass                      : 'lp_js_timePassPriceInput',
                timePassRevenueModel                    : '.lp_js_timePassRevenueModelInput',
                timePassDescription                     : '.lp_js_timePassDescriptionTextarea',
                timePassDescriptionClass                : 'lp_js_timePassDescriptionTextarea',
                timePassPreviewTitle                    : '.lp_js_timePassPreviewTitle',
                timePassPreviewDescription              : '.lp_js_timePassPreviewDescription',
                timePassPreviewValidity                 : '.lp_js_timePassPreviewValidity',
                timePassPreviewAccess                   : '.lp_js_timePassPreviewAccess',
                timePassPreviewPrice                    : '.lp_js_timePassPreviewPrice',
                timePassId                              : '.lp_js_timePassId',
                landingPageInput                        : '.lp_js_landingPageInput',
                landingPageSave                         : '#lp_js_landingPageSave',
                landingPageForm                         : $('#lp_js_landingPageForm'),

                // vouchers
                voucherPriceInput                       : '.lp_js_voucherPriceInput',
                generateVoucherCode                     : '.lp_js_generateVoucherCode',
                voucherDeleteLink                       : '.lp_js_deleteVoucher',
                voucherEditor                           : '.lp_js_voucherEditor',
                voucherHiddenPassId                     : '#lp_js_timePassEditor_hiddenPassId',
                voucherPlaceholder                      : '.lp_js_voucherPlaceholder',
                voucherList                             : '.lp_js_voucherList',
                voucher                                 : '.lp_js_voucher',
                voucherTimesRedeemed                    : '.lp_js_voucherTimesRedeemed',

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
                hidden                                  : 'lp_u_hide',
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

                // enabled revenue models events -----------------------------------------------------------------------
                // change
                $o.purchaseModeInput
                .on('change', function() {
                    changePurchaseMode($o.purchaseModeForm);
                });

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
                .mousedown(function() {
                    addTimePass();
                })
                .click(function(e) {e.preventDefault();});

                // edit
                $o.timePassEditor
                .on('mousedown', '.lp_js_editTimePass', function() {
                    editTimePass($(this).parents($o.timePassWrapper));
                })
                .on('click', '.lp_js_editTimePass' , function(e) {e.preventDefault();});

                // toggle revenue model
                $o.timePassEditor
                .on('change', $o.timePassRevenueModel, function() {
                    toggleTimePassRevenueModel($(this).parents('form'));
                });

                // change duration
                $o.timePassEditor
                .on('change', $o.timePassDuration, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // change period
                $o.timePassEditor
                .on('change', $o.timePassPeriod, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // change scope
                $o.timePassEditor
                .on('change', $o.timePassScope, function() {
                    changeTimePassScope($(this));
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                $o.timePassEditor
                .on('change', $o.timePassScopeCategory, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // update time pass configuration
                $o.timePassEditor
                .on('input', [$o.timePassTitle, $o.timePassDescription].join(), function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // set price
                $o.timePassEditor
                .on('keyup', $o.timePassPrice, debounce(function() {
                        validatePrice($(this).parents('form'), false, $(this));
                        updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                    }, 800)
                );

                // cancel
                $o.timePassEditor
                .on('click', '.lp_js_cancelEditingTimePass', function(e) {
                    cancelEditingTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // save
                $o.timePassEditor
                .on('click', '.lp_js_saveTimePass', function(e) {
                    saveTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // delete
                $o.timePassEditor
                .on('click', '.lp_js_deleteTimePass', function(e) {
                    deleteTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // flip
                $o.timePassEditor
                .on('mousedown', '.lp_js_flipTimePass', function() {
                    flipTimePass(this);
                })
                .on('click', '.lp_js_flipTimePass', function(e) {e.preventDefault();});

                // set voucher price
                $o.timePassEditor
                .on('keyup', $o.voucherPriceInput, debounce(function() {
                        validatePrice($(this).parents('form'), true, $(this));
                    }, 800)
                );

                // generate voucher code
                $o.timePassEditor
                .on('mousedown', $o.generateVoucherCode, function() {
                    generateVoucherCode($(this).parents($o.timePassWrapper));
                })
                .on('click', $o.generateVoucherCode, function(e) {
                    e.preventDefault();
                });

                // delete voucher code
                $o.timePassEditor
                .on('click', $o.voucherDeleteLink, function(e) {
                    deleteVoucher($(this).parent());
                    e.preventDefault();
                });

                $o.landingPageForm
                .on('click', $o.landingPageSave, function(e) {
                    saveLandingPage($o.landingPageForm);
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

            validatePrice = function($form, invalidPrice, $input) {
                var $priceInput = $input ? $input : $('.lp_numberInput', $form),
                    price       = $priceInput.val();

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
                }

                // prevent negative prices
                price = Math.abs(price);

                if (!invalidPrice) {
                    // correct prices outside the allowed range of 0.05 - 149.49
                    if (price > 149.99) {
                        price       = 149.99;
                    } else if (price > 0 && price < 0.05) {
                        price       = 0.05;
                    }

                    validateRevenueModel(price, $form);
                }

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
                var currentRevenueModel;

                // for passes
                if ($form.hasClass('lp_js_timePassEditor_form')) {
                    var $toggle         = $($o.timePassRevenueModel, $form),
                        hasRevenueModel = $toggle.prop('checked');

                    currentRevenueModel = hasRevenueModel ? $o.singleSale : $o.payPerUse;

                    // switch revenue model, if combination of price and revenue model is not allowed
                    if (price > 5 && currentRevenueModel === $o.payPerUse) {
                        // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                        $toggle.prop('checked', true);
                    } else if (price < 1.49 && currentRevenueModel === $o.singleSale) {
                        // Single Sale purchases are not allowed for prices < 1.49 Euro
                        $toggle.prop('checked', false);
                    }
                // for category price and global price
                } else {
                    var $payPerUse          = $('.lp_js_revenueModel_input[value=' + $o.payPerUse + ']', $form),
                        $singleSale         = $('.lp_js_revenueModel_input[value=' + $o.singleSale + ']', $form);

                    currentRevenueModel = $('input:radio:checked', $form).val();

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
                }
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
                renderCategorySelect(
                    $form,
                    $o.selectCategory,
                    'laterpay_get_categories_with_price',
                    formatSelect2Selection
                );
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

            formatSelect2TimePass = function(data, container) {
                var $form = $(container).parents('form');

                if (data.id) {
                    $($o.timePassCategoryId, $form).val(data.id);
                }
                $($o.timePassScopeCategory, $form).val(data.text);

                return data.text;
            },

            renderCategorySelect = function($form, selector, form, format_func) {
                $(selector, $form).select2({
                    allowClear      : true,
                    ajax            : {
                                        url         : ajaxurl,
                                        data        : function(term) {
                                                        return {
                                                            form    : form,
                                                            term    : term,
                                                            action  : 'laterpay_pricing'
                                                        };
                                                    },
                                        results     : function(data) {
                                                        var return_data = [];

                                                        $.each(data, function(index) {
                                                            var term = data[ index ];
                                                            return_data.push({
                                                                id     : term.term_id,
                                                                text   : term.name
                                                            });
                                                        });

                                                        return {results: return_data};
                                                    },
                                        dataType    : 'json',
                                        type: 'POST'
                                    },
                    initSelection   : function(element, callback) {
                                        var id = $(element).val();
                                        if (id !== '') {
                                            var data = {text: id};
                                            callback(data);
                                        } else {
                                            $.post(
                                                ajaxurl,
                                                {
                                                    form    : form,
                                                    term    : '',
                                                    action  : 'laterpay_pricing'
                                                },
                                                function(data) {
                                                    if (data && data[0] !== undefined) {
                                                        var term = data[0];
                                                        callback({id: term.term_id, text: term.name});
                                                    }
                                                }
                                            );
                                        }
                                    },
                    formatResult    : function(data) {return data.text;},
                    formatSelection : format_func,
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

            addTimePass = function() {
                // hide "add time pass" button
                $o.addTimePass.fadeOut(250);

                // prepend cloned time pass template to time pass editor
                $o.timePassEditor.prepend($o.timePassTemplate.clone().removeAttr('id'));
                var $timePass = $('.lp_js_timePassWrapper', $o.timePassEditor).first();
                $($o.timePassForm, $timePass).attr('id', $o.timePassFormId).addClass($o.unsaved);

                populateTimePassForm($timePass);

                // show time pass
                $timePass
                .slideDown(250, function() {
                    $(this).removeClass('lp_u_hide');
                })
                    .find($o.timePassForm)
                    .slideDown(250, function() {
                        $(this).removeClass('lp_u_hide');
                    });
            },

            editTimePass = function($timePass) {
                // insert cloned form into current time pass editor container
                var $timePassForm = $o.timePassFormTemplate.clone().attr('id', $o.timePassFormId);
                $('.lp_js_timePass_editorContainer', $timePass).html($timePassForm);

                populateTimePassForm($timePass);

                // hide action links required when displaying time pass
                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).addClass('lp_u_hide');

                // show action links required when editing time pass
                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).removeClass('lp_u_hide');

                $timePassForm.removeClass('lp_u_hide');
            },

            populateTimePassForm = function($timePass) {
                var passId      = $timePass.data('pass-id'),
                    passData    = lpVars.time_passes_list[passId],
                    vouchers    = lpVars.vouchers_list[passId],
                    $toggle     = $($o.timePassRevenueModel, $timePass),
                    name        = '';

                if (!passData) {
                    return;
                }

                // apply passData to inputs
                $('input, select, textarea', $timePass)
                .each(function(i, v) {
                    name = $(v, $timePass).attr('name');
                    if (name !== '' && passData[name] !== undefined && name !== 'revenue_model') {
                        $(v, $timePass).val(passData[name]);
                    }
                });

                // validate price after inserting
                validatePrice($timePass.find('form'), false, $($o.timePassPrice, $timePass));
                // set price input value into the voucher price input
                $($o.voucherPriceInput, $timePass).val($($o.timePassPrice, $timePass).val());

                // apply passData to revenue model toggle
                if (passData.revenue_model === $o.singleSale) {
                    $toggle.prop('checked', true);
                }

                $($o.timePassCategoryWrapper, $timePass).hide();
                // render category select
                renderCategorySelect(
                    $timePass,
                    $o.timePassScopeCategory,
                    'laterpay_get_categories',
                    formatSelect2TimePass
                );

                // show category select, if required
                var $currentScope = $($o.timePassScope, $timePass).find('option:selected');
                if ($currentScope.val() !== '0') {
                    // show category select, because scope is restricted to or excludes a specific category
                    $($o.timePassCategoryWrapper, $timePass).show();
                }

                // re-generate vouchers list
                clearVouchersList($timePass);
                if (vouchers instanceof Object) {
                    $.each(vouchers, function(code, priceValue) {
                        addVoucher(code, priceValue, $timePass);
                    });
                }
            },

            updateTimePassPreview = function($timePass, $input) {
                // insert at least one space to avoid placeholder to collapse
                var text = ($input.val() !== '') ? $input.val() : ' ';

                if ($input.hasClass($o.timePassDurationClass) || $input.hasClass($o.timePassPeriodClass)) {
                    var duration    = $($o.timePassDuration, $timePass).val(),
                        period      = $($o.timePassPeriod, $timePass).find('option:selected').text();
                    // pluralize period (TODO: internationalize properly)
                    period  = (parseInt(duration, 10) > 1) ? period + 's' : period;
                    text    = duration + ' ' + period;
                    // update pass validity in pass preview
                    $($o.timePassPreviewValidity, $timePass).text(text);
                } else if ($input.hasClass($o.timePassScopeClass) || $input.hasClass($o.timePassScopeCategoryClass)) {
                    var currentScope = $($o.timePassScope, $timePass).find('option:selected');
                    text = currentScope.text();
                    if (currentScope.val() !== '0') {
                        // append selected category, because scope is restricted to or excludes a specific category
                        text += ' ' + $($o.timePassScopeCategory, $timePass).val();
                    }
                    // update pass access in pass preview
                    $($o.timePassPreviewAccess, $timePass).text(text);
                } else if ($input.hasClass($o.timePassPriceClass)) {
                    // update pass price in pass preview
                    $('.lp_purchaseLink', $timePass).html(text + '<small>' + lpVars.defaultCurrency + '</small>');
                    $($o.timePassPreviewPrice, $timePass).text(text + ' ' + lpVars.defaultCurrency);
                } else if ($input.hasClass($o.timePassTitleClass)) {
                    // update pass title in pass preview
                    $($o.timePassPreviewTitle, $timePass).text(text);
                } else if ($input.hasClass($o.timePassDescriptionClass)) {
                    // update pass description in pass preview
                    $($o.timePassPreviewDescription, $timePass).text(text);
                }
            },

            cancelEditingTimePass = function($timePass) {
                // show vouchers
                $timePass.find($o.voucherList).show();

                if ($($o.timePassForm, $timePass).hasClass($o.unsaved)) {
                    // remove entire time pass, if it is a new, unsaved pass
                    $timePass.fadeOut(250, function() {
                        $(this).remove();
                    });
                } else {
                    // remove cloned time pass form
                    $($o.timePassForm, $timePass).fadeOut(250, function() {
                        $(this).remove();
                    });
                }
                // TODO: unbind events

                // show action links required when displaying time pass
                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).removeClass('lp_u_hide');

                // hide action links required when editing time pass
                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).addClass('lp_u_hide');

                // show "add time pass" button, if it is hidden
                if ($o.addTimePass.is(':hidden')) {
                    $o.addTimePass.fadeIn(250);
                }
            },

            saveTimePass = function($timePass) {
                $.post(
                    ajaxurl,
                    $($o.timePassForm, $timePass).serializeArray(),
                    function(r) {
                        if (r.success) {
                            // form has been saved
                            var passId = r.data.pass_id;
                            // update vouchers
                            lpVars.vouchers_list[passId] = r.vouchers;

                            // re-generate vouchers list
                            clearVouchersList($timePass);
                            if (lpVars.vouchers_list[passId] instanceof Object) {
                                $.each(lpVars.vouchers_list[passId], function(code, priceValue) {
                                    addVoucherToList(code, priceValue, $timePass);
                                });

                                // show vouchers
                                $timePass.find($o.voucherList).show();
                            }

                            if (lpVars.time_passes_list[passId]) {
                                // pass already exists (update)
                                lpVars.time_passes_list[passId] = r.data;
                                // insert time pass rendered on server
                                $('.lp_js_timePassPreview', $timePass).html(r.html);

                                // hide action links required when editing time pass
                                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).addClass('lp_u_hide');
                                // show action links required when displaying time pass
                                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).removeClass('lp_u_hide');
                                $($o.timePassForm, $timePass).fadeOut(250, function() {
                                    $(this).remove();
                                });
                            } else {
                                // pass was just created (add)
                                lpVars.time_passes_list[passId] = r.data;
                                var $newTimePass = $o.timePassTemplate.clone().removeAttr('id').data('pass-id', passId);

                                // show assigned pass id
                                $($o.timePassId, $newTimePass)
                                .text(passId)
                                    .parent()
                                    .show(250);

                                $('.lp_js_timePassPreview', $newTimePass).html(r.html);
                                $($o.timePassForm, $timePass).remove();

                                $o.addTimePass.after($newTimePass);

                                populateTimePassForm($newTimePass);

                                // hide action links required when editing time pass
                                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $newTimePass)
                                .addClass('lp_u_hide');
                                // show action links required when displaying time pass
                                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $newTimePass)
                                .removeClass('lp_u_hide');

                                $timePass.fadeOut(250, function() {
                                    $(this).remove();
                                    $newTimePass.removeClass('lp_u_hide');
                                });
                            }
                        }

                        if ($o.addTimePass.is(':hidden')) {
                            $o.addTimePass.fadeIn(250);
                        }

                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            deleteTimePass = function($timePass) {
                // require confirmation
                if (confirm(lpVars.i18n.confirmDeleteTimePass)) {
                    // fade out and remove time pass
                    $timePass
                    .slideUp({
                        duration: 250,
                        start: function() {
                            $.post(
                                ajaxurl,
                                {
                                    action  : 'laterpay_pricing',
                                    form    : 'time_pass_delete',
                                    pass_id : $timePass.data('pass-id'),
                                },
                                function(r) {
                                    if (r.success) {
                                        $(this).remove();
                                        // TODO: unbind events
                                    } else {
                                        $(this).stop().show();
                                    }
                                    setMessage(r.message, r.success);
                                },
                                'json'
                            );
                        }
                    });
                }
            },

            flipTimePass = function(trigger) {
                $(trigger).parents('.lp_timePass').toggleClass('lp_is-flipped');
            },

            changeTimePassScope = function($trigger) {
                var o = $('option:selected', $trigger).val();
                if (o === '0') {
                    // option 'all content'
                    $($o.timePassCategoryWrapper).hide();
                } else {
                    // option restricts access to or excludes access from specific category
                    $($o.timePassCategoryWrapper).show();
                }
            },

            toggleTimePassRevenueModel = function($form) {
                // validate price
                validatePrice($form, false, $($o.timePassPrice, $form));
            },

            generateVoucherCode = function($timePass) {
                $.post(
                    ajaxurl,
                    {
                        form   : 'generate_voucher_code',
                        action : 'laterpay_pricing'
                    },
                    function(r) {
                        if (r.success) {
                            addVoucher(r.code, $timePass.find($o.voucherPriceInput).val(), $timePass);
                        }
                    }
                );
            },

            addVoucher = function(code, priceValue, $timePass) {
                var price   = priceValue + ' ' + lpVars.defaultCurrency,
                    voucher =   '<div class="lp_js_voucher lp_voucherRow" ' +
                                        'data-code="' + code + '" ' +
                                        'style="display:none;">' +
                                    '<input type="hidden" name="voucher[]" value="' + code + '|' + priceValue + '">' +
                                    '<span class="lp_voucherCodeLabel">' + code + '</span>' +
                                    '<span class="lp_voucherCodeInfos">' +
                                        lpVars.i18n.voucherText + ' ' + price +
                                    '</span>' +
                                    '<a href="#" class="lp_js_deleteVoucher lp_editLink lp_deleteLink" data-icon="g">' +
                                        lpVars.i18n.delete +
                                    '</a>' +
                                '</div>';

                $timePass.find($o.voucherPlaceholder).prepend(voucher).find('div').first().slideDown(250);
            },

            addVoucherToList = function(code, priceValue, $timePass) {
                var passId          = $timePass.data('pass-id'),
                    timesRedeemed   = lpVars.vouchers_statistic[passId] ? lpVars.vouchers_statistic[passId] : 0,
                    price           = priceValue + ' ' + lpVars.defaultCurrency,
                    voucher =   '<div class="lp_js_voucher lp_voucherRow" ' + 'data-code="' + code + '">' +
                                    '<span class="lp_voucherCodeInfos">' +
                                        lpVars.i18n.voucherText + ' ' + price + '.<br>' +
                                        '<span class="lp_js_voucherTimesRedeemed">' +
                                            timesRedeemed +
                                        '</span>' + lpVars.i18n.timesRedeemed +
                                    '</span>' +
                                '</div>';

                $timePass.find($o.voucherList).append(voucher);
            },

            clearVouchersList = function($timePass) {
                $timePass.find($o.voucher).remove();
            },

            deleteVoucher = function($voucher) {
                // slide up and remove voucher
                $voucher
                .slideUp(250, function() {
                    $(this).remove();
                });
            },

            saveLandingPage = function($form) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(data) {
                        setMessage(data);
                    }
                );
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
                $o.bulkPriceObjects
                .removeClass($o.disabled)
                .append($('<option>', {
                    value    : 'in_category',
                    text     : lpVars.inCategoryLabel,
                    selected : !!categoryToBeSelected // coerce categoryToBeSelected to Boolean
                }));
            },

            handleBulkEditorSettingsUpdate = function(action, selector) {
                // hide some fields if needed, change separator, and add percent unit option
                var showCategory = (selector === 'in_category');

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
                            $o.bulkPriceObjects.addClass($o.disabled);
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

            saveBulkOperation = function() {
                var actionVal   = $.trim($o.bulkPriceAction.find('option:selected').val()),
                    action      = (actionVal === 'free') ?
                                    lpVars.i18n.make :
                                    $o.bulkPriceAction.find('option:selected').text(),
                    objects     = $o.bulkPriceObjects.find('option:selected').text(),
                    category    = ($.trim($o.bulkPriceObjects.find('option:selected').val()) === 'all') ?
                                    '' :
                                    '"' + $.trim($o.bulkPriceObjectsCategory.find('option:selected').text()) + '"',
                    preposition = ($.trim($o.bulkPriceAction.find('option:selected').val()) === 'free') ?
                                    '' :
                                    $o.bulkPriceChangeAmountPreposition.text(),
                    actionExt   = ($.trim($o.bulkPriceAction.find('option:selected').val()) === 'free') ?
                                    lpVars.i18n.free :
                                    '',
                    amount      = (actionVal === 'free' || actionVal === 'reset') ?
                                    '' :
                                    $o.bulkPriceChangeAmount.val() +
                                    $o.bulkPriceChangeUnit.find('option:selected').text(),
                    description = [action, objects, category, preposition, amount, actionExt];

                // concatenate description and remove excess spaces
                description = $.trim(description.join(' ').replace(/\s+/g, ' '));

                $o.bulkPriceFormHiddenField.val('bulk_price_form_save');
                $o.bulkPriceOperationIdHiddenField.val(undefined);
                $o.bulkPriceMessageHiddenField.val(description);

                $.post(
                    ajaxurl,
                    $o.bulkPriceForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // create new saved bulk operation
                            createSavedBulkOperation(r.data.id, r.data.message);
                        }
                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            createSavedBulkOperation = function(bulkOperationId, bulkMessage) {
                var operation = '<p class="lp_bulkOperation" data-value="' +  bulkOperationId + '">' +
                                    '<a href="#" class="lp_js_deleteSavedBulkOperation lp_editLink lp_deleteLink" ' +
                                            'data-icon="g">' +
                                        lpVars.i18n.delete +
                                    '</a>' +
                                    '<a href="#" class="lp_js_applySavedBulkOperation button button-primary lp_m-l2">' +
                                        lpVars.i18n.updatePrices +
                                    '</a>' +
                                    '<span>' + bulkMessage + '</span>' +
                                '</p>';

                $o.bulkPriceForm.after(operation);
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

            changePurchaseMode = function($form) {
                var onlyTimePassModeChecked = $o.purchaseModeInput.is(':checked');
                $.post(
                    ajaxurl,
                    $form.serialize(),
                    function(data) {
                        if (data.success) {
                            if (onlyTimePassModeChecked) {
                                $o.timePassOnlyHideElements.slideUp();
                            } else {
                                $o.timePassOnlyHideElements.slideDown();
                            }
                        } else {
                            setMessage(data.message, data.success);
                            $o.purchaseModeInput.attr('checked', false);
                        }
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

            initializePage = function() {
                bindEvents();

                // trigger change event of bulk price editor on page load
                // FIXME: why is this required?
                $o.bulkPriceAction.change();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
