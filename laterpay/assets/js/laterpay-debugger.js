(function($) {$(function() {

    function laterPayDebugger() {
        var $o = {
                menuItem            : $('#wp-admin-bar-lp_js_debugger-admin-bar-menu'),
                debugger            : $('.lp_debugger'),
                debuggerHeader      : 'header',
                tabs                : $('.lp_debugger-tabs li'),
                content             : $('.lp_debugger-content'),
                logMessage          : '.lp_log-entry-table',
                logMessageHeader    : $('.lp_log-entry-table thead'),
                logDetails          : '.lp_js_log-entry-details',

                hidden              : 'lp_is_hidden',
                selected            : 'lp_is_selected',

                hiddenAttr          : {
                                        'aria-hidden'   : true,
                                        'aria-visible'  : false
                                      },
                visibleAttr         : {
                                        'aria-hidden'   : false,
                                        'aria-visible'  : true
                                      }
            },

            bindEvents = function() {
                // toggle visibility of debugger pane
                $o.menuItem
                .mousedown(function() {
                    toggleDebuggerVisibility();
                })
                .click(function(e) {e.preventDefault();})
                .attr({'role': 'button'});

                // toggle visibility of debugger pane
                $($o.debuggerHeader, $o.debugger)
                .mousedown(function() {
                    toggleDebuggerVisibility();
                })
                .click(function(e) {e.preventDefault();});

                // switch tab
                $('a', $o.tabs)
                .mousedown(function() {
                    switchTab($(this));
                })
                .click(function(e) {e.preventDefault();});

                // toggle log message details
                $o.logMessageHeader
                .mousedown(function() {
                    toggleMessageDetails($(this));
                })
                .click(function(e) {e.preventDefault();});

                // display Javascript errors
                window.onerror = function(errorMsg, url, lineNumber) {
                    displayJavascriptErrors(errorMsg, url, lineNumber);
                }
            },

            toggleDebuggerVisibility = function() {
                if ($o.debugger.hasClass($o.hidden)) {
                    $o.debugger.removeClass($o.hidden).attr($o.visibleAttr);
                } else {
                    $o.debugger.addClass($o.hidden).attr($o.hiddenAttr);
                }
            },

            switchTab = function($trigger) {
                var currentTab  = $trigger.parent('li'),
                    tabIndex    = $o.tabs.index(currentTab);

                $o.tabs.removeClass($o.selected);
                currentTab.addClass($o.selected);

                // show / hide tab content
                $o.content.addClass($o.hidden);
                $o.content.eq(tabIndex).removeClass($o.hidden);
            },

            toggleMessageDetails = function($trigger) {
                var $messageBody = $trigger.parents($o.logMessage).find($o.logDetails);
                if ($messageBody.is(':hidden')) {
                    // hide all open log details
                    $($o.logDetails, $o.content).hide(0);
                    // show current log details
                    $messageBody.show(0);
                } else {
                    $($o.logDetails, $o.content).hide(0);
                }
            },

            displayJavascriptErrors = function(errorMsg, url, lineNumber) {
                var file        = url.substring(url.lastIndexOf('/') + 1),
                    logMessage  =   '<li>' +
                                        '<table class="lp_log-entry-table">' +
                                            '<thead>' +
                                                '<tr>' +
                                                    '<td><span class="lp_log-level lp_log-level-500 lp_vector-icon"></span>Javascript Error: ' + errorMsg + '</td>' +
                                                    '<td>' + file + ' (line ' + lineNumber + ')</td>' +
                                                '</li>' +
                                            '</thead>' +
                                        '</table>' +
                                    '</tr>';

                $o.content.eq(0).find('ul').prepend(logMessage);

                // show debugger pane, if it's hidden
                if ($o.debugger.hasClass($o.hidden)) {
                    $o.debugger.removeClass($o.hidden).attr($o.visibleAttr);
                }

                return false;
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
