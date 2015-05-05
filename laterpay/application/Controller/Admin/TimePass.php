<?php

/**
 * LaterPay time pass controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_TimePass extends LaterPay_Controller_Admin_Base
{

    private $ajax_nonce = 'laterpay_time_passes';

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_enqueue_script(
            'laterpay-flot',
            $this->config->get( 'js_url' ) . 'vendor/lp_jquery.flot.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script(
            'laterpay-backend-dashboard',
            $this->config->get( 'js_url' ) . 'laterpay-backend-dashboard-timepasses.js',
            array( 'jquery', 'laterpay-flot' ),
            $this->config->get( 'version' ),
            true
        );

        // pass localized strings and variables to script
        $i18n = array(
            'endingIn'              => _x( 'ending in', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay' ),
            'month'                 => _x( 'month', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay' ),
            'months'                => _x( 'months', 'used in wp_localize_script for the flot graph in loadTimePassLifecycles()', 'laterpay' ),
            'weeksLeft'             => _x( 'weeks left', 'used in wp_localize_script as x-axis label for loadTimePassLifecycles()', 'laterpay' ),
        );

        // get maximum number of expiring time passes per week across all time passes to scale the y-axis
        // of the timepass diagrams
        $max_y_value = max( LaterPay_Helper_TimePass::get_time_pass_expiry_by_weeks( null, LaterPay_Helper_TimePass::TIME_PASSES_WEEKS ) );

        $localization = array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonces'    => array( 'time_passes' => wp_create_nonce( $this->ajax_nonce ) ),
            'submenu'   => array(
                'view' => array(
                'standard' => 'standard-kpis',
                'passes'   => 'time-passes',
                )
            ),
            'locale'    => get_locale(),
            'i18n'      => $i18n,
            'maxYValue' => $max_y_value,
        );

        wp_localize_script(
            'laterpay-backend-dashboard',
            'lpVars',
            $localization
        );

        $this->logger->info( __METHOD__, $localization );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $view_args = array(
            'plugin_is_in_live_mode'    => $this->config->get( 'is_in_live_mode' ),
            'top_nav'                   => $this->get_menu(),
            'admin_menu'                => LaterPay_Helper_View::get_admin_menu(),
            'currency'                  => get_option( 'laterpay_currency' ),
            'passes'                    => LaterPay_Helper_TimePass::get_time_passes_statistic(),
        );

        $this->assign( 'laterpay', $view_args );

        $this->render( 'backend/time-passes' );
    }

    /**
     * Ajax callback to refresh the dashboard data.
     *
     * @wp-hook wp_ajax_laterpay_get_dashboard_data
     *
     * @return void
     */
    public function ajax_get_time_passes_data() {
        $this->validate_ajax_nonce();

        $pass_id    = 0;
        if ( isset( $_GET['pass_id'] ) ) {
            $pass_id = (int) $_GET['pass_id'];
        }

        $data = LaterPay_Helper_TimePass::time_pass_expiry_diagram( $pass_id );

        $response = array(
            'data'      => $data,
            'success'   => true,
        );

        wp_send_json( $response );
    }

    /**
     * Internal function to check the wpnonce on Ajax requests.
     *
     * @return void
     */
    private function validate_ajax_nonce() {
        if ( ! isset( $_POST['_wpnonce'] ) || empty( $_POST['_wpnonce'] ) ) {
            $error = array(
                'message'   => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' ),
                'step'      => 1,
                'success'   => false,
            );
            wp_send_json( $error );
        }

        $nonce = sanitize_text_field( $_POST['_wpnonce'] );
        if ( ! wp_verify_nonce( $nonce, $this->ajax_nonce ) ) {
            $error = array(
                'message'   => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' ),
                'step'      => 2,
                'success'   => false,
            );
            wp_send_json( $error );
        }
    }
}
