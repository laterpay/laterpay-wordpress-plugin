(function($) {$(function() {

        // encapsulate all LaterPay Javascript in function laterPayPostView
        function laterPayPostView() {
            var $o = {
                    // post statistics pane
                    postStatisticsPane              : $('#lp_js_post-statistics'),

                    // post preview mode
                    postPreviewModeForm             : $('#lp_plugin-preview-mode-form'),
                    postPreviewModeToggle           : $('#lp_js_toggle-post-preview-mode'),
                    postPreviewModeInput            : $('#lp_js_preview-post-input'),

                    // post statistics pane visibility
                    postStatisticsVisibilityForm    : $('#lp_js_post-statistics-visibility-form'),
                    postStatisticsVisibilityToggle  : $('#lp_js_toggle-post-statistics-visibility'),
                    postStatisticsVisibilityInput   : $('#lp_js_hide-statistics-pane-input'),

                    // placeholders for caching compatibility mode
                    postContentPlaceholder          : $('#lp_js_post-content-placeholder'),
                    postStatisticsPlaceholder       : $('#lp_js_post-statistics-placeholder'),

                    // purchase buttons and purchase links
                    purchaseLink                    : $('.lp_js_do-purchase'),

                    // strings cached for better compression
                    hidden                          : 'lp_is_hidden',
                },

                recachePostStatisticsPane = function() {
                    $o.postStatisticsPane              = $('#lp_js_post-statistics');
                    $o.postPreviewModeForm             = $('#lp_plugin-preview-mode-form');
                    $o.postPreviewModeToggle           = $('#lp_js_toggle-post-preview-mode');
                    $o.postPreviewModeInput            = $('#lp_js_preview-post-input');
                    $o.postStatisticsVisibilityForm    = $('#lp_js_post-statistics-visibility-form');
                    $o.postStatisticsVisibilityToggle  = $('#lp_js_toggle-post-statistics-visibility');
                    $o.postStatisticsVisibilityInput   = $('#lp_js_hide-statistics-pane-input');
                },

                bindPurchaseEvents = function() {
                    // handle clicks on purchase links in test mode
                    $o.purchaseLink
                    .on('mousedown', function() {
                        handlePurchaseInTestMode(this);
                    })
                    .on('click', function(e) {e.preventDefault();});
                },

                bindPostStatisticsEvents = function() {
                    // toggle visibility of post statistics pane
                    $o.postStatisticsVisibilityToggle
                    .on('mousedown', function() {
                        togglePostStatisticsVisibility();
                    })
                    .on('click', function(e) {e.preventDefault();});

                    // toggle plugin preview mode between 'preview as visitor' and 'preview as admin'
                    $o.postPreviewModeToggle
                    .on('change', function() {
                        togglePostPreviewMode();
                    });
                },

                loadPostStatistics = function() {
                    $.get(
                        lpVars.ajaxUrl,
                        {
                            action  : 'laterpay_post_statistic_render',
                            post_id : lpVars.post_id,
                            nonce   : lpVars.nonces.statistic
                        },
                        function(data) {
                            if (data) {
                                $o.postStatisticsPlaceholder.before(data).remove();
                                renderPostStatisticsPane();
                            }
                        }
                    );
                },

                renderPostStatisticsPane = function() {
                    // make sure all objects are in the cache
                    recachePostStatisticsPane();

                    // bind events to post statistics pane
                    bindPostStatisticsEvents();

                    // render sparklines within post statistics pane
                    $('.lp_sparkline-bar', $o.postStatisticsPane).peity('bar', {
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

                    $('.lp_sparkline-background-bar', $o.postStatisticsPane).peity('bar', {
                        delimiter   : ';',
                        width       : 182,
                        height      : 42,
                        gap         : 1,
                        fill        : function() { return '#ddd'; }
                    });
                },

                togglePostStatisticsVisibility = function() {
                    var doHide = $o.postStatisticsPane.hasClass($o.hidden) ? '0' : '1';
                    $o.postStatisticsVisibilityInput.val(doHide);

                    // toggle the visibility
                    $o.postStatisticsPane.toggleClass($o.hidden);

                    // save the state
                    $.post(
                        lpVars.ajaxUrl,
                        $o.postStatisticsVisibilityForm.serializeArray()
                    );
                },

                togglePostPreviewMode = function() {
                    if ($o.postPreviewModeToggle.prop('checked')) {
                        $o.postPreviewModeInput.val(1);
                    } else {
                        $o.postPreviewModeInput.val(0);
                    }

                    // save the state and reload the page in the new preview mode
                    $.post(
                        lpVars.ajaxUrl,
                        $o.postPreviewModeForm.serializeArray(),
                        function() {
                            window.location.reload();
                        }
                    );
                },

                loadPostContent = function() {
                    $.get(
                        lpVars.ajaxUrl,
                        {
                            action  : 'laterpay_post_load_purchased_content',
                            post_id : lpVars.post_id,
                            nonce   : lpVars.nonces.content
                        },
                        function(postContent) {
                            if (postContent) {
                                $o.postContentPlaceholder.html(postContent);
                            }
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
                    // (recognizable by the presence of lp_js_post-content-placeholder
                    if ($('#lp_js_post-content-placeholder').length == 1) {
                        loadPostContent();
                    }

                    // render the post statistics pane, if a placeholder exists for it
                    if ($('#lp_js_post-statistics-placeholder').length == 1) {
                        loadPostStatistics();
                    }

                    bindPurchaseEvents();
                };

            initializePage();
        }

        // initialize page
        laterPayPostView();

});})(jQuery);


// render LaterPay purchase dialogs using the LaterPay YUI dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {

    var $purchaseLink   = Y.one('.lp_js_do-purchase'),
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
            '.lp_js_do-purchase'
        );

        return;
    }

    dm.attachToLinks('.lp_js_do-purchase', ppuContext.showCloseBtn);

});
