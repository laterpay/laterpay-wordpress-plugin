<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    /**
     * Add LaterPay settings to the settings menu
     *
     * @return void
     */
    public function add_laterpay_settings_page() {
        add_options_page(
            __( 'LaterPay Options', 'laterpay' ),
            'LaterPay',
            'manage_options',
            'laterpay',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page() {
        $view_args = array(
            'settings_title' => __( 'LaterPay Options', 'laterpay'),
        );

        $this->assign( 'laterpay', $view_args );
        echo $this->get_text_view( 'backend/options' );
    }

    public function init_laterpay_settings() {
        // Add default section to laterpay settings
        add_settings_section(
            'laterpay_permission',
            __( 'Permissions', 'laterpay' ),
            array( $this, 'get_permission_section_description' ),
            'laterpay'
        );

        // Add unlimited content access field
        add_settings_field(
            'unlimited_post_access',
            __( 'Unlimited post access', 'laterpay' ),
            array( $this, 'get_unlimited_access_field_code' ),
            'laterpay',
            'laterpay_permission'
        );

        // Register setting
        register_setting( 'laterpay', 'unlimited_post_access' );
    }

    public function get_permission_section_description() {
        echo __( 'This section contain all LaterPay permission settings.', 'laterpay');
    }

    public function get_unlimited_access_field_code() {
        global $wp_roles;

        $field_code = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Roles', 'laterpay' ) . '</span></legend>';
        foreach ( $wp_roles->roles as $role ) {
            $field_code .= '<label title="' . $role['name'] . '">';
            $field_code .= '<input type="checkbox" name="unlimited_post_access[]" value="' . strtolower( $role['name'] ) . '" ';
            if ( isset( $role['capabilities']['laterpay_has_full_access_to_content'] ) ) {
                $field_code .= 'checked="checked"';
            }
            $field_code .= '>';
            $field_code .= '<span>' . $role['name'] . '</span>';
            $field_code .= '</label><br>';
        }
        $field_code .= '</fieldset>';

        echo $field_code;
    }
}

