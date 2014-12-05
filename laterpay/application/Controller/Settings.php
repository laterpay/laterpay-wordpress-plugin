<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    /**
     * Add LaterPay advanced settings to the settings menu.
     *
     * @return void
     */
    public function add_laterpay_settings_page() {
        add_options_page(
            __( 'LaterPay Advanced Settings', 'laterpay' ),
            'LaterPay',
            'manage_options',
            'laterpay',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render settings page for all advanced settings.
     *
     * @return string
     */
    public function render_settings_page() {
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
    public function init_laterpay_settings() {
        // caching compatible mode
        // - toggle caching compatibility
        // - purge cache

        // access logging for statistics
        // $access_logging_enabled = apply_filters( 'later_pay_access_logging_enabled', true );

        // activated post types
        // add_settings_section(
        //     'laterpay_activated_post_types',
        //     __( 'Activated Post Types', 'laterpay' ),
        //     array( $this, 'get_post_types_section_description' ),
        //     'laterpay'
        // );

        // add_settings_field(
        //     'activated_post_types',
        //     __( 'Activated Post Types', 'laterpay' ),
        //     array( $this, 'get_activated_post_types_markup' ),
        //     'laterpay',
        //     'laterpay_activated_post_types'
        // );

        // register_setting( 'laterpay', 'activated_post_types' );

        // show purchase button
        // 'content.show_purchase_button' => true,

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

        // content preview settings
        // 'content.auto_generated_teaser_content_word_count'  => 60,
        // 'content.preview_percentage_of_content'             => 25,
        // 'content.preview_word_count_min'                    => 26,
        // 'content.preview_word_count_max'                    => 200,

        // API endpoints settings
        // 'api.sandbox_url'           => 'https://api.sandbox.laterpaytest.net',
        // 'api.sandbox_web_url'       => 'https://web.sandbox.laterpaytest.net',
        // 'api.live_url'              => 'https://api.laterpay.net',
        // 'api.live_web_url'          => 'https://web.laterpay.net',
        // 'api.merchant_backend_url'  => 'https://merchant.laterpay.net/',
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

        $inputs_markup = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Roles', 'laterpay' ) . '</span></legend>';
        foreach ( $wp_roles->roles as $role ) {
            $inputs_markup .= '<label title="' . $role['name'] . '">';
            $inputs_markup .= '<input type="checkbox" name="unlimited_access_to_paid_content[]" value="' . strtolower( $role['name'] ) . '" ';
            if ( isset( $role['capabilities']['laterpay_has_full_access_to_content'] ) ) {
                $inputs_markup .= 'checked="checked"';
            }
            $inputs_markup .= '>';
            $inputs_markup .= '<span>' . $role['name'] . '</span>';
            $inputs_markup .= '</label><br>';
        }
        $inputs_markup .= '</fieldset>';

        echo $inputs_markup;
    }
}

