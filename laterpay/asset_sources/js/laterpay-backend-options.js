(function($) {$(function() {

    // encapsulate scripts for LaterPay plugin settings page in function laterPayBackendOptions
    function laterPayBackendOptions() {
        var $o = {
                browscapCacheUpdateButton   : $('#lp_js_updateBrowscapCache'),
                statusHintText              : $('#lp_js_updateBrowscapCache').next('.lp_appended-text'),
                laterpayApiFallbackSelect   : $('#lp_js_laterpayApiFallbackSelect'),
                spinnerWrapper              : $('<span id="lp_js_spinnerWrapper">'),
                spinnerWrapperSelector      : '#lp_js_spinnerWrapper',

                flashMessageTimeout         : 800,
                requestSent                 : false
            },

            bindEvents = function() {
                $o.browscapCacheUpdateButton
                .mousedown(function() {
                    updateBrowscapCache();
                })
                .click(function(e) {e.preventDefault();});

                $o.laterpayApiFallbackSelect
                .change(function() {
                    updateLaterPayApiDescription($(this));
                });
            },

            updateBrowscapCache = function() {
                // require confirmation that technical requirements are fulfilled
                if (confirm(lpVars.i18nconfirmTechnicalRequirementsForBrowscapUpdate)) {
                    // prevent duplicate Ajax requests
                    if (!$o.requestSent) {
                        $o.requestSent = true;

                        // show loading message and indicator
                        $o.statusHintText.text(lpVars.i18nFetchingUpdate);
                        $o.statusHintText.before($o.spinnerWrapper);
                        $($o.spinnerWrapperSelector).showLoadingIndicator();

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
                }
            },
            updateLaterPayApiDescription = function($select) {
                var $dfn = $o.laterpayApiFallbackSelect.next('dfn'),
                    selected_value = $select.val(),
                    description = '';
                if( lpVars.laterpayApiOptions[selected_value] !== undefined ) {
                    description = lpVars.laterpayApiOptions[selected_value].description;
                }
                $dfn.html(description);

            },

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendOptions();

});})(jQuery);
