(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAccount
    function laterPayBackendAccount() {
        var $o = {
                // API credentials
                sandboxMerchantIdInput      : $('#lp_js_sandbox-merchant-id'),
                sandboxApiKeyInput          : $('#lp_js_sandbox-api-key'),
                liveMerchantIdInput         : $('#lp_js_live-merchant-id'),
                liveApiKeyInput             : $('#lp_js_live-api-key'),
                credentialsHint             : $('#lp_js_credentials-hint'),
                merchantIds                 : '.lp_js_validate-merchant-id',
                apiKeys                     : '.lp_js_validate-api-key',
                showMerchantContractsButton : $('#lp_js_show-merchant-contracts'),

                // plugin mode
                pluginModeIndicator         : $('#lp_js_plugin-mode-indicator'),
                pluginModeToggle            : $('#lp_js_toggle-plugin-mode'),
                pluginModeInput             : $('#lp_js_plugin-mode-hidden-input'),
                pluginModeTextTest          : $('#lp_js_plugin-mode-test-text'),
                pluginModeTextLive          : $('#lp_js_plugin-mode-live-text'),

                throttledFlashMessage       : null,
                flashMessageTimeout         : 800,
                requestSent                 : false,
            },

            bindEvents = function() {
                // validate and save entered LaterPay Merchant IDs
                $($o.merchantIds).bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateMerchantId($input);
                    }, 50);
                });

                // validate and save entered LaterPay API Keys
                $($o.apiKeys).bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateAPIKey($input);
                    }, 50);
                });

                // switch plugin between TEST and LIVE mode
                $o.pluginModeToggle.change(function() {
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
                var $inputs = $($o.merchantIds + ',' + $o.apiKeys);
                for (var i = 0, l = $inputs.length; i < l; i++) {
                    if ($inputs.eq(i).val() === '') {
                        $inputs.eq(i).focus();
                        return;
                    }
                }
            },

            togglePluginModeIndicators = function(mode) {
                if (mode == 'live') {
                    $o.pluginModeTextTest.hide();
                    $o.pluginModeTextLive.show();
                    $o.pluginModeIndicator.fadeOut();
                } else {
                    $o.pluginModeTextLive.hide();
                    $o.pluginModeTextTest.show();
                    $o.pluginModeIndicator.fadeIn();
                }
            },

            togglePluginMode = function() {
                var testMode                = 0,
                    liveMode                = 1,
                    hasSwitchedToLiveMode   = $o.pluginModeToggle.prop('checked');

                if (hasNoValidCredentials()) {
                    // restore test mode
                    $o.pluginModeInput.val(testMode);
                    $o.pluginModeToggle.prop('checked', false);
                    // make sure Ajax request gets sent
                    $o.requestSent = false;
                } else if (hasSwitchedToLiveMode) {
                    $o.pluginModeInput.val(liveMode);
                } else {
                    $o.pluginModeInput.val(testMode);
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
                return (
                    (
                        // plugin is in test mode, but there are no valid Sandbox API credentials
                        !$o.pluginModeToggle.prop('checked') &&
                        (
                            $o.sandboxMerchantIdInput.val().length !== 22 ||
                            $o.sandboxApiKeyInput.val().length     !== 32
                        )
                    ) || (
                        // plugin is in live mode, but there are no valid Live API credentials
                        $o.pluginModeToggle.prop('checked') &&
                        (
                            $o.liveMerchantIdInput.val().length    !== 22 ||
                            $o.liveApiKeyInput.val().length        !== 32

                        )
                    )
                );
            },

            showMerchantContracts = function() {
                var src                     = 'https://laterpay.net/terms/index.html?group=merchant-contract',
                    viewportHeight          = parseInt($(window).height(), 10),
                    topMargin               = parseInt($('#wpadminbar').height(), 10) + 26,
                    iframeHeight            = viewportHeight - topMargin,
                    $iframeWrapperObject    = $('<div id="lp_legal-docs-iframe" style="height:' + iframeHeight + 'px;"></div>'),
                    $iframeWrapper          = $('#lp_legal-docs-iframe'),
                    iframeOffset,
                    scrollPosition;

                $o.showMerchantContractsButton.fadeOut();

                // remove possibly existing iframe and insert a wrapper to display the iframe in
                if ($('iframe', $iframeWrapper).length !== 0) {
                    $('iframe', $iframeWrapper).remove();
                }
                if ($iframeWrapper.length === 0) {
                    $o.credentialsHint.after($iframeWrapperObject.slideDown(400, function() {
                        // scroll document so that iframe fills viewport
                        iframeOffset = $('#lp_legal-docs-iframe').offset();
                        scrollPosition = iframeOffset.top - topMargin;
                        $('BODY, HTML').animate({
                            scrollTop: scrollPosition
                        }, 400);
                    }));
                }

                // cache object again after replacing it
                $iframeWrapper = $('#lp_legal-docs-iframe');

                // inject a new iframe into the wrapper with the requested src parameter
                $iframeWrapper
                .html(
                    '<a href="#" id="lp_js_hide-merchant-contracts" class="lp_close-iframe">x</a>' +
                    '<iframe ' +
                        'src="' + src + '" ' +
                        'frameborder="0" ' +
                        'height="' + iframeHeight + '" ' +
                        'width="100%">' +
                    '</iframe>'
                );

                // close merchant contracts
                $('#lp_js_hide-merchant-contracts', $iframeWrapper).bind('click', function(e) {
                    $(this).fadeOut()
                        .parent('#lp_legal-docs-iframe').slideUp(400, function() {
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
