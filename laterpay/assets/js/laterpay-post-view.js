/**
 * statistic tab functions
 */
( function( $ ) {

    $( document).ready( function() {

        "use strict";

        var statistic = {};

        /**
         * init function to load the tab and register the events
         * @return void
         */
        statistic.initialize = function(){
            var xhr;

            xhr = statistic.load_tab();
            xhr.done( function( data ) {

                if( !data || data == 0 ){
                    return;
                }

                statistic.render( data );

                $('body')
                    .on(
                        'mousedown',
                        '#toggle-laterpay-statistics-pane',
                        function( e ) { statistic.event_toggle_visibility( e ); }
                    )
                    .on(
                        'click',
                        '#toggle-laterpay-statistics-pane',
                        function( e ) { e.preventDefault(); }
                    )
                    .on(
                        'click',
                        '#preview-post-toggle',
                        function( e ) { statistic.event_toggle_preview_mode( e ); }
                    )
                ;

            } );
        };

        /**
         * creating the peity diagramms on our statistic tab
         * @return void
         */
        statistic.create_peity = function(){
            var $pane = $( '#statistics' );

            $pane.find( '.bar' ).peity('bar', {
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

            $pane.find( '.background-bar' ).peity('bar', {
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
        statistic.save_visibility = function(){
            var request_vars = $( '#laterpay_hide_statistics_form' ).serializeArray();
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
            var request_vars = $( '#plugin_mode' ).serializeArray();
            return $.post(
                lpVars.ajaxUrl,
                request_vars
            );
        };

        /**
         * render the statistic tab
         * @param data
         * @return void
         */
        statistic.render = function( data ){
            var $container = $( '#laterpay-statistic' );
            $container.html( data );
            statistic.create_peity();
        };

        /**
         * callback to toggle the visibility of the statistic tab
         * @param event
         * @return void
         */
        statistic.event_toggle_visibility = function( event ){
            event.preventDefault();

            var $pane = $( '#statistics' ),
                value = $pane.hasClass( 'hidden' ) ? '0' : '1',
                xhr
            ;

            $('#laterpay_hide_statistics_pane').val( value );

            // toggle the visibility
            $pane.toggleClass('hidden');

            // saving the state
            xhr = statistic.save_visibility();
            xhr.done( function( data, textStatus, jqXHR  ) {
                if( ( !data || !data.success ) && lpVars.debug ) {
                    console.error( data );
                    console.error( textStatus );
                    console.error( jqXHR );
                }
            } );


        };

        /**
         * callback to toggle the preview/live mode of the post
         * @param event
         * @return void
         */
        statistic.event_toggle_preview_mode = function( event ){
            event.preventDefault();
            var $toggle         = $( '#preview-post-toggle'),
                $preview_state  = $( '#preview_post_hidden_input'),
                xhr
            ;

            if ( $toggle.prop( 'checked' ) ) {
                $preview_state.val( 1 );
            }
            else {
                $preview_state.val( 0 );
            }

            xhr = statistic.save_plugin_mode();
            xhr.done( function( data, textStatus, jqXHR ) {
                if ( data && data.success ) {
                    window.location.reload();
                }
                else {
                    console.error( data );
                    console.error( textStatus );
                    console.error( jqXHR );
                }
            } );
        };

        // placeholder found, initialize the statistic tab
        if( $( '#laterpay-statistic').length > 0 ) {
            statistic.initialize();
        }

    } );

} )( jQuery );


/**
 * post purchase functions activated for caching
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

            xhr = post.load_purchased_content();
            xhr.done( function( data ){
                if( !data ){
                    return;
                }
                $cache_container.html( data );
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

} )( jQuery );


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