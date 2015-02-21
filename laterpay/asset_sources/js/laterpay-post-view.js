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

                // time passes
                timePass                        : '.lp_js_timePass',
                flipTimePassLink                : '.lp_js_flipTimePass',
                timePassPreviewPrice            : '.lp_js_timePassPreviewPrice',
                voucherCodeWrapper              : '#lp_js_voucherCodeWrapper',
                voucherCodeInput                : '.lp_js_voucherCodeInput',
                voucherRedeemButton             : '.lp_js_voucherRedeemButton',
                giftCardRedeemButton            : '.lp_js_giftCardRedeemButton',
                giftCardCodeInput               : '.lp_js_giftCardCodeInput',
                giftCardWrapper                 : '#lp_js_giftCardWrapper',
                giftCardActionsPlaceholder      : '.lp_js_giftCardActionsPlaceholder',
                giftsWrapper                    : $('.lp_js_giftsWrapper'),

                // placeholders for caching compatibility mode
                postContentPlaceholder          : $('#lp_js_postContentPlaceholder'),
                postStatisticsPlaceholder       : $('#lp_js_postStatisticsPlaceholder'),
                postRatingPlaceholder           : $('#lp_js_postRatingPlaceholder'),

                // purchase buttons and purchase links
                purchaseLink                    : '.lp_js_doPurchase',

                // content rating
                postRatingForm                  : $('.lp_js_ratingForm'),
                postRatingRadio                 : $('input[type=radio][name=rating_value]'),

                // strings cached for better compression
                hidden                          : 'lp_is-hidden',
                fadingOut                       : 'lp_is-fading-out',
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

            recacheRatingForm = function() {
                $o.postRatingForm  = $('.lp_js_ratingForm');
                $o.postRatingRadio = $('input[type=radio][name=rating_value]');
            },

            bindPurchaseEvents = function() {
                // handle clicks on purchase links in test mode
                $('body')
                .on('mousedown', $o.purchaseLink, function() {
                    handlePurchaseInTestMode(this);
                })
                .on('click', $o.purchaseLink, function(e) {e.preventDefault();});

                // handle clicks on time passes
                $('body')
                .on('click', $o.flipTimePassLink, function() {
                    flipTimePass(this);
                })
                .on('click', $o.flipTimePassLink, function(e) {e.preventDefault();});
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

            bindRatingEvents = function() {
                // save rating when input is selected
                $o.postRatingRadio
                .on('change', function() {
                    savePostRating();
                });
            },

            bindTimePassesEvents = function() {
                // redeem voucher code
                $($o.voucherRedeemButton)
                .on('mousedown', function() {
                    redeemVoucherCode($(this).parent(), $o.voucherCodeInput, false);
                })
                .on('click', function(e) {e.preventDefault();});

                $($o.giftCardRedeemButton)
                .on('mousedown', function() {
                    redeemVoucherCode($(this).parent(), $o.giftCardCodeInput, true);
                })
                .on('click', function(e) {e.preventDefault();});
            },

            redeemVoucherCode = function($wrapper, input, is_gift) {
                var code = $(input).val();

                if (code.length === 6) {
                    $.get(
                        lpVars.ajaxUrl,
                        {
                            action  : 'laterpay_redeem_voucher_code',
                            code    : code,
                            nonce   : lpVars.nonces.voucher,
                            link    : window.location.href,
                            is_gift : is_gift ? 1 : 0
                        },
                        function(r) {
                            if (r.success) {
                                if (!is_gift) {
                                    // clear input
                                    $(input).val('');

                                    var has_matches = false,
                                        passId;
                                    $($o.timePass).each(function() {
                                        // check for each shown time pass, if the request returned updated data for it
                                        passId = $(this).data('pass-id');
                                        if (passId === r.pass_id) {
                                            // update purchase button price and url
                                            var priceWithVoucher = r.price +
                                                '<small>' + lpVars.default_currency + '</small>';

                                            // update purchase button on time pass
                                            $(this)
                                                .find($o.purchaseLink)
                                                .attr('data-laterpay', r.url)
                                                .html(priceWithVoucher);

                                            // update price on time pass flipside as well
                                            $(this)
                                                .find($o.timePassPreviewPrice)
                                                .html(priceWithVoucher);

                                            has_matches = true;

                                            return false;
                                        }
                                    });

                                    if (has_matches) {
                                        // voucher is valid for at least one displayed time pass
                                        showVoucherCodeFeedbackMessage(lpVars.i18n.validVoucher, $wrapper);
                                    } else {
                                        // voucher is invalid for all displayed time passes
                                        showVoucherCodeFeedbackMessage(code + lpVars.i18n.invalidVoucher, $wrapper);
                                    }
                                } else {
                                    $('#fakebtn').attr('data-laterpay', r.url);
// fire purchase event on hidden fake button
YUI().use('node', 'node-event-simulate', function(Y) {
    Y.one('#fakebtn').simulate('click');
});
                                }
                            } else {
                                // clear input
                                $(input).val('');

                                // voucher is invalid for all displayed time passes
                                showVoucherCodeFeedbackMessage(code + lpVars.i18n.invalidVoucher, $wrapper);
                            }
                        },
                        'json'
                    );
                } else {
                    // request was not sent, because voucher code is not six characters long
                    showVoucherCodeFeedbackMessage(lpVars.i18n.codeTooShort);
                }
            },

            showVoucherCodeFeedbackMessage = function(message, $wrapper) {
                var $feedbackMessage =  $('<div class="lp_voucherCodeFeedbackMessage" style="display:none;">' +
                                            message +
                                        '</div>');

                $wrapper.prepend($feedbackMessage);

                $feedbackMessage = $('.lp_voucherCodeFeedbackMessage', $wrapper);
                $feedbackMessage
                .fadeIn(250)
                .click(function() {
                    // remove feedback message on click
                    removeVoucherCodeFeedbackMessage($feedbackMessage);
                });

                // automatically remove feedback message after 3 seconds
                setTimeout(function() {
                    removeVoucherCodeFeedbackMessage($feedbackMessage);
                }, 3000);
            },

            removeVoucherCodeFeedbackMessage = function($feedbackMessage) {
                $feedbackMessage.fadeOut(250, function() {
                    $feedbackMessage.unbind().remove();
                });
            },

            savePostRating = function() {
                $.post(
                    lpVars.ajaxUrl,
                    $o.postRatingForm.serializeArray(),
                    function(r) {
                        if (r.success) {
                            // replace rating form with thank you message and remove it after a few seconds
                            $('.lp_rating', $o.postRatingForm).addClass($o.fadingOut).html(r.message);
                            setTimeout(
                                function() {
                                    $o.postRatingForm.fadeOut(400, function() { $(this).remove(); });
                                },
                                4000
                            );
                        }
                    },
                    'json'
                );
            },

            loadRatingSummary = function() {
                $.get(
                    lpVars.ajaxUrl,
                    {
                        action  : 'laterpay_post_rating_summary',
                        post_id : lpVars.post_id,
                        nonce   : lpVars.nonces.rating
                    },
                    function(ratingSummary) {
                        if (ratingSummary) {
                            $o.postRatingPlaceholder.html(ratingSummary);
                        }
                    }
                );
            },

            loadGiftCards = function() {
                var ids     = [],
                    cards   = $o.giftsWrapper;

                // get all pass ids from wrappers
                $.each(cards, function(i) {
                    ids.push($(cards[i]).data('id'));
                });

                $.get(
                    lpVars.ajaxUrl,
                    {
                        action  : 'laterpay_get_gift_card_actions',
                        nonce   : lpVars.nonces.gift,
                        pass_id : ids,
                        link    : window.location.href
                    },
                    function(r) {
                        if (r.data) {
                            $.each(r.data, function(i) {
                                var gift    = r.data[i],
                                    $elem   = $($o.giftCardActionsPlaceholder + '_' + gift.id);

                                $elem.html(gift.html);

                                // add 'buy another gift card' after gift card
                                if (gift.buy_more) {
                                    // $elem.parent().after(gift.buy_more);
                                    $(gift.buy_more)
                                    .appendTo($elem.parent())
                                    .attr('href', window.location.href);
                                }
                            });

                            // remove gift code cookie if present
                            delete_cookie('laterpay_purchased_gift_card');
                        }
                    }
                );
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
                        action   : 'laterpay_post_load_purchased_content',
                        post_id  : lpVars.post_id,
                        is_front : true,
                        nonce    : lpVars.nonces.content
                    },
                    function(postContent) {
                        if (postContent) {
                            $o.postContentPlaceholder.html(postContent);
                            // load rating form
                            recacheRatingForm();
                            bindRatingEvents();
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
                if ($(trigger).data('preview-as-visitor') && !$(trigger).data('is-in-visible-test-mode')) {
                    // show alert instead of loading LaterPay purchase dialogs
                    alert(lpVars.i18n.alert);
                }
            },

            initiateAttachmentDownload = function() {
                // start attachment download, if requested
                if (lpVars.download_attachment) {
                    window.location.href = lpVars.download_attachment;
                }
            },

            flipTimePass = function(trigger) {
                $(trigger).parents('.lp_timePass').toggleClass('lp_is-flipped');
            },

            delete_cookie = function( name ) {
                document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
            },

            initializePage = function() {
                // load post content via Ajax, if plugin is in caching compatible mode
                // (recognizable by the presence of lp_js_postContentPlaceholder
                if ($o.postContentPlaceholder.length === 1) {
                    loadPostContent();
                    trackViews();
                }

                // render the post statistics pane, if a placeholder exists for it
                if ($o.postStatisticsPlaceholder.length === 1) {
                    loadPostStatistics();
                }

                if ($o.postRatingPlaceholder.length === 1) {
                    loadRatingSummary();
                }

                if ($o.giftsWrapper.length >= 1) {
                    loadGiftCards();
                }

                bindPurchaseEvents();
                bindRatingEvents();
                bindTimePassesEvents();

                initiateAttachmentDownload();
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
            if (
                event.currentTarget.getData('preview-as-visitor') &&
                !event.currentTarget.getData('is-in-visible-test-mode')
            ) {
                alert(lpVars.i18n.alert);
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
