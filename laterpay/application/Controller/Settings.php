<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    /**
     * Add LaterPay advanced settings to the settings menu.
     *
     * @return void
     */
    public function add_laterpay_advanced_settings_page() {
        add_options_page(
            __( 'LaterPay Advanced Settings', 'laterpay' ),
            'LaterPay',
            'manage_options',
            'laterpay',
            array( $this, 'render_advanced_settings_page' )
        );
    }

    /**
     * Render settings page for all advanced settings.
     *
     * @return string
     */
    public function render_advanced_settings_page() {
        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay'),
        );

        $this->assign( 'laterpay', $view_args );

        echo $this->get_text_view( 'backend/options' );
    }

    /**
     * Configure content of LaterPay advanced settings page.
     *
     * @return void
     */
    public function init_laterpay_advanced_settings() {
        // add sections with fields
        $this->add_post_settings();
        $this->add_permission_settings();
        $this->add_teaser_content_settings();
        $this->add_api_settings();
        $this->add_logger_settings();
        $this->add_caching_settings();
    }

    /**
     * Add caching section and fields.
     *
     * @return void
     */
    public function add_caching_settings() {
        // caching settings
        add_settings_section(
            'laterpay_caching',
            __( 'Caching', 'laterpay' ),
            array( $this, 'get_chaching_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_caching_compatibility',
            __( 'Caching compatibility', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_caching',
            array(
                'name'  => 'laterpay_caching_compatibility',
                'value' => 1,
            )
        );

        register_setting( 'laterpay', 'laterpay_caching_compatibility' );
    }

    /**
     * Add logger section and fields.
     *
     * @return void
     */
    public function add_logger_settings() {
        // logger settings
        add_settings_section(
            'laterpay_logger',
            __( 'Logger', 'laterpay' ),
            array( $this, 'get_logger_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_access_logging_enabled',
            __( 'Access logging enabled', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_logger',
            array(
                'name'  => 'laterpay_access_logging_enabled',
                'value' => 1,
            )
        );

        register_setting( 'laterpay', 'laterpay_access_logging_enabled' );
    }

    /**
     * Add permissions section and fields.
     *
     * @return void
     */
    public function add_permission_settings() {
        // permissions settings
        add_settings_section(
            'laterpay_permission',
            __( 'Unlimited Access to Paid Content', 'laterpay' ),
            array( $this, 'get_permission_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'unlimited_access_to_paid_content',
            __( 'Roles with unlimited access', 'laterpay' ),
            array( $this, 'get_unlimited_access_markup' ),
            'laterpay',
            'laterpay_permission'
        );

        register_setting( 'laterpay', 'unlimited_access_to_paid_content' );
    }

    /**
     * Add post section and fields.
     *
     * @return void
     */
    public function add_post_settings() {
        // post settings
        add_settings_section(
            'laterpay_post',
            __( 'Post', 'laterpay' ),
            array( $this, 'get_post_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_enabled_post_types',
            __( 'Enabled Post Types', 'laterpay' ),
            array( $this, 'get_enabled_post_types_markup' ),
            'laterpay',
            'laterpay_post'
        );

        register_setting( 'laterpay', 'laterpay_enabled_post_types' );
    }

    /**
     * Add teaser content section and fields.
     *
     * @return void
     */
    public function add_teaser_content_settings() {
        // content settings
        add_settings_section(
            'laterpay_content',
            __( 'Automatically Generated Teaser Content', 'laterpay' ),
            array( $this, 'get_teaser_content_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_content_show_purchase_button',
            __( 'Show purchase button', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name'  => 'laterpay_content_show_purchase_button',
                'value' => 1,
            )
        );

        add_settings_field(
            'laterpay_content_teaser_content_word_count',
            __( 'Teaser content word count', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name' => 'laterpay_content_teaser_content_word_count',
            )
        );

        add_settings_field(
            'laterpay_content_preview_percentage_of_content',
            __( 'Preview percentage of content', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name' => 'laterpay_content_preview_percentage_of_content',
            )
        );

        add_settings_field(
            'laterpay_content_preview_word_count_min',
            __( 'Preview word count minimum', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name' => 'laterpay_content_preview_word_count_min',
            )
        );

        add_settings_field(
            'laterpay_content_preview_word_count_max',
            __( 'Preview word count maximum', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name' => 'laterpay_content_preview_word_count_max',
            )
        );

        register_setting( 'laterpay', 'laterpay_content_show_purchase_button' );
        register_setting( 'laterpay', 'laterpay_content_teaser_content_word_count' );
        register_setting( 'laterpay', 'laterpay_content_preview_percentage_of_content' );
        register_setting( 'laterpay', 'laterpay_content_preview_word_count_min' );
        register_setting( 'laterpay', 'laterpay_content_preview_word_count_max' );
    }

    /**
     * Add API settings section and fields.
     *
     * @return void
     */
    public function add_api_settings() {
        // LaterPay API settings
        add_settings_section(
            'laterpay_api',
            __( 'LaterPay API Settings', 'laterpay' ),
            array( $this, 'get_api_settings_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_api_sandbox_url',
            __( 'Sandbox API endpoint', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_sandbox_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_sandbox_web_url',
            __( 'Sandbox web URL', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_sandbox_web_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_live_url',
            __( 'Live API endpoint', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_live_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_live_web_url',
            __( 'Live web URL', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_live_web_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        // TODO: I don't know any good reason why someone would want to change the URL of the merchantbackend;
        // -> this should not be included in the options page
        add_settings_field(
            'laterpay_api_merchant_backend_url',
            __( 'Merchant backend URL', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_merchant_backend_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        register_setting( 'laterpay', 'laterpay_api_sandbox_url' );
        register_setting( 'laterpay', 'laterpay_api_sandbox_web_url' );
        register_setting( 'laterpay', 'laterpay_api_live_url' );
        register_setting( 'laterpay', 'laterpay_api_live_web_url' );
        register_setting( 'laterpay', 'laterpay_api_merchant_backend_url' );
    }

    /**
     * Render the hint text for the permissions section.
     *
     * @return string description
     */
    public function get_permission_section_description() {
        echo __( "Logged in users can skip LaterPay entirely, if they have a role with unlimited access
                to paid content.<br>
                You can use this e.g. for giving free access to existing subscribers.<br>
                We recommend the plugin 'User Role Editor' for adding custom roles to WordPress.", 'laterpay');
    }

    /**
     * Render the hint text for the caching section.
     *
     * @return string description
     */
    public function get_chaching_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the hint text for the logger section.
     *
     * @return string description
     */
    public function get_logger_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the hint text for the posts section.
     *
     * @return string description
     */
    public function get_post_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the hint text for the teaser content section.
     *
     * @return string description
     */
    public function get_teaser_content_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the hint text for the API settings section.
     *
     * @return string description
     */
    public function get_api_settings_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the inputs for the permissions form.
     *
     * @return string permission checkboxes markup
     */
    public function get_unlimited_access_markup() {
        global $wp_roles;

        $default_roles    = array( 'administrator', 'editor', 'contributor', 'author', 'subscriber' );
        $has_custom_roles = false;

        $inputs_markup = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Roles', 'laterpay' ) . '</span></legend>';
        foreach ( $wp_roles->roles as $role => $role_data ) {
            if ( ! in_array( $role, $default_roles ) ) {
                $has_custom_roles = true;
                $inputs_markup .= '<label title="' . $role_data['name'] . '">';
                $inputs_markup .= '<input type="checkbox" name="unlimited_access_to_paid_content[]" value="' . $role . '" ';
                if ( isset( $role_data['capabilities']['laterpay_has_full_access_to_content'] ) ) {
                    $inputs_markup .= 'checked="checked"';
                }
                $inputs_markup .= '>';
                $inputs_markup .= '<span>' . $role_data['name'] . '</span>';
                $inputs_markup .= '</label><br>';
            }
        }
        $inputs_markup .= '</fieldset>';

        if ( ! $has_custom_roles ) {
            $inputs_markup = __( 'Please add a custom role first.', 'laterpay' );
        }

        echo $inputs_markup;
    }

    /**
     * Generic method to render text inputs (URL, email, text).
     *
     * @param array $field array of field params
     *
     * @return string text markup
     */
    public function get_text_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );
            $type         = isset( $field[ 'type' ] ) ? $field['type']  : 'text';
            $class        = isset( $field[ 'class'] ) ? $field['class'] : '';

            $inputs_markup = '<input type="' . $type .'" name="' . $field[ 'name' ] . '" ';
            $inputs_markup .= 'class="regular-text ' . $class . '" value="' . $option_value . '">';
        }

        echo $inputs_markup;
    }

    /**
     * Generic method to render checkboxes.
     *
     * @param array $field array of field params
     *
     * @return string checkbox markup
     */
    public function get_checkbox_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) && isset( $field[ 'value' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );
            $field_value  = $field[ 'value' ];

            $inputs_markup = '<input type="checkbox" name="' . $field[ 'name' ] . '" value="' . $field_value . '"';
            $inputs_markup .= $option_value ? ' checked="checked"' : '';
            $inputs_markup .= '>';
        }

        echo $inputs_markup;
    }

    /**
     * Render enabled post types inputs.
     *
     * @return string enabled post types checkboxes markup
     */
    public function get_enabled_post_types_markup() {
        $all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
        $enabled_post_types = get_option( 'laterpay_enabled_post_types' );

        $inputs_markup = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Enabled Post Types', 'laterpay' ) . '</span></legend>';
        foreach ( $all_post_types as $slug => $post_type ) {
            $inputs_markup .= '<label title="' . $post_type->labels->name . '">';
            $inputs_markup .= '<input type="checkbox" name="laterpay_enabled_post_types[]" value="' . $slug . '" ';
            if ( is_array( $enabled_post_types ) && in_array( $slug, $enabled_post_types ) ) {
                $inputs_markup .= 'checked="checked"';
            }
            $inputs_markup .= '>';
            $inputs_markup .= '<span>' . $post_type->labels->name . '</span>';
            $inputs_markup .= '</label><br>';
        }
        $inputs_markup .= '</fieldset>';

        echo $inputs_markup;
    }

}
