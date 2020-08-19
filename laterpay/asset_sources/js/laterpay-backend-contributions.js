/* globals lpGlobal */

(function ($) {
    $(function () {

        // encapsulate all LaterPay Javascript in function laterPayBackendContributions
        function laterPayBackendContributions() {
            var $o = {
                    body                     : $('body'),
                    revenueModelInput        : '.lp_js_revenueModelInput',
                    revenueModelContainer    : '.post_price_revenue_model',
                    priceInput               : '.lp_js_priceInput',
                    presetButtonAmount       : '.lp-amount-preset-button',
                    customAmountWrapper      : '.lp-custom-amount-wrapper',
                    selectedButton           : '.lp-amount-preset-button-selected',
                    singleRevenueModel       : $('#lp_single_contribution_revenue_model'),
                    revenueModelMultiple     : $('#lp_multiple_contribution_ul'),
                    revenueModelMultipleItems: $('#lp_multiple_contribution_ul li'),
                    revenueMultipleRadio     : $('input:radio', '#lp_multiple_contribution_ul'),

                    // Elements on the current page.
                    navigation                         : $('.lp_navigation'),
                    hideOnSinglePurchase               : $('.hide-on-single-purchase'),
                    contributionAllowMultiple          : $('#lp_contribution_allow_multiple_amount'),
                    contributionMultiplePurchaseOptions: $('.all_purchase_options'),
                    contributionSinglePurchaseOption   : $('.lp_single_contribution_dialog_options'),
                    contributionCustom                 : $('#lp_contribution_allow_custom_amount'),
                    contributionSingleButtonWrapper    : $('.lp-dialog-single-button-wrapper'),
                    contributionSinglePreviewButton    : $('#lp_jsLinkSingle'),
                    contributionMultipleWrapper        : $('.lp-dialog-multiple-contribution-wrapper'),
                    contributionGenerateCode           : $('#lp_js_contributionGenerateCode'),
                    customAmountInput                  : $('#lp_custom_amount_input'),
                    contributionName                   : $('#lp_contribution_name'),
                    thankYouPage                       : $('#lp_thank_you_page'),
                    dialogHeader                       : $('#lp_dialog_header'),
                    dialogDescription                  : $('#lp_dialog_description'),
                    previewDialogHeader                : $('.lp_contribution_preview .lp-header-text span'),
                    previewDialogDescription           : $('.lp_contribution_preview .lp-amount-text'),
                    singleContributionPrice            : $('#lp_single_contribution_price'),
                    singleContributionForm             : $('#lp_single_contribution_form'),
                    contributionErrorMessageWrapper    : $('.lp-contribution-error-wrapper'),
                    contributionErrorMessage           : '.lp-contribution-error-message',
                    contributionCustomErrorMessage     : '.lp-contribution-custom-error-message',
                    payPerUse                          : 'ppu',
                    singleSale                         : 'sis',
                    disabled                           : 'lp_is-disabled',
                    selected                           : 'lp_is-selected',
                },

                bindEvents = function () {

                    // validate price and revenue model when entering a price
                    // (function is only triggered 1500ms after the keyup)
                    $o.body.on('keyup', $o.priceInput, debounce(function () {
                            resetGenerateButton();
                            var formId = $(this).parents('form').attr('id');
                            var contribType = 'single';
                            if ('lp_multiple_contribution_form' === formId) {
                                contribType = 'multiple';
                            }
                            var price = validatePrice($(this).parents('form'), false, contribType, $(this));
                            updateLivePreview(price, $(this).parents('form'), contribType);
                        }, 1500)
                    );

                    var onchangeCallback = function () {
                        resetGenerateButton();
                        updateLivePreview( undefined, undefined, 'multiple' );
                    };

                    $o.dialogHeader.on( 'keyup', debounce( onchangeCallback, 500 ) );
                    $o.dialogDescription.on( 'keyup', debounce( onchangeCallback, 500 ) );

                    // Event handler for default amount selection in multiple contribution.
                    $($o.presetButtonAmount).on('click', function () {
                        resetGenerateButton();
                        var presetId = $(this).parent().attr('id').slice(-1);
                        $('#lp_js_multiple_amounts .lp-amount-preset-button')
                            .removeClass('lp-amount-preset-button-selected');
                        $(this).addClass('lp-amount-preset-button-selected');
                        var price = $('#lp_multiple_contribution_input_' + presetId).val();
                        updateLivePreview(price, $(this).parents('form'), 'multiple');
                    });

                    // validate choice of revenue model (validating the price switches the revenue model if required)
                    $('input:radio', $o.singleRevenueModel).add($o.revenueMultipleRadio).change(function () {
                        resetGenerateButton();
                        var formId = $(this).parents('form').attr('id');
                        var inputElement = $(this);
                        var contribType = 'single';
                        if ('lp_multiple_contribution_form' === formId) {
                            contribType = 'multiple';
                            inputElement = $(this).parents('.post_price_revenue_model')
                                .siblings().find('.lp_js_priceInput');
                        }
                        var price = validatePrice($(this).parents('form'), false, contribType, inputElement);
                        updateLivePreview(price, $(this).parents('form'), contribType);
                    });

                    // Event handler for contribution type check.
                    $o.contributionAllowMultiple.change(function () {
                        resetGenerateButton();
                        $($o.contributionCustomErrorMessage).hide();
                        if ($(this).prop('checked')) {
                            $(this).val(1);
                            $o.hideOnSinglePurchase.show();
                            $o.contributionMultiplePurchaseOptions.show();
                            $o.contributionMultipleWrapper.show();
                            $o.contributionSinglePurchaseOption.hide();
                            $o.contributionSingleButtonWrapper.hide();
                            $o.contributionCustom.attr('disabled', false);
                        } else {
                            $(this).val(0);
                            $o.contributionSinglePurchaseOption.show();
                            $o.contributionSingleButtonWrapper.show();
                            $o.hideOnSinglePurchase.hide();
                            $o.contributionMultiplePurchaseOptions.hide();
                            $o.contributionMultipleWrapper.hide();
                            $o.contributionCustom.attr('disabled', true);
                        }
                    });

                    // Event handler for custom amount checkbox.
                    $o.contributionCustom.change(function () {
                        resetGenerateButton();
                        if ($(this).prop('checked')) {
                            if ($o.contributionAllowMultiple.prop('checked')) {
                                $(this).val(1);
                                $($o.customAmountWrapper).show();
                                $($o.contributionCustomErrorMessage).hide();
                            } else {
                                $($o.contributionCustomErrorMessage).text(lpVars.i18n.errorCustomAmount);
                                $($o.contributionCustomErrorMessage).show();
                                $(this).attr('disabled', 'disabled');
                                return false;
                            }
                        } else {
                            $(this).val(0);
                            $($o.customAmountWrapper).hide();
                        }
                    });

                    // Event handler for campaign name.
                    $o.contributionName.on('keyup', debounce(function () {
                        resetGenerateButton();
                        validateContributionConfig();
                    }, 1000));

                    // Event handler for campaign thank you page.
                    $o.thankYouPage.on('keyup', debounce(function () {
                        resetGenerateButton();
                        if ($(this).val().trim().length) {
                            if (!isValidURL($(this).val())) {
                                $(this).next($o.contributionErrorMessage).text(lpVars.i18n.errorCampaignThanks);
                                $(this).next($o.contributionErrorMessage).show();
                            } else {
                                $(this).next($o.contributionErrorMessage).text('');
                                $(this).next($o.contributionErrorMessage).hide();
                            }
                        } else {
                            $(this).next($o.contributionErrorMessage).text('');
                            $(this).next($o.contributionErrorMessage).hide();
                        }
                    }, 1000));

                    //Event handler for custom amount.
                    $o.customAmountInput.on('focus input', debounce(function () {
                        resetGenerateButton();
                        var price = validatePrice($(this).parents('form'), false,
                            'multiple', $(this));
                        $(this).val(price);
                    }, 1500));

                    // Handle Generate Shortcode Button.
                    $o.contributionGenerateCode.on('click', function (e) {
                        e.preventDefault();

                        // Proceed if not disabled.
                        if (!$(this).attr('disabled')) {

                            var contributionName, thankYouPage, contributionType, singleAmount, countOfAmounts = 1,
                                singleRevenue, customAmount = '0.00', amountsArray = [], formData = {}, presetId,
                                isCustomAllowed = 1, dialogDescription, dialogHeader;

                            // Common data to process submission.
                            contributionName = $o.contributionName.val().length ? $o.contributionName.val() : '';
                            thankYouPage = $o.thankYouPage.val().length ? $o.thankYouPage.val() : '';
                            contributionType = $o.contributionAllowMultiple.val() === '1' ? 'multiple' : 'single';
                            dialogHeader = $o.dialogHeader.val();
                            dialogDescription = $o.dialogDescription.val();

                            if (!contributionName.trim().length) {
                                $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage)
                                    .text(lpVars.i18n.errorCampaignName);
                                $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage).show();
                                return false;
                            }

                            if ('single' === contributionType) {
                                singleAmount = $o.singleContributionPrice.val();

                                if (!singleAmount.length || 0.00 === parseFloat(singleAmount)) {
                                    $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage)
                                        .text(lpVars.i18n.errorNoAmount);
                                    $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage).show();
                                    return false;
                                }

                                singleRevenue = $('input:radio:checked', $o.singleContributionForm).val();
                                // Get clean data, remove unwanted form data and rebuild final array.
                                formData = getCleanFormData($('#lp_single_contribution_form').serializeArray());
                                formData
                                    .push(
                                        {name: 'contribution_name', value: contributionName},
                                        {name: 'thank_you_page', value: thankYouPage},
                                        {name: 'single_amount', value: singleAmount},
                                        {name: 'single_revenue', value: singleRevenue}
                                    );
                                isCustomAllowed = 0;
                            } else {

                                // Loop through each multiple contribution input and build data array.
                                $($o.revenueModelMultipleItems).each(function (idx) {
                                    var inputId = idx + 1;
                                    var $inputElement = $('#lp_multiple_contribution_input_' + inputId);

                                    var price = parseInt( $inputElement.val(), 10 );
                                    price     = price.toFixed( 2 );

                                    var revenueModel = $('#post_price_revenue_model_' + inputId)
                                        .find('input:checked').val();

                                    var $currentButton = $('.lp-amount-presets-wrapper').find($o.selectedButton);
                                    if ($currentButton.length) {
                                        presetId = $currentButton.parent().attr('id').slice(-1);
                                    } else {
                                        presetId = 1;
                                    }

                                    // Only add if price is greater than 0.00
                                    if (0.00 < price) {
                                        var priceInfo = {
                                            price      : price * 100,
                                            revenue    : revenueModel,
                                            is_selected: parseInt(inputId) === parseInt(presetId)
                                        };
                                        amountsArray.push(priceInfo);
                                    }
                                });

                                if (amountsArray.length < 2) {
                                    $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage)
                                        .text(lpVars.i18n.errorNoAmountMultiple);
                                    $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage).show();
                                    return false;
                                }

                                // For GA event.
                                countOfAmounts = amountsArray.length;
                                isCustomAllowed = $o.contributionCustom.val();

                                // Get clean data, remove unwanted form data and rebuild final array.
                                formData = getCleanFormData($('#lp_multiple_contribution_form').serializeArray());
                                formData
                                    .push(
                                        {name: 'contribution_name', value: contributionName},
                                        {name: 'dialog_header', value: dialogHeader},
                                        {name: 'dialog_description', value: dialogDescription},
                                        {name: 'thank_you_page', value: thankYouPage},
                                        {name: 'all_amounts', value: JSON.stringify(amountsArray)}
                                    );

                                if ($o.contributionCustom.val() === '1') {
                                    customAmount = $o.customAmountInput.val();
                                    formData
                                        .push({name: 'custom_amount', value: customAmount});
                                }
                            }

                            $o.contributionErrorMessageWrapper.find($o.contributionErrorMessage).hide();

                            // Submit the form and send GA event if shortcode is generated successfully.
                            makeAjaxRequest(formData, countOfAmounts, isCustomAllowed);
                        }
                    });
                },

                // Remove unwanted data from final data to be sent for generating shortcode.
                getCleanFormData = function (formData) {
                    var allowedKeys = ['form', 'action', '_wpnonce', '_wp_http_referer'];
                    $.each(formData, function (i, v) {
                        if (typeof v !== 'undefined' && allowedKeys.indexOf(v.name) === -1) {
                            delete formData[i];
                        }
                    });
                    formData = formData.slice(0, 4);
                    return formData;
                },

                //Validate provided input and fix the price if not valid.
                validatePrice = function ($form, disableRevenueValidation, contribType, $element) {
                    var $priceInput, price;
                    if ('multiple' === contribType) {
                        $priceInput = $('#' + $element.attr('id'), $form);
                    } else {
                        $priceInput = $('.lp_js_priceInput', $form);
                    }

                    price = $priceInput.val();

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

                    // correct prices outside the allowed range of 0.05 - 149.99
                    if (price > lpVars.currency.sis_max) {
                        price = lpVars.currency.sis_max;
                    } else if (price > 0 && price < lpVars.currency.ppu_min) {
                        price = lpVars.currency.ppu_min;
                    }

                    if (!disableRevenueValidation) {
                        validateRevenueModel(price, $form, contribType, $element);
                    }

                    // format price with two digits
                    price = price.toFixed(2);

                    // localize price
                    if (lpVars.locale.indexOf('de_DE') !== -1) {
                        price = price.replace('.', ',');
                    }

                    // update price input
                    $priceInput.val(price);

                    return price;
                },

                // Validate the current prices' revenue model, correct if necessary.
                validateRevenueModel = function (price, $form, contribType, $element) {

                    var currentRevenueModel, postRevenueContainer, $priceInput, $payPerUse, $singleSale,
                        input = $o.revenueModelInput;

                    if ('multiple' === contribType) {
                        $priceInput = $('#' + $element.attr('id'), $form);
                        postRevenueContainer = $priceInput.parent().siblings($o.revenueModelContainer);
                        $payPerUse = $(postRevenueContainer).find('input[value=' + $o.payPerUse + ']');
                        $singleSale = $(postRevenueContainer).find('input[value=' + $o.singleSale + ']');
                        currentRevenueModel = $(postRevenueContainer).find('input:radio:checked').val();
                    } else {
                        $payPerUse = $(input + '[value=' + $o.payPerUse + ']', $form);
                        $singleSale = $(input + '[value=' + $o.singleSale + ']', $form);
                        currentRevenueModel = $('input:radio:checked', $form).val();
                    }

                    if (price === 0 || (price >= lpVars.currency.ppu_min && price < lpVars.currency.ppu_max)) {
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
                    if (price >= lpVars.currency.ppu_max && currentRevenueModel === $o.payPerUse) {
                        // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                        $singleSale.prop('checked', 'checked');
                    } else if (price < lpVars.currency.sis_min && currentRevenueModel === $o.singleSale) {
                        // Single Sale purchases are not allowed for prices < 1.00 Euro
                        $payPerUse.prop('checked', 'checked');
                    }

                    // highlight current revenue model
                    if ('multiple' === contribType) {
                        $(postRevenueContainer).find('label').removeClass($o.selected);
                        $(postRevenueContainer).find('input:checked').parent('label').addClass($o.selected);
                    } else {
                        $('label', $form).removeClass($o.selected);
                        $(input + ':checked', $form).parent('label').addClass($o.selected);
                    }
                },

                // Update the preview box based on user actions and contribution type.
                updateLivePreview = function (price, $form, contributionType) {

                    var buttonText, currencySymbol;
                    currencySymbol = 'USD' === lpVars.currency.code ? ' $' : ' €';

                    if ('multiple' === contributionType) {

                        var dialogHeader = $o.dialogHeader.val();
                        var dialogDescription = $o.dialogDescription.val();

                        if ( ! dialogHeader ) {
                            dialogHeader = $o.dialogHeader.attr( 'placeholder' );
                        }

                        if ( ! dialogDescription ) {
                            dialogDescription = $o.dialogDescription.attr( 'placeholder' );
                        }

                        $o.previewDialogHeader.text( dialogHeader );
                        $o.previewDialogDescription.text( dialogDescription );

                        $($o.revenueModelMultipleItems).each(function (idx) {
                            // Get next id to work with hidden elements mainly.
                            var inputId = idx + 1;
                            var $inputElement = $('#lp_multiple_contribution_input_' + inputId);
                            var $amountPreset = $('#lp_js_multiple_amount_' + inputId);

                            // Find and update update preview div.
                            var buttonDiv = $amountPreset.find('.lp-amount-preset-button');
                            var price = $inputElement.val().length ? $inputElement.val() : '0.00';
                            buttonDiv.text(currencySymbol + price);

                            // Display hidden input elements.
                            if (2 === inputId || 3 === inputId || 4 === inputId) {
                                if ($inputElement.val().length) {
                                    var targetWrapper = inputId + 1;
                                    $('#lp_multiple_contribution_li_' + targetWrapper).show();
                                    buttonDiv.show();
                                }
                            }

                            // Check if current inputs are the hidden one's and update preview accordingly.
                            if (3 === inputId || 4 === inputId || 5 === inputId) {
                                if (price !== '0.00') {
                                    $amountPreset.show();
                                } else {
                                    $amountPreset.hide();
                                }
                            }
                        });
                    } else {
                        var revenue = $('input:radio:checked', $form).val();

                        // Check current revenue and update preview accordingly.
                        if ('ppu' === revenue) {
                            buttonText = lpVars.i18n.contribute +
                                currencySymbol + price + ' ' + lpVars.i18n.nowOrPayLater;
                        } else {
                            buttonText = lpVars.i18n.contribute + currencySymbol + price + ' ' + lpVars.i18n.now;
                        }

                        // Update the contribution button text in preview.
                        $o.contributionSinglePreviewButton.text(buttonText);
                    }

                },

                // If the selected config is invalid or missing input disable the generate button.
                validateContributionConfig = function () {
                    var isConfigValid = true;
                    if (!$o.contributionName.val().trim().length) {
                        isConfigValid = false;
                    }

                    if (!isConfigValid) {
                        $o.contributionGenerateCode.attr('disabled', true);
                    } else {
                        $o.contributionGenerateCode.attr('disabled', false);
                    }
                },

                // Load preview for first page load.
                loadPreview = function () {
                    $o.contributionAllowMultiple.trigger('change');
                    $o.contributionCustom.trigger('change');
                    $o.contributionGenerateCode.attr('disabled', true);
                },

                // To handle "form submission".
                makeAjaxRequest = function (data, countOfAmounts, customAmount) {
                    $.ajax({
                        url     : lpVars.ajaxUrl,
                        method  : 'POST',
                        data    : data,
                        dataType: 'json',
                    }).done(function (r) {
                        $o.navigation.showMessage(r);
                        if (r.success) {
                            // Copy the shortcode.
                            copyToClipboard(r.code);

                            // Let the user know.
                            $o.contributionGenerateCode.attr('data-icon', 'f');
                            $o.contributionGenerateCode.css('opacity', '0.4');

                            var eventCategory = 'LP WP Contributions';
                            var commonLabel = lpVars.gaData.sandbox_merchant_id;
                            lpGlobal.sendLPGAEvent('Contribution Options', eventCategory,
                                commonLabel, countOfAmounts);
                            lpGlobal.sendLPGAEvent('Custom contribution amount', eventCategory,
                                commonLabel, customAmount);
                        }
                    });
                },

                // Reset the Generate and Copy Code button.
                resetGenerateButton = function () {
                    $o.contributionGenerateCode.removeAttr('data-icon');
                    $o.contributionGenerateCode.css('opacity', '1');
                },

                // Check if provided URL is valid or not.
                isValidURL = function (string) {
                    var res = string.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g); // jshint ignore:line
                    return (res !== null);
                },

                // Copy provided text to clipboard.
                copyToClipboard = function (codeText) {
                    var $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(codeText).select();
                    document.execCommand('copy');
                    $temp.remove();
                },

                // Throttle the execution of a function by a given delay.
                debounce = function (fn, delay) {
                    var timer;
                    return function () {
                        var context = this,
                            args = arguments;

                        clearTimeout(timer);

                        timer = setTimeout(function () {
                            fn.apply(context, args);
                        }, delay);
                    };
                },

                // Functions to be executed on page load.
                initializePage = function () {
                    bindEvents();
                    loadPreview();
                };

            // Go.. Go.. Go..
            initializePage();
        }

        // initialize page
        laterPayBackendContributions();

    });
})(jQuery);
