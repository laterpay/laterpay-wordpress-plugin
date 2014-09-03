jQuery.noConflict();
(function($) {$(function() {

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
        // format price with two digits
        price = price.toFixed(2);

        // localize price
        if (lpVars.locale == 'de_DE') {
            price = price.replace('.', ',');
        }

        return price;
    }


    // #####################################################################
    // Edit Global Default Price
    // #####################################################################

    function enterEditModeGlobalPrice() {
        $('#lp_global-price-text, #lp_global-price-form .lp_change-link').hide();
        $('#lp_global-default-price, #lp_global-price-form .lp_cancel-link, #lp_global-price-form .lp_save-link').show(0, function() {
            setTimeout(function() { $('#lp_global-default-price').focus(); }, 50);
        });
        $('#lp_global-price-form').addClass('editing');
    }

    function exitEditModeGlobalPrice() {
        $('#lp_global-price-text, #lp_global-price-form .lp_change-link').show();
        $('#lp_global-default-price, #lp_global-price-form .lp_cancel-link, #lp_global-price-form .lp_save-link').hide();
        $('#lp_global-price-form').removeClass('editing');
    }

    // edit global default price
    $('#lp_global-price-form .lp_change-link')
    .mousedown(function() { enterEditModeGlobalPrice(); })
    .click(function(e) { e.preventDefault(); });

    // cancel editing global default price
    $('#lp_global-price-form .lp_cancel-link')
    .mousedown(function() { exitEditModeGlobalPrice(); })
    .click(function(e) { e.preventDefault(); });

    // save global default price
    $('#lp_global-price-form .lp_save-link').mousedown(function() {
        $('#lp_global-default-price').val(validatePrice($('#lp_global-default-price').val()));
        $.post(
            ajaxurl,
            $('#lp_global-price-form').serializeArray(),
            function(r) {
                if (r.success) {
                    $('#lp_global-price-text').html(r.laterpay_global_price);
                }
                setMessage(r.message, r.success);
            },
            'json'
        );
        exitEditModeGlobalPrice();
    })
    .click(function(e) { e.preventDefault(); });


    // #####################################################################
    // Edit Category Default Prices
    // #####################################################################

    function formatSelection(data, container) {
        var $form = $(container).parent().parent().parent();
        $form.find('input[name=category]').val(data.text);

        return data.text;
    }

    function renderCategorySelect($form) {
        $('.lp_category-select', $form).select2({
            allowClear      : true,
            ajax            : {
                                url         : ajaxurl,
                                data        : function(term) {
                                                return {
                                                    term        : term,
                                                    action      : 'laterpay_pricing',
                                                    category    : $(this).parent().find('input[name=category_id]').val()
                                                };
                                            },
                                results     : function(data) { return { results: data }; },
                                dataType    : 'json'
                            },
            initSelection   : function(element, callback) {
                                var id = $(element).val();
                                if (id !== '') {
                                    var data = { text: id };
                                    callback(data);
                                }
                            },
            formatResult    : function(data) { return data.text; },
            formatSelection : formatSelection,
            escapeMarkup    : function(m) { return m; }
        });
    }

    function exitEditModeCategoryPrice($form, editAnotherCategory) {
        $form.removeClass('editing');
        if ($form.hasClass('unsaved')) {
            // remove form, if creating a new category default price has been canceled
            $form.fadeOut(250, function() {
                $(this).remove();
            });
        } else {
            // hide form, if editing an existing category default price has been canceled
            $('.lp_number-input, .lp_save-link, .lp_cancel-link', $form).hide();
            $('.lp_category-select', $form).select2('destroy');
            $('.lp_category-title, .lp_category-price, .lp_change-link, .lp_delete-link', $form).show();
            // reset value of price input to current category default price
            $('.lp_number-input', $form).val($('.lp_category-price', $form).text());
        }
        if (!editAnotherCategory) {
            $('#lp_add-category-link').fadeIn(250);
        }
    }

    function editCategoryDefaultPrice($form) {
        // exit edit mode of all other category prices
        $('.lp_category-price-form.editing').each(function() {
            exitEditModeCategoryPrice($(this), true);
        });

        // initialize edit mode
        $form.addClass('editing');
        $('.lp_category-title, .lp_category-price, .lp_change-link, .lp_delete-link', $form).hide();
        $('#lp_add-category-link').fadeOut(250);
        $('.lp_number-input, .lp_save-link, .lp_cancel-link', $form).show();
        renderCategorySelect($form);

        // save category default price
        $form
        .on('mousedown', '.lp_save-link', function() {
            $('.lp_input.lp_number-input', $form).val(validatePrice($('.lp_input.lp_number-input', $form).val()));
            $form.removeClass('unsaved');
            $.post(
                ajaxurl,
                $form.serializeArray(),
                function(r) {
                    if (r.success) {
                        $('.lp_category-price', $form).text(r.price);
                        $('.lp_category-title', $form).text(r.category);
                        $('input[name=category_id]', $form).val(r.category_id);
                    }
                    setMessage(r.message, r.success);
                },
                'json'
            );
            exitEditModeCategoryPrice($form);
        })
        .on('click', '.lp_save-link', function(e) { e.preventDefault(); });

        // cancel editing category default price
        $form
        .on('mousedown', '.lp_cancel-link', function() { exitEditModeCategoryPrice($form); })
        .on('click',     '.lp_cancel-link', function(e) { e.preventDefault(); });
    }

    // add category default price for another category
    $('#lp_add-category-link')
    .mousedown(function() {
        $('#lp_add-category-link').fadeOut(250);
        var $form = $('#category-price-form-template')
                    .clone()
                    .removeAttr('id')
                    .appendTo('#lp_category-prices')
                    .fadeIn(250);
        editCategoryDefaultPrice($form);
    })
    .click(function(e) { e.preventDefault(); });

    // edit category default price
    $('#lp_category-prices')
    .on('mousedown', '.lp_change-link', function() {
        var $form = $(this).parents('.lp_category-price-form');
        editCategoryDefaultPrice($form);
    })
    .on('click', '.lp_change-link', function(e) { e.preventDefault(); });

    // delete category default price
    $('#lp_category-prices')
    .on('mousedown', '.lp_delete-link', function() {
        var $form = $(this).parents('.lp_category-price-form');
        $('input[name=form]', $form).val('price_category_form_delete');

        $.post(
            ajaxurl,
            $form.serializeArray(),
            function(r) {
                if (r.success) {
                    $form.fadeOut(400, function() {
                        $(this).remove();
                    });
                }
                setMessage(r.message, r.success);
            },
            'json'
        );
    })
    .on('click', '.lp_delete-link', function(e) { e.preventDefault(); });


    // #############################################################################################
    // Edit Default Currency
    // #############################################################################################

    function updateCurrency(currency) {
        $('.lp_currency').html(currency);
    }

    $('#lp_currency-select').change(function() {
        $.post(
            ajaxurl,
            $('#lp_currency-form').serializeArray(),
            function(r) {
                if (r.success) {
                    updateCurrency(r.laterpay_currency);
                }
                setMessage(r.message, r.success);
            },
            'json'
        );
    });

});})(jQuery);
