(function($) {$(function() {

    // encapsulate scripts for LaterPay plugin settings page in function laterPayBackendOptions
    function laterPayBackendOptions() {
        var $o = {
                browscapCacheUpdateButton   : $('#lp_js_updateBrowscapCache'),
                statusHintText              : $('#lp_js_updateBrowscapCache').next('.lp_appended-text'),
                spinnerWrapper              : $('<span id="lp_js_spinnerWrapper">'),
                spinnerWrapperSelector      : '#lp_js_spinnerWrapper',

                flashMessageTimeout         : 800,
                requestSent                 : false
            },

            updateBrowscapCache = function() {
                // prevent duplicate Ajax requests
                if (!$o.requestSent) {
                    $o.requestSent = true;

                    // show loading message and indicator
                    $o.statusHintText.text(lpVars.i18nFetchingUpdate);
                    $o.statusHintText.before($o.spinnerWrapper);
                    showLoadingIndicator($($o.spinnerWrapperSelector));

                    $.post(
                        ajaxurl,
                        {
                            action  : 'laterpay_backend_options',
                            form    : 'update_browscap_cache'
                        },
                        function(data) {
                            if (data.success) {
                                // disable update button
                                $o.browscapCacheUpdateButton.prop('disabled', true);

                                // update status hint
                                $o.statusHintText.text(lpVars.i18nUpToDate);
                            } else {
                                // update status hint
                                $o.statusHintText.text(data.message);
                            }
                        },
                        'json'
                    )
                    .fail(function() {
                        // re-enable update button
                        $o.browscapCacheUpdateButton.prop('disabled', false);

                        // update status hint
                        $o.statusHintText.text(lpVars.i18nUpdateFailed);
                    })
                    .always(function() {
                        $o.requestSent = false;
                        $($o.spinnerWrapperSelector).remove();
                    });
                }
            },

            bindEvents = function() {
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
