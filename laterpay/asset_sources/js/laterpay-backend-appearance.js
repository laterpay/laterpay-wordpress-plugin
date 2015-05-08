(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // appearance option
                switchButtonGroup   : $('.lp_js_switchButtonGroup'),
                buttonGroupButtons  : '.lp_js_buttonGroupButton',
                buttonGroupHint     : '.lp_js_buttonGroupHint',
                selected            : 'lp_is-selected',
                showHintOnTrue      : 'lp_js_showHintOnTrue',

                // ratings
                ratingsToggle       : $('#lp_js_enableRatingsToggle'),
                ratingsForm         : $('#lp_js_laterpayRatingsForm'),
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
                var $form                   = $trigger.parents('form'),
                    formValueIsTrue         = parseInt($('input:checked', $form).val(), 10) === 1,
                    shouldShowHintOnTrue    = $form.hasClass($o.showHintOnTrue),
                    $hint                   = $form.find($o.buttonGroupHint),
                    shouldShowHint          = shouldShowHintOnTrue && formValueIsTrue,
                    shouldHideHint          = shouldShowHintOnTrue && !formValueIsTrue;

                // mark clicked button as selected
                $($o.buttonGroupButtons, $form).removeClass($o.selected);
                $trigger.parent($o.buttonGroupButtons).addClass($o.selected);

                // show / hide hints
                if (shouldShowHint) {
                    $hint.velocity('slideDown', { duration: 250 });
                } else if (shouldHideHint) {
                    $hint.velocity('slideUp', { duration: 250 });
                }

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
