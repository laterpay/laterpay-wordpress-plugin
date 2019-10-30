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
            'laterpay_register_blocks' => array(
                array( 'lp_register_blocks' ),
            ),
        );
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
            array( ),
            filemtime( $this->config->get( 'css_dir' ) . 'laterpay-blocks.css' )
        );

        // Register Subscription / TimePass Purchase Button Block.
        register_block_type( 'laterpay/sub-pass-purchase-button', array(
            'style'         => 'laterpay-block-editor-assets',
            'editor_script' => 'laterpay-block-editor-assets',
        ) );
    }
}
