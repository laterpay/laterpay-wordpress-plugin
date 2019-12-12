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
                    if ( object.hasOwnProperty( index ) && object[ index ] && '' !== object[ index ] ) {
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
                var wp_media_args = field.settings.wp_media_args || {};
                var frame = wp.media( wp_media_args );

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

                    var preview_container_id = 'preview_' + field.settings.name;
                    var preview_container = $( '#' + preview_container_id );
                    var image_element = $( '.preview-image', preview_container );
                    var media_name_element = $( '.media-name', preview_container );
                    var clear_media_element = $( '.button-clear_media', preview_container );

                    if ( 0 !== image_element.length ) {
                        var preview_image = attachment.url;
                        var media_name = attachment.filename;

                        if ( 'video' === attachment.type ) {
                            preview_image = laterpay_shortcode_generator_labels.preview_images.video;
                        } else if ( 'audio' === attachment.type ) {
                            preview_image = laterpay_shortcode_generator_labels.preview_images.audio;
                        } else if ( 'text' === attachment.type ) {
                            preview_image = laterpay_shortcode_generator_labels.preview_images.text;
                        } else if ( 'image' !== attachment.type ) {
                            preview_image = laterpay_shortcode_generator_labels.preview_images.no_preview_image;
                        }

                        $( image_element ).attr( 'src', preview_image );
                        $( media_name_element ).attr( 'title', media_name ).text( media_name );
                        $( clear_media_element ).removeClass( 'hidden' );
                    }

                });

                frame.open();
            },

            /**
             * To generate media preview mark for media field in shortcode generator.
             *
             * @param {string} target_field ID of target field.
             *
             * @returns {string} HTML markup for media preview.
             */
            get_media_markup: function ( target_field ) {

                var labels = laterpay_shortcode_generator_labels;

                // @codingStandardsIgnoreStart
                return '<div id="preview_' + target_field + '" class="lp-media-preview" data-target_field="' + target_field + '">' + // jshint ignore:line
                       '<img class="preview-image" src="' + labels.preview_images.gallery + '"/>' +
                       '<span class="media-name"></span>' +
                       '<a class="button-clear_media hidden" href="javascript:">' + labels.button.clear + '</a>' +
                       '<br class="clear"/>' +
                       '</div>';
                // @codingStandardsIgnoreEnd

            },

            /**
             * To handle click event of clear media button.
             * Manage to remove preview of media and remove value in field.
             *
             * @param {object} element HTML element for clear media button.
             * @param {object} editor_window Object of editor window. To clear value from the field.
             *
             * @return void
             */
            on_clear_media: function (element, editor_window) {

                var preview_container = $( element ).parent( '.lp-media-preview' );
                var image_element = $( '.preview-image', preview_container );
                var media_name_element = $( '.media-name', preview_container );
                var clear_media_element = $( '.button-clear_media', preview_container );
                var target_field = $( preview_container ).data( 'target_field' );
                var controls = editor_window.controlIdLookup;
                var key = '';

                $( image_element ).attr( 'src', laterpay_shortcode_generator_labels.preview_images.gallery );
                $( media_name_element ).attr( 'title', '' ).text( '' );
                $( clear_media_element ).addClass( 'hidden' );

                /**
                 * Find field to remove value. And change the state/value of field.
                 */
                if ( 'object' === typeof controls ) {
                    for ( key in controls ) {
                        if ( controls.hasOwnProperty(key) && target_field === controls[ key ]._name ) {
                            editor_window.controlIdLookup[ key ].state.data.value = '';
                        }
                    }
                }

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
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                html: self.get_media_markup( 'target_post_id' )
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
                                                wp_media_args: {
                                                    library: {
                                                        type: 'image',
                                                    }
                                                },
                                                onclick: self.onclick_media_button,
                                        },
                                        {
                                            type: 'container',
                                            label: ' ',
                                            html: self.get_media_markup( 'teaser_image_path' ),
                                        },
                                        ],
                                        onopen: function () {
                                            var editor_window = this;

                                            $( '.lp-media-preview .button-clear_media' ).on( 'click', function () {
                                                self.on_clear_media( this, editor_window );
                                            } );
                                        },
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
                                                wp_media_args: {
                                                    library: {
                                                        type: 'image',
                                                    }
                                                },
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                html: self.get_media_markup( 'custom_image_path' ),
                                        },
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<div style="text-align: center;letter-spacing: 5px;">-------- <span style="letter-spacing: 0;">' + modal_data.or_text + '</span> --------</div>', // jshint ignore:line
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
                                        onopen: function () {
                                            var editor_window = this;

                                            $( '.lp-media-preview .button-clear_media' ).on( 'click', function () {
                                                self.on_clear_media( this, editor_window );
                                            } );
                                        },
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
                                                wp_media_args: {
                                                    library: {
                                                        type: 'image',
                                                    }
                                                },
                                                onclick: self.onclick_media_button,
                                        },
                                            {
                                                type: 'container',
                                                label: ' ',
                                                html: self.get_media_markup( 'custom_image_path' ),
                                        },
                                            {
                                                type: 'container',
                                                // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
                                                html: '<div style="text-align: center;letter-spacing: 5px;">--------<span style="letter-spacing: 0;">' + modal_data.or_text + '</span>--------</div>', // jshint ignore:line
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
                                        onopen: function () {
                                            var editor_window = this;

                                            $( '.lp-media-preview .button-clear_media' ).on( 'click', function () {
                                                self.on_clear_media( this, editor_window );
                                            } );
                                        },
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
