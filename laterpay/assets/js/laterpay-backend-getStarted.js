jQuery.noConflict();
(function($) { $(function() {

    var throttledFlashMessage;

    function validateAPIKey() {
        var $apiKey     = $('.api-key-input'),
            $merchantID = $('.merchant-id-input'),
            keyValue    = $apiKey.val().trim(),
            idValue     = $merchantID.val().trim();

        // clear flash message timeout
        window.clearTimeout(throttledFlashMessage);

        if (keyValue.length !== $apiKey.val().length) {
            $apiKey.val(keyValue);
        }
        if (idValue.length !== $merchantID.val().length) {
            $merchantID.val(idValue);
        }

        if (keyValue.length === 32 && idValue.length === 22) {
            $('.progress-line .st-1').removeClass('todo').addClass('done');
            clearMessage();

            return true;
        } else {
            $('.progress-line .st-1').removeClass('done').addClass('todo');
        }

        if (idValue.length > 0 && idValue.length !== 22) {
            // set timeout to throttle flash message
            throttledFlashMessage = window.setTimeout(function() {
                setMessage(lpVars.i18nInvalidMerchantId, false);
            }, 500);
        }
        if (keyValue.length > 0 && keyValue.length !== 32) {
            // set timeout to throttle flash message
            throttledFlashMessage = window.setTimeout(function() {
                setMessage(lpVars.i18nInvalidApiKey, false);
            }, 500);
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
            setMessage(lpVars.i18nOutsideAllowedPriceRange, false);
        }

        return price.toFixed(2);
    }

    $('#global-default-price').blur(function() {
        // validate price
        var $defaultPrice   = $('#global-default-price'),
            defaultPrice    = $defaultPrice.val(),
            validatedPrice  = validatePrice(defaultPrice);
        if (lpVars.locale == 'de_DE') {
            validatedPrice = validatedPrice.replace('.', ',');
        }
        $defaultPrice.val(validatedPrice);
    });

    $('.activate-lp').click(function() {
        if (!validateAPIKey()) {
            setMessage($(this).data().error, false);
            return;
        }

        // validate price
        var $defaultPrice   = $('#global-default-price'),
            defaultPrice    = $defaultPrice.val();
        // convert price to proper float value
        if (defaultPrice.indexOf(',') > -1) {
            defaultPrice = parseFloat(defaultPrice.replace(',', '.')).toFixed(2);
        } else {
            defaultPrice = parseFloat(defaultPrice).toFixed(2);
        }
        // prevent negative prices
        defaultPrice = Math.abs(defaultPrice);
        // correct prices outside the allowed range of 0.05 - 5.00
        if (defaultPrice > 5) {
            $defaultPrice.val(5);
        } else if (defaultPrice > 0 && defaultPrice < 0.05) {
            $defaultPrice.val(0.05);
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

    // hide pointer while viewing the getStarted tab
    $(document).ready(function() {
        if (typeof($().pointer) !== 'undefined' && $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer')) {
            $('#toplevel_page_laterpay-laterpay-admin').data('wpPointer').pointer.hide();
        }
    });

    // disable tabs
    $('.get-started .tabs li a')
    .mousedown(function() {
        alert(lpVars.i18nTabsDisabled);

        return false;
    });

});})(jQuery);
