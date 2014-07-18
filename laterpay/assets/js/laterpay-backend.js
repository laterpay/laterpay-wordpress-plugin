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

    var messageClass = success ? 'updated' : 'error';
    jQuery('html, body').animate({ scrollTop: 0 });
    jQuery('#message').attr('class', messageClass).find('p').html(message);
    if (jQuery('#message p:hidden')) {
        jQuery('#message').slideDown(250);
    }
    flashVisible = setTimeout(function() { jQuery('#message').slideUp(250); }, 3000);
}

jQuery.noConflict();
