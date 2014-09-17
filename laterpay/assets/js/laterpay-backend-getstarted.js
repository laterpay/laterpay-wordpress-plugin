(function($) { $(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendGetStarted
    function laterPayBackendGetStarted() {
        var $o = {
                // plugin navigation
                navigationTabs          : $('.lp_get-started-page .lp_nav-tabs li a'),

                form                    : $('#lp_get-started-form'),

                // progress indicator (todo / done)
                progressStepsTodo       : $('.lp_steps-background .lp_step-todo'),
                progressStepOne         : $('.lp_steps-background .lp_step-1'),

                // LaterPay API credentials
                apiCredentials          : $('.lp_api-key-input, .lp_merchant-id-input'),
                merchantIdInput         : $('.lp_merchant-id-input'),
                apiKeyInput             : $('.lp_api-key-input'),

                // global default price
                defaultPriceInput       : $('#lp_global-default-price'),
                defaultPrice            : $('#lp_global-default-price').val(),

                // activate button
                activatePlugin          : $('.lp_activate-plugin-button'),

                throttledFlashMessage   : null,

                // strings cached for better compression
                todo                    : 'lp_step-todo',
                done                    : 'lp_step-done',
            },

            bindEvents = function() {
                // validate entered LaterPay API credentials (Merchant ID + API Key)
                $o.apiCredentials.bind('input', function() {
                    validateAPICredentials();
                });

                // validate entered global default price
                $o.defaultPriceInput.blur(function() {
                    $o.defaultPriceInput.val(validatePrice($o.defaultPrice));
                });

                // activate LaterPay plugin in TEST mode
                $o.activatePlugin
                .click(function() {
                    activateTestMode();
                });

                // disable tabs of other plugin backend pages
                $o.navigationTabs
                .mousedown(function() {
                    alert(lpVars.i18nTabsDisabled);

                    return false;
                });
            },

            validateAPICredentials = function() {
                var keyValue    = $o.apiKeyInput.val().trim(),
                    idValue     = $o.merchantIdInput.val().trim();

                // clear flash message timeout
                window.clearTimeout($o.throttledFlashMessage);

                // trim credential values
                if (keyValue.length !== $o.apiKeyInput.val().length) {
                    $o.apiKeyInput.val(keyValue);
                }
                if (idValue.length !== $o.merchantIdInput.val().length) {
                    $o.merchantIdInput.val(idValue);
                }

                // check if Merchant ID and API Key with proper length exist
                if (keyValue.length === 32 && idValue.length === 22) {
                    $o.progressStepOne.removeClass($o.todo).addClass($o.done);
                    clearMessage();

                    return true;
                } else {
                    $o.progressStepOne.removeClass($o.done).addClass($o.todo);

                    // show error messages
                    if (idValue.length > 0 && idValue.length !== 22) {
                        // set timeout to throttle flash message
                        $o.throttledFlashMessage = window.setTimeout(function() {
                            setMessage(lpVars.i18nInvalidMerchantId, false);
                        }, 500);
                    }
                    if (keyValue.length > 0 && keyValue.length !== 32) {
                        // set timeout to throttle flash message
                        $o.throttledFlashMessage = window.setTimeout(function() {
                            setMessage(lpVars.i18nInvalidApiKey, false);
                        }, 500);
                    }

                    return false;
                }
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

                // show flash message when correcting an invalid price
                if (corrected) {
                    setMessage(lpVars.i18nOutsideAllowedPriceRange, false);
                }

                return price;
            },

            activateTestMode = function() {
                if (!validateAPICredentials()) {
                    setMessage($(this).data().error, false);
                    return;
                }

                // validate price
                $o.defaultPriceInput.val(validatePrice($o.defaultPrice));

                // update progress indicator
                $o.progressStepsTodo.removeClass($o.todo).addClass($o.done);

                $.post(
                    ajaxurl,
                    $o.form.serializeArray(),
                    function(data) {
                        window.location = 'post-new.php';
                    }
                );

                return false;
            },

            hideWordPressPointer = function() {
                // hide pointer hinting at the LaterPay plugin while viewing the getStarted tab
                if (typeof($().pointer) !== 'undefined' && $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer')) {
                    $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer').pointer.hide();
                }
            },

            initializePage = function() {
                bindEvents();
                hideWordPressPointer();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendGetStarted();

});})(jQuery);
