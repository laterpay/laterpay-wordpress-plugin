<?php

class LaterPay_Helper_User
{

    protected static $_preview_post_as_visitor  = null;
    protected static $_hide_statistics_pane     = null;

    /**
     * Check, if the current user has a given capability.
     *
     * @param string           $capability
     * @param WP_Post|int|null $post
     * @param boolean          $strict
     *
     * @return bool
     */
    public static function can( $capability, $post = null, $strict = true ) {
        $allowed = false;

        if ( ! function_exists( 'wp_get_current_user' ) ) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        if ( self::current_user_can( $capability ) ) {
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

                    case 'laterpay_has_full_access_to_content':
                        if ( ! empty( $post ) ) {
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
     * Check, if user has a given capability.
     *
     * @param string $capability capability
     *
     * @return bool
     */
    public static function current_user_can( $capability ) {
        $has_cap = false;

        if ( current_user_can( $capability ) ) {
            $has_cap = true;
        } else {
            $unlimited_access = get_option( 'laterpay_unlimited_access_to_paid_content' );
            if ( $unlimited_access ) {
                // check, if user has a role that has the given capability
                $user = wp_get_current_user();
                if ( $user instanceof WP_User ) {
                    if ( $user->roles ) {
                        foreach ( $user->roles as $role ) {
                            if ( in_array( $role, (array) $unlimited_access ) ) {
                                $has_cap = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $has_cap;
    }

    /**
     * Remove custom capabilities.
     *
     * @return void
     */
    public static function remove_custom_capabilities() {
        global $wp_roles;

        // array of capabilities (capability => option)
        $capabilities = array(
            'laterpay_read_post_statistics',
            'laterpay_edit_teaser_content',
            'laterpay_edit_individual_price',
            'laterpay_has_full_access_to_content',
        );

        foreach ( $capabilities as $cap_name ) {
            // loop through roles
            if ( $wp_roles instanceof WP_Roles ) {
                foreach ( array_keys( $wp_roles->roles ) as $role ) {
                    // get role
                    $role = get_role( $role );
                    // remove capability from role
                    $role->remove_cap( $cap_name );
                }
            }
        }
    }

    /**
     * Check, if a given user has a given role.
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
     * Check, if the current user wants to preview the post as it renders for an admin or as it renders for a visitor.
     *
     * @param null|WP_Post $post
     *
     * @return bool
     */
    public static function preview_post_as_visitor( $post = null ) {
        if ( is_null( LaterPay_Helper_User::$_preview_post_as_visitor ) ) {
            $preview_post_as_visitor = 0;
            $current_user            = wp_get_current_user();
            if ( $current_user instanceof WP_User ) {
                $preview_post_as_visitor = get_user_meta( $current_user->ID, 'laterpay_preview_post_as_visitor' );
                if ( ! empty( $preview_post_as_visitor ) ) {
                   $preview_post_as_visitor = $preview_post_as_visitor[0];
                }
            }
            LaterPay_Helper_User::$_preview_post_as_visitor = $preview_post_as_visitor && LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post );
        }

        return LaterPay_Helper_User::$_preview_post_as_visitor;
    }

    /**
     * Check, if the current user has hidden the post statistics pane.
     *
     * @return bool
     */
    public static function statistics_pane_is_hidden() {
        if ( is_null( LaterPay_Helper_User::$_hide_statistics_pane ) ) {
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User ) {
                $hide_statistics_pane = get_user_meta( $current_user->ID, 'laterpay_hide_statistics_pane' );
                if ( ! empty( $hide_statistics_pane ) ) {
                    $hide_statistics_pane = $hide_statistics_pane[0];
                } else {
                    $hide_statistics_pane = 0;
                }
            } else {
                $hide_statistics_pane = 0;
            }

            LaterPay_Helper_User::$_hide_statistics_pane = $hide_statistics_pane;
        }

        return LaterPay_Helper_User::$_hide_statistics_pane;
    }

    /**
     * Remove cookie by name
     *
     * @param $name
     *
     * @return void
     */
    public static function remove_cookie_by_name( $name ) {
        unset( $_COOKIE[$name] );
        setcookie(
            $name,
            null,
            time() - 60,
            '/'
        );
    }
}
