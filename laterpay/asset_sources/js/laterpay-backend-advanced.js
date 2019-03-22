(function ($) {
    $(function () {

        // encapsulate all LaterPay Javascript in function laterPayBackendAdvanced
        function laterPayBackendAdvanced() {
            var $o = {
                    // Elements on the current page.
                    showMerchantDashboard: $('#lp_js_showMerchantDashboard'),
                    showButtonGenerator  : $('#lp_js_showButtonGenerator'),
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
