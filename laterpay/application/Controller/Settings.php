<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    public static $defaults = array(
        'laterpay_api_sandbox_url'                          => 'https://api.sandbox.laterpaytest.net',
        'laterpay_api_sandbox_web_url'                      => 'https://web.sandbox.laterpaytest.net',

        'laterpay_api_live_url'                             => 'https://api.laterpay.net',
        'laterpay_api_live_web_url'                         => 'https://web.laterpay.net',

        'laterpay_api_merchant_backend_url'                 => 'https://merchant.laterpay.net/',

        'laterpay_content_show_purchase_button'             => 1,

        'laterpay_content_teaser_content_word_count'        => '60',
        'laterpay_content_preview_percentage_of_content'    => '25',
        'laterpay_content_preview_word_count_min'           => '26',
        'laterpay_content_preview_word_count_max'           => '200',
    );

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
        // permissions settings
        add_settings_section(
            'laterpay_permissions',
            __( 'Unlimited Access to Paid Content', 'laterpay' ),
            array( $this, 'get_permissions_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'unlimited_access_to_paid_content',
            __( 'Roles with unlimited access', 'laterpay' ),
            array( $this, 'get_unlimited_access_markup' ),
            'laterpay',
            'laterpay_permissions'
        );

        register_setting( 'laterpay', 'unlimited_access_to_paid_content' );


        // content settings
        add_settings_section(
            'laterpay_content',
            __( 'Automatically Generated Teaser Content', 'laterpay' ),
            array( $this, 'get_content_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_content_show_purchase_button',
            __( 'Show purchase button', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_content',
            array(
                'name' => 'laterpay_content_show_purchase_button',
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
     * Render the hint text for the activated post types section.
     *
     * @return string description
     */
    public function get_post_types_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * Render the inputs for the activated post types form.
     *
     * @return string activated post types checkboxes markup
     */
    public function get_activated_post_types_markup() {
        $inputs_markup = 'Stuff. And things.';

        echo $inputs_markup;
    }

    /**
     * Render the hint text for the permissions section.
     *
     * @return string description
     */
    public function get_permissions_section_description() {
        echo __( "Logged in users can skip LaterPay entirely, if they have a role with unlimited access
                to paid content.<br>
                You can use this e.g. for giving free access to existing subscribers.<br>
                We recommend the plugin 'User Role Editor' for adding custom roles to WordPress.", 'laterpay');
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
     * [get_content_section_description description]
     *
     * @return [type] [description]
     */
    public function get_content_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * [get_api_settings_section_description description]
     *
     * @return [type] [description]
     */
    public function get_api_settings_section_description() {
        echo __( 'lorem ipsum', 'laterpay');
    }

    /**
     * [get_text_field_markup description]
     *
     * @param  [type] $field [description]
     *
     * @return [type]        [description]
     */
    public function get_text_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );
            $type         = isset( $field[ 'type' ] ) ? $field['type']  : 'text';
            $class        = isset( $field[ 'class'] ) ? $field['class'] : '';

            $inputs_markup = '<input type="' . $type .'" name="' . $field[ 'name' ] . '" ';
            $inputs_markup .= 'class="regular-text ' . $class . '" value="';
            $inputs_markup .= $option_value ? $option_value : self::$defaults[ $field['name'] ];
            $inputs_markup .= '">';
        }

        echo $inputs_markup;
    }

    /**
     * [get_checkbox_field_markup description]
     *
     * @param  [type] $field [description]
     *
     * @return [type]        [description]
     */
    public function get_checkbox_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );

            $inputs_markup = '<input type="checkbox" name="' . $field[ 'name' ] . '" value="';
            $inputs_markup .= isset( $field[ 'value' ] ) ? $field[ 'value' ] : self::$defaults[ $field['name'] ];
            $inputs_markup .= '" ';
            $inputs_markup .= $option_value ? 'checked="checked"' : '';
            $inputs_markup .= '>';
        }

        echo $inputs_markup;
    }

}
