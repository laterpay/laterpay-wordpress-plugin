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

        if (idValue.length !== 22) {
            setMessage(i18n_invalidMerchantId, false);
        }
        if (keyValue.length !== 32) {
            setMessage(i18n_invalidApiKey, false);
        }

        return false;
    }

    function validatePrice(price) {
        var corrected;

        // strip non-number characters
        price = price.replace(/[^0-9\,\.]/g, '');
        // convert price to proper float value
        if (price.indexOf(',') > -1) {
            price = parseFloat(price.replace(',', '.')).toFixed(2);
        } else {
            price = parseFloat(price).toFixed(2);
        }
        // prevent non-number prices
        if (isNaN(price)) {
            price = 0;
            corrected = true;
        }
        // prevent negative prices
        price = Math.abs(price);
        // correct prices outside the allowed range of 0.05 - 5.00
        if (price > 5) {
            price = 5;
            corrected = true;
        } else if (price > 0 && price < 0.05) {
            price = 0.05;
            corrected = true;
        }

        // show flash message when correcting an invalid price
        if (corrected) {
            setMessage(i18n_outsideAllowedPriceRange, false);
        }

        return price.toFixed(2);
    }

    $('#global-default-price').blur(function() {
        // validate price
        var $defaultPrice   = $('#global-default-price'),
            defaultPrice    = $defaultPrice.val(),
            validatedPrice  = validatePrice(defaultPrice);
        if (locale == 'de_DE') {
            validatedPrice = validatedPrice.replace('.', ',');
        }
        $defaultPrice.val(validatedPrice);
    });

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
            defaultPrice    = $defaultPrice.val(),
            validatedPrice  = validatePrice(defaultPrice);
        if (locale == 'de_DE') {
            validatedPrice = validatedPrice.replace('.', ',');
        }
        $defaultPrice.val(validatedPrice);

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

    // disable tabs
    $('.tabs.getstarted li a').unbind('mousedown');

});})(jQuery);
