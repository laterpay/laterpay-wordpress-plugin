<?php

/**
 * LaterPay menu controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Base extends LaterPay_Controller_Base
{
    /**
     * Render the navigation for the plugin backend.
     *
     * @param string $file
     * @param string $view_dir view directory
     *
     */
    public function get_menu( $file = null, $view_dir = null ) {
        if ( empty( $file ) ) {
            $file = 'backend/partials/navigation';
        }

        $current_page_value = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
        if ( null !== $current_page_value ) {
            $current_page = $current_page_value;
        } else {
            $current_page = LaterPay_Helper_View::$pluginPage;
        }
        $menu           = LaterPay_Helper_View::get_admin_menu();
        $plugin_page    = LaterPay_Helper_View::$pluginPage;

        $view_args      = array(
            'menu'         => $menu,
            'current_page' => $current_page,
            'plugin_page'  => $plugin_page,
        );

        $this->assign( 'laterpay', $view_args );
        $this->render( $file, $view_dir );
    }

    /**
     * Render FAQ and Support section.
     *
     * @return void
     */
    public function render_faq_support() {
        $this->render( 'backend/partials/faq-support' );
    }

    /**
     * Check if laterpay wisdom is allowed tracking.
     *
     * @return bool
     */
    public function lp_is_wisdom_tracking_allowed() {
        $lp_wisdom_allowed_tracking = get_option( 'wisdom_allow_tracking' );
        if ( false === $lp_wisdom_allowed_tracking ) {
            return false;
        } elseif ( isset( $lp_wisdom_allowed_tracking['laterpay'] ) && 'laterpay' === $lp_wisdom_allowed_tracking['laterpay'] ) {
            return true;
        }

        return false;
    }

    /**
     * Custom code to get opt out value since wisdom_opt_out can't be used with custom usage.
     * @return bool
     */
    public function lp_update_optout_value() {
        if ( $this->lp_is_wisdom_tracking_allowed() ) {
            $lp_wisdom_tracking_info = get_option( 'lp_wisdom_tracking_info' );
            if ( false === $lp_wisdom_tracking_info ) {
                return false;
            }

            if ( isset( $lp_wisdom_tracking_info['wisdom_opt_out'] ) ) {
                if ( 0 === absint( $lp_wisdom_tracking_info['wisdom_opt_out'] ) ) {
                    return false;
                } else {
                    $lp_wisdom_tracking_info['wisdom_opt_out']    = 0;
                    $lp_wisdom_tracking_info['lp_wisdom_opt_out'] = 0;
                }
            } else {
                $lp_wisdom_tracking_info['wisdom_opt_out']    = 0;
                $lp_wisdom_tracking_info['lp_wisdom_opt_out'] = 0;
            }

            return update_option( 'lp_wisdom_tracking_info', $lp_wisdom_tracking_info );
        }

        return false;
    }
}
