(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayPostView
    function laterPayPostView() {
        var $o = {
                // post statistics pane
                postStatisticsPane              : $('#lp_js_postStatistics'),

                // post preview mode
                postPreviewModeForm             : $('#lp_js_postStatistics_pluginPreviewModeForm'),
                postPreviewModeToggle           : $('#lp_js_togglePostPreviewMode'),
                postPreviewModeInput            : $('#lp_js_postPreviewModeInput'),

                // post statistics pane visibility
                postStatisticsVisibilityForm    : $('#lp_js_postStatistics_visibilityForm'),
                postStatisticsVisibilityToggle  : $('#lp_js_togglePostStatisticsVisibility'),
                postStatisticsVisibilityInput   : $('#lp_js_postStatistics_visibilityInput'),

                // placeholders for caching compatibility mode
                postContentPlaceholder          : $('#lp_js_postContentPlaceholder'),
                postStatisticsPlaceholder       : $('#lp_js_postStatisticsPlaceholder'),

                // purchase buttons and purchase links
                purchaseLink                    : '.lp_js_doPurchase',

                // strings cached for better compression
                hidden                          : 'lp_is-hidden',
            },

            recachePostStatisticsPane = function() {
                $o.postStatisticsPane              = $('#lp_js_postStatistics');
                $o.postPreviewModeForm             = $('#lp_js_postStatistics_pluginPreviewModeForm');
                $o.postPreviewModeToggle           = $('#lp_js_togglePostPreviewMode');
                $o.postPreviewModeInput            = $('#lp_js_postPreviewModeInput');
                $o.postStatisticsVisibilityForm    = $('#lp_js_postStatistics_visibilityForm');
                $o.postStatisticsVisibilityToggle  = $('#lp_js_togglePostStatisticsVisibility');
                $o.postStatisticsVisibilityInput   = $('#lp_js_postStatistics_visibilityInput');
            },

            bindPurchaseEvents = function() {
                // handle clicks on purchase links in test mode
                $('body')
                .on('mousedown', $o.purchaseLink, function() {
                    handlePurchaseInTestMode(this);
                })
                .on('click', $o.purchaseLink, function(e) {e.preventDefault();});
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
                $('.lp_sparklineBar', $o.postStatisticsPane).peity('bar', {
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
                                    if (index === (daysCount - 1)) {
                                        color = '#555';
                                    }
                                    // highlight Saturdays and Sundays
                                    if (date.getDay() === 0 || date.getDay() === 6) {
                                        color = '#c1c1c1';
                                    }
                                    return color;
                                }
                });

                $('.lp_sparklineBackgroundBar', $o.postStatisticsPane).peity('bar', {
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
            trackViews = function() {
                $.post(
                    lpVars.ajaxUrl,
                    {
                        action  : 'laterpay_post_track_views',
                        post_id : lpVars.post_id,
                        nonce   : lpVars.nonces.tracking
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
                // (recognizable by the presence of lp_js_postContentPlaceholder
                if ($('#lp_js_postContentPlaceholder').length === 1) {
                    loadPostContent();
                    trackViews();
                }

                // render the post statistics pane, if a placeholder exists for it
                if ($('#lp_js_postStatisticsPlaceholder').length === 1) {
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

    var ppuContext      = {
                            showCloseBtn        : true,
                            canSkipAddToInvoice : false,
                          },
        dm              = new Y.LaterPay.DialogManager();

    // bind event to purchase link and if 'preview as visitor' is activated for admins handle it accordingly
    Y.one(Y.config.doc).delegate(
        'click',
        function(event) {
            event.preventDefault();
            if (event.currentTarget.getData('preview-as-visitor')) {
                alert(lpVars.i18nAlert);
            } else {
                var url = event.currentTarget.getAttribute('href');
                if (event.currentTarget.hasAttribute('data-laterpay')) {
                    url = event.currentTarget.getAttribute('data-laterpay');
                }
                dm.openDialog(url, ppuContext.showCloseBtn);
            }
        },
        '.lp_js_doPurchase'
    );
});

