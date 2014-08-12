var flashVisible;

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

    var $message        = jQuery('#message'),
        messageClass    = success ? 'updated' : 'error';

    $message.attr('class', messageClass).find('p').html(message);
    if (jQuery('p:hidden', $message)) {
        $message.slideDown(250);
    }
    flashVisible = setTimeout(function() { clearMessage(); }, 3000);
}

function clearMessage() {
    jQuery('#message').slideUp(250);
}

jQuery.noConflict();
