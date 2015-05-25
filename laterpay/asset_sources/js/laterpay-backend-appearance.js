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

                // hide free posts
                hideFreePostsToggle : $('#lp_js_hideFreePostsToggle'),
                hideFreePostsForm   : $('#lp_js_laterpayHideFreePostsForm'),
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

                // toggle activation status of hide free posts
                $o.hideFreePostsToggle
                .change(function() {
                    saveData($o.hideFreePostsForm);
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
                    $hint.velocity('slideDown', { duration: 250, easing: 'ease-out' });
                } else if (shouldHideHint) {
                    $hint.velocity('slideUp', { duration: 250, easing: 'ease-out' });
                }

                saveData($form);
            },

            saveData = function($form) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(data) {
                        $('.lp_navigation').showMessage(data);
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
