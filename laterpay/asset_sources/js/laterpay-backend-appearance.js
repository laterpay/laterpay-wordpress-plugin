(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // preview
                previewForm   : $('#laterpay_paid_content_preview_form'),

                // ratings
                ratingsToggle : $('#lp_js_enableRatingsToggle'),
                ratingsForm   : $('#lp_js_laterpayRatingsForm')
            },

            bindEvents = function() {
                // switch paid content preview mode
                $('.lp_js_togglePreviewMode', $o.previewForm)
                .change(function() {
                    saveAppearance();
                });

                // save ratings
                $o.ratingsToggle.change(function() {
                    saveRatings();
                });
            },

            saveAppearance = function() {
                $.post(
                    ajaxurl,
                    $o.previewForm.serializeArray(),
                    function(data) {setMessage(data);}
                );
            },

            saveRatings = function() {
                $.post(
                    ajaxurl,
                    $o.ratingsForm.serializeArray(),
                    function(data) {setMessage(data);}
                );
            },

            styleInputs = function() {
                $('.lp_js_styleInput').ezMark();
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
