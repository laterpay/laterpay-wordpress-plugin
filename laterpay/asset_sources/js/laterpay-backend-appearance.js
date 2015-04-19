(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // appearance option
                switchButtonGroup               : $('.lp_js_switchButtonGroup'),
                buttonGroupButtons              : '.lp_js_buttonGroupButton',
                selected                        : 'lp_is-selected',

                // // position of LaterPay elements
                // purchaseButtonExplanation       : $('#lp_js_purchaseButtonPositionExplanation'),
                // timePassesExplanation           : $('#lp_js_timePassesPositionExplanation'),

                // ratings
                ratingsToggle                   : $('#lp_js_enableRatingsToggle'),
                ratingsForm                     : $('#lp_js_laterpayRatingsForm'),
            },

            bindEvents = function() {
                // toggle appearance option
                $($o.switchButtonGroup)
                .change(function() {
                    switchButtonGroup($(this));
                });

                // toggle activation status of content rating
                $o.ratingsToggle
                .change(function() {
                    saveData($o.ratingsForm);
                });
            },

            switchButtonGroup = function($trigger) {
                var $form = $trigger.parents('form');

                // mark clicked button as selected
                $($o.buttonGroupButtons, $form).removeClass($o.selected);
                $trigger.parent($o.buttonGroupButtons).addClass($o.selected);

                // // show / hide explanations
                // if ($o.purchaseButtonExplanation.is(':visible')) {
                //     $o.purchaseButtonExplanation.slideUp(250);
                // } else {
                //     $o.purchaseButtonExplanation.slideDown(250);
                // }

                saveData($form);
            },

            saveData = function($form) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(data) {
                        setMessage(data);
                    }
                );
            },

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
