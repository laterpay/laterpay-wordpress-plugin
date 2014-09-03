(function($) { $(document).ready(function() {

        // encapsulate all LaterPay Javascript in function laterPayViewPost
        function laterPayViewPost() {
            var bindPurchaseEvents = function() {
                    // handle clicks on purchase links in test mode
                    $('.lp_purchase-link')
                    .on('mousedown', function() {handlePurchaseInTestMode(this);})
                    .on('click', function(e) {e.preventDefault();});
                },

                bindPostStatisticsEvents = function() {
                    // toggle visibility of post statistics pane
                    $('#lp_toggle-post-statistics-visibility')
                    .on('mousedown', function() {
                        togglePostStatisticsVisibility();
                    })
                    .on('click', function(e) {e.preventDefault();});

                    // toggle plugin preview mode between 'preview as visitor' and 'preview as admin'
                    $('#lp_plugin-preview-mode-form .switch-input')
                    .on('change', function() {
                        togglePluginPreviewMode();
                    });
                },

                loadPostStatistics = function() {
                    var $placeholder    = $('#lp_post-statistics-placeholder'),
                        requestVars     = {
                                            action  : 'laterpay_post_statistic_render',
                                            post_id : lpVars.post_id,
                                            nonce   : lpVars.nonces.statistic
                                          };

                    $.get(
                        lpVars.ajaxUrl,
                        requestVars,
                        function(data) {
                            if (data) {
                                $placeholder.before(data).remove();
                                renderPostStatisticsPane();
                            }
                        }
                    );
                },

                renderPostStatisticsPane = function() {
                    var $postStatisticsPane = $('.lp_post-statistics');

                    // bind events to post statistics pane
                    bindPostStatisticsEvents();

                    // render sparklines within post statistics pane
                    $('.lp_sparkline-bar', $postStatisticsPane).peity('bar', {
                        delimiter   : ';',
                        width       : 182,
                        height      : 42,
                        gap         : 1,
                        fill        : function(value, index, array) {
                                        var date        = new Date(),
                                            daysCount   = array.length,
                                            color       = '#999';
                                        date.setDate(date.getDate() - (daysCount - index));
                                        // highlight the last (current) day
                                        if (index === (daysCount - 1))
                                            color = '#555';
                                        // highlight Saturdays and Sundays
                                        if (date.getDay() === 0 || date.getDay() === 6)
                                            color = '#c1c1c1';
                                        return color;
                                    }
                    });

                    $('.lp_sparkline-background-bar', $postStatisticsPane).peity('bar', {
                        delimiter   : ';',
                        width       : 182,
                        height      : 42,
                        gap         : 1,
                        fill        : function() { return '#ddd'; }
                    });
                },

                togglePostStatisticsVisibility = function() {
                    var $form   = $('#lp_toggle-post-statistics-visibility-form'),
                        $pane   = $('.lp_post-statistics'),
                        $input  = $('input[name=hide_statistics_pane]'),
                        doHide  = $pane.hasClass('hidden') ? '0' : '1';

                    $input.val(doHide);

                    // toggle the visibility
                    $pane.toggleClass('hidden');

                    // save the state
                    $.post(
                        lpVars.ajaxUrl,
                        $form.serializeArray()
                    );
                },

                togglePluginPreviewMode = function() {
                    var $form   = $('#lp_plugin-preview-mode-form'),
                        $toggle = $('.switch-input', $form),
                        $input  = $('input[name=preview_post]', $form);

                    if ($toggle.prop('checked')) {
                        $input.val(1);
                    } else {
                        $input.val(0);
                    }

                    // save the state and reload the page in the new preview mode
                    $.post(
                        lpVars.ajaxUrl,
                        $form.serializeArray(),
                        function() {
                            window.location.reload();
                        }
                    );
                },

                loadPostContent = function() {
                    var $placeholder    = $('#lp_post-content-placeholder'),
                        requestVars     = {
                                            action  : 'laterpay_post_load_purchased_content',
                                            post_id : lpVars.post_id,
                                            nonce   : lpVars.nonces.content
                                          };

                    $.get(
                        lpVars.ajaxUrl,
                        requestVars,
                        function(data) {
                            $placeholder.before(data).remove();
                        }
                    );
                },

                handlePurchaseInTestMode = function(trigger) {
                    if ($(trigger).data('preview-as-visitor')) {
                        // show alert instead of loading LaterPay purchase dialogs
                        alert(lpVars.i18nAlert);
                    }
                },

                initializePage = function() {
                    // load post content via Ajax, if plugin is in caching compatible mode
                    // (recognizable by the presence of lp_post-content-placeholder
                    if ($('#lp_post-content-placeholder').length == 1) {
                        loadPostContent();
                    }

                    // render the post statistics pane, if a placeholder exists for it
                    if ($('#lp_post-statistics-placeholder').length == 1) {
                        loadPostStatistics();
                    }

                    bindPurchaseEvents();
                };

            initializePage();
        }

        // initialize page
        laterPayViewPost();

});})(jQuery);


// render LaterPay purchase dialogs using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {
    var $purchaseLink  = Y.one('.lp_purchase-link'),
        ppuContext      = {
                            showCloseBtn        : true,
                            canSkipAddToInvoice : false
                          },
        dm              = new Y.LaterPay.DialogManager();

    if (!$purchaseLink) {
        // don't register the dialogs, if there's no purchase link in the page
        return;
    }

    if ($purchaseLink.getData('preview-as-visitor')) {
        // bind event to purchase link and return, if 'preview as visitor' is activated for admins
        Y.one(Y.config.doc).delegate(
            'click',
            function(event) {
                event.preventDefault();
                alert(lpVars.i18nAlert);
            },
            '.lp_purchase-link'
        );

        return;
    }

    dm.attachToLinks('.lp_purchase-link', ppuContext.showCloseBtn);

    // render invoice indicator iframe
    if (!lpVars || !lpVars.lpBalanceUrl) {
        // don't render the invoice indicator, if no URL is provided in the variables
        return;
    }

    new Y.LaterPay.IFrame(
        Y.one('#laterpay-invoice-indicator'),
        lpVars.lpBalanceUrl,
        {
            width       : '110',
            height      : '30',
            scrolling   : 'no',
            frameborder : '0'
        }
    );
});
