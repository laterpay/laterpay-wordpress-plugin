(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // preview
                previewForm                  : $('#laterpay_paid_content_preview_form'),

                // ratings
                ratingsToggle                : $('#lp_js_enableRatingsToggle'),
                ratingsForm                  : $('#lp_js_laterpayRatingsForm'),

                // elements position type
                togglePurchaseButtonPosition : $('#lp_js_togglePurchaseButtonPosition'),
                purchaseButtonPositionForm   : $('#lp_js_laterpayPurchaseButtonPositionForm'),
                toggleTimePassesPosition     : $('#lp_js_toggleTimePassesPosition'),
                timePassPositionForm         : $('#lp_js_laterpayTimePassPositionForm'),
            },

            bindEvents = function() {
                // switch paid content preview mode
                $('.lp_js_togglePreviewMode', $o.previewForm)
                .change(function() {
                    saveData( $o.previewForm );
                });

                // save ratings
                $o.ratingsToggle.change(function() {
                    saveData( $o.ratingsForm );
                });

                $o.togglePurchaseButtonPosition.change(function() {
                    saveData( $o.purchaseButtonPositionForm );
                });

                $o.toggleTimePassesPosition.change(function() {
                    saveData( $o.timePassPositionForm );
                });
            },

            saveData = function( $form ) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
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
