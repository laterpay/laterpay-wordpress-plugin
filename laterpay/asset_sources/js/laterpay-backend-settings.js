(function($) {$(function() {
    // encapsulate all LaterPay Javascript in function laterPayBackendSettings
    function laterPayBackendSettings() {
        var $o = {
                advancedSettingsSubmitButton : '.lp_js_advancedSettingsSubmitButton',
            },

        bindEvents = function() {
            $($o.advancedSettingsSubmitButton)
            .mousedown(function() {
                saveAdvancedSettings($(this).parents('form'));
            })
            .click(function(e) {e.preventDefault();});
        },

        saveAdvancedSettings = function($form) {
            $.post(
                ajaxurl,
                $form.serializeArray(),
                function(response) {
                    if (response.success) {
                        // reload page
                        window.location.reload();
                    }
                },
                'json'
            );
        },

        initializePage = function() {
            bindEvents();
        };

        initializePage();
    }

    // initialize page
    laterPayBackendSettings();
});})(jQuery);