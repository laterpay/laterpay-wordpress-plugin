<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    /**
     * @see LaterPay_Controller_Abstract::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific CSS
        // TODO: add styles if needed

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-settings',
            $this->config->get( 'js_url' ) . 'laterpay-backend-settings.js',
            array( 'jquery'),
            $this->config->get( 'version' ),
            true
        );

        wp_enqueue_script( 'laterpay-backend-settings' );

        // add lpVars if needed
        wp_localize_script(
            'laterpay-backend-settings',
            'lpVars',
            array(
            )
        );
    }

    /**
     * Ajax method to save laterpay advanced settings
     *
     * @wp-hook wp_ajax_laterpay_advanced_settings
     */
    public function save_laterpay_advanced_settings() {
        $advanced_settings_form = new LaterPay_Form_Settings( $_POST );

        if ( $advanced_settings_form->is_valid() ) {
            global $wp_roles;
            $unlimited_post_access = $advanced_settings_form->get_field_value( 'unlimited_post_access' );

            foreach( $wp_roles->roles as $role => $role_data ) {
                $role_obj = get_role( $role );
                if ( ! $unlimited_post_access ) {
                    $role_obj->remove_cap( 'laterpay_has_full_access_to_content' );
                } else {
                    if ( in_array( $role, $unlimited_post_access ) ) {
                        $role_obj->add_cap( 'laterpay_has_full_access_to_content' );
                    } else {
                        $role_obj->remove_cap( 'laterpay_has_full_access_to_content' );
                    }
                }
            }

            wp_send_json(
                array(
                    'success' => true,
                )
            );
        }

        wp_send_json(
            array(
                'success' => false,
            )
        );
    }

    /**
     * Add LaterPay settings to the settings menu
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
     * Render settings page
     *
     * @return void
     */
    public function render_advanced_settings_page() {
        $this->load_assets();

        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay'),
        );

        $this->assign( 'laterpay', $view_args );
        echo $this->get_text_view( 'backend/options' );
    }

    /**
     * Init laterpay settings
     *
     * @return void
     */
    public function init_laterpay_advanced_settings() {
        // Add default section to laterpay settings
        add_settings_section(
            'laterpay_capabilities',
            __( 'Capabilities', 'laterpay' ),
            array( $this, 'get_capabilities_section_description' ),
            'laterpay'
        );

        // Add unlimited content access field
        add_settings_field(
            'unlimited_post_access',
            __( 'Unlimited post access', 'laterpay' ),
            array( $this, 'get_unlimited_post_access_field_code' ),
            'laterpay',
            'laterpay_capabilities'
        );

        // Register setting
        register_setting( 'laterpay', 'unlimited_post_access' );
    }

    /**
     * Get permission section description
     *
     * @return void
     */
    public function get_capabilities_section_description() {
        echo __( 'This section contain all LaterPay capabilities settings.', 'laterpay');
    }

    /**
     * Get unlimited post access field code
     *
     * @return void
     */
    public function get_unlimited_post_access_field_code() {
        global $wp_roles;

        $field_code = '<fieldset><legend class="screen-reader-text"><span>' . __( 'Roles', 'laterpay' ) . '</span></legend>';
        foreach ( $wp_roles->roles as $role => $role_data ) {
            $field_code .= '<label title="' . $role_data['name'] . '">';
            $field_code .= '<input type="checkbox" name="unlimited_post_access[]" value="' . $role . '" ';
            if ( isset( $role_data['capabilities']['laterpay_has_full_access_to_content'] ) ) {
                $field_code .= 'checked="checked"';
            }
            $field_code .= '>';
            $field_code .= '<span>' . $role_data['name'] . '</span>';
            $field_code .= '</label><br>';
        }
        $field_code .= '</fieldset>';

        echo $field_code;
    }
}

