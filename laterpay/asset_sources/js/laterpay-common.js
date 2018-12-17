/* global lpCommonVar, lpGlobal */
(function($) {$(function() {

    function laterPayCommonModules() {

        var $o = {
                lp_ga_element : $('#lp_ga_tracking'),
                pricing       : {
                    setGlobalPrice : $('#lp_js_saveGlobalDefaultPrice'),
                },
        },

        lp_delete_cookie = function( name ) {
            document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
        },

        lp_get_cookie = function(name) {
            var matches = document.cookie.match(
                new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        },

        // Injects Google Analytics Script.
        injectGAScript = function ( injectNow ) {
            if ( true === injectNow ) {
                // This injector script is for GA have made minor modifications to fix linting issue.
                (function(i, s, o, g, r, a, m) {
                    i.GoogleAnalyticsObject = r;
                    i[r] = i[r] || function() {
                        (i[r].q = i[r].q || []).push(arguments);
                    }; i[r].l = 1 * new Date();
                    a = s.createElement(o);
                    m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m);
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'lpga');
                return window[window.GoogleAnalyticsObject || 'lpga'];
            }
        },

        // Send event to LaterPay GA.
        sendParentEvent = function( injectNow, eventlabel, eventAction, eventCategory, eventValue ) {
            var lpga = injectGAScript( injectNow );
            if (typeof lpga === 'function') {
                lpga( 'create', lpCommonVar.lp_tracking_id, 'auto', 'lpParentTracker' );
                lpga('lpParentTracker.send', 'event', {
                    eventCategory : eventCategory,
                    eventAction   : eventAction,
                    eventLabel    : eventlabel,
                    eventValue    : eventValue,
                });
            }
        },

        // Send event to User GA.
        sendUserEvent = function( injectNow, eventlabel, eventAction, eventCategory, eventValue ) {
            var lpga = injectGAScript( injectNow );
            if (typeof lpga === 'function') {
                lpga( 'create', lpCommonVar.lp_user_tracking_id, 'auto', 'lpUserTracker' );
                lpga( 'lpUserTracker.send', 'event', {
                    eventCategory : eventCategory,
                    eventAction   : eventAction,
                    eventLabel    : eventlabel,
                    eventValue    : eventValue,
                });
            }
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

        // Detect if GA is Enabled by MonsterInsights Plugin.
        detectMonsterInsightsGA = function () {
            if ( typeof window.mi_track_user === 'boolean' && true === window.mi_trac_user ) {
                return window[window.GoogleAnalyticsObject || '__gaTracker'];
            }
        },

        // Create a tracker and send event to GA.
        createTrackerAndSendEvent = function ( gaTracker, trackingId, trackerName, eventAction, eventLabel,
                                               eventCategory, eventValue ) {
            gaTracker( 'create', trackingId, 'auto', trackerName );
            gaTracker( trackerName + '.send', 'event', {
                eventCategory : eventCategory,
                eventAction   : eventAction,
                eventLabel    : eventLabel,
                eventValue    : eventValue,
            });
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
            statusLabel   = commonLabel + lpCommonVar.lp_plugin_status + ' | ' + lpCommonVar.site_url;


            var eveCategory = 'LP WP Pricing';

            // Send Summary GA Events.
            lpGlobal.sendLPGAEvent( 'Pricing Summary', eveCategory, categoryLabel, lpCommonVar.categories_count );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', eveCategory, timepassLabel, lpCommonVar.time_passes_count );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', eveCategory, subsLabel, lpCommonVar.subscriptions_count );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', eveCategory, versionLabel );
            lpGlobal.sendLPGAEvent( 'Account Status Summary', eveCategory, statusLabel );

            setDataInStorage( 'lpSummarySentDate', Date.now() );

        },

        supportsLocalStorage = function () {
            try {
                return 'localStorage' in window && window.localStorage !== null;
            } catch (e) {
                return false;
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
                lpGlobal.sendLPGAEvent( 'Paid Content Replacement Show', 'LaterPay WordPress Plugin', eventlabel );
            }
        };

        window.lpGlobal = {

            // Send GA Event conditionally.
            sendLPGAEvent: function ( eventAction, eventCategory, eventLabel, eventValue ) {

                var sentUserEvent = false;
                var __gaTracker   = detectMonsterInsightsGA();
                var trackers      = '';
                var userUAID      = lpCommonVar.lp_user_tracking_id;
                var lpUAID        = lpCommonVar.lp_tracking_id;

                if( userUAID.length > 0 && lpUAID.length > 0 ) {

                    if (typeof __gaTracker === 'function' ) {
                        trackers = __gaTracker.getAll();
                        trackers.forEach(function(tracker) {
                            if ( userUAID === tracker.get('trackingId') ) {
                                sentUserEvent = true;
                                var trackerName = tracker.get('name');
                                __gaTracker( trackerName + '.send', 'event', {
                                    eventCategory : eventCategory,
                                    eventAction   : eventAction,
                                    eventLabel    : eventLabel,
                                    eventValue    : eventValue,
                                });
                            }
                        });

                        if ( true === sentUserEvent ) {
                            createTrackerAndSendEvent( lpUAID, 'lpParentTracker', eventAction, eventLabel,
                                eventCategory, eventValue );
                        } else {
                            createTrackerAndSendEvent( __gaTracker, lpUAID, 'lpParentTracker', eventAction,
                                eventLabel, eventCategory, eventValue );
                            createTrackerAndSendEvent( __gaTracker, userUAID, 'lpUserTracker', eventAction,
                                eventLabel, eventCategory, eventValue );
                        }
                    } else {
                        sendParentEvent( true, eventLabel, eventAction, eventCategory );
                        sendUserEvent( true, eventLabel, eventAction, eventCategory );
                    }
                } else if( userUAID.length > 0 && lpUAID.length === 0 ) {
                    if (typeof __gaTracker === 'function') {
                        trackers = __gaTracker.getAll();
                        trackers.forEach(function (tracker) {
                            if (userUAID === tracker.get('trackingId')) {
                                sentUserEvent = true;
                                var trackerName = tracker.get('name');
                                __gaTracker(trackerName + '.send', 'event', {
                                    eventCategory: eventCategory,
                                    eventAction  : eventAction,
                                    eventLabel   : eventLabel,
                                    eventValue   : eventValue,
                                });
                            }
                        });

                        if (true !== sentUserEvent) {
                            sendUserEvent(true, eventLabel, eventAction, eventCategory, eventValue);
                        }
                    } else {
                        sendUserEvent(true, eventLabel, eventAction, eventCategory, eventValue);
                    }
                } else if( userUAID.length === 0 && lpUAID.length > 0 ) {
                    if (typeof __gaTracker === 'function' ) {
                        createTrackerAndSendEvent( __gaTracker, lpUAID, 'lpParentTracker', eventAction, eventLabel,
                            eventCategory, eventValue );
                    } else{
                        sendParentEvent( true, eventLabel, eventAction, eventCategory, eventValue );
                    }
                }
            }
        };

        initializePage();
    }

    laterPayCommonModules();
});})(jQuery);
