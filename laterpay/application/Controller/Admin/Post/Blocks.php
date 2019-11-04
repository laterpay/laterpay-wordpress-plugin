<?php

/**
 * LaterPay blocks controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Post_Blocks extends LaterPay_Controller_Admin_Base {

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_register_blocks'      => array(
                array( 'lp_register_blocks' ),
            ),
            'laterpay_add_block_categories' => array(
                array( 'lp_add_block_categories' ),
            ),
        );
    }

    /**
     * Render Purchase Button Block.
     *
     * @param array $attributes Purchase button block data.
     *
     * @return string
     */
    public function purchase_button_render_callback( $attributes ) {

        // Maybe display error if nothing set.
        if ( empty( $attributes ) ) {
            return $this->maybe_return_error_message( __( 'We couldn\'t find a timepass with id="0" on this site.', 'laterpay' ) );
        }

        // Default values for attributes.
        $lp_sub_pass_defaults  = [
            'purchaseId'            => 0,
            'purchaseType'          => 'tp',
            'buttonText'            => '',
            'alignment'             => 'left',
            'buttonBackgroundColor' => '#00aaa2',
            'buttonTextColor'       => '#fffff',
        ];
        $attributes            = wp_parse_args( $attributes, $lp_sub_pass_defaults );
        $lpEntityId            = $attributes['purchaseId'];
        $lpPurchaseType        = $attributes['purchaseType'];
        $is_subscription       = 'sub' === $lpPurchaseType ? true : false;
        $entity_text           = $is_subscription ? __( 'Subscription', 'laterpay' ) : __( 'Time Pass', 'laterpay' );
        $buttonText            = $attributes['buttonText'];
        $buttonAlignment       = $attributes['alignment'];
        $buttonBackgroundColor = $attributes['buttonBackgroundColor'];
        $buttonTextColor       = empty( $attributes['buttonTextColor'] ) ? '#fffff' : $attributes['buttonTextColor'];

        if ( empty( $lpEntityId ) ) {
            return $this->maybe_return_error_message( sprintf( __( 'We couldn\'t find a %s with id="0" on this site.', 'laterpay' ), $entity_text ) );
        } else {
            // Get Time Pass / Subscription data according to ID.
            $entity = $is_subscription ? LaterPay_Helper_Subscription::get_subscription_by_id( $lpEntityId, true ) : LaterPay_Helper_TimePass::get_time_pass_by_id( $lpEntityId, true );
            if ( empty( $entity ) ) {
                return $this->maybe_return_error_message( sprintf( __( 'We couldn\'t find a %s with id="%s" on this site.', 'laterpay' ), $entity_text, $lpEntityId ) );
            }

            // Update purchase button text.
            if ( empty( $buttonText ) && ! $is_subscription ) {
                if ( 'ppu' === $entity['revenue_model'] ) {
                    $buttonText = esc_html__( 'Buy Now, Pay Later', 'laterpay' );
                } else {
                    $buttonText = esc_html__( 'Buy Now', 'laterpay' );
                }
            }

            // Override purchase button text value if subscription.
            if ( empty( $buttonText ) && $is_subscription ) {
                $buttonText = esc_html__( 'Subscribe Now', 'laterpay' );
            }

            // Get the purchase URL.
            $purchase_url = $is_subscription ? LaterPay_Helper_Subscription::get_subscription_purchase_link( $lpEntityId ) : LaterPay_Helper_TimePass::get_laterpay_purchase_link( $lpEntityId );

            $lp_buttonContainerStyle = sprintf( 'text-align: %s;', $buttonAlignment );
            $lp_buttonStyle          = sprintf( 'background-color: %s; color: %s;', $buttonBackgroundColor, $buttonTextColor );
            $lp_purchaseButton       = sprintf(
                '<div class="wp-block-laterpay-sub-pass-purchase-button" style="%s">
                            <a class="lp_purchase-overlay__purchase" href="%s" style="%s">
                                <span data-icon="b" />
                                <span class="lp_purchase-button__text">%s</span>
                            </a>
                        </div>',
                esc_attr( $lp_buttonContainerStyle ),
                esc_url( $purchase_url ),
                esc_attr( $lp_buttonStyle ),
                esc_html( $buttonText )
            );

            return $lp_purchaseButton;
        }
    }

    /**
     * Render Purchase Button Block.
     *
     * @param array $attributes Purchase button block data.
     *
     * @return string
     */
    public function dynamic_access_render_callback( $attributes, $content ) {
        // Render function for dynamic access block.
    }

    /**
     * Wrapper function to return error message, to be displayed only to logged in user with privileges.
     *
     * @param string $error_message Error message to be displayed.
     *
     * @return string
     */
    protected function maybe_return_error_message( $error_message ) {
        // Template for error message.
        $template   = '<div class="lp_shortcode-error">%s</div>';
        $show_error = is_user_logged_in() && current_user_can( 'manage_options' );

        // Only show error if user is looged in and has privileges.
        if ( $show_error ) {
            return sprintf( $template, esc_html( $error_message ) );
        } else {
            return '';
        }
    }

    /**
     *  Registers block assets to be enqueued through Gutenberg editor accordingly.
     *
     * @wp-hook init
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function lp_register_blocks( LaterPay_Core_Event $event ) {
        // Load dependencies and version from build file.
        $asset_file = include( $this->config->get( 'block_build_dir' ) . 'laterpay-blocks.asset.php' );

        // Register main script to load all blocks.
        wp_register_script(
            'laterpay-block-editor-assets',
            $this->config->get( 'block_build_url' ) . 'laterpay-blocks.js',
            $asset_file['dependencies'],
            $asset_file['version']
        );

        // Register main style for all blocks.
        wp_register_style(
            'laterpay-block-editor-assets',
            $this->config->get( 'css_url' ) . 'laterpay-blocks.css',
            array(),
            filemtime( $this->config->get( 'css_dir' ) . 'laterpay-blocks.css' )
        );

        // Register Subscription / TimePass Purchase Button Block.
        register_block_type( 'laterpay/sub-pass-purchase-button', array(
            'style'           => 'laterpay-block-editor-assets',
            'editor_script'   => 'laterpay-block-editor-assets',
            'render_callback' => array( $this, 'purchase_button_render_callback' ) // For Dynamic rendering of content.
        ) );

        // Register Dynamic Access Block.
        register_block_type( 'laterpay/dynamic-access', array(
            'style'           => 'laterpay-block-editor-assets',
            'editor_script'   => 'laterpay-block-editor-assets',
            'render_callback' => array( $this, 'dynamic_access_render_callback' ) // For Dynamic rendering of content.
        ) );

        // Sets translated strings for a script.
        if ( function_exists( 'wp_set_script_translations' ) ) {
            // Sets translated strings for a script.
            wp_set_script_translations(
                'laterpay-block-editor-assets',
                'laterpay',
                $this->config->get( 'languages_dir' )
            );
        }
    }

    /**
     * Add a category for LaterPay Blocks.
     *
     * @wp-hook block_categories
     *
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function lp_add_block_categories( LaterPay_Core_Event $event ) {

        list( $block_categories ) = $event->get_arguments() + array( '' );

        $event->set_result( array_merge(
            $block_categories,
            [
                [
                    'slug'  => 'laterpay-blocks',
                    'title' => __( 'LaterPay Blocks', 'laterpay' ),
                ],
            ]
        ) );
    }
}
