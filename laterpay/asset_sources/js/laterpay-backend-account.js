(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAccount
    function laterPayBackendAccount() {
        var $o = {
                // API credentials
                apiKeyInput                     : $('.lp_js_validateApiKey'),
                merchantIdInput                 : $('.lp_js_validateMerchantId'),
                apiCredentialsInputs            : $('.lp_js_validateApiKey, .lp_js_validateMerchantId'),
                testMerchantId                  : $('#lp_js_sandboxMerchantId'),
                testApiKey                      : $('#lp_js_sandboxApiKey'),
                liveMerchantId                  : $('#lp_js_liveMerchantId'),
                liveApiKey                      : $('#lp_js_liveApiKey'),

                // plugin mode
                pluginModeIndicator             : $('#lp_js_pluginModeIndicator'),
                pluginModeToggle                : $('#lp_js_togglePluginMode'),
                pluginVisibilitySetting         : $('#lp_js_pluginVisibilitySetting'),
                pluginVisibilityToggle          : $('#lp_js_toggleVisibilityInTestMode'),
                hasInvalidSandboxCredentials    : $('#lp_js_hasInvalidSandboxCredentials'),
                isLive                          : 'lp_is-live',

                showMerchantContractsButton     : $('#lp_js_showMerchantContracts'),

                throttledFlashMessage           : undefined,
                flashMessageTimeout             : 800,
                requestSent                     : false,
            },

            bindEvents = function() {
                // validate and save entered LaterPay API Keys
                $o.apiKeyInput
                .bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateAPIKey($input);
                    }, 50);
                });

                // validate and save entered LaterPay Merchant IDs
                $o.merchantIdInput
                .bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateMerchantId($input);
                    }, 50);
                });

                // switch plugin between TEST and LIVE mode
                $o.pluginModeToggle
                .change(function() {
                    togglePluginMode();
                });

                // switch plugin visibility in TEST mode
                $o.pluginVisibilityToggle
                .change(function() {
                    toggleVisibilityInTestMode();
                });

                // ask user for confirmation, if he tries to leave the page without a set of valid API credentials
                window.onbeforeunload = function() {
                    preventLeavingWithoutValidCredentials();
                };

                // show LaterPay merchant contracts for requesting LIVE API credentials
                $o.showMerchantContractsButton
                .mousedown(function() {
                    showMerchantContracts();
                })
                .click(function(e) {e.preventDefault();});
            },

            autofocusEmptyInput = function() {
                var i = 0,
                    l = $o.apiCredentialsInputs.length;

                for (; i < l; i++) {
                    if ($o.apiCredentialsInputs.eq(i).val() === '') {
                        $o.apiCredentialsInputs.eq(i).focus();
                        return;
                    }
                }
            },

            toggleVisibilityInTestMode = function() {
                if (hasNoValidCredentials()) {
                    // save information in form that credentials are invalid
                    $o.hasInvalidSandboxCredentials.val(1);

                    // switch to invisible test mode to make sure visitors don't see a broken site
                    $o.pluginVisibilityToggle.prop('checked', false);

                    // focus Merchant ID input in case the user just forgot to enter his credentials
                    $o.testMerchantId.focus();

                    // make sure Ajax request gets sent
                    $o.requestSent = false;
                } else {
                    $o.hasInvalidSandboxCredentials.val(0);
                }

                // save visibility in test mode
                makeAjaxRequest('laterpay_test_mode');
            },

            togglePluginModeIndicators = function(mode) {
                if (mode === 'live') {
                    $('#lp_js_pluginModeIndicator').fadeOut();
                    $('#lp_js_liveCredentials').addClass($o.isLive);
                } else {
                    $('#lp_js_pluginModeIndicator').fadeIn();
                    $('#lp_js_liveCredentials').removeClass($o.isLive);
                }
            },

            togglePluginMode = function() {
                var $toggle                 = $o.pluginModeToggle,
                    hasSwitchedToLiveMode   = $toggle.prop('checked');

                if (hasNoValidCredentials()) {
                    // restore test mode
                    $toggle.prop('checked', false);

                    // focus Merchant ID input in case the user just forgot to enter his credentials
                    $o.liveMerchantId.focus();

                    // make sure Ajax request gets sent
                    $o.requestSent = false;

                    // show additional toggle for switching between visible and invisible test mode
                    $o.pluginVisibilitySetting.fadeIn(250);
                } else if (hasSwitchedToLiveMode) {
                    // hide toggle for switching between visible and invisible test mode
                    $o.pluginVisibilitySetting.fadeOut(250);
                } else {
                    // hide toggle for switching between visible and invisible test mode
                    $o.pluginVisibilitySetting.fadeIn(250);
                }

                // save plugin mode
                makeAjaxRequest('laterpay_plugin_mode');
            },

            makeAjaxRequest = function(form_id) {
                // prevent duplicate Ajax requests
                if (!$o.requestSent) {
                    $o.requestSent = true;

                    $.post(
                        ajaxurl,
                        $('#' + form_id).serializeArray(),
                        function(data) {
                            setMessage(data.message, data.success);
                            togglePluginModeIndicators(data.mode);
                        },
                        'json'
                    )
                    .done(function() {
                        $o.requestSent = false;
                    });
                }
            },

            validateAPIKey = function(api_key_input) {
                var $input          = $(api_key_input),
                    $form           = $input.parents('form'),
                    value           = $input.val().trim(),
                    apiKeyLength    = 32;

                // clear flash message timeout
                window.clearTimeout($o.throttledFlashMessage);

                // trim spaces from input
                if (value.length !== $input.val().length) {
                    $input.val(value);
                }

                if (value.length === 0 || value.length === apiKeyLength) {
                    // save the value, because it's valid (empty input or string of correct length)
                    makeAjaxRequest($form.attr('id'));
                } else {
                    // set timeout to throttle flash message
                    $o.throttledFlashMessage = window.setTimeout(function() {
                        setMessage(lpVars.i18nApiKeyInvalid, false);
                    }, $o.flashMessageTimeout);

                    // switch to invisible test mode to make sure visitors don't see a broken site,
                    // if we are in test mode;
                    // no action is required here, if we are in live mode, because there is another check below,
                    // if we need to switch from live to test mode
                    var currentFormId = $o.testApiKey.parents('form').attr('id');
                    if ($form.attr('id') === currentFormId) {
                        toggleVisibilityInTestMode();
                    }
                }

                // switch from live mode to test mode, if there are no valid live credentials
                if (hasNoValidCredentials()) {
                    togglePluginMode();
                }
            },

            validateMerchantId = function(merchant_id_input) {
                var $input              = $(merchant_id_input),
                    $form               = $input.parents('form'),
                    value               = $input.val().trim(),
                    merchantIdLength    = 22;

                // clear flash message timeout
                window.clearTimeout($o.throttledFlashMessage);

                // trim spaces from input
                if (value.length !== $input.val().length) {
                    $input.val(value);
                }

                if (value.length === 0 || value.length === merchantIdLength) {
                    // save the value, because it's valid (empty input or string of correct length)
                    makeAjaxRequest($form.attr('id'));
                } else {
                    // set timeout to throttle flash message
                    $o.throttledFlashMessage = window.setTimeout(function() {
                        setMessage(lpVars.i18nMerchantIdInvalid, false);
                    }, $o.flashMessageTimeout);

                    // switch to invisible test mode to make sure visitors don't see a broken site,
                    // if we are in test mode;
                    // no action is required here, if we are in live mode, because there is another check below,
                    // if we need to switch from live to test mode
                    var currentFormId = $o.testMerchantId.parents('form').attr('id');
                    if ($form.attr('id') === currentFormId) {
                        toggleVisibilityInTestMode();
                    }
                }

                // switch from live mode to test mode, if there are no valid live credentials
                if (hasNoValidCredentials()) {
                    togglePluginMode();
                }
            },

            hasNoValidCredentials = function() {
                if (
                    (
                        // plugin is in test mode, but there are no valid Sandbox API credentials
                        !$o.pluginModeToggle.prop('checked') &&
                        (
                            $o.testApiKey.val().length     !== 32 ||
                            $o.testMerchantId.val().length !== 22
                        )
                    ) || (
                        // plugin is in live mode, but there are no valid Live API credentials
                        $o.pluginModeToggle.prop('checked') &&
                        (
                            $o.liveApiKey.val().length        !== 32 ||
                            $o.liveMerchantId.val().length    !== 22
                        )
                    )
                ) {
                    return true;
                } else {
                    return false;
                }
            },

            showMerchantContracts = function() {
                var src                     = 'https://laterpay.net/terms/index.html?group=merchant-contract',
                    viewportHeight          = parseInt($(window).height(), 10),
                    topMargin               = parseInt($('#wpadminbar').height(), 10) + 26,
                    iframeHeight            = viewportHeight - topMargin,
                    $iframeWrapperObject    = $('<div id="lp_js_legalDocsIframe" class="lp_legal-docs-iframe" ' +
                                                    'style="height:' + iframeHeight + 'px;">' +
                                                '</div>'),
                    $iframeWrapper          = $('#lp_js_legalDocsIframe'),
                    iframeOffset,
                    scrollPosition;

                $o.showMerchantContractsButton.fadeOut();

                // remove possibly existing iframe and insert a wrapper to display the iframe in
                if ($('iframe', $iframeWrapper).length !== 0) {
                    $('iframe', $iframeWrapper).remove();
                }
                if ($iframeWrapper.length === 0) {
                    $('#lp_js_credentialsHint').after($iframeWrapperObject.slideDown(400, function() {
                        // scroll document so that iframe fills viewport
                        iframeOffset = $('#lp_js_legalDocsIframe').offset();
                        scrollPosition = iframeOffset.top - topMargin;
                        $('BODY, HTML').animate({
                            scrollTop: scrollPosition
                        }, 400);
                    }));
                }

                // re-cache object after replacing it
                $iframeWrapper = $('#lp_js_legalDocsIframe');

                // inject a new iframe into the wrapper with the requested src parameter
                $iframeWrapper
                .html(
                    '<a href="#" id="lp_js_hideMerchantContracts" class="lp_legal-docs-iframe__close-link">x</a>' +
                    '<iframe ' +
                        'src="' + src + '" ' +
                        'frameborder="0" ' +
                        'height="' + iframeHeight + '" ' +
                        'width="100%">' +
                    '</iframe>'
                );

                // close merchant contracts
                $('#lp_js_hideMerchantContracts', $iframeWrapper).bind('click', function(e) {
                    $(this).fadeOut()
                        .parent('#lp_js_legalDocsIframe').slideUp(400, function() {
                            $(this).remove();
                        });
                    $o.showMerchantContractsButton.fadeIn();
                    e.preventDefault();
                });
            },

            preventLeavingWithoutValidCredentials = function() {
                if (hasNoValidCredentials()) {
                    return lpVars.i18nPreventUnload;
                }
            },

            initializePage = function() {
                bindEvents();
                autofocusEmptyInput();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAccount();

});})(jQuery);
