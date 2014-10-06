(function($) {$(function() {

        function laterPayDebugger() {
			var $o = {
					menuItem: $( '#wp-admin-bar-lp_js_debugger-admin-bar-menu' ),
					debugger: $( '.lp_debugger' ),
					tabItem: $( '.lp_debugger-tab a' ),

					tabContentSelector: '.lp_debugger-content',

					hiddenTabClass: 'lp_debugger_tab_is_hidden',
					visibleTabClass: 'lp_debugger_tab_is_visible',

					hiddenDebuggerClass: 'lp_debugger_is_hidden',
					visibleDebuggerClass: 'lp_debugger_is_visible',

					hiddenAttr: { 'aria-hidden' : true, 'aria-visible' : false },
					visibleAttr: { 'aria-hidden' : false, 'aria-visible' : true }
				},


				bindDebugbarEvents = function () {

					$o.menuItem
						.on( 'mousedown', function () {
							handleMenuItemEvent( this );
						} )
						.on( 'click', function ( e ) {
							e.preventDefault();
						} )
						.attr({'role' : 'button'});
				},

				bindTabEvents = function () {

					$o.tabItem
						.on( 'mousedown', function () {
							handleTabEvent( this );
						} )
						.on( 'click', function ( e ) {
							e.preventDefault();
						} )

				},

				handleTabEvent = function ( that ) {
					var target = $( that ).attr( 'href' ),
						$tabContainer = $( target )
					;

					if( $tabContainer.hasClass( $o.hiddenTabClass ) ){
						$tabContainer.removeClass( $o.hiddenTabClass ).addClass( $o.visibleTabClass ).attr( $o.visibleAttr );
					}
					else {
						$tabContainer.removeClass( $o.visibleTabClass ).addClass( $o.hiddenTabClass ).attr( $o.visibleAttr );
					}

				},

				handleMenuItemEvent = function () {

					if( $o.debugger.hasClass( $o.hiddenDebuggerClass ) ){
						$o.debugger.removeClass( $o.hiddenDebuggerClass ).addClass( $o.visibleDebuggerClass ).attr( $o.visibleAttr );
					}
					else {
						$o.debugger.removeClass( $o.visibleDebuggerClass ).addClass( $o.hiddenDebuggerClass );
					}


				},

				initialize = function(){

					// hide the debugger
					$o.debugger.addClass( $o.hiddenDebuggerClass ).attr( $o.hiddenAttr );

					// hide all tabs
					$( $o.tabItem ).parent().addClass( $o.hiddenTabClass ).attr( $o.hiddenAttr );

					bindDebugbarEvents();
					bindTabEvents();
				}


			initialize();
		}

        // initialize page
	laterPayDebugger();

});})(jQuery);
