/* globals lpGlobal */
(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // appearance option
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
                buttonHoverColor    : 'lp_js_purchaseButtonHoverColor',
                buttonTextColor     : 'lp_js_purchaseButtonTextColor',
                linkMainColor       : 'lp_js_purchaseLinkMainColor',
                linkHoverColor      : 'lp_js_purchaseLinkHoverColor',
                footerBgColor       : 'lp_js_purchaseFooterBackgroundColor',
                showFooter          : 'lp_js_overlayShowFooter',

                // overlay
                overlayHeader       : '.lp_purchase-overlay__header',
                overlayForm         : '.lp_purchase-overlay__form',
                overlayOptionTitle  : '.lp_purchase-overlay-option__title',
                overlayDescription  : '.lp_purchase-overlay-option__description',
                overlayLink         : '.lp_purchase-overlay__notification',
                overlayButton       : '.lp_purchase-overlay__submit',
                overlayFooter       : '.lp_purchase-overlay__footer',

                // forms
                previewSwitch       : $('#lp_js_paidContentPreview').find('.lp_js_switchButtonGroup'),
                purchaseForm        : $('#lp_js_purchaseForm'),

                purchaseButtonForm  : $('#lp_js_purchaseButton'),
                purchaseButtonSwitch: $('#lp_js_purchaseButton').find('.lp_js_switchButtonGroup'),

                timePassesForm      : $('#lp_js_timePasses'),
                timePassesSwitch    : $('#lp_js_timePasses').find('.lp_js_switchButtonGroup'),

                appearance_loading      : $('#lp_appearance_loading'),
                appearance_preview      : $('#lp_appearance_preview'),
                show_purchase_button    : $('#lp_show_purchase_button_above_article'),
                purchase_button_custom  : $('#lp_purchase_button_custom_positioned'),
                purchase_button_hint    : $('#lp_purchase_button_hint'),
                backend_purchase_button : $('#lp_backend_purchase_button'),
                show_purchase_overlay   : $('#lp_show_purchase_overlay'),
                purchase_overlay        : $('#lp_purchase_overlay'),
                overlay_body            : $('#lp_overlay_body'),
                purchase_link           : $('#lp_purchase_link'),
                explanatory_button      : $('#lp_explanatory_button'),
                timePassWidget          : $('#lp_js_timePassWidget'),
                timepass_widget_hint    : $('#lp_timepass_widget_hint'),
                section_header_text     : $('#lp_header_text'),
                purchase_header         : $('#lp_purchase_header'),
                show_introduction       : $('#lp_show_introduction'),
                benefits_section        : $('#lp_benefits'),
                benefits_list           : $('#lp_benefits_list'),
                show_tp_sub_below_modal : $('#lp_show_tp_sub_below_modal'),
                tp_sub_custom_positioned: $('#lp_is_tp_sub_custom_positioned'),
                show_body_text          : $('#lp_show_body_text'),
                body_text_content       : $('#lp_body_text_content'),
                body_text_content_holder: $('#lp_body_text_content_holder'),
                show_footer             : $('#lp_show_footer'),
                overlay_footer          : $('#lp_overlay_footer'),
                savePurchaseFormColors  : $('.lp_js_savePurchaseFormColors'),
                overlayLinkNotification : $('.lp_bought_notification'),
                lp_purchase_button      : $('.lp_purchase_button'),
                lp_purchase_button_tp   : $('.lp_purchase-button'),
                navigation              : $('.lp_navigation'),
                flip                    : $('a.lp_js_flipTimePass, a.lp_js_flipSubscription'),
                config_disclaimer       : $('#lp_config_disclaimer'),
            },

            bindEvents = function() {

                $o.flip.on( 'click', function(e) {
                    e.preventDefault();
                    flipEntity(this);
                });

                //Position of the LaterPay Purchase Button
                $o.purchaseButtonSwitch
                    .click(function() {
                        purchaseButtonSwitch($(this));
                    });

                //Display of LaterPay Time Passes
                $o.timePassesSwitch
                    .click(function() {
                        timePassesSwitch($(this));
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
                .click(function(e){
                    e.preventDefault();
                    var $form = $(this).parents('form');

                    // set correct form name
                    $('input[name=form]', $form).val('appearance_config');

                    // Event Action for each input.
                    var elementActionsData = {
                        'lp_show_purchase_button_above_article': 'Purchase Button',
                        'lp_purchase_button_custom_positioned' : 'Purchase Button Custom Position',
                        'lp_show_purchase_overlay'             : 'Purchase Overlay',
                        'lp_show_introduction'                 : 'LP Intro',
                        'lp_show_tp_sub_below_modal'           : 'TP & Sub Outside Overlay',
                        'lp_is_tp_sub_custom_positioned'       : 'TP & Sub Custom Position',
                        'lp_show_body_text'                    : 'Custom HTML',
                        'lp_show_footer'                       : 'Footer'
                    };

                    // Get all disabled inputs so that if they are checked, that value be sent for saving.
                    $('input[type="checkbox"]').each(function () {
                        // Send GA events for appearance config.
                        var eventActionLabel = elementActionsData[$(this).attr('id')];
                        if ( typeof( eventActionLabel ) !== 'undefined' ) {
                            var commonLabel = lpVars.gaData.sandbox_merchant_id + ' | ' + eventActionLabel ;
                            var eventValue  = $(this).prop('checked') ? 1 : 0;
                            lpGlobal.sendLPGAEvent( 'Update Appearance', 'LP WP Appearance', commonLabel, eventValue );
                        }
                    });

                    lpGlobal.sendLPGAEvent( 'Update Colors', 'LP WP Appearance', lpVars.gaData.sandbox_merchant_id );
                    saveData($form);
                });

                // save customize colors settings
                $o.savePurchaseFormColors
                    .click(function(e){
                        e.preventDefault();
                        var $form = $(this).parents('form');

                        // set correct form name
                        $('input[name=form]', $form).val('overlay_settings');

                        saveData($form);
                    });

                // restore original data
                $o.cancelFormEdit
                .click(function(e){
                    e.preventDefault();
                    resetOverlaySettings(lpVars.overlaySettings.current);
                });

                // set default settings
                $o.restoreDefaults
                .click(function(e){
                    e.preventDefault();
                    resetOverlaySettings(lpVars.overlaySettings.default);
                });

                $o.show_purchase_button
                    .change(function () {
                        if ( $(this).prop('checked') ) {
                            $(this).val(1);
                            $o.purchase_button_custom.parent().show();
                            $o.backend_purchase_button.show();
                        } else {
                            $(this).val(0);
                            $o.purchase_button_custom.parent().hide();
                            $o.backend_purchase_button.hide();
                        }

                        if ( $(this).prop('checked') && $o.purchase_button_custom.prop('checked') ) {
                            $o.purchase_button_hint.show();
                        } else {
                            $o.purchase_button_hint.hide();
                        }

                        verifyAppearanceOptions();
                    });

                $o.purchase_button_custom
                    .change(function () {
                        if ( $(this).prop('checked') ) {
                            $(this).val(1);
                            $o.purchase_button_hint.show();
                        } else {
                            $(this).val(0);
                            $o.purchase_button_hint.hide();
                        }
                    });

                $o.show_purchase_overlay
                    .change(function () {
                        if ( ! $(this).prop('checked')  ) {
                            $(this).val(0);
                            $o.purchase_overlay.hide();
                            $o.purchase_header.parent().css('visibility', 'hidden');
                            $o.show_introduction.parent().css('visibility', 'hidden');
                            $o.show_body_text.parent().css('visibility', 'hidden');
                            $o.show_footer.parent().css('visibility', 'hidden');
                        } else {
                            $(this).val(1);
                            $o.purchase_overlay.show();
                            $o.purchase_header.parent().css('visibility', 'visible');
                            $o.show_introduction.parent().css('visibility', 'visible');
                            $o.show_body_text.parent().css('visibility', 'visible');
                            $o.show_footer.parent().css('visibility', 'visible');
                        }
                        verifyAppearanceOptions();
                    });

                $o.purchase_header.on('change paste keyup', function() {
                    $o.section_header_text.text($(this).val());
                });

                $o.show_introduction
                    .change(function () {
                        if ( ! $(this).prop('checked') ) {
                            $(this).val(0);
                            $o.purchase_overlay.attr( 'style', 'min-height:330px !important');
                            $o.benefits_list.hide();
                        } else {
                            $(this).val(1);
                            $o.purchase_overlay.attr( 'style', 'min-height:500px !important');
                            $o.benefits_section.show();
                            $o.benefits_list.show();
                        }
                    });

                $o.show_tp_sub_below_modal
                    .change(function () {
                        if ( ! $(this).prop('checked') ) {
                            $(this).val(0);
                            $o.overlay_body.show();
                            $o.timePassWidget.hide();
                            $o.purchase_link.hide();
                            $o.explanatory_button.hide();
                            $o.tp_sub_custom_positioned.parent().hide();
                            $o.timepass_widget_hint.hide();
                        } else {
                            $(this).val(1);
                            $o.overlay_body.hide();
                            $o.timePassWidget.show();
                            if ( ! $o.show_purchase_overlay.prop('checked') ) {
                                $o.purchase_link.show();
                            }
                            $o.explanatory_button.show();
                            $o.tp_sub_custom_positioned.parent().show();
                            $o.tp_sub_custom_positioned.trigger('change');
                        }
                        verifyAppearanceOptions();
                    });

                $o.show_body_text
                    .change(function () {
                        if ( ! $(this).prop('checked')  ) {
                            $(this).val(0);
                            $o.body_text_content.hide();
                            $o.body_text_content_holder.hide();
                        } else {
                            $(this).val(1);
                            $o.body_text_content_holder.empty().append($o.body_text_content.val());
                            $o.body_text_content.show();
                            $o.body_text_content_holder.show();
                        }
                    });

                $o.body_text_content.on('change paste keyup', function() {
                    $o.body_text_content_holder.empty().append($(this).val());
                });

                $o.show_footer
                    .change(function () {
                        if ( ! $(this).prop('checked')  ) {
                            $(this).val(0);
                            $o.overlay_footer.hide();
                        } else {
                            $(this).val(1);
                            $o.overlay_footer.show();
                        }
                    });

                $o.tp_sub_custom_positioned
                    .change(function () {
                        if ( $(this).prop('checked') ) {
                            $(this).val(1);
                            $o.timepass_widget_hint.show();
                        } else {
                            $(this).val(0);
                            $o.timepass_widget_hint.hide();
                        }
                    });
            },

            updateAppearancePreview = function() {
                $o.show_purchase_button.trigger('change');
                $o.purchase_button_custom.trigger('change');
                $o.show_purchase_overlay.trigger('change');
                $o.purchase_header.trigger('change');
                $o.show_introduction.trigger('change');
                $o.show_tp_sub_below_modal.trigger('change');
                $o.show_body_text.trigger('change');
                $o.body_text_content_holder.trigger('change');
                $o.show_footer.trigger('change');
                $o.tp_sub_custom_positioned.trigger('change');
            },

            verifyAppearanceOptions = function() {
                var invalidConfig = false;
                if ( ! $o.show_purchase_button.prop('checked') &&
                    ! $o.show_purchase_overlay.prop('checked') &&
                    ! $o.show_tp_sub_below_modal.prop('checked') ) {
                    invalidConfig = true;
                    $o.show_purchase_button.addClass('recommended_option');
                    $o.show_purchase_overlay.addClass('recommended_option');
                    $o.show_tp_sub_below_modal.addClass('recommended_option');
                } else if ( $o.show_purchase_button.prop('checked') &&
                    ! $o.show_purchase_overlay.prop('checked') &&
                    ! $o.show_tp_sub_below_modal.prop('checked') ) {
                    invalidConfig = true;
                    $o.show_purchase_button.removeClass('recommended_option');
                    $o.show_purchase_overlay.addClass('recommended_option');
                    $o.show_tp_sub_below_modal.addClass('recommended_option');
                }

                if ( invalidConfig ) {
                    $o.navigation.showMessage( lpVars.invalidConfigError, false );
                    $o.savePurchaseForm.attr('disabled', true);
                    $o.config_disclaimer.show();
                    return;
                }

                $o.show_purchase_button.removeClass('recommended_option');
                $o.show_purchase_overlay.removeClass('recommended_option');
                $o.show_tp_sub_below_modal.removeClass('recommended_option');
                $o.savePurchaseForm.attr('disabled', false);
                $o.config_disclaimer.hide();
            },

            purchaseButtonSwitch = function($trigger) {
                var $form = $trigger.parents('form');

                // mark clicked button as selected
                $($o.buttonGroupButtons, $form).removeClass($o.selected);
                $trigger.parent($o.buttonGroupButtons).addClass($o.selected);

                switch($('input:checked', $form).val())
                {
                    case '0':
                        $form.find($o.buttonGroupHint).fadeOut();
                        break;
                    case '1':
                        $form.find($o.buttonGroupHint).fadeIn();
                        break;
                    default:
                        break;
                }

                saveData($form);
            },

            timePassesSwitch = function($trigger) {
                var $form = $trigger.parents('form');

                // mark clicked button as selected
                $($o.buttonGroupButtons, $form).removeClass($o.selected);
                $trigger.parent($o.buttonGroupButtons).addClass($o.selected);

                switch($('input:checked', $form).val())
                {
                    case '0':
                        $form.find($o.buttonGroupHint).fadeOut();
                        break;
                    case '1':
                        $form.find($o.buttonGroupHint).fadeIn();
                        break;
                    default:
                        break;
                }

                saveData($form);
            },
            updateOverlayOptions = function($trigger) {
                var style;
                var textColorValue;

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
                    var currentTextColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                    var bgColorValue = $('.' + $o.buttonBgColor).val();
                    style = 'background-color: ' + bgColorValue + ' !important;';
                    setStyle($($o.overlayButton), style + currentTextColorValue);
                    setStyle($($o.lp_purchase_button), style + currentTextColorValue);
                    setStyle($($o.lp_purchase_button_tp), style + currentTextColorValue);

                    if ( $o.flip.length ) {
                        textColorValue = 'color:' + bgColorValue + ' !important;';
                        setStyle($o.flip, textColorValue);
                    }
                }

                // change button text color
                if ($trigger.hasClass($o.buttonTextColor)) {
                    var currentBgColorValue = 'background-color:' + $('.' + $o.buttonBgColor).val() + ' !important;';
                    style = 'color: ' + $('.' + $o.buttonTextColor).val() + ' !important;';
                    setStyle($($o.overlayButton), style + currentBgColorValue);
                    setStyle($($o.lp_purchase_button), style + currentBgColorValue);
                    setStyle($($o.lp_purchase_button_tp), style + currentBgColorValue);
                }

                // change link hover color
                if ($trigger.hasClass($o.buttonHoverColor)) {

                    $($o.overlayButton).hover(
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonHoverColor).val() + ' !important;';
                            setStyle($($o.overlayButton), style + textColorValue);
                        },
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonBgColor).val() + ' !important;';
                            setStyle($($o.overlayButton), style + textColorValue);
                        }
                    );

                    $($o.lp_purchase_button).hover(
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonHoverColor).val() + ' !important;';
                            setStyle($($o.lp_purchase_button), style + textColorValue);
                        },
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonBgColor).val() + ' !important;';
                            setStyle($($o.lp_purchase_button), style + textColorValue);
                        }
                    );

                    $($o.lp_purchase_button_tp).hover(
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonHoverColor).val() + ' !important;';
                            setStyle($($o.lp_purchase_button_tp), style + textColorValue);
                        },
                        function() {
                            textColorValue = 'color:' + $('.' + $o.buttonTextColor).val() + ' !important;';
                            style = 'background-color: ' + $('.' + $o.buttonBgColor).val() + ' !important;';
                            setStyle($($o.lp_purchase_button_tp), style + textColorValue);
                        }
                    );
                }

                // change link main color
                if ($trigger.hasClass($o.linkMainColor)) {
                    style = 'color: ' + $('.' + $o.linkMainColor).val() + ' !important;';
                    setStyle($($o.overlayLink + ' a'), style);
                    setStyle($($o.overlayLink), style);

                    if ( ($($o.overlayLinkNotification).length ) ) {
                        setStyle($($o.overlayLinkNotification), style);
                    }
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

                    $($o.overlayLinkNotification).hover(
                        function() {
                            style = 'color: ' + $('.' + $o.linkHoverColor).val() + ' !important;';
                            setStyle($($o.overlayLinkNotification), style);
                        },
                        function() {
                            style = 'color: ' + $('.' + $o.linkMainColor).val() + ' !important;';
                            setStyle($($o.overlayLinkNotification), style);
                        }
                    );
                }

                // change footer bg color
                if ($trigger.hasClass($o.footerBgColor)) {
                    style = 'background-color: ' + $('.' + $o.footerBgColor).val() + ' !important;';

                    if ($($o.overlayFooter).is(':hidden'))
                    {
                        style += 'display: none;';
                    }

                    setStyle($($o.overlayFooter), style);
                }
            },

            flipEntity = function(trigger) {
                $(trigger).parents('.lp_time-pass').toggleClass('lp_is-flipped');
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
                $('.' + $o.buttonHoverColor).val(settings.button_hover_color).change();
                $('.' + $o.buttonTextColor).val(settings.button_text_color).change();
                $('.' + $o.linkMainColor).val(settings.link_main_color).change();
                $('.' + $o.linkHoverColor).val(settings.link_hover_color).change();
                $('.' + $o.footerBgColor).val(settings.footer_bg_color).change();

                if (true === settings.show_footer) {
                    $('.' + $o.showFooter).attr('checked', 'checked');
                }
                else
                {
                    $('.' + $o.showFooter).removeAttr('checked');
                }
            },

            initializePage = function() {
                bindEvents();
                updateAppearancePreview ();
                $o.appearance_loading.fadeOut(500);
                $o.appearance_preview.delay(500).fadeIn(800);
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
