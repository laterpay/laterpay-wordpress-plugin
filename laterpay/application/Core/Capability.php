<?php

/**
 * LaterPay core capabilities.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Capability 
{

    /**
     *  Execute LaterPay role creation for the various plugin versions.
     *
     * @return void
     */
    public function populate_roles() {
        $this->populate_roles_0951();
    }

    /**
    * Create and modify LaterPay roles.
    *
    * @return void
    */
   protected function populate_roles_0951() {
        $roles = array( 'administrator', 'editor' );
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            if ( empty( $role ) ) {
                continue;
            }
            $role->add_cap( 'laterpay_read_post_statistics' );
            $role->add_cap( 'laterpay_edit_individual_price' );
            $role->add_cap( 'laterpay_edit_teaser_content' );
            $role->add_cap( 'laterpay_has_full_access_to_content' );
        }

        $roles = array( 'author', 'contributor' );
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            if ( empty( $role ) ) {
                continue;
            }
            $role->add_cap( 'laterpay_read_post_statistics' );
            $role->add_cap( 'laterpay_edit_teaser_content' );
        }

        $role = get_role( 'author' );
        if ( ! empty( $role ) ) {
            $role->add_cap( 'laterpay_edit_individual_price' );
        }
    }

}
