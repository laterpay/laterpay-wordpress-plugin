(function($) {$(function() {

        function laterPayDebugger() {
			var $o = {
					menuItem: $( '#wp-admin-bar-lp_js_debugger-admin-bar-menu' ),
					debugger: $( '.lp_debugger' ),

					hiddenClass: 'lp_debugger_is_hidden',
					visibleClass: 'lp_debugger_is_visible',

					hiddenAttr: { 'aria-hidden' : true, 'aria-visible' : false },
					visibleAttr: { 'aria-hidden' : false, 'aria-visible' : true }
				},


				bindEvents = function () {

					$o.menuItem
						.on( 'mousedown', function () {
							handleMenuItemEvent( this );
						} )
						.on( 'click', function ( e ) {
							e.preventDefault();
						} )
						.attr({'role' : 'button'});
				},

				handleMenuItemEvent = function () {

					if( $o.debugger.hasClass( $o.hiddenClass ) ){
						$o.debugger.removeClass( $o.hiddenClass ).addClass( $o.visibleClass ).attr( $o.visibleAttr );
					}
					else {
						$o.debugger.removeClass( $o.visibleClass ).addClass( $o.hiddenClass ).attr( $o.hiddenAttr );
					}


				},

				initialize = function(){

					$o.debugger.addClass( $o.hiddenClass ).attr( $o.hiddenAttr );

					bindEvents();
				}


			initialize();
		}

        // initialize page
	laterPayDebugger();

});})(jQuery);
