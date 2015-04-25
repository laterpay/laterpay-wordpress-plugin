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
     * @return string $html
     */
    public function get_menu( $file = null, $view_dir = null ) {
        if ( empty( $file ) ) {
            $file = 'backend/partials/navigation';
        }

        $current_page   = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : LaterPay_Helper_View::$pluginPage;
        $menu           = LaterPay_Helper_View::get_admin_menu();
        $plugin_page    = LaterPay_Helper_View::$pluginPage;

        $view_args      = array(
            'menu'         => $menu,
            'current_page' => $current_page,
            'plugin_page'  => $plugin_page,
        );

        $this->assign( 'laterpay', $view_args );

        $this->logger->info(
            __METHOD__ . ' - ' . $file,
            $view_args
        );

        return $this->get_text_view( $file, $view_dir );
    }
}
