<?php

/**
 * LaterPay advanced controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Advanced extends LaterPay_Controller_Admin_Base {

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        LaterPay_Controller_Admin::register_common_scripts( 'advanced' );

        // Add thickbox to display modal.
        add_thickbox();

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-advanced',
            $this->config->js_url . 'laterpay-backend-advanced.js',
            array( 'jquery', 'laterpay-common' ),
            $this->config->version,
            true
        );

        wp_enqueue_script( 'laterpay-backend-advanced' );

        $nonce = wp_create_nonce( 'plugin_disable_nonce' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-advanced',
            'lpVars',
            array(
                'region'               => get_option( 'laterpay_region', 'us' ),
                'liveKeyAvailable'     => empty( get_option( 'laterpay_live_merchant_id', '' ) ) ? 'false' : 'true',
                'plugin_disable_nonce' => $nonce,
                'modal'                => array(
                    'id'    => 'lp_plugin_disable_modal_id',
                    'title' => ( laterpay_check_is_vip() ) ? esc_html__( 'Delete Plugin Data', 'laterpay' ) : esc_html__( 'Deactivate Plugin & Delete Data', 'laterpay' ),
                ),
                'pluginsUrl'           => admin_url( 'plugins.php' ),
            )
        );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        // View data for laterpay/views/backend/advanced.php.
        $view_args = array(
            'plugin_is_in_live_mode' => $this->config->get( 'is_in_live_mode' ),
            'admin_menu'             => LaterPay_Helper_View::get_admin_menu(),
            'advanced_obj'           => $this,
            'live_key'               => get_option( 'laterpay_live_merchant_id', '' ),
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( 'backend/advanced' );
    }
}
