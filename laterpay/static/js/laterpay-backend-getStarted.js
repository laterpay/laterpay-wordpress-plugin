jQuery.noConflict();
(function($) { $(function() {

    function validateAPIKey() {
        var $apiKey     = $('.api-key-input'),
            $merchantID = $('.merchant-id-input'),
            keyValue    = $apiKey.val().trim(),
            idValue     = $merchantID.val().trim();
        if (keyValue.length !== $apiKey.val().length) {
            $apiKey.val(keyValue);
        }
        if (idValue.length !== $merchantID.val().length) {
            $merchantID.val(idValue);
        }

        if (keyValue.length === 32 && idValue.length === 22) {
            $('.progress-line .st-1').removeClass('todo').addClass('done');
            return true;
        } else {
            $('.progress-line .st-1').removeClass('done').addClass('todo');
        }

        return false;
    }

    $('.activate-lp').click(function(e) {
        if (!validateAPIKey()) {
            setMessage({
                'message': $(this).data().error,
                'success': false
            });
            return;
        }

        // validate price
        var $defaultPrice   = $('#global-default-price'),
            defaultPrice    = $defaultPrice.val();
        // convert value to proper float
        if (defaultPrice.indexOf(',') > -1) {
            defaultPrice = parseFloat(defaultPrice.replace(',', '.')).toFixed(2);
        } else {
            defaultPrice = parseFloat(defaultPrice).toFixed(2);
        }
        // correct invalid values
        if (defaultPrice > 5) {
            $defaultPrice.val(5);
        } else if (defaultPrice < 0) {
            $defaultPrice.val(0);
        }

        $('.progress-line .todo').removeClass('todo').addClass('done');

        $.post(
            ajaxurl,
            $('#get_started_form').serializeArray(),
            function(data) {
                window.location = 'post-new.php';
            }
        );

        return false;
    });

    $('.api-key-input, .merchant-id-input').bind('input', function() {
        validateAPIKey();
    });

    // initialize page
    $('.api-key-input, .merchant-id-input').first().focus();

    // hide pointer while viewing the getStarted tab
    $(document).ready(function() {
        if (typeof($().pointer) !== 'undefined' && $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer')) {
            $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer').pointer.hide();
        }
    });

});})(jQuery);
