(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                form: $('#laterpay_paid_content_preview_form'),
            },

            bindEvents = function() {
                // switch paid content preview mode
                $('input[name=paid_content_preview]', $o.form)
                .change(function() {
                    saveAppearance();
                });
            },

            saveAppearance = function() {
                $.post(
                    ajaxurl,
                    $o.form.serializeArray(),
                    function(data) {setMessage(data);}
                );
            },

            styleInputs = function() {
                $('input[type=checkbox].styled, input[type=radio].styled').ezMark();
            },

            initializePage = function() {
                bindEvents();
                styleInputs();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
