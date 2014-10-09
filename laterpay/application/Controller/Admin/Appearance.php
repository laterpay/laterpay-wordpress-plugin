<?php

class LaterPay_Controller_Admin_Appearance extends LaterPay_Controller_Abstract
{

    /**
     * @see LaterPay_Controller_Abstract::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'laterpay-ezmark',
            $this->config->js_url . 'vendor/jquery.ezmark.min.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-backend-appearance',
            $this->config->js_url . '/laterpay-backend-appearance.js',
            array( 'jquery', 'laterpay-ezmark' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-ezmark' );
        wp_enqueue_script( 'laterpay-backend-appearance' );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page()
     */
    public function render_page() {
        $this->load_assets();

        $this->assign( 'plugin_is_in_live_mode',     $this->config->get( 'is_in_live_mode' ) );
        $this->assign( 'show_teaser_content_only',   get_option( 'laterpay_teaser_content_only' ) == 1 );
        $this->assign( 'top_nav',                    $this->get_menu() );
        $this->assign( 'admin_menu',                 LaterPay_Helper_View::get_admin_menu() );

        $this->render( 'backend/appearance' );
    }

    /**
     * Process Ajax requests from appearance tab.
     *
     * @return void
     */
    public static function process_ajax_requests() {
        // check for required capabilities to perform action
        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' )
                )
            );
        }

        if ( function_exists( 'check_admin_referer' ) ) {
            check_admin_referer( 'laterpay_form' );
        }

        switch ( $_POST['form'] ) {
            // update the post types which LaterPay supports for purchasing
            case 'enabled_post_types':
                $enabled_post_types_form = new LaterPay_Form_EnabledPostTypes();

                if ( ! $enabled_post_types_form->is_valid( $_POST ) ) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                } else {
                    update_option( 'laterpay_enabled_post_types', $enabled_post_types_form->get_field_value( 'enabled_post_types' ) );

                    wp_send_json(
                        array(
                            'success' => TRUE,
                            'message' => __( 'Your changes have been saved', 'laterpay' )
                        )
                    );
                }
                break;

            // update presentation mode for paid content
            case 'paid_content_preview':
                $paid_content_preview_form = new LaterPay_Form_PaidContentPreview();

                if ( ! $paid_content_preview_form->is_valid( $_POST ) ) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                        )
                    );
                } else {
                    $result = update_option( 'laterpay_teaser_content_only', $paid_content_preview_form->get_field_value( 'paid_content_preview' ) );

                    if ( $result ) {
                        if ( get_option( 'laterpay_teaser_content_only' ) ) {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Visitors will now see only the teaser content of paid posts.', 'laterpay' )
                                )
                            );
                        } else {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Visitors will now see the teaser content of paid posts plus an excerpt of the real content under an overlay.', 'laterpay' )
                                )
                            );
                        }
                    }
                }
                break;

            default:
                wp_send_json(
                    array(
                        'success' => false,
                        'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
                    )
                );
                break;
        }

        die;
    }

}
