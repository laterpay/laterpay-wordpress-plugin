/**
 * statistics pane functions
 */
(function($) {
    $(document).ready(function() {
        var statistic = {};

        /**
         * init function to load the tab and register the events
         * @return void
         */
        statistic.init = function() {
            var xhr;

            xhr = statistic.load_tab();
            xhr.done(function(data) {
                if (!data || data === 0) {
                    return;
                }

                statistic.render(data);

                $('body')
                .on('mousedown', '#toggle-laterpay-statistics-pane', function(e) {
                    statistic.event_toggle_visibility(e);
                })
                .on('click', '#toggle-laterpay-statistics-pane', function(e) {
                    e.preventDefault();
                })
                .on('click', '#preview-post-toggle', function(e) {
                    statistic.event_toggle_preview_mode(e);
                });
            } );
        };

        /**
         * render the sparklines in the statistics pane
         * @return void
         */
        statistic.render_sparklines = function() {
            var $pane = $('#statistics');

            $('.bar', $pane).peity('bar', {
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
                    if (index === (daysCount - 1)){
                        color = '#555';
                    }
                    // highlight Saturdays and Sundays
                    if (date.getDay() === 0 || date.getDay() === 6){
                        color = '#c1c1c1';
                    }
                    return color;
                }
            });

            $('.background-bar', $pane).peity('bar', {
                delimiter   : ';',
                width       : 182,
                height      : 42,
                gap         : 1,
                fill        : function() { return '#ddd'; }
            });
        };

        /**
         * load the statistic
         * @return xhr promise
         */
        statistic.load_tab = function() {
            var request_vars = {
                action  : 'laterpay_post_statistic_render',
                post_id : lpVars.post_id,
                nonce   : lpVars.nonces.statistic
            };

            return $.get(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        /**
         * ajax request to save the visibility
         * @return xhr promise
         */
        statistic.save_visibility = function() {
            var request_vars = $('#laterpay_hide_statistics_form').serializeArray();

            return $.post(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        /**
         * ajax request to toggle the plugin mode
         * @return xhr promise
         */
        statistic.save_plugin_mode = function(){
            var request_vars = $('#plugin_mode').serializeArray();

            return $.post(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        /**
         * render the statistics pane
         * @param data
         * @return void
         */
        statistic.render = function( data ){
            var $container = $('#laterpay-statistic');
            $container.html(data);
            statistic.render_sparklines();
        };

        /**
         * callback to toggle the visibility of the statistics pane
         * @param event
         * @return void
         */
        statistic.event_toggle_visibility = function(e) {
            e.preventDefault();

            var $pane = $('#statistics'),
                value = $pane.hasClass('hidden') ? '0' : '1',
                xhr;

            $('#laterpay_hide_statistics_pane').val(value);

            // toggle the visibility
            $pane.toggleClass('hidden');

            // save the state
            xhr = statistic.save_visibility();
            xhr.done(function(data, textStatus, jqXHR) {
                if ( (!data || !data.success) && lpVars.debug) {
                    console.error(data);
                    console.error(textStatus);
                    console.error(jqXHR);
                }
            } );
        };

        /**
         * callback to toggle the preview/live mode of the post
         * @param event
         * @return void
         */
        statistic.event_toggle_preview_mode = function(e) {
            e.preventDefault();

            var $toggle         = $( '#preview-post-toggle'),
                $preview_state  = $( '#preview_post_hidden_input'),
                xhr;

            if ($toggle.prop('checked')) {
                $preview_state.val(1);
            } else {
                $preview_state.val(0);
            }

            xhr = statistic.save_plugin_mode();
            xhr.done(function(data, textStatus, jqXHR) {
                if (data && data.success) {
                    window.location.reload();
                } else if( lpVars.debug ){
                    console.error(data);
                    console.error(textStatus);
                    console.error(jqXHR);
                }
            } );
        };

        // placeholder found, initialize the statistics pane
        if ($('#laterpay-statistic').length > 0) {
            statistic.init();
        }
    } );

})(jQuery);

/**
 * post purchase functions for activated caching
 */
( function( $ ) {

    $( document).ready( function(){

        "use strict";

        var post_cache          = {},
            $cache_container    = $( '#laterpay-cache-wrapper' )
            ;

        /**
         * init function for our post_caching
         * @return void
         */
        post_cache.init = function(){
            var xhr;

            xhr = post_cache.load_purchased_content();
            xhr.done( function(data){
                if( !data ){
                    return;
                }
                $cache_container.html(data);
            } );

        };

        /**
         * load purchased content via Ajax, if plugin is in page caching compatible mode
         * @return jqxhr promise
         */
        post_cache.load_purchased_content = function(){
            var request_vars = {
                action  : 'laterpay_post_load_purchased_content',
                post_id : lpVars.post_id,
                nonce   : lpVars.nonces.content
            };
            return $.get(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        if( lpVars.caching && $cache_container.length > 0 ){
            post_cache.init();
        }

    } );

})(jQuery);

// render LaterPay elements using the LaterPay dialog manager library
YUI().use('node', 'laterpay-dialog', 'laterpay-iframe', 'laterpay-easyxdm', function(Y) {
    // render purchase dialogs
    var $purchase_link  = Y.one( '.laterpay-purchase-link' ),
        ppuContext      = {
            showCloseBtn: true,
            canSkipAddToInvoice: false
        },
        dm              = new Y.LaterPay.DialogManager();

    if ( ! $purchase_link ){
        // no purchase-link found, so we've not to register the dialog
        return;
    }

    if( $purchase_link.getData( 'preview-as-visitor' ) ){
        // preview as visitor on testing mode for logged in user isset, attach event to purchase link and return
        Y.one( Y.config.doc ).delegate(
            'click',
            function( event ){
                event.preventDefault();
                alert( lpVars.i18nAlert );
            },
            '.laterpay-purchase-link'
        );
        return;
    }

    dm.attachToLinks('.laterpay-purchase-link', ppuContext.showCloseBtn);

    // render invoice indicator iframe
    if ( !lpVars || !lpVars.lpBalanceUrl) {
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