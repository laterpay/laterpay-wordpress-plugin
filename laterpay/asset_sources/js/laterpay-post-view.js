/* global lpGlobal */
(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayPostView
    function laterPayPostView() {
        var $o = {
            body                            : $('body'),

                // post preview mode
            previewModePlaceholder          : $('#lp_js_previewModePlaceholder'),
            previewModeContainer            : '#lp_js_previewModeContainer',
            previewModeForm                 : '#lp_js_previewModeForm',
            previewModeToggle               : '#lp_js_togglePreviewMode',
            previewModeInput                : '#lp_js_previewModeInput',

            previewModeVisibilityForm       : '#lp_js_previewModeVisibilityForm',
            previewModeVisibilityToggle     : '#lp_js_togglePreviewModeVisibility',
            previewModeVisibilityInput      : '#lp_js_previewModeVisibilityInput',

            optionContainer                 : '.lp_purchase-overlay-option',
            optionInput                     : '.lp_purchase-overlay-option__input',
            submitButtonText                : '.lp_purchase-overlay__submit-text',

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

                // subscriptions
            subscription                    : '.lp_js_subscription',
            flipSubscriptionLink            : '.lp_js_flipSubscription',

                // placeholders for caching compatibility mode
            postContentPlaceholder          : $('#lp_js_postContentPlaceholder'),

                // purchase buttons and purchase links
            purchaseOverlay                 : '.lp_js_overlayPurchase',
            currentOverlay                  : 'input[name="lp_purchase-overlay-option"]:checked',

                // strings cached for better compression
            hidden                          : 'lp_is-hidden',
            fadingOut                       : 'lp_is-fading-out',

                // premium content
            premiumBox                      : '.lp_js_premium-file-box',

                // redeem voucher
            redeemVoucherBlock              : $('.lp_purchase-overlay__voucher'),
            notificationButtons             : $('.lp_js_notificationButtons'),
            notificationCancel              : $('.lp_js_notificationCancel'),
            voucherCancel                   : '.lp_js_voucherCancel',
            redeemVoucherButton             : '.lp_js_redeemVoucher',
            overlayMessageContainer         : '.lp_js_purchaseOverlayMessageContainer',
            overlayTimePassPrice            : '.lp_js_timePassPrice',

            lp_already_bought               : '.lp_bought_notification',

            // Contribution elements.
            lp_preset_buttons               : '.lp-amount-preset-button',
            lp_custom_amount                : $('.lp-custom-amount-input'),
            lp_custom_amount_wrapper        : $('.lp-custom-input-wrapper'),
            lp_singleContribution           : $('.lp-link-single'),
        },

            // Messages templates

            timePassFeedbackMessage = function (msg) {
                var message = $('<div/>', {
                    id: 'lp_js_voucherCodeFeedbackMessage',
                    class: 'lp_voucher__feedback-message',
                    style: 'display:none;'
                }).text(msg);

                return message;
            },

            purchaseOverlayFeedbackMessage = function (msg) {
                var message = $('<div/>', {
                    id: 'lp_js_voucherCodeFeedbackMessage',
                    class: 'lp_purchase-overlay__voucher-error'
                }).text(msg);

                return message;
            },

            // DOM cache

            recachePreviewModeContainer = function() {
                $o.previewModeContainer = $('#lp_js_previewModeContainer');
                $o.previewModeForm  = $('#lp_js_previewModeForm');
                $o.previewModeToggle = $('#lp_js_togglePreviewMode');
                $o.previewModeInput = $('#lp_js_previewModeInput');

                $o.previewModeVisibilityForm = $('#lp_js_previewModeVisibilityForm');
                $o.previewModeVisibilityToggle = $('#lp_js_togglePreviewModeVisibility');
                $o.previewModeVisibilityInput = $('#lp_js_previewModeVisibilityInput');
            },

            // Binding Events

            bindPreviewModeEvents = function() {
                $o.previewModeToggle.on('change', function() {
                    togglePreviewMode();
                });

                // toggle visibility of post statistics pane
                $o.previewModeVisibilityToggle
                    .on('mousedown', function() {
                        togglePreviewModeVisibility();
                    })
                    .on('click', function(e) {e.preventDefault();});
            },

            bindPurchaseEvents = function() {

                var eventlabel = lpVars.gaData.postTitle + ',' + lpVars.gaData.blogName + ',' +
                    lpVars.gaData.postPermalink;

                // handle clicks on purchase links in test mode
                $o.body
                    .on('mousedown', '.lp_js_doPurchase, .lp_premium_link', function() {
                        handlePurchaseInTestMode(this);
                    })
                    .on('click', '.lp_js_doPurchase, .lp_premium_link', function(e) {
                        // redirect to the laterpay side
                        e.preventDefault();

                        // Add new var to handle premium shortcode link type.
                        var actionElement = $(this);
                        if ( typeof $(this).data('content-type') !== 'undefined' &&
                            $(this).data('content-type').toString() === 'link' ) {
                            actionElement = $(this).find('span.lp_shortcode_link');
                        }

                        if ( actionElement.data( 'preview-post-as-visitor' ) ) {
                            alert(lpVars.i18n.alert);
                        } else {
                            // Check if purchase url is available.
                            if ( actionElement.data('laterpay') ) {
                                // Send GA Event On Click of Buy Button.
                                lpGlobal.sendLPGAEvent( 'Paid Content Purchase', 'LaterPay WordPress Plugin',
                                    eventlabel );
                                // phpcs:ignore WordPressVIPMinimum.JS.Window.location -- Safe value is assigned here.
                                window.location.href = actionElement.data('laterpay');
                            }
                        }
                    });

                $o.body
                    .on('mousedown', $o.purchaseOverlay, function() {
                        handlePurchaseInTestMode(this);
                    })
                    .on('click', $o.purchaseOverlay, function(e) {
                        // redirect to the laterpay side
                        e.preventDefault();
                        if ( $(this).data( 'preview-post-as-visitor' ) ) {
                            alert(lpVars.i18n.alert);
                        } else {
                            // Send GA Event On Click of Buy Button.
                            lpGlobal.sendLPGAEvent( 'Paid Content Purchase', 'LaterPay WordPress Plugin', eventlabel );
                            purchaseOverlaySubmit($(this).attr('data-purchase-action'));
                        }
                    });

                // select radio input by clicking on a container
                $o.body
                    .on('click', $o.optionContainer, function () {
                        // Remove checked prop from previously selected option for overlay.
                        $('input[name="lp_purchase-overlay-option"]').removeProp('checked');

                        // Check the currently chosen option.
                        $(this).find($o.optionInput).prop('checked', 'checked');

                        switch( $(this).data('revenue') ) {
                            // buy now
                            case 'sis':
                                $($o.submitButtonText).text(lpVars.i18n.revenue.sis);
                                break;
                            // subscription
                            case 'sub':
                                $($o.submitButtonText).text(lpVars.i18n.revenue.sub);
                                break;
                            // pay later
                            case 'ppu':
                            /* falls through */
                            default:
                                $($o.submitButtonText).text(lpVars.i18n.revenue.ppu);
                                break;
                        }
                    });

                // show redeem voucher input
                $o.body
                    .on('click', $o.redeemVoucherButton, function (e) {
                        e.preventDefault();

                        $o.redeemVoucherBlock.removeClass('lp_hidden');
                        $o.notificationButtons.addClass('lp_hidden');
                        $o.notificationCancel.removeClass('lp_hidden');

                        $($o.purchaseOverlay).find('[data-buy-label="true"]').addClass('lp_hidden');
                        $($o.purchaseOverlay).find('[data-voucher-label="true"]').removeClass('lp_hidden');
                        $($o.purchaseOverlay).attr('data-purchase-action', 'voucher');
                    });

                // hide redeem voucher input
                $o.body
                    .on('click', $o.voucherCancel, function (e) {
                        e.preventDefault();

                        $o.redeemVoucherBlock.addClass('lp_hidden');
                        $o.notificationButtons.removeClass('lp_hidden');
                        $o.notificationCancel.addClass('lp_hidden');

                        $($o.purchaseOverlay).find('[data-buy-label="true"]').removeClass('lp_hidden');
                        $($o.purchaseOverlay).find('[data-voucher-label="true"]').addClass('lp_hidden');
                        $($o.purchaseOverlay).attr('data-purchase-action', 'buy');
                    });

                // handle clicks on time passes
                $o.body
                    .on('click', $o.flipTimePassLink, function(e) {
                        e.preventDefault();
                        flipTimePass(this);
                    });

                // handle clicks on subscription
                $o.body
                    .on('click', $o.flipSubscriptionLink, function(e) {
                        e.preventDefault();
                        flipTimePass(this);
                    });
            },

            bindAlreadyPurchasedEvents = function() {
              // handle clicks on already bought link.
                $o.body
                .on('click', $o.lp_already_bought, function(e) {
                    e.preventDefault();

                    var eventlabel = lpVars.gaData.postTitle + ',' + lpVars.gaData.blogName + ',' +
                    lpVars.gaData.postPermalink;

                    lpGlobal.sendLPGAEvent( 'Paid Content Identify', 'LaterPay WordPress Plugin', eventlabel );
                    // phpcs:ignore WordPressVIPMinimum.JS.Window.location -- Safe value is assigned here.
                    window.location.href = $(this).attr( 'href' );
                });
            },

            // Binding events for contribution dialog.
            bindContributionEvents = function () {

                // Event handler for clicking on the amounts in contribution dialog.
                $($o.lp_preset_buttons).click(function () {
                    $(this).parents('.lp-amount-presets').find('.lp-amount-preset-button')
                        .removeClass('lp-amount-preset-button-selected');
                    $(this).addClass('lp-amount-preset-button-selected');
                    changeButtonText(null, null, $(this).data('revenue'));
                });

                // Handle custom amount input.
                $o.lp_custom_amount.keyup(
                    debounce(function () {
                        $(this).parents('.lp-body-wrapper').find('.lp-amount-preset-button')
                            .removeClass('lp-amount-preset-button-selected');
                        var validatedPrice = validatePrice($(this).val());
                        $(this).val(validatedPrice);
                        /*
                         * get selected amount. Value needs to be in cents,
                         * so multiply by 100 and round to avoid floating point errors
                         */
                        var lp_amount = Math.round($(this).val() * 100);
                        changeButtonText('custom', lp_amount);
                    }, 800)
                );

                // Handle multiple contribution button click.
                $('.lp-contribution-button').click(function () {
                    var currentAmount = $(this).parents('.lp-body-wrapper').find('.lp-amount-preset-button-selected'),
                        payurl;
                    if (currentAmount.length) {
                        payurl = currentAmount.data('url');
                    } else {
                        var customAmount = $o.lp_custom_amount.val() * 100;
                        if (customAmount > 300) {
                            payurl = $o.lp_custom_amount_wrapper.data('sis-url') + '&custom_pricing=' +
                                lpVars.default_currency + customAmount;
                        } else {
                            payurl = $o.lp_custom_amount_wrapper.data('ppu-url') + '&custom_pricing=' +
                                lpVars.default_currency + customAmount;
                        }
                    }
                    // Open payment url in new tab.
                    window.open(payurl);
                });

                // Handle multiple contribution button click.
                $o.lp_singleContribution.click(function () {
                    window.open($(this).data('url') + '&custom_pricing=' +
                        lpVars.default_currency + $(this).data('amount'));
                });
            },

            // Change the contribution button text based on selection.
            changeButtonText = function (type, lp_amount, revenue) {
                if ('custom' === type) {
                    if (lp_amount > 300) {
                        revenue = 'sis';
                    } else {
                        revenue = 'ppu';
                    }
                }
            },

            // Validate custom input price.
            validatePrice = function (price) {
                // strip non-number characters
                price = price.toString().replace(/[^0-9\,\.]/g, '');

                // convert price to proper float value
                if (typeof price === 'string' && price.indexOf(',') > -1) {
                    price = parseFloat(price.replace(',', '.')).toFixed(2);
                } else {
                    price = parseFloat(price).toFixed(2);
                }

                // prevent non-number prices
                if (isNaN(price)) {
                    price = 0.05;
                }

                // prevent negative prices
                price = Math.abs(price);

                // correct prices outside the allowed range of 0.05 - 1000.00
                if (price > 1000.00) {
                    price = 1000.00;
                } else if (price < 0.05) {
                    price = 0.05;
                }

                // format price with two digits
                price = price.toFixed(2);

                return price;
            },

            purchaseOverlaySubmit = function (action) {
                if (action === 'buy') {
                    // phpcs:ignore WordPressVIPMinimum.JS.Window.location -- Safe value is assigned here.
                    window.location.href = $($o.currentOverlay).val();
                }

                if (action === 'voucher') {
                    $($o.overlayMessageContainer).html('');

                    redeemVoucherCode(
                        $($o.overlayMessageContainer),
                        purchaseOverlayFeedbackMessage,
                        $( $o.voucherCodeInput, $o.redeemVoucherBlock ),
                        'purchase-overlay',
                        false
                    );
                }

                return false;
            },

            bindTimePassesEvents = function() {
                // redeem voucher code
                $($o.voucherRedeemButton)
                    .on('mousedown', function() {

                        var type = $( this ).data( 'type' );
                        var pass_id = $( this ).data( 'id' );
                        var parent = $(this).parent();

                        redeemVoucherCode(
                            parent,
                            timePassFeedbackMessage,
                            $($o.voucherCodeInput, parent),
                            type,
                            false,
                            pass_id
                        );
                    })
                    .on('click', function(e) {e.preventDefault();});

                $($o.giftCardRedeemButton)
                    .on('mousedown', function() {

                        var type = $( this ).data( 'type' );
                        var pass_id = $( this ).data( 'id' );
                        var parent = $(this).parent();

                        redeemVoucherCode(
                            parent,
                            timePassFeedbackMessage,
                            $( $o.giftCardCodeInput, parent ),
                            type,
                            true,
                            pass_id
                        );
                    })
                    .on('click', function(e) {e.preventDefault();});
            },

            /**
             * To validate voucher code, and Redirect to paywall if it's valid.
             * If voucher code is invalid then show appropriate error message.
             *
             * @param {object}  $wrapper           jQuery Element where any error message will render
             * @param {object}  feedbackMessageTpl Template for error message.
             * @param {string}  input              jQuery element for input control where user entered coupon code.
             * @param {string}  type               Type of coupon code that is allowed.
             *                                     timepass, subscription, global, purchase-overlay
             * @param {boolean} is_gift
             * @param {int}     pass_id            Pass ID that is allowed.
             *
             * @return void
             */
            redeemVoucherCode = function($wrapper, feedbackMessageTpl, input, type, is_gift, pass_id) {
                var code = $( input ).val();
                pass_id = ( 'number' === typeof pass_id ) ? pass_id : 0;

                if ( 'string' !== typeof type ) {
                    type = 'global';
                }

                // Passed value to link is escaped before sent further for processing.
                if (code.length === 6) {
                    $.ajax( {
                        url       : lpVars.ajaxUrl,
                        method    : 'GET',
                        data      :{
                            action     : 'laterpay_redeem_voucher_code',
                            code       : code,
                            type       : type,
                            pass_id    : pass_id,
                            link       : window.location.href, // phpcs:ignore WordPressVIPMinimum.JS.Window.location
                            lp_post_id : typeof lpVars.post_id !== 'undefined' ? lpVars.post_id : ''
                        },
                        xhrFields : {
                            withCredentials : true
                        },
                        dataType  : 'json',
                    } ).done( function ( r ) {
                        // clear input
                        $( input ).val( '' );

                        if (r.success) {
                            if (!is_gift) {
                                var has_matches = false,
                                    passId,subId;

                                if ( 'time_pass' === r.type ) {
                                    $($o.timePass).each(function() {
                                        // Check for each shown time pass,
                                        // if the request returned updated data for it.
                                        passId = $(this).data('pass-id');
                                        if (passId === r.pass_id) {
                                            has_matches = true;
                                            return false;
                                        }
                                    });
                                }

                                if ( 'subscription' === r.type ) {
                                    $($o.subscription).each(function() {
                                        // Check for each shown subscription,
                                        // if the request returned updated data for it.
                                        subId = $(this).data('sub-id');
                                        if (subId === r.sub_id) {
                                            has_matches = true;
                                            return false;
                                        }
                                    });
                                }

                                if ( 'global' === r.type ) {
                                    // always match if global.
                                    has_matches = true;
                                }

                                if (has_matches) {
                                    // voucher is valid for at least one displayed time pass ->
                                    // forward to purchase dialog
                                    // phpcs:ignore WordPressVIPMinimum.JS.Window.location -- Valid Purchase URL.
                                    window.location.href = r.url;
                                } else {
                                    // voucher is invalid for all displayed time passes
                                    showVoucherCodeFeedbackMessage(
                                        code + lpVars.i18n.invalidVoucher,
                                        feedbackMessageTpl,
                                        type,
                                        $wrapper
                                    );
                                }
                            } else {
                                $('#fakebtn')
                                    .attr('data-laterpay', r.url)
                                    .click();
                            }
                        } else {
                            // voucher is invalid for all displayed time passes
                            showVoucherCodeFeedbackMessage(
                                code + lpVars.i18n.invalidVoucher,
                                feedbackMessageTpl,
                                type,
                                $wrapper
                            );
                        }
                    } );
                } else {
                    // request was not sent, because voucher code is not six characters long
                    showVoucherCodeFeedbackMessage(lpVars.i18n.codeTooShort, feedbackMessageTpl, type, $wrapper);
                }
            },

            showVoucherCodeFeedbackMessage = function(message, tpl, type, $wrapper) {
                var $feedbackMessage = tpl(message);

                if (type === 'purchase-overlay') {
                    // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.append -- Safe Markup, Div with Text.
                    $wrapper.empty().append($feedbackMessage);
                }

                if ( '' === type || 'global' === type || 'timepass' === type || 'subscription' === type ) {
                    $wrapper.prepend($feedbackMessage);

                    $feedbackMessage = $('#lp_js_voucherCodeFeedbackMessage', $wrapper);
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
                }
            },

            removeVoucherCodeFeedbackMessage = function($feedbackMessage) {
                $feedbackMessage.fadeOut(250, function() {
                    $feedbackMessage.unbind().remove();
                });
            },

            loadPremiumUrls = function() {
                var ids   = [],
                    types = [],
                    boxes = $($o.premiumBox);

                // get all pass ids from wrappers
                $.each(boxes, function(i) {
                    ids.push($(boxes[i]).data('post-id'));
                    types.push($(boxes[i]).data('content-type'));
                });

                $.ajax( {
                    url       : lpVars.ajaxUrl,
                    method    : 'GET',
                    data      :{
                        action     : 'laterpay_get_premium_shortcode_link',
                        ids        : ids,
                        types      : types,
                        parent_pid : lpVars.post_id
                    },
                    xhrFields : {
                        withCredentials : true
                    },
                    dataType  : 'json',
                } ).done( function ( r ) {
                    if (r.data) {
                        var url = null;
                        $.each(r.data, function(i) {
                            url = r.data[i];
                            $.each(boxes, function(j) {
                                if ($(boxes[j]).data('post-id').toString() === i) {
                                    if ( $(boxes[j]).data('content-type').toString() === 'link' ) {
                                        if ($(url).attr('href')) {
                                            $(boxes[j]).attr('href', $(url).attr('href'))
                                                .removeClass('lp_premium_link');
                                        } else {
                                            $(boxes[j]).prepend(url);
                                        }
                                    } else {
                                        $(boxes[j]).prepend(url);
                                    }
                                }
                            });
                        });
                    }
                    initiateAttachmentDownload();
                } );
            },

            loadPreviewModeContainer = function() {
                $.ajax( {
                    url       : lpVars.ajaxUrl,
                    method    : 'GET',
                    data      :{
                        action  : 'laterpay_preview_mode_render',
                        post_id : lpVars.post_id
                    },
                    xhrFields : {
                        withCredentials : true
                    }
                } ).done( function ( data ) {
                    if (data) {
                        $o.previewModePlaceholder.before(data).remove();
                        recachePreviewModeContainer();
                        bindPreviewModeEvents();
                    }
                } );
            },

            togglePreviewMode = function() {
                if ($o.previewModeToggle.prop('checked')) {
                    $o.previewModeInput.val(1);
                } else {
                    $o.previewModeInput.val(0);
                }

                // save the state and reload the page in the new preview mode
                $.ajax( {
                    url       : lpVars.ajaxUrl,
                    method    : 'POST',
                    data      : $o.previewModeForm.serializeArray(),
                    xhrFields : {
                        withCredentials : true
                    }
                } ).done( function () {
                    window.location.reload();
                } );
            },

            togglePreviewModeVisibility = function() {
                var doHide = $o.previewModeContainer.hasClass($o.hidden) ? '0' : '1';
                $o.previewModeVisibilityInput.val(doHide);

                // toggle the visibility
                $o.previewModeContainer.toggleClass($o.hidden);

                // save the state
                $.ajax( {
                    url       : lpVars.ajaxUrl,
                    method    : 'POST',
                    data      : $o.previewModeVisibilityForm.serializeArray(),
                    xhrFields : {
                        withCredentials : true
                    }
                } );
            },

            handlePurchaseInTestMode = function(trigger) {
                var actionElement = $(trigger);
                if ( typeof $(trigger).data('content-type') !== 'undefined' &&
                    $(trigger).data('content-type').toString() === 'link' ) {
                    actionElement = $(this).find('span.lp_shortcode_link');
                }
                if (actionElement.data('preview-as-visitor')) {
                    // show alert instead of loading LaterPay purchase dialogs
                    alert(lpVars.i18n.alert);
                }
            },

            initiateAttachmentDownload = function() {
                var url = get_cookie('laterpay_download_attached');
                // start attachment download, if requested, url contains attachment url which is safe.
                if ( url ) {
                    delete_cookie('laterpay_download_attached');
                    window.location.href = url; // phpcs:ignore WordPressVIPMinimum.JS.Window.location
                }
            },

            flipTimePass = function(trigger) {
                $(trigger).parents('.lp_time-pass').toggleClass('lp_is-flipped');
            },

            delete_cookie = function( name ) {
                document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
            },

            get_cookie = function(name) {
                var matches = document.cookie.match(
                    new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
                return matches ? decodeURIComponent(matches[1]) : undefined;
            },

            // Throttle the execution of a function by a given delay.
            debounce = function(fn, delay) {
                var timer;
                return function() {
                    var context = this,
                        args    = arguments;

                    clearTimeout(timer);

                    timer = setTimeout(function() {
                        fn.apply(context, args);
                    }, delay);
                };
            },

            initializePage = function() {

                if ($o.previewModePlaceholder.length === 1) {
                    loadPreviewModeContainer();
                }

                if ($($o.premiumBox).length >= 1) {
                    loadPremiumUrls();
                }

                bindPurchaseEvents();
                bindTimePassesEvents();
                bindAlreadyPurchasedEvents();
                bindContributionEvents();
            };

        initializePage();
    }

// initialize page
    laterPayPostView();

});})(jQuery);
