(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // paid content preview
                previewForm                     : $('#lp_js_previewModeForm'),
                togglePreviewMode               : $('.lp_js_togglePreviewMode'),

                // ratings
                ratingsToggle                   : $('#lp_js_enableRatingsToggle'),
                ratingsForm                     : $('#lp_js_laterpayRatingsForm'),

                // position of LaterPay elements
                purchaseButtonPositionForm      : $('#lp_js_purchaseButtonPositionForm'),
                togglePurchaseButtonPosition    : $('#lp_js_togglePurchaseButtonPosition'),
                purchaseButtonExplanation       : $('#lp_js_purchaseButtonPosition__explanation'),
                timePassPositionForm            : $('#lp_js_timePassesPositionForm'),
                toggleTimePassesPosition        : $('#lp_js_toggleTimePassesPosition'),
                timePassesExplanation           : $('#lp_js_timePassesPosition__explanation'),
            },

            bindEvents = function() {
                // toggle paid content preview mode
                $($o.togglePreviewMode, $o.previewForm)
                .change(function() {
                    saveData($o.previewForm);
                });

                // toggle activation status of content rating
                $o.ratingsToggle
                .change(function() {
                    saveData($o.ratingsForm);
                });

                // toggle positioning mode of purchase button
                $o.togglePurchaseButtonPosition
                .change(function() {
                    saveData($o.purchaseButtonPositionForm);

                    // show / hide explanation how to customize position
                    if ($o.purchaseButtonExplanation.is(':visible')) {
                        $o.purchaseButtonExplanation.slideUp(250);
                    } else {
                        $o.purchaseButtonExplanation.slideDown(250);
                    }
                });

                // toggle positioning mode of time passes
                $o.toggleTimePassesPosition
                .change(function() {
                    saveData($o.timePassPositionForm);

                    // show / hide explanation how to customize position
                    if ($o.timePassesExplanation.is(':visible')) {
                        $o.timePassesExplanation.slideUp(250);
                    } else {
                        $o.timePassesExplanation.slideDown(250);
                    }
                });
            },

            saveData = function( $form ) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(data) {
                        setMessage(data);
                    }
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
