(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAccount
    function laterPayBackendAccount() {
        var $o = {
                // API credentials
                apiKeyInput                 : $('.lp_js_validateApiKey'),
                merchantIdInput             : $('.lp_js_validateMerchantId'),
                // ...
                isLive                      : 'lp_is-live',

                // plugin mode
                pluginModeToggle            : $('#lp_js_togglePluginMode'),

                showMerchantContractsButton : $('#lp_js_showMerchantContracts'),

                throttledFlashMessage       : undefined,
                flashMessageTimeout         : 800,
                requestSent                 : false,
                // TODO: extract common HTML elements
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
                var $inputs = $('.lp_js_validateApiKey, .lp_js_validateMerchantId');
                for (var i = 0, l = $inputs.length; i < l; i++) {
                    if ($inputs.eq(i).val() === '') {
                        $inputs.eq(i).focus();
                        return;
                    }
                }
            },

            togglePluginModeIndicators = function(mode) {
                if (mode === 'live') {
                    $('#lp_js_pluginMode_testText').hide();
                    $('#lp_js_pluginMode_liveText').show();
                    $('#lp_js_pluginModeIndicator').fadeOut();
                    $('.lp_liveCredentials').addClass($o.isLive);
                } else {
                    $('#lp_js_pluginMode_liveText').hide();
                    $('#lp_js_pluginMode_testText').show();
                    $('#lp_js_pluginModeIndicator').fadeIn();
                    $('.lp_liveCredentials').removeClass($o.isLive);
                }
            },

            togglePluginMode = function() {
                var $toggle                 = $o.pluginModeToggle,
                    $input                  = $('#lp_js_pluginMode_hiddenInput'),
                    testMode                = 0,
                    liveMode                = 1,
                    hasSwitchedToLiveMode   = $toggle.prop('checked');

                if (hasNoValidCredentials()) {
                    // restore test mode
                    $input.val(testMode);
                    $toggle.prop('checked', false);
                    // make sure Ajax request gets sent
                    $o.requestSent = false;
                } else if (hasSwitchedToLiveMode) {
                    $input.val(liveMode);
                } else {
                    $input.val(testMode);
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
                            $('#lp_js_sandboxApiKey').val().length     !== 32 ||
                            $('#lp_js_sandboxMerchantId').val().length !== 22
                        )
                    ) || (
                        // plugin is in live mode, but there are no valid Live API credentials
                        $o.pluginModeToggle.prop('checked') &&
                        (
                            $('#lp_js_liveApiKey').val().length        !== 32 ||
                            $('#lp_js_liveMerchantId').val().length    !== 22
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
                    $iframeWrapperObject    = $('<div id="lp_legalDocs_iframe" style="height:' +
                                                iframeHeight +
                                              'px;"></div>'),
                    $iframeWrapper          = $('#lp_legalDocs_iframe'),
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
                        iframeOffset = $('#lp_legalDocs_iframe').offset();
                        scrollPosition = iframeOffset.top - topMargin;
                        $('BODY, HTML').animate({
                            scrollTop: scrollPosition
                        }, 400);
                    }));
                }

                // cache object again after replacing it
                $iframeWrapper = $('#lp_legalDocs_iframe');

                // inject a new iframe into the wrapper with the requested src parameter
                $iframeWrapper
                .html(
                    '<a href="#" id="lp_js_hideMerchantContracts" class="lp_legalDocs_closeLink">x</a>' +
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
                        .parent('#lp_legalDocs_iframe').slideUp(400, function() {
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
