(function($) {$(function() {
    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
            body                                    : $('body'),

            revenueModel                            : '.lp_js_revenueModel',
            revenueModelLabel                       : '.lp_js_revenueModelLabel',
            revenueModelLabelDisplay                : '.lp_js_revenueModelLabelDisplay',
            revenueModelInput                       : '.lp_js_revenueModelInput',
            priceInput                              : '.lp_js_priceInput',
            emptyState                              : '.lp_js_emptyState',

            // enabled revenue models
            purchaseModeForm                        : $('#lp_js_changePurchaseModeForm'),
            purchaseModeInput                       : $('.lp_js_onlyTimePassPurchaseModeInput'),
            timePassOnlyHideElements                : $('.lp_js_hideInTimePassOnlyMode'),

            // global default price
            globalDefaultPriceForm                  : $('#lp_js_globalDefaultPriceForm'),
            globalDefaultPriceInput                 : $('#lp_js_globalDefaultPriceInput'),
            globalDefaultPriceDisplay               : $('#lp_js_globalDefaultPriceDisplay'),
            globalDefaultPriceRevenueModelDisplay   : $('#lp_js_globalDefaultPriceRevenueModelDisplay'),
            editGlobalDefaultPrice                  : $('#lp_js_editGlobalDefaultPrice'),
            cancelEditingGlobalDefaultPrice         : $('#lp_js_cancelEditingGlobalDefaultPrice'),
            saveGlobalDefaultPrice                  : $('#lp_js_saveGlobalDefaultPrice'),
            globalDefaultPriceShowElements          : $('#lp_js_globalDefaultPriceShowElements'),
            globalDefaultPriceEditElements          : $('#lp_js_globalDefaultPriceEditElements'),

            // category default price
            categoryDefaultPrices                   : $('#lp_js_categoryDefaultPriceList'),
            addCategory                             : $('#lp_js_addCategoryDefaultPrice'),

            categoryDefaultPriceTemplate            : $('#lp_js_categoryDefaultPriceTemplate'),
            categoryDefaultPriceForm                : '.lp_js_categoryDefaultPriceForm',
            editCategoryDefaultPrice                : '.lp_js_editCategoryDefaultPrice',
            cancelEditingCategoryDefaultPrice       : '.lp_js_cancelEditingCategoryDefaultPrice',
            saveCategoryDefaultPrice                : '.lp_js_saveCategoryDefaultPrice',
            deleteCategoryDefaultPrice              : '.lp_js_deleteCategoryDefaultPrice',
            categoryDefaultPriceShowElements        : '.lp_js_categoryDefaultPriceShowElements',
            categoryDefaultPriceEditElements        : '.lp_js_categoryDefaultPriceEditElements',

            categoryTitle                           : '.lp_js_categoryDefaultPriceCategoryTitle',
            categoryDefaultPriceDisplay             : '.lp_js_categoryDefaultPriceDisplay',

            selectCategory                          : '.lp_js_selectCategory',
            categoryDefaultPriceInput               : '.lp_js_categoryDefaultPriceInput',
            categoryId                              : '.lp_js_categoryDefaultPriceCategoryId',

            // time passes
            timepass                                : {
                editor                                  : $('#lp_time-passes'),
                template                                : $('#lp_js_timePassTemplate'),
                form                                    : '.lp_js_timePassEditorForm',
                editorContainer                         : '.lp_js_timePassEditorContainer',
                id                                      : '.lp_js_timePassId',
                wrapper                                 : '.lp_js_timePassWrapper',
                categoryWrapper                         : '.lp_js_timePassCategoryWrapper',
                fields                              : {
                    duration                            : '.lp_js_switchTimePassDuration',
                    period                              : '.lp_js_switchTimePassPeriod',
                    scope                               : '.lp_js_switchTimePassScope',
                    scopeCategory                       : '.lp_js_switchTimePassScopeCategory',
                    categoryId                          : '.lp_js_timePassCategoryId',
                    title                               : '.lp_js_timePassTitleInput',
                    price                               : '.lp_js_timePassPriceInput',
                    revenueModel                        : '.lp_js_timePassRevenueModelInput',
                    description                         : '.lp_js_timePassDescriptionTextarea'
                },
                classes                             : {
                    form                                : 'lp_js_timePassForm',
                    editorForm                          : 'lp_js_timePassEditorForm',
                    durationClass                       : 'lp_js_switchTimePassDuration',
                    titleClass                          : 'lp_js_timePassTitleInput',
                    priceClass                          : 'lp_js_timePassPriceInput',
                    descriptionClass                    : 'lp_js_timePassDescriptionTextarea',
                    periodClass                         : 'lp_js_switchTimePassPeriod',
                    scopeClass                          : 'lp_js_switchTimePassScope',
                    scopeCategoryClass                  : 'lp_js_switchTimePassScopeCategory'
                },
                preview                             : {
                    placeholder                         : '.lp_js_timePassPreview',
                    wrapper                             : '.lp_js_timePass',
                    title                               : '.lp_js_timePassPreviewTitle',
                    description                         : '.lp_js_timePassPreviewDescription',
                    validity                            : '.lp_js_timePassPreviewValidity',
                    access                              : '.lp_js_timePassPreviewAccess',
                    price                               : '.lp_js_timePassPreviewPrice'
                },
                actions                             : {
                    create                              : $('#lp_js_addTimePass'),
                    show                                : '.lp_js_saveTimePass, .lp_js_cancelEditingTimePass',
                    modify                              : '.lp_js_editTimePass, .lp_js_deleteTimePass',
                    save                                : '.lp_js_saveTimePass',
                    cancel                              : '.lp_js_cancelEditingTimePass',
                    delete                              : '.lp_js_deleteTimePass',
                    edit                                : '.lp_js_editTimePass',
                    flip                                : '.lp_js_flipTimePass'
                },
                ajax                                : {
                    form                                : {
                        delete                          : 'time_pass_delete'
                    }
                },
                data                                : {
                    id                                  : 'pass-id',
                    list                                : lpVars.time_passes_list,
                    vouchers                            : lpVars.vouchers_list,
                    deleteConfirm                       : lpVars.i18n.confirmDeleteTimepass,
                    fields                              : {
                        id                              : 'pass_id'
                    }
                }
            },

            // subscriptions
            subscription                            : {
                editor                                  : $('#lp_subscriptions'),
                template                                : $('#lp_js_subscriptionTemplate'),
                form                                    : '.lp_js_subscriptionEditorForm',
                editorContainer                         : '.lp_js_subscriptionEditorContainer',
                id                                      : '.lp_js_subscriptionId',
                wrapper                                 : '.lp_js_subscriptionWrapper',
                categoryWrapper                         : '.lp_js_subscriptionCategoryWrapper',
                fields                              : {
                    duration                            : '.lp_js_switchSubscriptionDuration',
                    period                              : '.lp_js_switchSubscriptionPeriod',
                    scope                               : '.lp_js_switchSubscriptionScope',
                    scopeCategory                       : '.lp_js_switchSubscriptionScopeCategory',
                    categoryId                          : '.lp_js_subscriptionCategoryId',
                    title                               : '.lp_js_subscriptionTitleInput',
                    price                               : '.lp_js_subscriptionPriceInput',
                    description                         : '.lp_js_subscriptionDescriptionTextarea'
                },
                classes                             : {
                    form                                : 'lp_js_subscriptionForm',
                    editorForm                          : 'lp_js_subscriptionEditorForm',
                    durationClass                       : 'lp_js_switchSubscriptionDuration',
                    titleClass                          : 'lp_js_subscriptionTitleInput',
                    priceClass                          : 'lp_js_subscriptionPriceInput',
                    descriptionClass                    : 'lp_js_subscriptionDescriptionTextarea',
                    periodClass                         : 'lp_js_switchSubscriptionPeriod',
                    scopeClass                          : 'lp_js_switchSubscriptionScope',
                    scopeCategoryClass                  : 'lp_js_switchSubscriptionScopeCategory'
                },
                preview                             : {
                    placeholder                         : '.lp_js_subscriptionPreview',
                    wrapper                             : '.lp_js_subscription',
                    title                               : '.lp_js_subscriptionPreviewTitle',
                    description                         : '.lp_js_subscriptionPreviewDescription',
                    validity                            : '.lp_js_subscriptionPreviewValidity',
                    access                              : '.lp_js_subscriptionPreviewAccess',
                    price                               : '.lp_js_subscriptionPreviewPrice',
                    renewal                             : '.lp_js_subscriptionPreviewRenewal'
                },
                actions                             : {
                    create                              : $('#lp_js_addSubscription'),
                    show                                : '.lp_js_saveSubscription, .lp_js_cancelEditingSubscription',
                    modify                              : '.lp_js_editSubscription, .lp_js_deleteSubscription',
                    save                                : '.lp_js_saveSubscription',
                    edit                                : '.lp_js_editSubscription',
                    cancel                              : '.lp_js_cancelEditingSubscription',
                    delete                              : '.lp_js_deleteSubscription',
                    flip                                : '.lp_js_flipSubscription'
                },
                ajax                                : {
                    form                                : {
                        delete                          : 'subscription_delete'
                    }
                },
                data                                : {
                    id                                  : 'sub-id',
                    list                                : lpVars.subscriptions_list,
                    deleteConfirm                       : lpVars.i18n.confirmDeleteSubscription,
                    fields                              : {
                        id                              : 'id'
                    }
                }
            },

            // vouchers
            voucherPriceInput                       : '.lp_js_voucherPriceInput',
            generateVoucherCode                     : '.lp_js_generateVoucherCode',
            voucherDeleteLink                       : '.lp_js_deleteVoucher',
            voucherEditor                           : '.lp_js_voucherEditor',
            voucherHiddenPassId                     : '#lp_js_timePassEditorHiddenPassId',
            voucherPlaceholder                      : '.lp_js_voucherPlaceholder',
            voucherList                             : '.lp_js_voucherList',
            voucher                                 : '.lp_js_voucher',
            voucherTimesRedeemed                    : '.lp_js_voucherTimesRedeemed',

            // strings cached for better compression
            editing                                 : 'lp_is-editing',
            unsaved                                 : 'lp_is-unsaved',
            payPerUse                               : 'ppu',
            singleSale                              : 'sis',
            selected                                : 'lp_is-selected',
            disabled                                : 'lp_is-disabled',
            hidden                                  : 'lp_hidden',
            navigation                              : $('.lp_navigation')
        },

        bindEvents = function() {
            // global default price and category default price events ----------------------------------------------
            // validate price and choice of revenue model when switching revenue model
            // (validating the price switches the revenue model if required)
            $o.body.on('change', $o.revenueModelInput, function() {
                validatePrice($(this).parents('form'));
            });

            // validate price and revenue model when entering a price
            // (function is only triggered 1500ms after the keyup)
            $o.body.on('keyup', $o.priceInput, debounce(function() {
                    validatePrice($(this).parents('form'));
                }, 1500)
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
            .on('mousedown', function() {
                enterEditModeGlobalDefaultPrice();
            })
            .click(function(e) {e.preventDefault();});

            // cancel
            $o.cancelEditingGlobalDefaultPrice
            .on('mousedown', function() {
                exitEditModeGlobalDefaultPrice();
            })
            .click(function(e) {e.preventDefault();});

            // save
            $o.saveGlobalDefaultPrice
            .on('mousedown', function() {
                saveGlobalDefaultPrice();
            })
            .click(function(e) {e.preventDefault();});

            // category default prices events ----------------------------------------------------------------------
            // add
            $o.addCategory
            .on('mousedown', function() {
                addCategoryDefaultPrice();
            })
            .click(function(e) {e.preventDefault();});

            // edit
            $o.body
            .on('click', $o.editCategoryDefaultPrice, function() {
                var $form = $(this).parents($o.categoryDefaultPriceForm);
                editCategoryDefaultPrice($form);
            });

            // cancel
            $o.body
            .on('click', $o.cancelEditingCategoryDefaultPrice, function() {
                var $form = $(this).parents($o.categoryDefaultPriceForm);
                exitEditModeCategoryDefaultPrice($form);
            });

            // save
            $o.body
            .on('click', $o.saveCategoryDefaultPrice, function() {
                var $form = $(this).parents($o.categoryDefaultPriceForm);
                saveCategoryDefaultPrice($form);
            });

            // delete
            $o.body
            .on('click', $o.deleteCategoryDefaultPrice, function() {
                var $form = $(this).parents($o.categoryDefaultPriceForm);
                deleteCategoryDefaultPrice($form);
            });

            // time passes events ----------------------------------------------------------------------------------
            // add
            $o.timepass.actions.create
            .on('mousedown', function() {
                addEntity('timepass');
            })
            .on('click', function(e) {e.preventDefault();});

            // edit
            $o.timepass.editor
            .on('mousedown', $o.timepass.actions.edit, function() {
                editEntity('timepass', $(this).parents($o.timepass.wrapper));
            })
            .on('click', $o.timepass.actions.edit , function(e) {e.preventDefault();});

            // toggle revenue model
            $o.timepass.editor
            .on('change', $o.timepass.fields.revenueModel, function() {
                var $form = $(this).parents('form');
                // validate price
                validatePrice($form, false, $($o.timepass.fields.price, $form));
            });

            // change duration
            $o.timepass.editor
            .on('change', $o.timepass.fields.duration, function() {
                updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
            });

            // change period
            $o.timepass.editor
            .on('change', $o.timepass.fields.period, function() {
                changeDurationOptions('timepass', $(this), $(this).parents($o.timepass.wrapper));
                updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
            });

            // change scope
            $o.timepass.editor
            .on('change', $o.timepass.fields.scope, function() {
                changeEntityScope('timepass', $(this));
                updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
            });

            $o.timepass.editor
            .on('change', $o.timepass.fields.scopeCategory, function() {
                updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
            });

            // update time pass configuration
            $o.timepass.editor
            .on('input', [$o.timepass.fields.title, $o.timepass.fields.description].join(), function() {
                updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
            });

            // set price
            $o.timepass.editor
            .on('keyup', $o.timepass.fields.price, debounce(function() {
                    validatePrice($(this).parents('form'), false, $(this));
                    updateEntityPreview('timepass', $(this).parents($o.timepass.wrapper), $(this));
                }, 1500)
            );

            // cancel
            $o.timepass.editor
            .on('click', $o.timepass.actions.cancel, function(e) {
                cancelEditingEntity('timepass', $(this).parents($o.timepass.wrapper));
                e.preventDefault();
            });

            // save
            $o.timepass.editor
            .on('click', $o.timepass.actions.save, function(e) {
                saveEntity('timepass', $(this).parents($o.timepass.wrapper));
                e.preventDefault();
            });

            // delete
            $o.timepass.editor
            .on('click', $o.timepass.actions.delete, function(e) {
                deleteEntity('timepass', $(this).parents($o.timepass.wrapper));
                e.preventDefault();
            });

            // flip
            $o.timepass.editor
            .on('mousedown', $o.timepass.actions.flip, function() {
                flipEntity('timepass', this);
            })
            .on('click', $o.timepass.actions.flip, function(e) {e.preventDefault();});

            // set voucher price
            $o.timepass.editor
            .on('keyup', $o.voucherPriceInput, debounce(function() {
                    validatePrice($(this).parents('form'), true, $(this));
                }, 1500)
            );

            // generate voucher code
            $o.timepass.editor
            .on('mousedown', $o.generateVoucherCode, function() {
                generateVoucherCode($(this).parents($o.timepass.wrapper));
            })
            .on('click', $o.generateVoucherCode, function(e) {
                e.preventDefault();
            });

            // delete voucher code
            $o.timepass.editor
            .on('click', $o.voucherDeleteLink, function(e) {
                deleteVoucher($(this).parent());
                e.preventDefault();
            });

            // subscription events ----------------------------------------------------------------------------------
            // add
            $o.subscription.actions.create
            .on('mousedown', function() {
                addEntity('subscription');
            })
            .on('click', function(e) {e.preventDefault();});

            // edit
            $o.subscription.editor
            .on('mousedown', $o.subscription.actions.edit, function() {
                editEntity('subscription', $(this).parents($o.subscription.wrapper));
            })
            .on('click', $o.subscription.actions.edit, function(e) {e.preventDefault();});

            // change duration
            $o.subscription.editor
            .on('change', $o.subscription.fields.duration, function() {
                updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
            });

            // change period
            $o.subscription.editor
            .on('change', $o.subscription.fields.period, function() {
                changeDurationOptions('subscription', $(this), $(this).parents($o.subscription.wrapper));
                updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
            });

            // change scope
            $o.subscription.editor
            .on('change', $o.subscription.fields.scope, function() {
                changeEntityScope('subscription', $(this));
                updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
            });

            // category change
            $o.subscription.editor
            .on('change', $o.subscription.fields.scopeCategory, function() {
                updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
            });

            // update time pass configuration
            $o.subscription.editor
            .on('input', [$o.subscription.fields.title, $o.subscription.fields.description].join(), function() {
                updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
            });

            // set price
            $o.subscription.editor
            .on('keyup', $o.subscription.fields.price, debounce(function() {
                    validatePrice($(this).parents('form'), true, $(this), true);
                    updateEntityPreview('subscription', $(this).parents($o.subscription.wrapper), $(this));
                }, 1500)
            );

            // cancel
            $o.subscription.editor
            .on('click', $o.subscription.actions.cancel, function(e) {
                cancelEditingEntity('subscription', $(this).parents($o.subscription.wrapper));
                e.preventDefault();
            });

            // save
            $o.subscription.editor
            .on('click', $o.subscription.actions.save, function(e) {
                saveEntity('subscription', $(this).parents($o.subscription.wrapper));
                e.preventDefault();
            });

            // delete
            $o.subscription.editor
            .on('click', $o.subscription.actions.delete, function(e) {
                deleteEntity('subscription', $(this).parents($o.subscription.wrapper));
                e.preventDefault();
            });

            // flip
            $o.subscription.editor
            .on('mousedown', $o.subscription.actions.flip, function() {
                flipEntity('subscription', this);
            })
            .on('click', $o.subscription.actions.flip, function(e) {e.preventDefault();});
        },

        validatePrice = function($form, disableRevenueValidation, $input, subscriptionValidation) {
            var $priceInput = $input ? $input : $('.lp_number-input', $form),
                price       = $priceInput.val();

            // strip non-number characters
            price = price.replace(/[^0-9\,\.]/g, '');

            // convert price to proper float value
            price = parseFloat(price.replace(',', '.')).toFixed(2);

            // prevent non-number prices
            if (isNaN(price)) {
                price = 0;
            }

            // prevent negative prices
            price = Math.abs(price);

            if (subscriptionValidation) {
                if (price < lpVars.currency.sis_min) {
                    price = lpVars.currency.sis_min;
                } else if (price > lpVars.currency.sis_max) {
                    price = lpVars.currency.sis_max;
                }
            } else {
                // correct prices outside the allowed range of 0.05 - 149.99
                if (price > lpVars.currency.sis_max) {
                    price = lpVars.currency.sis_max;
                } else if (price > 0 && price < lpVars.currency.ppu_min) {
                    price = lpVars.currency.ppu_min;
                }
            }

            if ( ! disableRevenueValidation ) {
                validateRevenueModel(price, $form);
            }

            // format price with two digits
            price = price.toFixed(2);

            // localize price
            if (lpVars.locale.indexOf( 'de_DE' ) !== -1) {
                price = price.replace('.', ',');
            }

            // update price input
            $priceInput.val(price);

            return price;
        },

        validateRevenueModel = function(price, $form) {
            var currentRevenueModel,
                input = $o.revenueModelInput;

            if ($form.hasClass($o.timepass.classes.editorForm)) {
                input = $o.timepass.fields.revenueModel;
            }

            var $payPerUse  = $(input + '[value=' + $o.payPerUse + ']', $form),
                $singleSale = $(input + '[value=' + $o.singleSale + ']', $form);

            currentRevenueModel = $('input:radio:checked', $form).val();

            if (price === 0 || (price >= lpVars.currency.ppu_min && price <= lpVars.currency.ppu_max)) {
                // enable Pay-per-Use
                $payPerUse.removeProp('disabled')
                    .parent('label').removeClass($o.disabled);
            } else {
                // disable Pay-per-Use
                $payPerUse.prop('disabled', 'disabled')
                    .parent('label').addClass($o.disabled);
            }

            if (price >= lpVars.currency.sis_min) {
                // enable Single Sale for prices
                // (prices > 149.99 Euro are fixed by validatePrice already)
                $singleSale.removeProp('disabled')
                    .parent('label').removeClass($o.disabled);
            } else {
                // disable Single Sale
                $singleSale.prop('disabled', 'disabled')
                    .parent('label').addClass($o.disabled);
            }

            // switch revenue model, if combination of price and revenue model is not allowed
            if (price > lpVars.currency.ppu_max && currentRevenueModel === $o.payPerUse) {
                // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                $singleSale.prop('checked', 'checked');
            } else if (price < lpVars.currency.sis_min && currentRevenueModel === $o.singleSale) {
                // Single Sale purchases are not allowed for prices < 1.49 Euro
                $payPerUse.prop('checked', 'checked');
            }

            // highlight current revenue model
            $('label', $form).removeClass($o.selected);
            $(input + ':checked', $form).parent('label').addClass($o.selected);
        },

        enterEditModeGlobalDefaultPrice = function() {
            $o.globalDefaultPriceShowElements.velocity('slideUp', { duration: 250, easing: 'ease-out' });
            $o.globalDefaultPriceEditElements.velocity('slideDown', {
                duration: 250,
                easing: 'ease-out',
                complete: function() {
                    setTimeout(function() {
                        $o.globalDefaultPriceInput.focus();
                    }, 50);
                }
            });
            $o.globalDefaultPriceForm.addClass($o.editing);
        },

        exitEditModeGlobalDefaultPrice = function() {
            $o.globalDefaultPriceShowElements.velocity('slideDown', { duration: 250, easing: 'ease-out' });
            $o.globalDefaultPriceEditElements.velocity('slideUp', { duration: 250, easing: 'ease-out' });
            $o.globalDefaultPriceForm.removeClass($o.editing);
            // reset value of price input to current global default price
            $o.globalDefaultPriceInput.val($o.globalDefaultPriceDisplay.data('price'));
            // reset revenue model input to current revenue model
            var currentRevenueModel = $o.globalDefaultPriceRevenueModelDisplay.data('revenue');
            $($o.revenueModelLabel, $o.globalDefaultPriceForm).removeClass($o.selected);
            $('.lp_js_revenueModelInput[value=' + currentRevenueModel + ']', $o.globalDefaultPriceForm)
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
                        $o.globalDefaultPriceDisplay.text(r.localized_price).data('price', r.price);
                        $o.globalDefaultPriceRevenueModelDisplay
                            .text(r.revenue_model_label)
                            .data('revenue', r.revenue_model);
                    }
                    $o.navigation.showMessage(r);
                    exitEditModeGlobalDefaultPrice();
                },
                'json'
            );
        },

        addCategoryDefaultPrice = function() {
            $o.addCategory.velocity('fadeOut', { duration: 250 });

            // hide empty state hint, if it is visible
            if ($($o.emptyState, $o.categoryDefaultPrices).is(':visible')) {
                $($o.emptyState, $o.categoryDefaultPrices).velocity('fadeOut', { duration: 400 });
            }

            // clone category default price template
            var $form = $o.categoryDefaultPriceTemplate
                        .clone()
                        .removeAttr('id')
                        .insertBefore('#lp_js_categoryDefaultPriceList')
                        .velocity('slideDown', { duration: 250, easing: 'ease-out' });

            editCategoryDefaultPrice($form);
        },

        editCategoryDefaultPrice = function($form) {
            // exit edit mode of all other category prices
            $('.lp_js_categoryDefaultPriceForm.lp_is-editing').each(function() {
                exitEditModeCategoryDefaultPrice($(this), true);
            });

            // initialize edit mode
            $form.addClass($o.editing);
            $($o.categoryDefaultPriceShowElements, $form)
            .velocity('slideUp', { duration: 250, easing: 'ease-out' });
            $o.addCategory.velocity('fadeOut', { duration: 250 });
            $($o.categoryDefaultPriceEditElements, $form).velocity('slideDown', {
                duration: 250,
                easing: 'ease-out',
                complete: function() {
                    $($o.categoryDefaultPriceInput, $form).focus();
                }
            });
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
                        $($o.categoryDefaultPriceDisplay, $form).text(r.localized_price).data('price', r.price);
                        $($o.revenueModelLabelDisplay, $form)
                            .text(r.revenue_model_label)
                            .data('revenue', r.revenue_model);
                        $($o.categoryDefaultPriceInput, $form).val(r.price);
                        $($o.categoryTitle, $form).text(r.category);
                        $($o.categoryId, $form).val(r.category_id);

                        // mark the form as saved
                        $form.removeClass($o.unsaved);
                    }
                    exitEditModeCategoryDefaultPrice($form);
                    $o.navigation.showMessage(r);
                },
                'json'
            );
        },

        exitEditModeCategoryDefaultPrice = function($form, editAnotherCategory) {
            // mark the form as not being edited anymore
            $form.removeClass($o.editing);

            if ($form.hasClass($o.unsaved)) {
                // remove form, if creating a new category default price has been canceled
                $form.velocity('slideUp', {
                    duration: 250,
                    easing: 'ease-out',
                    complete: function() {
                        $(this).remove();

                        // show empty state hint, if there are no category default prices
                        if ($($o.categoryDefaultPriceForm + ':visible').length === 0) {
                            $($o.emptyState, $o.categoryDefaultPrices).velocity('fadeIn', { duration: 400 });
                        }
                    }
                });
            } else {
                // hide form, if a new category default price has been saved
                // or editing an existing category default price has been canceled
                $($o.categoryDefaultPriceEditElements, $form)
                .velocity('slideUp', { duration: 250, easing: 'ease-out' });
                $($o.selectCategory, $form).select2('destroy');
                // reset value of price input to current category default price
                $($o.categoryDefaultPriceInput, $form).val($($o.categoryDefaultPriceDisplay, $form).data('price'));
                // reset revenue model input to current revenue model
                var currentRevenueModel = $($o.revenueModelLabelDisplay, $form).data('revenue');
                $($o.revenueModelLabel, $form).removeClass($o.selected);
                $('.lp_js_revenueModelInput[value=' + currentRevenueModel + ']', $form)
                .prop('checked', 'checked')
                    .parent('label')
                    .addClass($o.selected);
                // show elements for displaying defined price again
                $($o.categoryDefaultPriceShowElements, $form)
                .velocity('slideDown', { duration: 250, easing: 'ease-out' });
            }

            // show 'Add' button again
            if (!editAnotherCategory) {
                $o.addCategory.velocity('fadeIn', { duration: 250, display: 'inline-block' });
            }
        },

        deleteCategoryDefaultPrice = function($form) {
            validatePrice($form, false, $('input[name=price]', $form));
            $('input[name=form]', $form).val('price_category_form_delete');

            $.post(
                ajaxurl,
                $form.serializeArray(),
                function(r) {
                    if (r.success) {
                        $form.velocity('slideUp', {
                            duration: 250,
                            easing: 'ease-out',
                            complete: function() {
                                $(this).remove();

                                // show empty state hint, if there are no category default prices
                                if ($($o.categoryDefaultPriceForm + ':visible').length === 0) {
                                    $($o.emptyState, $o.categoryDefaultPrices)
                                    .velocity('fadeIn', { duration: 400 });
                                }
                            }
                        });
                    }
                    $o.navigation.showMessage(r);
                },
                'json'
            );
        },

        formatSelect2Selection = function(data, container) {
            var $form = $(container).parent().parent().parent();
            $('.lp_js_selectCategory', $form).val(data.text);
            $('.lp_js_categoryDefaultPriceCategoryId', $form).val(data.id);

            return data.text;
        },

        formatSelect2ForEntity = function(data, container) {
            var form = $(container).parents('form'),
                $entity = $o.timepass;

            if ($(form).hasClass($o.subscription.classes.editorForm)) {
                $entity = $o.subscription;
            }

            if (data.id) {
                $($entity.fields.categoryId, $(form)).val(data.id);
            }
            $($entity.fields.scopeCategory, $(form)).val(data.text);

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

                                                    $.each(data.categories, function(index) {
                                                        var term = data.categories[ index ];
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

        addEntity = function(type) {
            var $entity = $o[type];

            // hide 'add' button
            $entity.actions.create.velocity('fadeOut', { duration: 250 });

            // hide empty state hint, if it is visible
            if ($($o.emptyState, $entity.editor).is(':visible')) {
                $($o.emptyState, $entity.editor).velocity('fadeOut', { duration: 400 });
            }

            // prepend cloned entity template to editor
            $($entity.wrapper).first().before($entity.template.clone().removeAttr('id'));

            // we added the template as first thing in the list, so let's select the first entity
            var $template = $($entity.wrapper, $entity.editor).first();
            $($entity.form, $template).addClass($o.unsaved);

            populateEntityForm(type, $template);

            // show template
            $template
            .velocity('slideDown', {
                duration: 250,
                easing: 'ease-out',
                complete: function() {
                    $(this).removeClass($o.hidden);
                }
            })
            .find($entity.form)
            .velocity('slideDown', {
                duration: 250,
                easing: 'ease-out',
                complete: function() {
                    $(this).removeClass($o.hidden);
                }
            });
        },

        editEntity = function(type, $wrapper) {
            var $entity = $o[type];

            // insert cloned form into current entity editor container
            var $form = $($entity.form, $entity.template).clone();
            $($entity.editorContainer, $wrapper).empty().append($form);

            populateEntityForm(type, $wrapper);

            // hide action links required when displaying entity
            $($entity.actions.modify, $wrapper).addClass($o.hidden);

            // show action links required when editing entity
            $($entity.actions.show, $wrapper).removeClass($o.hidden);

            $form.removeClass($o.hidden);
        },

        populateEntityForm = function(type, $wrapper) {
            var $entity  = $o[type],
                entityId = $wrapper.data($entity.data.id),
                data     = $entity.data.list[entityId],
                name     = '';

            if (!data) {
                return;
            }

            // apply passData to inputs
            $('input, select, textarea', $wrapper)
            .each(function(i, v) {
                name = $(v, $wrapper).attr('name');
                if (name !== '' && data[name] !== undefined && name !== 'revenue_model') {
                    $(v, $wrapper).val(data[name]);
                }
            });

            if (type === 'timepass') {
                var vouchers      = $entity.data.vouchers[entityId];
                // validate price after inserting
                validatePrice($wrapper.find('form'), false, $($entity.fields.price, $wrapper));
                // set price input value into the voucher price input
                $($o.voucherPriceInput, $wrapper).val($($entity.fields.price, $wrapper).val());

                // highlight current revenue model
                $($o.revenueModelLabel, $wrapper).removeClass($o.selected);

                var $revenue = $($entity.fields.revenueModel + '[value=' + data.revenue_model + ']', $wrapper);
                $revenue.prop('checked', 'checked');
                $revenue.parent('label').addClass($o.selected);

                // re-generate vouchers list
                clearVouchersList($wrapper);
                if (vouchers instanceof Object) {
                    $.each(vouchers, function(code, voucherData) {
                        addVoucher(code, voucherData, $wrapper);
                    });
                }
            } else if (type === 'subscription') {
                validatePrice($wrapper.find('form'), true, $($entity.fields.price, $wrapper));
            }

            $($entity.categoryWrapper, $wrapper).hide();
            // render category select
            renderCategorySelect(
                $wrapper,
                $entity.fields.scopeCategory,
                'laterpay_get_categories',
                formatSelect2ForEntity
            );

            // show category select, if required
            var $currentScope = $($entity.fields.scope, $wrapper).find('option:selected');
            if ($currentScope.val() !== '0') {
                // show category select, because scope is restricted to or excludes a specific category
                $($entity.categoryWrapper, $wrapper).show();
            }
        },

        updateEntityPreview = function(type, $wrapper, $input) {
            // insert at least one space to avoid placeholder to collapse
            var $entity = $o[type],
                text = ($input.val() !== '') ? $input.val() : ' ';

            if ($input.hasClass($entity.classes.durationClass) || $input.hasClass($entity.classes.periodClass)) {
                var duration    = $($entity.fields.duration, $wrapper).val(),
                    period      = $($entity.fields.period, $wrapper).find('option:selected').text();
                // pluralize period (TODO: internationalize properly)
                period  = (parseInt(duration, 10) > 1) ? period + 's' : period;
                text    = duration + ' ' + period;
                // update pass validity in pass preview
                $($entity.preview.validity, $wrapper).text(text);
                // update renewal if subscription
                if (type === 'subscription') {
                    $($entity.preview.renewal, $wrapper).text(lpVars.i18n.after + ' ' + text);
                }
            } else if (
                $input.hasClass($entity.classes.scopeClass) || $input.hasClass($entity.classes.scopeCategoryClass)
            ) {
                var currentScope = $($entity.fields.scope, $wrapper).find('option:selected');
                text = currentScope.text();
                if (currentScope.val() !== '0') {
                    // append selected category, because scope is restricted to or excludes a specific category
                    text += ' ' + $($entity.fields.scopeCategory, $wrapper).val();
                }
                // update pass access in pass preview
                $($entity.preview.access, $wrapper).text(text);
            } else if ($input.hasClass($entity.classes.priceClass)) {
                var small = $('<small />', {
                    class: 'lp_purchase-link__currency',
                });
                small.text(lpVars.currency.code);
                // update pass price in pass preview
                $('.lp_js_purchaseLink', $wrapper)
                .empty().append(text).append(small);
                $($entity.preview.price).text(text + ' ' + lpVars.currency.code);
            } else if ($input.hasClass($entity.classes.titleClass)) {
                // update pass title in pass preview
                $($entity.preview.title, $wrapper).text(text);
            } else if ($input.hasClass($entity.classes.descriptionClass)) {
                // update pass description in pass preview
                $($entity.preview.description, $wrapper).text(text);
            }
        },

        cancelEditingEntity = function(type, $wrapper) {
            var $entity = $o[type],
                id = $wrapper.find($entity.preview.wrapper).data($entity.data.id);

            if ($($entity.form, $wrapper).hasClass($o.unsaved)) {
                // remove entire time pass, if it is a new, unsaved pass
                $wrapper
                .velocity('fadeOut', {
                    duration: 250,
                    complete: function() {
                        $(this).remove();

                        // show empty state hint, if there are no time passes
                        if ($($entity.wrapper + ':visible').length === 0) {
                            $($o.emptyState, $entity.editor).velocity('fadeIn', { duration: 400 });
                        }
                    }
                });
            } else {
                // remove cloned time pass form
                $($entity.form, $wrapper)
                .velocity('fadeOut', {
                    duration: 250,
                    complete: function() {
                        $(this).remove();
                    }
                });
            }

            // show action links required when displaying time pass
            $($entity.actions.modify, $wrapper).removeClass($o.hidden);

            // hide action links required when editing time pass
            $($entity.actions.show, $wrapper).addClass($o.hidden);

            if (type === 'timepass') {
                // re-generate vouchers list
                clearVouchersList($wrapper);
                if ($entity.data.vouchers[id] instanceof Object) {
                    $.each($entity.data.vouchers[id], function(code, voucherData) {
                        addVoucherToList(code, voucherData, $wrapper);
                    });

                    // show vouchers
                    $wrapper.find($o.voucherList).show();
                }
            }

            // show 'create' button, if it is hidden
            if ($entity.actions.create.is(':hidden')) {
                $entity.actions.create.velocity('fadeIn', { duration: 250, display: 'inline-block' });
            }
        },

        saveEntity = function(type, $wrapper) {
            var $entity = $o[type];

            $.post(
                ajaxurl,
                $($entity.form, $wrapper).serializeArray(),
                function(r) {
                    if (r.success) {
                        // form has been saved
                        var id = r.data[$entity.data.fields.id];

                        if (type === 'timepass') {
                            // update vouchers
                            $entity.data.vouchers[id] = r.vouchers;
                        }

                        if (!$entity.data.list[id]) {
                            $wrapper.data($entity.data.id, id);

                            // show assigned id
                            $($entity.id, $wrapper)
                            .text(id)
                            .parent()
                            .velocity('fadeIn', { duration: 250 });
                        }

                        // pass data to list
                        $entity.data.list[id] = r.data;

                        // insert entity rendered on server
                        $($entity.preview.placeholder, $wrapper).empty().append(r.html);

                        // hide action links required when editing entity
                        $($entity.actions.show, $wrapper).addClass($o.hidden);

                        // show action links required when displaying entity
                        $($entity.actions.modify, $wrapper).removeClass($o.hidden);

                        // remove edit form
                        $($entity.form, $wrapper)
                        .velocity('fadeOut', {
                            duration: 250,
                            complete: function () {
                                $(this).remove();

                                // re-generate vouchers list
                                if (type === 'timepass') {
                                    regenerateVouchers($wrapper, $entity, id);
                                }
                            }
                        });

                        // show create button
                        if ($entity.actions.create.is(':hidden')) {
                            $entity.actions.create.velocity('fadeIn', { duration: 250, display: 'inline-block' });
                        }
                    }

                    $o.navigation.showMessage(r);
                },
                'json'
            );
        },

        deleteEntity = function(type, $wrapper) {
            var $entity = $o[type];

            // require confirmation
            if (confirm($entity.data.deleteConfirm)) {
                // fade out and remove time pass
                $wrapper
                .velocity('slideUp', {
                    duration: 250,
                    easing: 'ease-out',
                    begin: function() {
                        $.post(
                            ajaxurl,
                            {
                                action  : 'laterpay_pricing',
                                form    : $entity.ajax.form.delete,
                                id      : $wrapper.data($entity.data.id)
                            },
                            function(r) {
                                if (r.success) {
                                    $wrapper.remove();

                                    // show empty state hint, if there are no time passes
                                    if ($($entity.wrapper + ':visible').length === 0) {
                                        $($o.emptyState, $entity.editor).velocity('fadeIn', { duration: 400 });

                                        // set toggle according to current purchase mode value.
                                        if ( '1' === r.purchase_mode_value ) {
                                            $o.purchaseModeInput.prop('checked', true );
                                        } else {
                                            $o.purchaseModeInput.prop('checked', false );
                                        }
                                    }
                                } else {
                                    $(this).stop().show();
                                }

                                $o.navigation.showMessage(r);
                            },
                            'json'
                        );
                    }
                });
            }
        },

        flipEntity = function(type, trigger) {
            $(trigger).parents($o[type].preview.wrapper).toggleClass('lp_is-flipped');
        },

        changeEntityScope = function(type, $trigger) {
            var $entity = $o[type],
                o = $('option:selected', $trigger).val();

            if (o === '0') {
                // option 'all content'
                $($entity.categoryWrapper).hide();
            } else {
                // option restricts access to or excludes access from specific category
                $($entity.categoryWrapper).show();
            }
        },

        changeDurationOptions = function(type, $period, $form) {
            var $entity = $o[type],
                i, options = [],
                limit = 24,
                period = $period.val(),
                duration = $($entity.fields.duration, $form).val();


            // change duration options
            if (period === '4') {
                limit = 1;
            } else if (period === '3') {
                limit = 12;
            }

            for(i = 1; i <= limit; i++) {
                var option = $('<option/>', {
                    value:i,
                });
                option.text(i);
                options.push(option);
            }

            $($entity.fields.duration, $form)
                .find('option')
                .remove()
                .end()
            .append(options)
            .val(duration && duration <= limit ? duration : 1);
        },

        regenerateVouchers = function($wrapper, $entity, id) {
            clearVouchersList($wrapper);
            if ($entity.data.vouchers[id] instanceof Object) {
                $.each($entity.data.vouchers[id], function (code, voucherData) {
                    addVoucherToList(code, voucherData, $wrapper);
                });

                // show vouchers
                $wrapper.find($o.voucherList).show();
            }
        },

        generateVoucherCode = function($timePass) {
            $.post(
                ajaxurl,
                {
                    form   : 'generate_voucher_code',
                    action : 'laterpay_pricing',
                    price  : $timePass.find($o.voucherPriceInput).val()
                },
                function(r) {
                    if (r.success) {
                        addVoucher(r.code, $timePass.find($o.voucherPriceInput).val(), $timePass);
                    } else {
                        $o.navigation.showMessage(r);
                    }
                },
                'json'
            );
        },

        addVoucher = function(code, voucherData, $timePass) {
            var priceValue = voucherData.price ? voucherData.price : voucherData,
                price      = priceValue + ' ' + lpVars.currency.code,
                title      = voucherData.title ? voucherData.title : '';

            var voucher = $('<div/>', {
                'class': 'lp_js_voucher lp_voucher',
                'data-code': code,
                'style': 'display:none;',

            });

            var voucherCode = $('<input/>', {
                type: 'hidden',
                name: 'voucher_code[]',
                value: code
            });

            var voucherPrice = $('<input/>', {
                type: 'hidden',
                name: 'voucher_price[]',
                value: priceValue
            });

            var spanVoucherCode = $('<span/>', {
                class: 'lp_voucher__code',
            }).text(code);

            var spanVoucherInfo = $('<span/>', {
                class: 'lp_voucher__code-infos',
            }).text(lpVars.i18n.voucherText + ' ' + price);

            var inputTitle = $('<input/>', {
                class: 'lp_input__title',
                type: 'text',
                name: 'voucher_title[]',
                value: title,
            });

            var deleteLink = $('<a/>', {
                'class': 'lp_js_deleteVoucher lp_edit-link--bold',
                'data-icon': 'g'
            });

            voucher.empty().append(voucherCode)
                .append(voucherPrice)
                .append(spanVoucherCode)
                .append(spanVoucherInfo)
                .append(inputTitle)
                .append(deleteLink);

            $timePass
                .find($o.voucherPlaceholder)
                .prepend(voucher)
                    .find('div')
                        .first()
                        .velocity('slideDown', { duration: 250, easing: 'ease-out' });
        },

        addVoucherToList = function(code, voucherData, $timePass) {
            var passId = $timePass.data($o.timepass.data.id),
                timesRedeemed = lpVars.vouchers_statistic[passId] ? lpVars.vouchers_statistic[passId] : 0,
                title = voucherData.title ? voucherData.title : '',
                price = voucherData.price + ' ' + lpVars.currency.code;

            var voucher = $('<div/>', {
                'class': 'lp_js_voucher lp_voucher',
                'data-code': code,
            });

            var voucherTitle = $('<span/>', {
                class: 'lp_voucher__title',
            }).append($('<b/>').text(title));

            var voucherCode = $('<span/>', {
                class: 'lp_voucher__code',
            }).text(code);

            var voucherInfo = $('<span/>', {
                class: 'lp_voucher__code-infos'
            }).text(lpVars.i18n.voucherText + ' ' + price)
                .append('<br/>')
                .append($('<span/>', {
                    class: 'lp_js_voucherTimesRedeemed',
                }).text(timesRedeemed))
                .append(' ' + lpVars.i18n.timesRedeemed);

            var redeemDetail = $('<div/>').append(voucherCode).append(voucherInfo);

            voucher.append(voucherTitle).append(redeemDetail);

            $timePass.find($o.voucherList).append(voucher);
        },

        clearVouchersList = function($timePass) {
            $timePass.find($o.voucher).remove();
        },

        deleteVoucher = function($voucher) {
            // slide up and remove voucher
            $voucher
            .velocity('slideUp', {
                duration: 250,
                easing: 'ease-out',
                complete: function() {
                    $(this).remove();
                }
            });
        },

        changePurchaseMode = function($form) {
            var serializedForm = $form.serialize();
            // disable button during Ajax request
            $o.purchaseModeInput.prop('disabled', true);

            $.post(
                ajaxurl,
                serializedForm,
                function(data) {
                    if (!data.success) {
                        $o.navigation.showMessage(data);
                        $o.purchaseModeInput.prop('checked', false);
                    }
                },
                'json'
            );

            // re-enable button after Ajax request
            $o.purchaseModeInput.prop('disabled', false);
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
        };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
