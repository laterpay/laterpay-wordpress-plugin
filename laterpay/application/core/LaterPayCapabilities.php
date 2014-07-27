<?php

class LaterPayCapabilities
{

    /**
     *  Execute LaterPay role creation for the various plugin versions.
     */
    public function populate_roles() {
        $this->populate_roles_0951();
    }

    /**
    * Create and modify LaterPay roles
    *
    * @since 0.9.5.1
    */
   protected function populate_roles_0951() {
        $role = get_role( 'administrator' );
        if ( ! empty( $role ) ) {
            $role->add_cap( 'laterpay_read_plugin_pages' );
            $role->add_cap( 'laterpay_edit_plugin_settings' );
        }

        $roles = array('administrator', 'editor');
        foreach ($roles as $role) {
            $role = get_role($role);
            if ( empty($role) )
                continue;
            $role->add_cap( 'laterpay_read_post_statistics' );
            $role->add_cap( 'laterpay_edit_individual_price' );
            $role->add_cap( 'laterpay_edit_teaser_content' );
        }

        $roles = array('author', 'contributor');
        foreach ($roles as $role) {
            $role = get_role($role);
            if ( empty($role) )
                continue;
            $role->add_cap( 'laterpay_read_post_statistics' );
            $role->add_cap( 'laterpay_edit_teaser_content' );
        }

        $role = get_role( 'author' );
        if ( ! empty( $role ) ) {
            $role->add_cap( 'laterpay_edit_individual_price' );
        }
    }
}
