<?php

/**
 * LaterPay appearance controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
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

        $view_args = array(
            'plugin_is_in_live_mode'              => $this->config->get( 'is_in_live_mode' ),
            'show_teaser_content_only'            => get_option( 'laterpay_teaser_content_only' ) == 1,
            'top_nav'                             => $this->get_menu(),
            'admin_menu'                          => LaterPay_Helper_View::get_admin_menu(),
            'is_rating_enabled'                   => $this->config->get( 'ratings_enabled' ),
            'purchase_button_positioned_manually' => get_option( 'laterpay_purchase_button_positioned_manually' ),
            'time_passes_positioned_manually'     => get_option( 'laterpay_time_passes_positioned_manually' ),
        );

        $this->assign( 'laterpay', $view_args );

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

            // update rating functionality (on / off) for purchased items
            case 'ratings':
                $ratings_form = new LaterPay_Form_Rating();

                if ( ! $ratings_form->is_valid( $_POST ) ) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                        )
                    );
                } else {
                    $result = update_option( 'laterpay_ratings', !! $ratings_form->get_field_value( 'enable_ratings' ) );

                    if ( $result ) {
                        if ( get_option( 'laterpay_ratings' ) ) {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Visitors can now rate the posts they have purchased.', 'laterpay' ),
                                )
                            );
                        } else {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'The rating of posts has been disabled.', 'laterpay' ),
                                )
                            );
                        }
                    }
                }
                break;

            case 'purchase_button_position':
                $purchase_button_pos_form = new LaterPay_Form_PurchaseButtonPosition( $_POST );

                if ( ! $purchase_button_pos_form->is_valid( ) ) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                        )
                    );
                } else {
                    $result = update_option( 'laterpay_purchase_button_positioned_manually', !! $purchase_button_pos_form->get_field_value( 'purchase_button_positioned_manually' ) );

                    if ( $result ) {
                        if ( get_option( 'laterpay_purchase_button_positioned_manually' ) ) {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Purchase buttons are now rendered at a custom position.', 'laterpay' ),
                                )
                            );
                        } else {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Purchase buttons are now rendered at their default position.', 'laterpay' ),
                                )
                            );
                        }
                    }
                }
                break;

            case 'time_passes_position':
                $time_passes_pos_form = new LaterPay_Form_TimePassPosition( $_POST );

                if ( ! $time_passes_pos_form->is_valid() ) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                        )
                    );
                } else {
                    $result = update_option( 'laterpay_time_passes_positioned_manually', !! $time_passes_pos_form->get_field_value( 'time_passes_positioned_manually' ) );

                    if ( $result ) {
                        if ( get_option( 'laterpay_time_passes_positioned_manually' ) ) {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Time passes are now rendered at a custom position.', 'laterpay' ),
                                )
                            );
                        } else {
                            wp_send_json(
                                array(
                                    'success' => true,
                                    'message' => __( 'Time passes are now rendered at their default position.', 'laterpay' ),
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
                        'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
                    )
                );
                break;
        }

        die;
    }
}
