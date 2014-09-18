(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                form: $('#laterpay_paid_content_preview_form'),
            },

            bindEvents = function() {
                // switch paid content preview mode
                $('.lp_js_toggle-preview-mode', $o.form)
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
                $('.lp_js_style-input').ezMark();
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
