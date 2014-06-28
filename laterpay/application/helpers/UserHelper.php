<?php

class UserHelper {
    protected static $_preview_post_as_visitor = null;

    /**
     * Checks if the current user is part of group LATERPAY_ACCESS_ALL_ARTICLES_GROUP.
     * Returns true if a match was found.
     *
     * @return bool
     */
    public static function user_has_full_access() {

        if ( LATERPAY_ACCESS_ALL_ARTICLES_GROUP == '' )
            return false;

        return self::user_has_role(LATERPAY_ACCESS_ALL_ARTICLES_GROUP);
    }


    /**
     * Checks if a particular user has a particular role.
     * Returns true if a match was found.
     *
     * @param string  $role    Role name.
     * @param int     $user_id (Optional) The ID of a user. Defaults to the current user.
     *
     * @return bool
     */
    public static function user_has_role( $role, $user_id = null ) {

        if ( is_numeric($user_id) ) {
            $user = get_userdata( $user_id );
        } else {
            $user = wp_get_current_user();
        }

        if ( empty($user) ) {
            return false;
        }

        return in_array($role, (array)$user->roles);
    }
    
    /**
     * Get post preview mode
     * 
     * @return bool
     */
    public static function previewPostAsVisitor() {
        if (is_null( self::$_preview_post_as_visitor) ) {
            $preview_post_as_visitor = 0;
            $current_user            = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $preview_post_as_visitor = get_user_meta($current_user->ID, 'laterpay_preview_post_as_visitor');
                if ( !empty($preview_post_as_visitor) ) {
                   $preview_post_as_visitor = $preview_post_as_visitor[0]; 
                }
            }
            self::$_preview_post_as_visitor = $preview_post_as_visitor && current_user_can('manage_options');
        }
        
        return self::$_preview_post_as_visitor;
    }

}
