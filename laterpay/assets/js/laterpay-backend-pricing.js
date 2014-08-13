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
        $('#laterpay-global-price-text, #global-price-form .laterpay-change-link').hide();
        $('#global-default-price, #global-price-form .laterpay-cancel-link, #global-price-form .laterpay-save-link').show(0, function() {
            setTimeout(function() { $('#global-default-price').focus(); }, 50);
        });
        $('#global-price-form').addClass('editing');
    }

    function exitEditModeGlobalPrice() {
        $('#laterpay-global-price-text, #global-price-form .laterpay-change-link').show();
        $('#global-default-price, #global-price-form .laterpay-cancel-link, #global-price-form .laterpay-save-link').hide();
        $('#global-price-form').removeClass('editing');
    }

    // edit global default price
    $('#global-price-form .laterpay-change-link')
    .mousedown(function() { enterEditModeGlobalPrice(); })
    .click(function(e) { e.preventDefault(); });

    // cancel editing global default price
    $('#global-price-form .laterpay-cancel-link')
    .mousedown(function() { exitEditModeGlobalPrice(); })
    .click(function(e) { e.preventDefault(); });

    // save global default price
    $('#global-price-form .laterpay-save-link').mousedown(function() {
        $('#global-default-price').val(validatePrice($('#global-default-price').val()));
        $.post(
            ajaxurl,
            $('#global-price-form').serializeArray(),
            function(r) {
                if (r.success) {
                    $('#laterpay-global-price-text').html(r.laterpay_global_price);
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
        $('.category-select', $form).select2({
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

    function exitEditModeCategoryPrice($form) {
        $form.removeClass('editing');
        if ($form.hasClass('unsaved')) {
            $form.fadeOut(250, function() {
                $(this).remove();
            });
        } else {
            $('.number, .laterpay-save-link, .laterpay-cancel-link', $form).hide();
            $('.category-select', $form).select2('destroy');
            $('.category-title, .category-price, .laterpay-change-link, .laterpay-delete-link', $form).show();
        }
        $('#add_category_button').fadeIn(250);
    }

    function editCategoryDefaultPrice($form) {
// TODO: end edit mode for all other category prices

        // initialize edit mode
        $form.addClass('editing');
        $('.category-title, .category-price, .laterpay-change-link, .laterpay-delete-link', $form).hide();
        $('#add_category_button').fadeOut(250);
        $('.number, .laterpay-save-link, .laterpay-cancel-link', $form).show();
        renderCategorySelect($form);

        // save category default price
        $form
        .on('mousedown', '.laterpay-save-link', function() {
            $('.lp-input.number', $form).val(validatePrice($('.lp-input.number', $form).val()));
            $form.removeClass('unsaved');
            $.post(
                ajaxurl,
                $form.serializeArray(),
                function(r) {
                    if (r.success) {
                        $('.category-price', $form).text(r.price);
                        $('.category-title', $form).text(r.category);
                        $('input[name=category_id]', $form).val(r.category_id);
                    }
                    setMessage(r.message, r.success);
                },
                'json'
            );
            exitEditModeCategoryPrice($form);
        })
        .on('click', '.laterpay-save-link', function(e) { e.preventDefault(); });

        // cancel editing category default price
        $form
        .on('mousedown', '.laterpay-cancel-link', function() { exitEditModeCategoryPrice($form); })
        .on('click',     '.laterpay-cancel-link', function(e) { e.preventDefault(); });
    }

    // add category default price for another category
    $('#add_category_button')
    .mousedown(function() {
        $('#add_category_button').fadeOut(250);
        var $form = $('#category-price-form-template')
                    .clone()
                    .removeAttr('id')
                    .appendTo('#category-prices')
                    .fadeIn(250);
        editCategoryDefaultPrice($form);
    })
    .click(function(e) { e.preventDefault(); });

    // edit category default price
    $('#category-prices')
    .on('mousedown', '.laterpay-change-link', function() {
        var $form = $(this).parents('.category-price-form');
        editCategoryDefaultPrice($form);
    })
    .on('click', '.laterpay-change-link', function(e) { e.preventDefault(); });

    // delete category default price
    $('#category-prices')
    .on('mousedown', '.laterpay-delete-link', function() {
        var $form = $(this).parents('.category-price-form');
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
    .on('click', '.laterpay-delete-link', function(e) { e.preventDefault(); });


    // #############################################################################################
    // Edit Default Currency
    // #############################################################################################

    function updateCurrency(currency) {
        $('.laterpay_currency').html(currency);
    }

    $('#laterpay_currency').change(function() {
        $.post(
            ajaxurl,
            $('#currency_form').serializeArray(),
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
