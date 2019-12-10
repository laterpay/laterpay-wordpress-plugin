/**
 * To add TinyMCE drop down for shortcode generator.
 *
 * @package laterpay
 */
/* globals laterpay_shortcode_generator_labels, tinymce, wp */

(function ($) {
    $(function () {
        'use strict';

        var laterpay_shortcode_generator = {

            /**
             * Init function,
             * To register function to add dropdown for shortcode generator.
             *
             * @return void
             */
            init: function () {

                if ('object' !== typeof tinymce) {
                    return;
                }

                var self = this;

                tinymce.PluginManager.add('laterpay_shortcode_generator', function (editor, url) {
                    self.register_dropdown(editor, url);
                });

            },

            /**
             * To convert object into string.
             *
             * @param {object} object Object that need to convert.
             *
             * @return {string} Converted string.
             */
            object_to_string: function (object) {

                if ('object' !== typeof object) {
                    return '';
                }

                var string = '',
                    index = 0;

                for (index in object) {
                    if (object.hasOwnProperty(index)) {
                        string += index + '="' + object[index] + '" ';
                    }
                }

                return string;
            },

            /**
             * On click event of color field.
             *
             * @return void
             */
            colorbox_on_action: function () {

                if ('object' !== typeof tinymce) {
                    return;
                }

                var editor = tinymce.activeEditor;

                var colorPickerCallback = editor.settings.color_picker_callback;

                if (colorPickerCallback) {
                    return function () {
                        var self = this;

                        colorPickerCallback.call(
                            editor,
                            function (value) {
                                self.value(value).fire('change');
                            },
                            self.value()
                        );
                    };
                }
            },

            /**
             * Callback of on click media button.
             * To open WordPress media library and set URL as value when user select image.
             *
             * @return void
             */
            onclick_media_button: function () {
                var field = this;
                var frame = wp.media();

                frame.on('select', function () {

                    var attachment = frame.state().get('selection').first().toJSON();

                    if ('object' !== typeof attachment) {
                        return;
                    }

                    var value_to_save = attachment.url;

                    if ( 'undefined' !== typeof field.settings.save_id && true === field.settings.save_id ) {
                        value_to_save = attachment.id;
                    }

                    field.state.data.value = value_to_save;

                    var image_element_id = field.settings.preview_element_id || '';
                    var image_element = $('#' + image_element_id);

                    if ( 0 !== image_element.length ) {
                        $( image_element ).attr( 'src', attachment.url );
                    }

                });

                frame.open();
            },

            /**
             * To add dropdown for shortcode generator.
             *
             * @param {object} editor Object of TinyMCE editor.
             *
             * @return void
             */
            register_dropdown: function (editor) {

                var self = this;

                editor.addButton(
                    'laterpay_shortcode_generator',
                    {
                        text: laterpay_shortcode_generator_labels.button.text,
                        icon: 'laterpay-logo',
                        type: 'menubutton',
                        menu: [
                            {
                                text   : laterpay_shortcode_generator_labels.premium_download.title,
                                onclick: function () {

                                    var modal_data = laterpay_shortcode_generator_labels.premium_download;

                                    // Open window
                                    editor.windowManager.open({
                                        title   : modal_data.title,
                                        width   : 512,
                                        height  : 480,
                                        body    : [
                                            {
                                                type : 'button',
                                                name : 'target_post_id',
                                                label: modal_data.target_post_id.label,
                                                text: modal_data.target_post_id.text,
                                                save_id: true,
                                                preview_element_id:'preview_target_post_id',
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<img id="preview_target_post_id" src="' +
                                                    laterpay_shortcode_generator_labels.preview_image +
                                                    '" style="width: 100px; height: 100px; margin: 10px;"/>',
                                        },
                                            {
                                                type : 'textbox',
                                                name : 'heading_text',
                                                label: modal_data.heading_text.label,
                                                value: modal_data.heading_text.value,
                                        },
                                            {
                                                type : 'textbox',
                                                name : 'description_text',
                                                label: modal_data.description_text.label,
                                        },
                                            {
                                                type  : 'listbox',
                                                name  : 'content_type',
                                                label : modal_data.content_type.label,
                                                values: modal_data.content_type.values,
                                        },
                                            {
                                                type   : 'button',
                                                name   : 'teaser_image_path',
                                                label  : modal_data.teaser_image_path.label,
                                                text   : modal_data.teaser_image_path.text,
                                                preview_element_id:'preview_teaser_image_path',
                                                onclick: self.onclick_media_button,
                                        },
                                        {
                                            type: 'container',
                                            label: ' ',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                            html: '<img id="preview_teaser_image_path" src="' +
                                                    laterpay_shortcode_generator_labels.preview_image +
                                                    '" style="width: 100px; height: 100px; margin: 10px;"/>',
                                        },
                                        ],
                                        onsubmit: function (e) {
                                            var values = self.object_to_string(e.data);
                                            var shortcode = '[laterpay_premium_download ' + values + ' ]';
                                            editor.insertContent(shortcode);
                                        }
                                    });

                                }
                        },
                            {
                                text   : laterpay_shortcode_generator_labels.time_pass_purchase_button.title,
                                onclick: function () {

                                    var modal_data = laterpay_shortcode_generator_labels.time_pass_purchase_button,
                                        body = [],
                                        height = 380;

                                    if (0 >= modal_data.id.values.length) {
                                        // We don't have any item to show.
                                        // Text passed to html is escaped string.
                                        height = 50;
                                        body = [
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<b>' + modal_data.no_item_text + '</b>',
                                        }
                                        ];
                                    } else {
                                        body = [
                                            {
                                                type  : 'listbox',
                                                name  : 'id',
                                                label : modal_data.id.label,
                                                values: modal_data.id.values,
                                        },
                                            {
                                                type   : 'button',
                                                name   : 'custom_image_path',
                                                label  : modal_data.custom_image_path.label,
                                                text   : modal_data.custom_image_path.text,
                                                preview_element_id:'preview_custom_image_path',
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<img id="preview_custom_image_path" src="' +
                                                    laterpay_shortcode_generator_labels.preview_image +
                                                    '" style="width: 100px; height: 100px; margin: 10px;"/>',
                                        },
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<div style="text-align: center;letter-spacing: 5px;"> ' + '-------- <span style="letter-spacing: 0;">' + modal_data.or_text + '</span> --------</div>', // jshint ignore:line
                                        },
                                            {
                                                type : 'textbox',
                                                name : 'button_text',
                                                label: modal_data.button_text.label,
                                        },
                                            {
                                                type    : 'colorbox',
                                                name    : 'button_background_color',
                                                label   : modal_data.button_background_color.label,
                                                value   : modal_data.button_background_color.value,
                                                onaction: self.colorbox_on_action,
                                        },
                                            {
                                                type    : 'colorbox',
                                                name    : 'button_text_color',
                                                label   : modal_data.button_text_color.label,
                                                value   : '#ffffff',
                                                onaction: self.colorbox_on_action,
                                        },
                                        ];
                                    }

                                    editor.windowManager.open({
                                        title   : modal_data.title,
                                        width   : 512,
                                        height  : height,
                                        body    : body,
                                        onsubmit: function (e) {

                                            // If there is no item then we don't need to render shortcode.
                                            if (0 >= modal_data.id.values.length) {
                                                return;
                                            }

                                            var values = self.object_to_string(e.data);
                                            var shortcode = '[laterpay_time_pass_purchase ' + values + ' ]';
                                            editor.insertContent(shortcode);
                                        }
                                    });

                                }
                        },
                            {
                                text   : laterpay_shortcode_generator_labels.subscription_purchase_button.title,
                                onclick: function () {

                                    var modal_data = laterpay_shortcode_generator_labels.subscription_purchase_button,
                                        body = [],
                                        height = 380;

                                    if (0 >= modal_data.id.values.length) {
                                        // We don't have any item to show.
                                        // Text passed to html is escaped string.
                                        height = 50;
                                        body = [
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<b>' + modal_data.no_item_text + '</b>',
                                        }
                                        ];
                                    } else {
                                        body = [
                                            {
                                                type  : 'listbox',
                                                name  : 'id',
                                                label : modal_data.id.label,
                                                values: modal_data.id.values,
                                        },
                                            {
                                                type   : 'button',
                                                name   : 'custom_image_path',
                                                label  : modal_data.custom_image_path.label,
                                                text   : modal_data.custom_image_path.text,
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<img id="preview_custom_image_path" src="' + laterpay_shortcode_generator_labels.preview_image + '" style="width: 100px; height: 100px; margin: 10px;"/>', // jshint ignore:line
                                        },
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<div style="text-align: center;letter-spacing: 5px;"> ' + '--------<span style="letter-spacing: 0;">' + modal_data.or_text + '</span>--------</div>', // jshint ignore:line
                                        },
                                            {
                                                type : 'textbox',
                                                name : 'button_text',
                                                label: modal_data.button_text.label,
                                        },
                                            {
                                                type    : 'colorbox',
                                                name    : 'button_background_color',
                                                label   : modal_data.button_background_color.label,
                                                value   : modal_data.button_background_color.value,
                                                onaction: self.colorbox_on_action,
                                        },
                                            {
                                                type    : 'colorbox',
                                                name    : 'button_text_color',
                                                label   : modal_data.button_text_color.label,
                                                value   : '#ffffff',
                                                onaction: self.colorbox_on_action,
                                        },
                                        ];
                                    }

                                    editor.windowManager.open({
                                        title   : modal_data.title,
                                        width   : 512,
                                        height  : height,
                                        body    : body,
                                        onsubmit: function (e) {

                                            // If there is no item then we don't need to render shortcode.
                                            if (0 >= modal_data.id.values.length) {
                                                return;
                                            }

                                            var values = self.object_to_string(e.data);
                                            var shortcode = '[laterpay_subscription_purchase ' + values + ' ]';
                                            editor.insertContent(shortcode);
                                        }
                                    });

                                }
                        },
                        ]
                    }
                );
            },

        };

        laterpay_shortcode_generator.init();
    });
})(jQuery);
