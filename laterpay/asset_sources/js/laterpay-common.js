/* global lpCommonVar, lpGlobal */
(function($) {$(function() {

    function laterPayCommonModules() {

        var $o = {
                lp_ga_element : $('#lp_ga_tracking')
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
        sendParentEvent = function( injectNow, eventlabel, eventAction, eventCategory ) {
            var lpga = injectGAScript( injectNow );
            if (typeof lpga === 'function') {
                lpga( 'create', lpCommonVar.lp_tracking_id, 'auto', 'lpParentTracker' );
                lpga('lpParentTracker.send', 'event', {
                    eventCategory : eventCategory,
                    eventAction   : eventAction,
                    eventLabel    : eventlabel,
                });
            }
        },

        // Send event to User GA.
        sendUserEvent = function( injectNow, eventlabel, eventAction, eventCategory ) {
            var lpga = injectGAScript( injectNow );
            if (typeof lpga === 'function') {
                lpga( 'create', lpCommonVar.lp_user_tracking_id, 'auto', 'lpUserTracker' );
                lpga( 'lpUserTracker.send', 'event', {
                    eventCategory : eventCategory,
                    eventAction   : eventAction,
                    eventLabel    : eventlabel,
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
        createTrackerAndSendEvent = function ( gaTracker, trackingId, trackerName, eventAction, eventLabel, eventCategory ) {
            gaTracker( 'create', trackingId, 'auto', trackerName );
            gaTracker( trackerName + '.send', 'event', {
                eventCategory : eventCategory,
                eventAction   : eventAction,
                eventLabel    : eventLabel,
            });
        },

        daysPassedSinceEvent = function( date1, date2 ) {

            //Get 1 day in milliseconds
            var one_day = 1000*60*60*24;


            // Calculate the difference in milliseconds
            var difference_ms = date2 - date1;

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

            var lp_post_types = '', contentLabel, categoryLabel, timepassLabel, subsLabel = '';

            $.each(lpCommonVar.lp_enabled_post_types, function(i){
                lp_post_types += lpCommonVar.lp_enabled_post_types[i] + ',';
            });

            contentLabel = lpCommonVar.sandbox_merchant_id + ',' + lpCommonVar.site_url + ',' + lp_post_types;
            categoryLabel = lpCommonVar.sandbox_merchant_id + ',' + lpCommonVar.categories_count + ' Category Prices';
            timepassLabel = lpCommonVar.sandbox_merchant_id + ',' + lpCommonVar.time_passes_count + ' Time Passes';
            subsLabel = lpCommonVar.sandbox_merchant_id + ',' + lpCommonVar.subscriptions_count + ' Subscriptions';


            lpGlobal.sendLPGAEvent( 'LaterPay Content', 'LaterPay WordPress Plugin Pricing', contentLabel );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', 'LaterPay WordPress Plugin Pricing', categoryLabel );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', 'LaterPay WordPress Plugin Pricing', timepassLabel );
            lpGlobal.sendLPGAEvent( 'Pricing Summary', 'LaterPay WordPress Plugin Pricing', subsLabel );


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
            sendLPGAEvent: function ( eventAction, eventCategory, eventLabel ) {

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
                                });
                            }
                        });

                        if ( true === sentUserEvent ) {
                            createTrackerAndSendEvent( lpUAID, 'lpParentTracker', eventAction, eventLabel, eventCategory );
                        } else {
                            createTrackerAndSendEvent( __gaTracker, lpUAID, 'lpParentTracker', eventAction, eventLabel, eventCategory );
                            createTrackerAndSendEvent( __gaTracker, userUAID, 'lpUserTracker', eventAction, eventLabel, eventCategory );
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
                                });
                            }
                        });

                        if (true !== sentUserEvent) {
                            sendUserEvent(true, eventLabel, eventAction, eventCategory);
                        }
                    } else {
                        sendUserEvent(true, eventLabel, eventAction, eventCategory);
                    }
                } else if( userUAID.length === 0 && lpUAID.length > 0 ) {
                    if (typeof __gaTracker === 'function' ) {
                        createTrackerAndSendEvent( __gaTracker, lpUAID, 'lpParentTracker', eventAction, eventLabel, eventCategory );
                    } else{
                        sendParentEvent( true, eventLabel, eventAction, eventCategory );
                    }
                }
            }
        };

        initializePage();
    }

    laterPayCommonModules();
});})(jQuery);