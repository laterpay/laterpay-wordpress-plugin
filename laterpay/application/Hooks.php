<?php
/**
 * LaterPay hooks class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */

class LaterPay_Hooks {

    protected $wp_actions = array(
        'admin_notices'
    );

    public function __call( $name, $args ) {

    }

    public function init() {

    }

    /**
     * TODO: #612 will be removed/cleaned
     * Proof of concept
     */
    public function init_actions() {
        /* Event dispatcher */
        // add listeners
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Purchase() );
        laterpay_event_dispatcher()->add_subscriber( new LaterPay_Module_Appearance() );

        // show purchase button LP style
        laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_button' ); // will echo purchase button
        // show purchase button wordpress style
        do_action( 'laterpay_purchase_button' );

        /*Wordpress case*/
        // add listeners
        add_action( 'laterpay_purchase_button', 'view_controller::render_purchase_button' ); // runs apply_filter( 'laterpay_purchase_button', $html )
        add_filter( 'laterpay_purchase_button', 'purchase_controller::is_purchasable' ); // make response empty if not purchasable
        add_filter( 'laterpay_purchase_button', 'preview_controller::is_preview_as_visitor' ); // make response empty if not purchasable

        // show purchase button
        do_action( 'laterpay_purchase_button' );
    }


}
