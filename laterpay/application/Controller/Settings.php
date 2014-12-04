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
     * @return void
     */
    public function render_settings_page() {
        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay'),
        );

        $this->assign( 'laterpay', $view_args );

        echo $this->get_text_view( 'backend/options' );
    }

    /**
     * FIXME: [init_laterpay_settings description]
     *
     * @return void
     */
    public function init_laterpay_settings() {
        // form for permissions settings
        add_settings_section(
            'laterpay_permissions',
            __( 'Unlimited Access to Paid Content', 'laterpay' ),
            array( $this, 'get_permissions_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'unlimited_post_access',
            __( 'Roles with unlimited access', 'laterpay' ),
            array( $this, 'get_unlimited_access_markup' ),
            'laterpay',
            'laterpay_permissions'
        );

        register_setting( 'laterpay', 'unlimited_post_access' );


    }

    /**
     * Render a hint text for the permissions section.
     *
     * @return string description
     */
    public function get_permissions_section_description() {
        echo __( "Logged in users skip LaterPay entirely, if they have a role with unlimited access
                to paid content.<br>
                You can use this e.g. for giving free access to existing subscribers.<br>
                We recommend the plugin 'User Role Editor' for adding custom roles to WordPress.", 'laterpay');
    }

    /**
     * Render permissions form content.
     *
     * @return string permission checkboxes markup
     */
    public function get_unlimited_access_markup() {
        global $wp_roles;

        $inputs_markup = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Roles', 'laterpay' ) . '</span></legend>';
        foreach ( $wp_roles->roles as $role ) {
            $inputs_markup .= '<label title="' . $role['name'] . '">';
            $inputs_markup .= '<input type="checkbox" name="unlimited_post_access[]" value="' . strtolower( $role['name'] ) . '" ';
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

