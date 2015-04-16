(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAccount
    function laterPayBackendOptions() {
        var $o = {
                // API credentials
                browscapCacheUpdateButton       : $('.lp_js_BrowscapCacheUpdate'),
                flashMessageTimeout             : 800,
                requestSent                     : false
            },

            updateBrowscapCache = function() {
                // prevent duplicate Ajax requests
                if (!$o.requestSent) {
                    $o.requestSent = true;
                    var $spinnerContainer   = $('<span class="lp_loading-container">'),
                        $optionContainer    = $o.browscapCacheUpdateButton.parents('.form-table'),
                        $buttonContainer    = $o.browscapCacheUpdateButton.parent(),
                        $buttonDescription  = $o.browscapCacheUpdateButton.parent().find('dfn');

                    $buttonContainer.append($spinnerContainer);
                    showLoadingIndicator($spinnerContainer);
                    $.post(
                        ajaxurl,
                        {action:'laterpay_backend_options', 'form': 'update_browscap_cache'},
                        function(data) {
                            $optionContainer.showMessage(data);
                            if(data.success) {
                                $o.browscapCacheUpdateButton.attr('disabled', true);
                                $buttonDescription.text(lpVars.i18nUpToDate);
                            }
                        },
                        'json'
                    )
                    .fail(function() {
                        $optionContainer.showMessage({message: lpVars.i18nUpdateFailed, success: false});
                    })
                    .always(function() {
                        $spinnerContainer.remove();
                        $o.requestSent = false;
                    });
                }
            },

            bindEvents = function() {
                // show LaterPay merchant contracts for requesting LIVE API credentials
                $o.browscapCacheUpdateButton
                    .mousedown(function() {
                        updateBrowscapCache();
                    })
                    .click(function(e) {e.preventDefault();});
            },

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendOptions();

});})(jQuery);
