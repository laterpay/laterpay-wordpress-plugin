(function($) {$(function() {

    function laterPayDebugger() {
		var $o = {
				menuItem 		: $('#wp-admin-bar-lp_js_debugger-admin-bar-menu'),
				debugger 		: $('.lp_debugger'),
				closeLink 		: '.lp_close-link',
				tabs 			: $('.lp_debugger-tabs li'),
				content 		: $('.lp_debugger-content'),

				hidden 			: 'lp_is_hidden',
				selected		: 'lp_is_selected',

				hiddenAttr 		: {
									'aria-hidden' 	: true,
									'aria-visible' 	: false
								  },
				visibleAttr 	: {
									'aria-hidden' 	: false,
									'aria-visible' 	: true
								  }
			},

			bindEvents = function() {
				// toggle visibility of debugger pane
				$o.menuItem
				.mousedown(function() {
					handleMenuItemEvent(this);
				})
				.click(function(e) {e.preventDefault();})
				.attr({'role': 'button'});

				// close debugger pane
				$($o.closeLink, $o.debugger)
				.mousedown(function() {
					handleMenuItemEvent(this);
				})
				.click(function(e) {e.preventDefault();});

				// switch tab
				$('a', $o.tabs)
				.mousedown(function() {
					switchTab($(this));
				})
				.click(function(e) {e.preventDefault();});
			},

			handleMenuItemEvent = function() {
				if ($o.debugger.hasClass($o.hidden)) {
					$o.debugger.removeClass($o.hidden).attr($o.visibleAttr);
				} else {
					$o.debugger.addClass($o.hidden).attr($o.hiddenAttr);
				}
			},

			switchTab = function($this) {
				var currentTab 	= $this.parent('li'),
					tabIndex 	= $o.tabs.index(currentTab);

				$o.tabs.removeClass($o.selected);
				currentTab.addClass($o.selected);

				// show / hide tab content
				$o.content.addClass($o.hidden);
				$o.content.eq(tabIndex).removeClass($o.hidden);
			},

			initialize = function() {
				$o.debugger.addClass($o.hidden).attr($o.hiddenAttr);

				bindEvents();
			};

		initialize();
	}

    // initialize page
	laterPayDebugger();

});})(jQuery);
