<?php

/**
 * LaterPay TimePasses class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Module_TimePasses extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_post_save' => array(
                array( 'remove_metabox_save_hook', 200 ),
                array( 'save_laterpay_post_data_without_pricing' ),
            ),
            'laterpay_attachment_edit' => array(
                array( 'remove_metabox_save_hook', 200 ),
                array( 'save_laterpay_post_data_without_pricing' ),
            ),
            'laterpay_post_custom_column' => array(
                array( 'remove_post_custom_column', 200 ),
            ),
            'laterpay_post_custom_column_data' => array(
                array( 'remove_post_custom_column_data', 200 ),
            ),
            'laterpay_time_passes' => array(
                array( 'the_time_passes_widget' ),
            ),
        );
    }

    /**
     * Remove hook on save metabox data.
     *
     * @param LaterPay_Core_Event $event
     */
    public function remove_metabox_save_hook( LaterPay_Core_Event $event ) {
        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) ) {
            $listener = LaterPay_Core_Bootstrap::get_controller( 'Admin_Post_Metabox' );
            laterpay_event_dispatcher()->remove_listener( $event->get_name(), array( $listener, 'save_laterpay_post_data' ) );
        }
    }

    /**
     * Remove filter for the posts columns.
     *
     * @param LaterPay_Core_Event $event
     */
    public function remove_post_custom_column( LaterPay_Core_Event $event ) {
        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) ) {
            $listener = LaterPay_Core_Bootstrap::get_controller( 'Admin_Post_Column' );
            laterpay_event_dispatcher()->remove_listener( $event->get_name(), array( $listener, 'add_columns_to_posts_table' ) );
        }
    }

    /**
     * Remove action for the posts columns.
     *
     * @param LaterPay_Core_Event $event
     */
    public function remove_post_custom_column_data( LaterPay_Core_Event $event ) {
        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) ) {
            $listener = LaterPay_Core_Bootstrap::get_controller( 'Admin_Post_Column' );
            laterpay_event_dispatcher()->remove_listener( $event->get_name(), array( $listener, 'add_data_to_posts_table' ) );
        }
    }

    /**
     * Save LaterPay post data without saving price data.
     *
     * @wp-hook save_post, edit_attachments
     *
     * LaterPay_Core_Event $event
     *
     * @return void
     */
    public function save_laterpay_post_data_without_pricing( LaterPay_Core_Event $event ) {
        list( $post_id ) = $event->get_arguments();
        if ( ! $this->has_permission( $post_id ) ) {
            return;
        }

        // no post found -> do nothing
        $post = get_post( $post_id );
        if ( $post === null ) {
            return;
        }

        // set up new form
        $post_form = new LaterPay_Form_PostWithoutPricing( $_POST );
        $condition = array(
            'verify_nonce' => array(
                'action' => $this->config->get( 'plugin_base_name' ),
            )
        );
        $post_form->add_validation( 'laterpay_teaser_content_box_nonce', $condition );

        // nonce not valid -> do nothing
        if ( $post_form->is_valid() ) {
            // no rights to edit laterpay_edit_teaser_content -> do nothing
            if ( LaterPay_Helper_User::can( 'laterpay_edit_teaser_content', $post_id ) ) {
                $teaser = $post_form->get_field_value( 'laterpay_post_teaser' );
                LaterPay_Helper_Post::add_teaser_to_the_post( $post, $teaser );
            }
        }
    }

    /**
     * Check the permissions on saving the metaboxes.
     *
     * @wp-hook save_post
     *
     * @param int $post_id
     *
     * @return bool true|false
     */
    protected function has_permission( $post_id ) {
        // autosave -> do nothing
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        // Ajax -> do nothing
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return false;
        }

        // no post found -> do nothing
        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        // current post type is not enabled for LaterPay -> do nothing
        if ( ! in_array( $post->post_type, $this->config->get( 'content.enabled_post_types' ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Callback to render a widget with the available LaterPay time passes within the theme
     * that can be freely positioned.
     *
     * @wp-hook laterpay_time_passes
     *
     * @var string $introductory_text     additional text rendered at the top of the widget
     * @var string $call_to_action_text   additional text rendered after the time passes and before the voucher code input
     * @var int    $time_pass_id          id of one time pass to be rendered instead of all time passes
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function the_time_passes_widget( LaterPay_Core_Event $event ) {
        list( $introductory_text, $call_to_action_text, $time_pass_id ) = $event->get_arguments();
        if ( empty( $introductory_text ) ) {
            $introductory_text = '';
        }
        if ( empty( $call_to_action_text ) ) {
            $call_to_action_text = '';
        }

        $is_homepage                     = is_front_page() && is_home();
        $show_widget_on_free_posts       = get_option( 'laterpay_show_time_passes_widget_on_free_posts' );
        $time_passes_positioned_manually = get_option( 'laterpay_time_passes_positioned_manually' );

        // prevent execution, if the current post is not the given post and we are not on the homepage,
        // or the action was called a second time,
        // or the post is free and we can't show the time pass widget on free posts
        if ( LaterPay_Helper_Pricing::is_purchasable() === false && ! $is_homepage ||
             did_action( 'laterpay_time_passes' ) > 1 ||
             LaterPay_Helper_Pricing::is_purchasable() === null && ! $show_widget_on_free_posts
        ) {
            return;
        }

        // don't display widget on a search or multiposts page, if it is positioned automatically
        if ( ! is_singular() && ! $time_passes_positioned_manually ) {
            return;
        }

        // get time passes list
        $time_passes_with_access = $this->get_time_passes_with_access();

        // check, if we are on the homepage or on a post / page page
        if ( $is_homepage ) {
            $time_passes_list = LaterPay_Helper_TimePass::get_time_passes_list_by_post_id(
                null,
                $time_passes_with_access,
                true
            );
        } else {
            $time_passes_list = LaterPay_Helper_TimePass::get_time_passes_list_by_post_id(
                get_the_ID(),
                $time_passes_with_access,
                true
            );
        }

        if ( isset( $time_pass_id ) ) {
            if ( in_array( $time_pass_id, $time_passes_with_access ) ) {
                return;
            }
            $time_passes_list = array( LaterPay_Helper_TimePass::get_time_pass_by_id( $time_pass_id, true ) );
        }

        // don't render the widget, if there are no time passes
        if ( count( $time_passes_list ) === 0 ) {
            return;
        }

        // check, if the time passes to be rendered have vouchers
        $has_vouchers = LaterPay_Helper_Voucher::passes_have_vouchers( $time_passes_list );

        $view_args = array(
            'passes_list'                    => $time_passes_list,
            'has_vouchers'                   => $has_vouchers,
            'time_pass_introductory_text'    => $introductory_text,
            'time_pass_call_to_action_text'  => $call_to_action_text,
        );

        $this->assign( 'laterpay_widget', $view_args );

        echo laterpay_sanitized( $this->get_text_view( 'frontend/partials/widget/time-passes' ) );
    }

    /**
     * Get time passes that have access to the current posts.
     *
     * @return array of time pass ids with access
     */
    protected function get_time_passes_with_access() {
        $access                     = LaterPay_Helper_Post::get_access_state();
        $time_passes_with_access    = array();

        // get time passes with access
        foreach ( $access as $access_key => $access_value ) {
            // if access was purchased
            if ( $access_value === true ) {
                $access_key_exploded = explode( '_', $access_key );
                // if this is time pass key - store time pass id
                if ( $access_key_exploded[0] === LaterPay_Helper_TimePass::PASS_TOKEN ) {
                    $time_passes_with_access[] = $access_key_exploded[1];
                }
            }
        }

        return $time_passes_with_access;
    }
}
