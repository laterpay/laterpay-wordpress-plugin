(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAccount
    function laterPayBackendAccount() {
        var $o = {
                throttledFlashMessage   : null,
                flashMessageTimeout     : 800,
                requestSent             : false,
                // TODO: extract common HTML elements
            },

            bindEvents = function() {
                // validate and save entered LaterPay API Keys
                $('.lp_api-key-input').bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateAPIKey($input);
                    }, 50);
                });

                // validate and save entered LaterPay Merchant IDs
                $('.lp_merchant-id-input').bind('input', function() {
                    var $input = this;
                    setTimeout(function() {
                        validateMerchantId($input);
                    }, 50);
                });

                // switch plugin between TEST and LIVE mode
                $('#lp_plugin-mode-toggle').click(function() {
                    return togglePluginMode();
                });

                // ask user for confirmation, if he tries to leave the page without a set of valid API credentials
                window.onbeforeunload = function() {
                    preventLeavingWithoutValidCredentials();
                };

                // show LaterPay merchant contracts for requesting LIVE API credentials
                $('#lp_request-live-credentials a')
                .mousedown(function() {
                    showMerchantContracts();
                })
                .click(function(e) {e.preventDefault();});
            },

            autofocusEmptyInput = function() {
                var $inputs = $('.lp_api-key-input, .lp_merchant-id-input');
                for (var i = 0, l = $inputs.length; i < l; i++) {
                    if ($inputs.eq(i).val() === '') {
                        $inputs.eq(i).focus();
                        return;
                    }
                }
            },

            togglePluginModeIndicators = function(mode) {
                if (mode == 'live') {
                    $('#lp_plugin-mode-test-text').hide();
                    $('#lp_plugin-mode-live-text').show();
                    $('.lp_plugin-mode-indicator').fadeOut();
                } else {
                    $('#lp_plugin-mode-live-text').hide();
                    $('#lp_plugin-mode-test-text').show();
                    $('.lp_plugin-mode-indicator').fadeIn();
                }
            },

            togglePluginMode = function() {
                var $toggle                 = $('#lp_plugin-mode-toggle'),
                    $input                  = $('#lp_plugin-mode-hidden-input'),
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
                        !$('#lp_plugin-mode-toggle').prop('checked') &&
                        (
                            $('#lp_sandbox-api-key').val().length     !== 32 ||
                            $('#lp_sandbox-merchant-id').val().length !== 22
                        )
                    ) || (
                        // plugin is in live mode, but there are no valid Live API credentials
                        $('#lp_plugin-mode-toggle').prop('checked') &&
                        (
                            $('#lp_live-api-key').val().length        !== 32 ||
                            $('#lp_live-merchant-id').val().length    !== 22
                        )
                    )
                ) {
                    return true;
                } else {
                    return false;
                }
            },

            showMerchantContracts = function() {
                var $button                 = $(this),
                    src                     = 'https://laterpay.net/terms/index.html?group=merchant-contract',
                    viewportHeight          = parseInt($(window).height(), 10),
                    topMargin               = parseInt($('#wpadminbar').height(), 10) + 26,
                    iframeHeight            = viewportHeight - topMargin,
                    $iframeWrapperObject    = $('<div id="lp_legal-docs-iframe" style="height:' + iframeHeight + 'px;"></div>'),
                    $iframeWrapper          = $('#lp_legal-docs-iframe'),
                    iframeOffset,
                    scrollPosition;

                $button.fadeOut();

                // remove possibly existing iframe and insert a wrapper to display the iframe in
                if ($('iframe', $iframeWrapper).length !== 0) {
                    $('iframe', $iframeWrapper).remove();
                }
                if ($iframeWrapper.length === 0) {
                    $('.lp_credentials-hint').after($iframeWrapperObject.slideDown(400, function() {
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
                    '<a href="#" class="lp_close-iframe">x</a>' +
                    '<iframe ' +
                        'src="' + src + '" ' +
                        'frameborder="0" ' +
                        'height="' + iframeHeight + '" ' +
                        'width="100%">' +
                    '</iframe>'
                );
                $('.lp_close-iframe', $iframeWrapper).bind('click', function(e) {
                    $(this).fadeOut()
                        .parent('#lp_legal-docs-iframe').slideUp(400, function() {
                            $(this).remove();
                        });
                    $button.fadeIn();
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
