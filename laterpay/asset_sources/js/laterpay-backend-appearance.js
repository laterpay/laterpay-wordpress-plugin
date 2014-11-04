(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                previewForm   : $('#laterpay_paid_content_preview_form'),
                postTypeForm  : $('#laterpay_enabled_post_types_form'),

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

                // save post types LaterPay is enabled for
                $o.postTypeForm
                .change(function() {
                    saveEnabledPostTypes();
                });

                // save ratings
                $o.ratingsToggle.change(function() {
                    saveRatings();
                });
            },

            saveEnabledPostTypes = function() {
                $.post(
                    ajaxurl,
                    $o.postTypeForm.serializeArray(),
                    function(data) {setMessage(data);}
                );
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
