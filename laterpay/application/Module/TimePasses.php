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
     * @param int $post_id
     *
     * @return void
     */
    public function save_laterpay_post_data_without_pricing( $post_id ) {
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
}
