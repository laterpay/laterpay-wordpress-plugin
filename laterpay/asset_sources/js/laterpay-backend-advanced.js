/* globals tb_show */
(function ($) {
    $(function () {

        // encapsulate all LaterPay Javascript in function laterPayBackendAdvanced
        function laterPayBackendAdvanced() {
            var $o = {
                    // Elements on the current page.
                    showMerchantDashboard     : $('#lp_js_showMerchantDashboard, #lp_js_showMerchantDashboardImage'),
                    showButtonGenerator       : $('#lp_js_showButtonGenerator, #lp_js_showButtonGeneratorImage'),
                    navigation                : $('.lp_navigation'),
                    pluginDelete              : $('.lp_js_disablePlugin'),
                    pluginDeleteConfirm       : $('.lp_js_disablePluginConfirm'),
                    modalClose                : $('button.lp_js_ga_cancel')
                },


                bindEvents = function () {

                    // Add href to dashboard based on location.
                    $o.showMerchantDashboard.bind('click', function () {
                        $(this).attr('href', $(this).data('href-' + lpVars.region));
                        return true;
                    });

                    // Add href to button generator based on location and live key value.
                    $o.showButtonGenerator.bind('click', function () {
                        var region = lpVars.region;
                        if ('false' === lpVars.liveKeyAvailable) {
                            region = 'default';
                        }
                        $(this).attr('href', $(this).data('href-' + region));
                        return true;
                    });

                    // Display modal for plugin disable.
                    $o.pluginDelete.on('click', function () {
                        if (typeof tb_show === 'function') {
                            tb_show(lpVars.modal.title, '#TB_inline?inlineId=' + lpVars.modal.id +
                                '&height=185&width=375');
                            $('div#TB_ajaxContent').css('padding', '30px');

                        }
                    });

                    // Close the modal and disable plugin.
                    $o.pluginDeleteConfirm.click(function () {
                        $('#TB_closeWindowButton').click();
                        disablePluginEraseData();
                    });

                    // Close the plugin disable modal.
                    $o.modalClose.click(function () {
                        $('#TB_closeWindowButton').click();
                    });
                },

                disablePluginEraseData = function () {
                    var data = {
                        action  : 'laterpay_disable_plugin',
                        security: lpVars.plugin_disable_nonce,
                    };

                    // Disable plugin and redirect to plugins page.
                    $.post(ajaxurl, data, function (response) {

                        if ($.type(response) === 'string') {
                            response = JSON.parse(response);
                        }

                        $o.navigation.showMessage(response);

                        if (false === response.is_vip) {
                            setTimeout(function () {
                                window.location.replace(lpVars.pluginsUrl);
                            }, 2000);
                        } else {
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        }
                    });
                },

                initializePage = function () {
                    bindEvents();
                };

            initializePage();
        }

        // initialize page
        laterPayBackendAdvanced();

    });
})(jQuery);
