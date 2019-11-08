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
            return $this->maybe_return_error_message( __( 'We couldn\'t find a TimePass with id="0" on this site.', 'laterpay' ) );
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
        $entity_text           = $is_subscription ? __( 'Subscription', 'laterpay' ) : __( 'TimePass', 'laterpay' );
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
     * Render for Dynamic Access Content Block.
     *
     * @param array $attributes Dynamic access block data.
     *
     * @return string
     */
    public function dynamic_access_render_callback( $attributes ) {

        // Default values for attributes.
        $lp_dynamic_access_defaults = [
            'accessBehaviour'           => 'show',
            'content'                   => '',
            'subscriptionSelectionType' => 'none',
            'subscriptionIds'           => '',
            'timePassSelectionType'     => 'none',
            'timePassIds'               => '',
        ];

        // Store reused values in variables.
        $attributes          = wp_parse_args( $attributes, $lp_dynamic_access_defaults );
        $accessBehaviour     = $attributes['accessBehaviour'];
        $accessContent       = $attributes['content'];
        $accessSubSelection  = $attributes['subscriptionSelectionType'];
        $accessPassSelection = $attributes['timePassSelectionType'];

        // Set subscription Ids.
        if ( 'multiple' === $accessSubSelection ) {
            $accessSubIds = $attributes['subscriptionIds'];
        } elseif ( 'all' === $accessSubSelection ) {
            $accessSubIds = $accessSubSelection;
        } else {
            $accessSubIds = '';
        }

        // Set time pass Ids.
        if ( 'multiple' === $accessPassSelection ) {
            $accessPassIds = $attributes['timePassIds'];
        } elseif ( 'all' === $accessPassSelection ) {
            $accessPassIds = $accessPassSelection;
        } else {
            $accessPassIds = '';
        }

        // Display error if all is selected for both TimePas and Subscription at the same time.
        if ( 'all' === $accessPassIds && 'all' === $accessSubIds ) {
            return $this->maybe_return_error_message(
                __( "All cannot be used for both 'timepasses' and 'subscriptions' at the same time.", 'laterpay' )
            );
        }

        // Check if user has access to provided time passes / subscriptions.
        $has_access = LaterPay_Helper_Pricing::check_time_pass_subscription_access( $accessPassIds, $accessSubIds );

        /**
         * If user has access to any of the given content display/hide based on selected behaviour else
         * do the opposite of selected behaviour.
         */
        if ( $has_access ) {
            if ( 'show' === $accessBehaviour ) {
                return wp_kses_post( $accessContent );
            } elseif ( 'hide' === $accessBehaviour ) {
                return '';
            }
        } else {
            if ( 'show' === $accessBehaviour ) {
                return '';
            } elseif ( 'hide' === $accessBehaviour ) {
                return wp_kses_post( $accessContent );
            }
        }

        return '';

    }

    /**
     * Render for Premium Download Box Block.
     *
     * @param array $attributes Premium download box block data.
     *
     * @return string
     */
    public function premium_download_box_render_callback( $attributes ) {

        // Default values for attributes.
        $lp_premium_box_defaults = [
            'mediaID'          => '',
            'mediaIcon'        => '',
            'mediaName'        => '',
            'mediaHeading'     => __( 'Additional Premium Content', 'laterpay' ),
            'mediaDescription' => '',
            'mediaType'        => '',
            'mediaTeaserID'    => '',
            'mediaTeaserImage' => '',
        ];

        // Store reused values in variables.
        $attributes       = wp_parse_args( $attributes, $lp_premium_box_defaults );
        $mediaID          = absint( $attributes['mediaID'] );
        $mediaType        = $attributes['mediaType'];
        $mediaTitle       = $attributes['mediaHeading'];
        $mediaDescription = $attributes['mediaDescription'];
        $mediaTeaserImage = $attributes['mediaTeaserImage'];

        if ( empty( $mediaID ) ) {
            return $this->maybe_return_error_message(
                __( 'Please select Downloadable Media', 'laterpay' )
            );
        }

        // Get post info and URL.
        $lpMedia  = get_post( $mediaID );
        $page_url = get_permalink( $mediaID );

        // Media was selected but doesn't exist now.
        if ( null === $lpMedia ) {
            return $this->maybe_return_error_message( sprintf(
                esc_html__( 'We couldn\'t find selected media with id="%s" on this site.', 'laterpay' ),
                absint( $lpMedia )
            ) );
        } else {
            // Don't render the shortcode, if the target page has a post type for which LaterPay is disabled.
            if ( ! in_array( $lpMedia->post_type, $this->config->get( 'content.enabled_post_types' ), true ) ) {
                return $this->maybe_return_error_message( sprintf(
                    esc_html__( 'LaterPay has been disabled for the post type of the target page.', 'laterpay' ),
                    absint( $lpMedia )
                ) );
            }

            // Supported content data types.
            $content_types = [ 'file', 'gallery', 'audio', 'video', 'text' ];

            // If media type is set to auto then identify automatically.
            if ( empty( $mediaType ) ) {
                // determine $content_type from MIME type of files attached to post
                $page_mime_type = get_post_mime_type( $mediaID );
                switch ( $page_mime_type ) {
                    case 'application/zip':
                    case 'application/x-rar-compressed':
                    case 'application/pdf':
                        $content_type = 'file';
                        break;
                    case 'image/jpeg':
                    case 'image/png':
                    case 'image/gif':
                        $content_type = 'gallery';
                        break;
                    case 'audio/vnd.wav':
                    case 'audio/mpeg':
                    case 'audio/mp4':
                    case 'audio/ogg':
                    case 'audio/aac':
                    case 'audio/aacp':
                        $content_type = 'audio';
                        break;
                    case 'video/mpeg':
                    case 'video/mp4':
                    case 'video/quicktime':
                        $content_type = 'video';
                        break;
                    default:
                        $content_type = 'text';
                }
            } elseif ( in_array( $mediaType, $content_types, true ) ) {
                $content_type = $mediaType;
            } else {
                $content_type = 'text';
            }

            // If teaser image is not set add class based on content type.
            if ( empty( $mediaTeaserImage ) ) {
                $lp_premiumBox = '<div class="lp_js_premium-file-box lp_premium-file-box lp_is-' . esc_attr( $content_type )
                                 . '" data-post-id="' . esc_attr( $mediaID )
                                 . '" data-content-type="' . esc_attr( $content_type )
                                 . '" data-page-url="' . esc_url( $page_url )
                                 . '">';
            } else {
                $lp_premiumBox = '<div class="lp_js_premium-file-box lp_premium-file-box" '
                                 . 'style="background-image:url(' . esc_url( $mediaTeaserImage ) . ')'
                                 . '" data-post-id="' . esc_attr( $mediaID )
                                 . '" data-content-type="' . esc_attr( $content_type )
                                 . '" data-page-url="' . esc_url( $page_url )
                                 . '">';
            }

            // Create a premium box
            $lp_premiumBox .= '<div class="lp_premium-file-box__details">';
            $lp_premiumBox .= '<h3 class="lp_premium-file-box__title">' . esc_attr( $mediaTitle ) . '</h3>';
            if ( ! empty( $mediaDescription ) ) {
                $lp_premiumBox .= '<p class="lp_premium-file-box__text">' . esc_attr( $mediaDescription ) . '</p>';
            }
            $lp_premiumBox .= '</div>';
            $lp_premiumBox .= '</div>';

            return $lp_premiumBox;
        }
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

        // Only show error if user is logged in and has privileges.
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

        // Register Premium Download Box Block.
        register_block_type( 'laterpay/premium-download-box', array(
            'style'           => 'laterpay-block-editor-assets',
            'editor_script'   => 'laterpay-block-editor-assets',
            'render_callback' => array( $this, 'premium_download_box_render_callback' )
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
