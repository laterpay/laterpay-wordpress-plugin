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

jQuery.noConflict();
