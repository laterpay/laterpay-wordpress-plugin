(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // appearance option
                switchButtonGroup   : $('.lp_js_switchButtonGroup'),
                overlayPreview      : $('.lp_js_purchaseForm'),
                savePurchaseForm    : $('.lp_js_savePurchaseForm'),
                cancelFormEdit      : $('.lp_js_cancelEditingPurchaseForm'),
                restoreDefaults     : $('.lp_js_restoreDefaultPurchaseForm'),
                buttonGroupButtons  : '.lp_js_buttonGroupButton',
                buttonGroupHint     : '.lp_js_buttonGroupHint',
                overlayOptions      : '.lp_js_overlayOptions',
                overlayShowFooter   : '.lp_js_overlayShowFooter',
                selected            : 'lp_is-selected',
                showHintOnTrue      : 'lp_js_showHintOnTrue',
                headerBgColor       : 'lp_js_purchaseHeaderBackgroundColor',
                headerTitle         : 'lp_js_purchaseHeaderTitle',
                purchaseBgColor     : 'lp_js_purchaseBackgroundColor',
                purchaseMainText    : 'lp_js_purchaseMainTextColor',
                purchaseDescription : 'lp_js_purchaseDescriptionTextColor',
                buttonBgColor       : 'lp_js_purchaseButtonBackgroundColor',
                buttonTextColor     : 'lp_js_purchaseButtonTextColor',
                footerBgColor       : 'lp_js_purchaseFooterBackgroundColor',
                linkMainColor       : 'lp_js_purchaseLinkMainColor',
                linkHoverColor      : 'lp_js_purchaseLinkHoverColor',

                // overlay
                overlayHeader       : '.lp_purchase-overlay__header',
                overlayForm         : '.lp_purchase-overlay__form',
                overlayOptionTitle  : '.lp_purchase-overlay-option__title',
                overlayDescription  : '.lp_purchase-overlay-option__description',
                overlayLink         : '.lp_purchase-overlay__notification',
                overlayButton       : '.lp_purchase-overlay__submit',
                overlayFooter       : '.lp_purchase-overlay__footer',

                // ratings
                ratingsToggle       : $('#lp_js_enableRatingsToggle'),
                ratingsForm         : $('#lp_js_laterpayRatingsForm'),

                // hide free posts
                hideFreePostsToggle : $('#lp_js_hideFreePostsToggle'),
                hideFreePostsForm   : $('#lp_js_laterpayHideFreePostsForm')
            },

            bindEvents = function() {
                // toggle appearance option
                $o.switchButtonGroup
                .change(function() {
                    switchButtonGroup($(this));
                });

                // toggle elements change
                $($o.overlayOptions)
                .change(function() {
                    updateOverlayOptions($(this));
                });

                // show/hide footer
                $($o.overlayShowFooter)
                .click(function(){
                    processFooter($(this));
                });

                // save overlay settings
                $o.savePurchaseForm
                .click(function(){
                    var $form = $(this).parents('form');

                    // set correct form name
                    $("input[name='form']", $form).val('overlay_settings');

                    saveData($form);
                });

                // restore original data
                $o.cancelFormEdit
                .click(function(){
                    resetOverlaySettings(lpVars.overlaySettings.current);
                });

                // set default settings
                $o.restoreDefaults
                .click(function(){
                    resetOverlaySettings(lpVars.overlaySettings.default);
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
                var $form = $trigger.parents('form');

                // set correct form name
                $("input[name='form']", $form).val('paid_content_preview');

                // mark clicked button as selected
                $($o.buttonGroupButtons, $form).removeClass($o.selected);
                $trigger.parent($o.buttonGroupButtons).addClass($o.selected);

                // disable all inputs
                $o.overlayPreview.hide();
                $(':input', $o.overlayPreview).attr("disabled", true);

                saveData($form);

                // enable inputs for purchase overlay
                if ($('input:checked', $form).val() === '2') {
                    $o.overlayPreview.show();
                    $(':input', $o.overlayPreview).attr("disabled", false);
                }
            },

            updateOverlayOptions = function($trigger) {
                var style;

                // change header bg
                if ($trigger.hasClass($o.headerBgColor)) {
                    style = 'background-color: ' + $('.' + $o.headerBgColor).val() + ' !important;';
                    setStyle($o.overlayHeader, style);
                }

                // change header title
                if ($trigger.hasClass($o.headerTitle)) {
                    $($o.overlayHeader).text($('.' + $o.headerTitle).val());
                }

                // change form bg color
                if ($trigger.hasClass($o.purchaseBgColor)) {
                    style = 'background-color: ' + $('.' + $o.purchaseBgColor).val() + ' !important;';
                    setStyle($($o.overlayForm), style);
                }

                // change form text color
                if ($trigger.hasClass($o.purchaseMainText)) {
                    style = 'color: ' + $('.' + $o.purchaseMainText).val() + ' !important;';
                    setStyle($($o.overlayOptionTitle), style);
                }

                // change form description color
                if ($trigger.hasClass($o.purchaseDescription)) {
                    style = 'color: ' + $('.' + $o.purchaseDescription).val() + ' !important;';
                    setStyle($($o.overlayDescription), style);
                }

                // change button bg color
                if ($trigger.hasClass($o.buttonBgColor)) {
                    style = 'background-color: ' + $('.' + $o.buttonBgColor).val() + ' !important;';
                    setStyle($($o.overlayButton), style);
                }

                // change button text color
                if ($trigger.hasClass($o.buttonTextColor)) {
                    style = 'color: ' + $('.' + $o.buttonTextColor).val() + ' !important;';
                    setStyle($($o.overlayButton), style);
                }

                // change link main color
                if ($trigger.hasClass($o.linkMainColor)) {
                    style = 'color: ' + $('.' + $o.linkMainColor).val() + ' !important;';
                    setStyle($($o.overlayLink + ' a'), style);
                }

                // change link hover color
                if ($trigger.hasClass($o.linkHoverColor)) {
                    $($o.overlayLink + ' a').hover(
                        function() {
                            style = 'color: ' + $('.' + $o.linkHoverColor).val() + ' !important;';
                            setStyle($($o.overlayLink + ' a'), style);
                        },
                        function() {
                            style = 'color: ' + $('.' + $o.linkMainColor).val() + ' !important;';
                            setStyle($($o.overlayLink + ' a'), style);
                        }
                    );
                }

                // change footer bg color
                if ($trigger.hasClass($o.footerBgColor)) {
                    style = 'background-color: ' + $('.' + $o.footerBgColor).val() + ' !important;';
                    setStyle($($o.overlayFooter), style);
                }
            },

            processFooter = function($trigger) {
                if ($trigger.is(':checked')) {
                    $($o.overlayFooter).show();
                } else {
                    $($o.overlayFooter).hide();
                }
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

            setStyle = function(target, style) {
                $(target).attr('style', style);
            },

            resetOverlaySettings = function(settings) {
                $('.' + $o.headerBgColor).val(settings.header_bg_color).change();
                $('.' + $o.headerTitle).val(settings.header_title).change();
                $('.' + $o.purchaseBgColor).val(settings.main_bg_color).change();
                $('.' + $o.purchaseMainText).val(settings.main_text_color).change();
                $('.' + $o.purchaseDescription).val(settings.description_color).change();
                $('.' + $o.buttonBgColor).val(settings.button_bg_color).change();
                $('.' + $o.buttonTextColor).val(settings.button_text_color).change();
                $('.' + $o.linkMainColor).val(settings.link_main_color).change();
                $('.' + $o.linkHoverColor).val(settings.link_hover_color).change();
            },

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
