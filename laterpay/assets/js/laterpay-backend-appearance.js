(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                preview_form: $('#laterpay_paid_content_preview_form'),
				post_type_form: $('#laterpay_supported_post_types_form')
            },

            bindEvents = function() {
                // switch paid content preview mode
                $('.lp_js_toggle-preview-mode', $o.preview_form)
                .change(function() {
                    saveAppearance();
                });

				$o.post_type_form.change( function(){
					saveSupportedPostTypes();
				});
            },

			saveSupportedPostTypes = function(){
				$.post(
					ajaxurl,
					$o.post_type_form.serializeArray(),
					function(data) {setMessage(data);}
				);
			},

            saveAppearance = function() {
                $.post(
                    ajaxurl,
                    $o.preview_form.serializeArray(),
                    function(data) {setMessage(data);}
                );
            },

            styleInputs = function() {
                $('.lp_js_style-input').ezMark();
            },

            initializePage = function() {
                bindEvents();
                styleInputs();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
