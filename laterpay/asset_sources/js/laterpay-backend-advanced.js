/* globals tb_show */
(function ($) {
    $(function () {

        // encapsulate all LaterPay Javascript in function laterPayBackendAdvanced
        function laterPayBackendAdvanced() {
            var $o = {
                    // Elements on the current page.
                showMerchantDashboard     : $('#lp_js_showMerchantDashboard, #lp_js_showMerchantDashboardImage'),
                navigation                : $('.lp_navigation'),
                pluginDelete              : $('.lp_js_disablePlugin'),
                pluginDeleteConfirm       : $('.lp_js_disablePluginConfirm'),
                modalClose                : $('button.lp_js_ga_cancel'),
                pluginTrackingToggle      : $('#lp_js_toggleWisdomTracking'),
                lpGoodByeForm             : $('#put-goodbye-form-laterpay'),
            },


                bindEvents = function () {

                    // Add href to dashboard based on location.
                    $o.showMerchantDashboard.bind('click', function () {
                        $(this).attr('href', $(this).data('href-' + lpVars.region));
                        return true;
                    });

                    // Display modal for plugin disable.
                    $o.pluginDelete.on('click', function () {
                        if (typeof tb_show === 'function') {
                            tb_show(lpVars.modal.title, '#TB_inline?inlineId=' + lpVars.modal.id +
                                '&height=185&width=375');
                            $('div#TB_ajaxContent').css('padding', '30px');

                        }
                    });

                    // Close the modal and disable plugin.
                    $o.pluginDeleteConfirm.click(function () {
                        if ( true === $o.pluginTrackingToggle.prop('checked') ) {
                            // Add wisdom tracking deactivation survey form.
                            showWisdomDeactivationSurvey();
                        } else {
                            $('#TB_closeWindowButton').click();
                            disablePluginEraseData();
                        }
                    });

                    // Close the plugin disable modal.
                    $o.modalClose.click(function () {
                        $('#TB_closeWindowButton').click();
                    });

                    // switch plugin tracking permission.
                    $o.pluginTrackingToggle
                        .change(function() {
                            togglePluginTrackingMode();
                        });
                },

                showWisdomDeactivationSurvey = function () {

                        // Hide the first pop out window and display survey form.
                        $('#TB_window').css('visibility','hidden');
                        $o.lpGoodByeForm.fadeIn();

                        /* Since markup is constant keeping the html string as it is in wisdom tracking
                         * instead of creating jQuery objects.
                         */
                        $o.lpGoodByeForm.empty().append( '<div class="put-goodbye-form-head">' +
                        '<strong>Sorry to see you go</strong></div><div class="put-goodbye-form-body">' +
                        '<p>Before you deactivate the plugin, would you quickly give us your reason for doing so?' +
                        '</p><div class="put-goodbye-options"><p><input type="checkbox" ' +
                        'name="put-goodbye-options[]" id="Setupistoodifficult" value="Set up is too difficult"> ' +
                        '<label for="Setupistoodifficult">Set up is too difficult</label><br><input ' +
                        'type="checkbox" name="put-goodbye-options[]" id="Lackofdocumentation" ' +
                        'value="Lack of documentation"> ' +
                        '<label for="Lackofdocumentation">Lack of documentation</label><br>' +
                        '<input type="checkbox" name="put-goodbye-options[]" id="NotthefeaturesIwanted" ' +
                        'value="Not the features I wanted"> ' +
                        '<label for="NotthefeaturesIwanted">Not the features I wanted</label><br>' +
                        '<input type="checkbox" name="put-goodbye-options[]" id="Foundabetterplugin"' +
                        ' value="Found a better plugin"> <label for="Foundabetterplugin">Found a better plugin' +
                        '</label><br><input type="checkbox" name="put-goodbye-options[]" id="Installedbymistake" ' +
                        'value="Installed by mistake"> <label for="Installedbymistake">Installed by mistake' +
                        '</label><br><input type="checkbox" name="put-goodbye-options[]" ' +
                        'id="Onlyrequiredtemporarily" value="Only required temporarily"> ' +
                        '<label for="Onlyrequiredtemporarily">Only required temporarily</label><br>' +
                        '<input type="checkbox" name="put-goodbye-options[]" id="Didn\'twork"' +
                        ' value="Didn&#039;t work"> <label for="Didn\'twork">Didn\'t work</label><br>' +
                        '</p><label for="put-goodbye-reasons">Details (optional)</label>' +
                        '<textarea name="put-goodbye-reasons" id="put-goodbye-reasons" rows="2"' +
                        ' style="width:100%"></textarea></div></div>' +
                        '<p class="deactivating-spinner"><span class="spinner"></span> Submitting form</p>' +
                        '<div class="put-goodbye-form-footer"><p><a id="put-submit-form" ' +
                        'class="button primary" href="#">Submit and Deactivate</a>&nbsp;<a id="just-deactivate" ' +
                        'class="secondary button" href="#">Just Deactivate</a></p></div>');

                        // Handle survey submission and deactivation.
                        $('#put-submit-form').on('click', function(e){
                            // As soon as we click, the body of the form should disappear
                            $('#put-goodbye-form-laterpay .put-goodbye-form-body').fadeOut();
                            $('#put-goodbye-form-laterpay .put-goodbye-form-footer').fadeOut();
                            // Fade in spinner
                            $('#put-goodbye-form-laterpay .deactivating-spinner').fadeIn();
                            e.preventDefault();

                            // Collect form data.
                            var values = [];
                            $.each($('input[name="put-goodbye-options[]"]:checked'), function(){
                                values.push($(this).val());
                            });
                            var details = $('#put-goodbye-reasons').val();

                            // send following data to wisdom goodbye_form action.
                            var data = {
                                'action': 'goodbye_form',
                                'values': values,
                                'details': details,
                                'security': lpVars.wisdom_survey_nonce,
                                'dataType': 'json'
                            };

                            // Submit survey.
                            $.post(
                                ajaxurl,
                                data,
                                function(response){
                                    if ('success' === response) {
                                        disablePluginEraseData();
                                    }
                                }
                            );
                        });

                        // Handle click for just deactivation button, when survey is skipped.
                        $('#just-deactivate').on('click',function(){
                            disablePluginEraseData();
                        });

                        // If we click outside the form, the form will close
                        $('#TB_overlay').on('click',function(){
                            $('#put-goodbye-form-laterpay').hide();
                        });

                },

                disablePluginEraseData = function () {
                    var data = {
                        action  : 'laterpay_disable_plugin',
                        security: lpVars.plugin_disable_nonce,
                    };

                    // Disable plugin and redirect to plugins page.
                    $.post(ajaxurl, data, function (response) {

                        if ($.type(response) === 'string') {
                            response = JSON.parse(response);
                        }

                        $o.navigation.showMessage(response);

                        if (false === response.is_vip) {
                            setTimeout(function () {
                                window.location.replace(lpVars.pluginsUrl);
                            }, 2000);
                        } else {
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        }
                    });
                },

                togglePluginTrackingMode = function () {
                    makeAjaxRequest('laterpay_wisdom_optinout');
                },

                makeAjaxRequest = function ( form_id ) {
                    // prevent duplicate Ajax requests
                    $.post(
                        ajaxurl,
                        $('#' + form_id).serializeArray(),
                        function(data) {
                            $o.navigation.showMessage(data);
                        },
                        'json'
                    ).done( function () {
                        window.location.reload();
                    } );

                },

                initializePage = function () {
                    bindEvents();
                };

            initializePage();
        }

        // initialize page
        laterPayBackendAdvanced();

    });
})(jQuery);
