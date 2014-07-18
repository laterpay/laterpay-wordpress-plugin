jQuery.noConflict();
(function($) { $(function() {

    $('input[type=checkbox].styled, input[type=radio].styled').ezMark();

    // teaser content visibility Ajax form
    $('#teaser_content_only input[name="teaser_content_only"]').change(function() {
        $.post(
            ajaxurl,
            $('#teaser_content_only').serializeArray(),
            function(data) { setMessage(data); }
        );
    });

});})(jQuery);
