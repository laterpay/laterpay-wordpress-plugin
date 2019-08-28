(function ($) {
    $(function () {

        // encapsulate all LaterPay Javascript in function laterPayBackendContributions
        function laterPayBackendContributions() {
            var $o = {
                // Elements on the current page.
                navigation: $('.lp_navigation'),
            },

                bindEvents = function () {
                },

                makeAjaxRequest = function (form_id) {
                    // prevent duplicate Ajax requests
                    $.post(
                        ajaxurl,
                        $('#' + form_id).serializeArray(),
                        function (data) {
                            $o.navigation.showMessage(data);
                        },
                        'json'
                    ).done(function () {
                        window.location.reload();
                    });

                },

                initializePage = function () {
                    bindEvents();
                };

            initializePage();
        }

        // initialize page
        laterPayBackendContributions();

    });
})(jQuery);
