/* global lpCommonVar, lpGlobal */
(function($) {$(function() {

    function laterPayCommonModules() {

        var $o = {
            lp_ga_element : $('#lp_ga_tracking'),
            pricing       : {
                setGlobalPrice : $('#lp_js_saveGlobalDefaultPrice'),
            },
            wp_body_content     : $('#wpbody-content'),
            backendPage         : $('.lp_page'),
            close_update_notice : $('#close_update_notice'),
            close_info_notice   : $('#close_info_notice'),
            navigation          : $('.lp_navigation'),
        },

        bindEvents = function() {

            // Reset update highlights data on click.
            $o.backendPage.add($o.wp_body_content).on('click', $o.close_update_notice, function( e ) {
                if ( 'close_update_notice' === e.target.id ) {
                    $.post(
                        lpCommonVar.ajaxUrl, {
                            action   : 'laterpay_reset_highlights_data',
                            security : lpCommonVar.update_highlights_nonce,
                        },
                        function(data) {
                            if (data.success) {
                                $o.backendPage.find('div.lp_update_notification').remove();
                                if ( $o.wp_body_content.length ) {
                                    $o.wp_body_content.find('div.lp_update_notification').remove();
                                }
                            }
                        },
                        'json'
                    );
                }
            });

            // Remove tabular instruction data on click.
            $o.backendPage.on('click', $o.close_info_notice, function( e ) {
                if ( 'close_info_notice' === e.target.id ) {
                    $.post(
                        lpCommonVar.ajaxUrl, {
                            action   : 'laterpay_read_tabular_instructions',
                            security : lpCommonVar.read_tabular_nonce,
                            lppage   : lpCommonVar.current_page,
                        },
                        function(data) {
                            if (data.success) {
                                $o.backendPage.find('div.lp_info_notification').remove();
                            }
                        },
                        'json'
                    );
                }
            });
        },

        lp_delete_cookie = function( name ) {
            document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
        },

        lp_get_cookie = function(name) {
            var matches = document.cookie.match(
                new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        },

        /**
         * Injects Google Analytics Script.
         *
         * Removed in 2.9.7. Always returns false.
         */
        injectGAScript = function ( injectNow ) {
            return false;
        },

        // Read Post Purchased Cookie.
        readPurchasedCookie = function() {
            if ( '1' === lp_get_cookie( 'lp_ga_purchased' ) ) {
                var eventlabel = lpCommonVar.postTitle + ',' + lpCommonVar.blogName + ',' +
                    lpCommonVar.postPermalink;
                lpGlobal.sendLPGAEvent( 'Paid Content Purchase Complete', 'LaterPay WordPress Plugin', eventlabel );
                lp_delete_cookie('lp_ga_purchased');
            }
        },

        daysPassedSinceEvent = function( date1, date2 ) {

            //Get 1 day in milliseconds
            var one_day = 1000*60*60*24;


            // Calculate the difference in milliseconds
            var difference_ms = date1 - date2;

            // Convert back to days and return
            return parseFloat(difference_ms/one_day);
        },

        setDataInStorage = function( storageName, storageValue ) {

            if ( supportsLocalStorage() ) {

                localStorage.setItem( storageName, storageValue );
            }

        },

        getDataFromStorage = function( storageName ) {

            if ( supportsLocalStorage() ) {

                return localStorage.getItem( storageName );
            }
        },

        sendSummaryEvents = function () {

            var categoryLabel, timepassLabel, subsLabel, versionLabel, statusLabel = '';

            var commonLabel = lpCommonVar.sandbox_merchant_id + ' | ';

            categoryLabel = commonLabel + 'Count Category Prices';
            timepassLabel = commonLabel + 'Count Time Passes';
            subsLabel     = commonLabel + 'Count Subscriptions';
            versionLabel  = commonLabel + lpCommonVar.lp_current_version;
            statusLabel   = lpCommonVar.sb_merchant_id + ' | ' + lpCommonVar.live_merchant_id + ' | ' +
                lpCommonVar.site_url + ' | ' + lpCommonVar.lp_plugin_status;


            var eveCategory = 'LP WP Pricing';
            var eveAction   = 'Pricing Summary';

            // Send Summary GA Events.
            lpGlobal.sendLPGAEvent( eveAction, eveCategory, categoryLabel, lpCommonVar.categories_count, true );
            lpGlobal.sendLPGAEvent( eveAction, eveCategory, timepassLabel, lpCommonVar.time_passes_count, true );
            lpGlobal.sendLPGAEvent( eveAction, eveCategory, subsLabel, lpCommonVar.subscriptions_count, true );
            lpGlobal.sendLPGAEvent( eveAction, eveCategory, versionLabel, 0, true );
            lpGlobal.sendLPGAEvent( 'Account Status Summary', eveCategory, statusLabel, 0, true );

            setDataInStorage( 'lpSummarySentDate', Date.now() );

        },

        supportsLocalStorage = function () {
            try {
                return 'localStorage' in window && window.localStorage !== null;
            } catch (e) {
                return false;
            }
        },

        // Create markup for highlights notice dynamically if there is notice data.
        addUpdateHighlights = function () {

            if ( 'settings' === lpCommonVar.current_page || 'post_edit' === lpCommonVar.current_page ||
                'front_post' === lpCommonVar.current_page ) {
                return;
            }

            if ( Object.keys(lpCommonVar.update_highlights).length ) {

                // Notice Div.
                var updateWrapper = $('<div/>', {
                    class: 'lp_update_notification',
                });

                // Version Text.
                var version = $('<b/>', {
                    text: lpCommonVar.update_highlights.version
                });

                // Version Description.
                var versionDescritpion = $('<p/>', {
                    class: 'version_text'
                });

                // Version Description.
                var versionDescritpionExtra = $('<p/>', {
                    text: lpCommonVar.update_highlights.notice,
                    class: 'version_info'
                });

                // Learn More CTA.
                var updateDetailsCallToAction = $('<a/>', {
                    class : 'lp_purchase-overlay__submit',
                    href  : 'https://wordpress.org/plugins/laterpay/#developers',
                    text  : lpCommonVar.learn_more,
                    target: '_blank',
                });

                // Dismiss button.
                var updateDetailsDismiss = $('<a/>', {
                    class: 'close_notice',
                    id   : 'close_update_notice'
                }).attr('data-icon', 'e').css('cursor', 'pointer');

                // Safe HTML markup created above.
                // phpcs:disable WordPressVIPMinimum.JS.HTMLExecutingFunctions.append
                versionDescritpion.prepend(version);
                updateWrapper.append(versionDescritpion);
                versionDescritpionExtra.append(updateDetailsCallToAction);
                versionDescritpionExtra.append(updateDetailsDismiss);
                updateWrapper.append(versionDescritpionExtra);
                // phpcs:enable

                if ( 'advanced' !== lpCommonVar.current_page && $o.wp_body_content.length ) {
                    $o.wp_body_content.prepend(updateWrapper);
                } else {
                    $('#lp_js_flashMessage').after(updateWrapper);
                }
            }
        },

        // Create markup for instructional notice dynamically if not dismissed already.
        addInstructionalNotice = function () {

            if ( 'settings' === lpCommonVar.current_page || 'post_edit' === lpCommonVar.current_page ||
                'front_post' === lpCommonVar.current_page ) {
                return;
            }

            if ( Object.keys(lpCommonVar.lp_instructional_info).length ) {

                if ( typeof lpCommonVar.lp_instructional_info[lpCommonVar.current_page] === 'undefined' ) {
                    return;
                }

                // Notice Div.
                var infoWrapper = $('<div/>', {
                    class: 'lp_info_notification',
                });

                // Info.
                // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.append -- Just Text.
                infoWrapper.append(lpCommonVar.lp_instructional_info[lpCommonVar.current_page]);

                if ( 'pricing' === lpCommonVar.current_page ) {
                    // Learn More CTA.
                    var updateDetailsCallToAction = $('<a/>', {
                        class : 'lp_purchase-overlay__submit',
                        href  : 'https://www.laterpay.net/academy/wordpress-pricing',
                        text  : lpCommonVar.learn_more,
                        target: '_blank',
                    });
                    // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.append -- Anchor Tag created above.
                    infoWrapper.append(updateDetailsCallToAction);
                }

                // Dismiss button.
                var updateDetailsDismiss = $('<a/>', {
                    class: 'close_info',
                    id   : 'close_info_notice'
                }).attr('data-icon', 'e').css('cursor', 'pointer');
                // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.append -- Anchor Tag created above.
                infoWrapper.append(updateDetailsDismiss);

                $o.navigation.after(infoWrapper);
            }
        },

        initializePage = function() {

            if ( typeof(lpCommonVar) !== 'undefined' ) {

                if ( 'pricing' === lpCommonVar.current_page ) {

                    if ( supportsLocalStorage() ) {
                        var lastSent = getDataFromStorage( 'lpSummarySentDate' );

                        if ( daysPassedSinceEvent( Date.now(), lastSent ) > 1 || null === lastSent ) {
                            sendSummaryEvents();
                            localStorage.setItem( 'lpSummarySentDate', Date.now() );
                        }

                    } else {
                        sendSummaryEvents();
                    }
                }

            }

            // Read purchased cookie on page load.
            readPurchasedCookie();

            // Send GA Event on Page load.
            if ( $($o.lp_ga_element).length >= 1 ) {
                var eventlabel = lpCommonVar.postTitle + ',' + lpCommonVar.blogName + ',' +
                    lpCommonVar.postPermalink;
                var eventCategory = 'LaterPay WordPress Plugin';
                lpGlobal.sendLPGAEvent( 'Paid Content Replacement Show', eventCategory, eventlabel, 0, true );
            }

            addUpdateHighlights();
            addInstructionalNotice();
            bindEvents();
        };

        window.lpGlobal = {

            /**
             * Send GA Event conditionally.
             *
             * Removed in 2.9.7, always returns false.
             */
            sendLPGAEvent: function ( eventAction, eventCategory, eventLabel, eventValue, eventInteraction ) {
                return false;
            }
        };

        initializePage();
    }

    laterPayCommonModules();
});})(jQuery);
