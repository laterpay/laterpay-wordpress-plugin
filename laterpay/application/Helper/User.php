<?php

class LaterPay_Helper_User
{

    protected static $_preview_post_as_visitor  = null;
    protected static $_hide_statistics_pane     = null;

	/**
	 * @param string           $capability
	 * @param WP_Post|int|null $post
	 * @param boolean          $strict
     *
	 * @return bool
	 */
    public static function can( $capability, $post = null, $strict = true ) {
        $allowed = false;

        if ( ! function_exists( 'wp_get_current_user' )) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        if ( current_user_can( $capability ) ) {
            if ( ! $strict ) {
                // if $strict = false, it's sufficient that a capability is added to the role of the current user
                $allowed = true;
            } else {
                switch ( $capability ) {
                    case 'laterpay_read_post_statistics':
                    case 'laterpay_edit_teaser_content':
                        if ( ! empty( $post ) && current_user_can( 'edit_post', $post ) ) {
                            // use edit_post capability as proxy:
                            // - super admins, admins, and editors can edit all posts
                            // - authors and contributors can edit their own posts
                            $allowed = true;
                        }
                        break;

                    case 'laterpay_edit_individual_price':
                        if ( ! empty( $post ) && current_user_can( 'publish_post', $post ) ) {
                            // use publish_post capability as proxy:
                            // - super admins, admins, and editors can publish all posts
                            // - authors can publish their own posts
                            // - contributors can not publish posts
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
     * Check if a particular user has a particular role.
     *
     * @param string $role    role name
     * @param int    $user_id (optional) ID of a user. Defaults to the current user.
     *
     * @return bool
     */
    public static function user_has_role( $role, $user_id = null ) {

        if ( is_numeric( $user_id ) ) {
            $user = get_userdata( $user_id );
        } else {
            $user = wp_get_current_user();
        }

        if ( empty( $user ) ) {
            return false;
        }

        return in_array( $role, (array) $user->roles );
    }

	/**
	 * Check if the current user wants to preview the post as it renders for an admin or as it renders for a visitor.
	 *
	 * @param   null|WP_Post $post
     *
	 * @return  bool
	 */
    public static function preview_post_as_visitor( $post = null ) {
        if ( is_null( self::$_preview_post_as_visitor ) ) {
            $preview_post_as_visitor = 0;
            $current_user            = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $preview_post_as_visitor = get_user_meta( $current_user->ID, 'laterpay_preview_post_as_visitor' );
                if ( ! empty( $preview_post_as_visitor ) ) {
                   $preview_post_as_visitor = $preview_post_as_visitor[0];
                }
            }
            self::$_preview_post_as_visitor = $preview_post_as_visitor && self::can( 'laterpay_read_post_statistics', $post );
        }

        return self::$_preview_post_as_visitor;
    }

    /**
     * Check if the current user has hidden the post statistics pane.
     *
     * @return  bool
     */
    public static function statistics_pane_is_hidden() {
        if ( is_null( self::$_hide_statistics_pane ) ) {
            $hide_statistics_pane = 0;
            $current_user = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $hide_statistics_pane = get_user_meta( $current_user->ID, 'laterpay_hide_statistics_pane' );
                if ( ! empty( $hide_statistics_pane ) ) {
                   $hide_statistics_pane = $hide_statistics_pane[0];
                }
            }
            self::$_hide_statistics_pane = $hide_statistics_pane;
        }

        return self::$_hide_statistics_pane;
    }

}
