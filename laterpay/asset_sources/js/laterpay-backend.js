var flashVisible;

/*jshint unused: false */
function setMessage(message, success) {
    window.clearTimeout(flashVisible);

    try {
        var m = JSON.parse(message);
        success = m.success;
        message = m.message;
    } catch(e) {
        if (typeof message !== 'string') {
            success = message.success;
            message = message.message;
        }
    }

    var $message        = jQuery('#lp_js_flashMessage'),
        messageClass    = success ? 'updated' : 'error';

    $message.addClass(messageClass).find('p').html(message);
    if (jQuery('p:hidden', $message)) {
        $message.slideDown(250);
    }
    flashVisible = setTimeout(function() { clearMessage(); }, 3000);
}

function clearMessage() {
    jQuery('#lp_js_flashMessage').slideUp(250);
}

function showLoadingIndicator($target) {
    // add a state class, indicating that the element will be showing a loading indicator after a delay
    $target.addClass('lp_is-delayed');

    setTimeout(function() {
        if ($target.hasClass('lp_is-delayed')) {
            // inject the loading indicator after a delay, if the element still has that state class
            $target.html('<div class="lp_loadingIndicator"></div>');
        }
    }, 600);
}

function removeLoadingIndicator ($target) {
    if ($target.hasClass('lp_is-delayed')) {
        // remove the state class, thus canceling adding the loading indicator
        $target.removeClass('lp_is-delayed');
    } else {
        // remove the loading indicator
        $target.find('.lp_loadingIndicator').remove();
    }
}

jQuery.noConflict();
