<?php

class LaterPayUserHelper {
    protected static $_preview_post_as_visitor = null;
    protected static $_hide_statistics_pane = null;

    /**
    * @param string $capability
    */
    public static function can($capability, $post = null, $strict = true) {
        $allowed = false;
        $post = get_post($post);

        if ( !function_exists('wp_get_current_user')) {
            include_once(ABSPATH . 'wp-includes/pluggable.php');
        }
        if (current_user_can($capability)) {
            if ( !$strict ) {
                $allowed = true;
            } else {
                switch ($capability) {
                    case 'laterpay_read_post_statistics':
                    case 'laterpay_edit_individual_price':
                    case 'laterpay_edit_teaser_content':
                        $user = wp_get_current_user();
                        if (in_array('administrator', $user->roles) || in_array('editor', $user->roles)) {
                            $allowed = true;
                        } elseif (!empty($post) && $post->post_author == $user->ID) {
                            $allowed = true;
                        }
                        break;
                    default:
                        $allowed = true;
                        break;
                }
            }
        }
        return $allowed;
    }

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
     * @param string $role    Role name.
     * @param int    $user_id (Optional) The ID of a user. Defaults to the current user.
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
    public static function previewPostAsVisitor($post = null) {
        if ( is_null(self::$_preview_post_as_visitor) ) {
            $preview_post_as_visitor = 0;
            $current_user            = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $preview_post_as_visitor = get_user_meta($current_user->ID, 'laterpay_preview_post_as_visitor');
                if ( !empty($preview_post_as_visitor) ) {
                   $preview_post_as_visitor = $preview_post_as_visitor[0];
                }
            }
            self::$_preview_post_as_visitor = $preview_post_as_visitor && self::can('laterpay_read_post_statistics', $post);
        }

        return self::$_preview_post_as_visitor;
    }

    /**
     * Get post preview mode
     *
     * @return bool
     */
    public static function isHiddenStatisticsPane() {
        if ( is_null(self::$_hide_statistics_pane) ) {
            $hide_statistics_pane = 0;
            $current_user = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $hide_statistics_pane = get_user_meta($current_user->ID, 'laterpay_hide_statistics_pane');
                if ( !empty($hide_statistics_pane) ) {
                   $hide_statistics_pane = $hide_statistics_pane[0];
                }
            }
            self::$_hide_statistics_pane = $hide_statistics_pane;
        }

        return self::$_hide_statistics_pane;
    }

}
