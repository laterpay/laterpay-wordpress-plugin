<?php

class LaterPay_Controller_Admin_Dashboard extends LaterPay_Controller_Abstract
{

    /**
     * @see LaterPay_Controller_Abstract::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'laterpay-flot',
            $this->config->js_url . 'vendor/jquery.flot.min.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-flot-time',
            $this->config->js_url . 'vendor/jquery.flot.time.js',
            array( 'jquery', 'laterpay-flot' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-peity',
            $this->config->get( 'js_url' ) . 'vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-backend-dashboard',
            $this->config->js_url . 'laterpay-backend-dashboard.js',
            array( 'jquery', 'laterpay-flot', 'laterpay-flot-time', 'laterpay-peity' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-flot' );
        wp_enqueue_script( 'laterpay-flot-time' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-backend-dashboard' );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-dashboard',
            'lpVars',
            array(
                // stuff
            )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        // Mock data:
        $best_converting_items  = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_converting_items = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $most_selling_items     = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_selling_items    = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $most_revenue_items     = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );
        $least_revenue_items    = array(
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                    array(
                                        'sparkline' => '5,3,6,9,6,5,9',
                                        'amount'    => '6.8',
                                        'title'     => 'Video Tutorial 12',
                                    ),
                                );

        // assign all required vars to the view template
        $view_args = array(
            'plugin_is_in_live_mode'    => $this->config->get( 'is_in_live_mode' ),
            'top_nav'                   => $this->get_menu(),
            'admin_menu'                => LaterPay_Helper_View::get_admin_menu(),
            'currency'                  => get_option( 'laterpay_currency' ),
            'best_converting_items'     => $best_converting_items,
            'least_converting_items'    => $least_converting_items,
            'most_selling_items'        => $most_selling_items,
            'least_selling_items'       => $least_selling_items,
            'most_revenue_items'        => $most_revenue_items,
            'least_revenue_items'       => $least_revenue_items,
        );
        $this->assign( 'laterpay', $view_args );

        $this->render( 'backend/dashboard' );
    }

}
