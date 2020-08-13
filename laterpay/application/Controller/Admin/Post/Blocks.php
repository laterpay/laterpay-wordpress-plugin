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
            'laterpay_register_blocks'       => array(
                array( 'lp_register_blocks' ),
            ),
            'laterpay_add_block_categories'  => array(
                array( 'lp_add_block_categories' ),
            ),
            'laterpay_register_block_routes' => array(
                array( 'lp_register_routes' ),
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
            return $this->maybe_return_error_message( __( 'Please provide valid Time Pass / Subscription ID.', 'laterpay' ) );
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
        $is_subscription       = ( 'sub' === $lpPurchaseType ) ? true : false;
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
            'purchaseRequirement'       => 'any',
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
        $purchaseRequirement = $attributes['purchaseRequirement'];

        if ( 'any' === $purchaseRequirement ) {
            // Check if user has access to current post by any method of purchase.
            $has_access = LaterPay_Helper_Pricing::check_current_post_access();
        } else {
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

            // Display error if all is selected for both Time Pass and Subscription at the same time.
            if ( 'all' === $accessPassIds && 'all' === $accessSubIds ) {
                return $this->maybe_return_error_message(
                    __( "All cannot be used for both 'Time Passes' and 'Subscriptions' at the same time.", 'laterpay' )
                );
            }

            // Check if user has access to provided time passes / subscriptions.
            $has_access = LaterPay_Helper_Pricing::check_time_pass_subscription_access( $accessPassIds, $accessSubIds );
        }

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
            return $this->maybe_return_error_message( esc_html__( 'Selected content doesn\'t exist now', 'laterpay' ) );
        } else {
            // Don't render the shortcode, if the target page has a post type for which LaterPay is disabled.
            if ( ! in_array( $lpMedia->post_type, $this->config->get( 'content.enabled_post_types' ), true ) ) {
                return $this->maybe_return_error_message( esc_html__( 'Laterpay has been disabled for the post type of the target page.', 'laterpay' ) );
            }

            // Supported content data types.
            $content_types = [ 'file', 'gallery', 'audio', 'video', 'text', 'link' ];

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

            if ( 'link' === $content_type ) {
                // Build anchor text for premium link.
                $anchor_text   = empty( $mediaDescription ) ? $mediaTitle : sprintf( '%s - %s', $mediaTitle, $mediaDescription );
                $lp_premiumBox = '<a class="lp_js_premium-file-box lp_premium_link lp_premium_link_anchor" title="'
                                 . esc_html__( 'Buy now with Laterpay', 'laterpay' )
                                 . '" data-content-type="'
                                 . esc_attr( $content_type )
                                 . '" data-post-id="'
                                 . esc_attr( $mediaID )
                                 . '" data-page-url="'
                                 . esc_url( $page_url )
                                 . '">';
                $lp_premiumBox .= esc_html( $anchor_text ) . '</a>';
            } else {
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
            }

            return $lp_premiumBox;
        }
    }

    /**
     * Render for Contribution Block.
     *
     * @param array $attributes Contribution block data.
     *
     * @return string
     */
    public function contribution_render_callback( $attributes ) {

        // Default values for attributes.
        $lp_contribution_defaults = [
            'campaignName'         => '',
            'campaignThankYouPage' => '',
            'dialogHeader'         => __( 'Support the author', 'laterpay' ),
            'dialogDescription'    => __( 'How much would you like to contribute?', 'laterpay' ),
            'contributionType'     => 'multiple',
            'allowCustomAmount'    => true,
            'singleContribution'   => '',
            'multipleContribution' => '',
            'selectedAmount'       => 3,
        ];

        // Store reused values in variables.
        $attributes           = wp_parse_args( $attributes, $lp_contribution_defaults );
        $campaignName         = $attributes['campaignName'];
        $campaignThankYouPage = $attributes['campaignThankYouPage'];
        $contributionType     = $attributes['contributionType'];
        $allowCustomAmount    = $attributes['allowCustomAmount'];
        $singleContribution   = $attributes['singleContribution'];
        $multipleContribution = $attributes['multipleContribution'];
        $selectedAmount       = $attributes['selectedAmount'];
        $dialog_header        = $attributes['dialogHeader'];
        $dialog_description   = $attributes['dialogDescription'];

        // Get currency config.
        $currency_config = LaterPay_Helper_Config::get_currency_config();

        // Check if campaign name is empty.
        if ( empty( $campaignName ) ) {
            return $this->maybe_return_error_message( __( 'Please enter a Campaign Name.', 'laterpay' ) );
        }

        // Set redirect URL, if empty use current page where block resides.
        if ( ! empty( $campaignThankYouPage ) ) {
            $current_url = esc_url( $campaignThankYouPage );
        } else {
            global $wp;
            $current_url = trailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
        }

        // Client Library Instance for purchase URL creation.
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client         = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $payment_config    = [];
        $contribution_urls = '';
        $campaign_id       = str_replace( ' ', '-', strtolower( $campaignName ) ) . '-' . (string) time();

        if ( 'single' === $contributionType ) {
            if ( floatval( $singleContribution['amount'] ) === floatval( 0.0 ) ) {
                return $this->maybe_return_error_message( __( 'Please enter a valid contribution amount.', 'laterpay' ) );
            } else {
                $singleAmount  = (float) $singleContribution['amount'] * 100;
                $singleRevenue = empty( $singleContribution['revenue'] ) ? 'ppu' : $singleContribution['revenue'];

                $payment_config = [
                    'amount'  => $singleAmount,
                    'revenue' => $singleRevenue,
                    'url'     => $client->get_single_contribution_url( [
                        'revenue'     => $singleRevenue,
                        'campaign_id' => $campaign_id,
                        'title'       => $campaignName,
                        'url'         => $current_url,
                    ] )
                ];
            }
        } else {

            // Handle default values for multiple contribution.
            if ( empty( $multipleContribution ) ) {
                $multipleContribution = [
                    'amountOne'           => '1.00',
                    'revenueOne'          => 'ppu',
                    'revenueDisableOne'   => true,
                    'amountTwo'           => '2.00',
                    'revenueTwo'          => 'ppu',
                    'revenueDisableTwo'   => true,
                    'amountThree'         => '5.00',
                    'revenueThree'        => 'sis',
                    'revenueDisableThree' => true,
                    'amountFour'          => '0.00',
                    'revenueFour'         => 'ppu',
                    'revenueDisableFour'  => true,
                    'amountFive'          => '0.00',
                    'revenueFive'         => 'ppu',
                    'revenueDisableFive'  => true,
                ];
            }

            $allContributionConfig = array_filter( $multipleContribution, function ( $key ) {
                return 0 !== strpos( $key, 'revenueDisable' );
            }, ARRAY_FILTER_USE_KEY );

            $allAmounts = array_filter( $allContributionConfig, function ( $key ) use ( $allContributionConfig ) {
                if ( 0 === strpos( $key, 'amount' ) && floatval( $allContributionConfig[ $key ] ) !== floatval( 0.00 ) ) {
                    return $allContributionConfig[ $key ];
                }
            }, ARRAY_FILTER_USE_KEY );

            $allRevenues = array_filter( $allContributionConfig, function ( $key ) use ( $allContributionConfig ) {
                if ( 0 === strpos( $key, 'revenue' ) ) {
                    return $allContributionConfig[ $key ];
                }
            }, ARRAY_FILTER_USE_KEY );

            $allAmounts  = array_values( $allAmounts );
            $allRevenues = array_values( $allRevenues );

            // Loop through each amount  and configure amount attributes.
            foreach ( $allAmounts as $key => $value ) {
                $contribute_url = $client->get_single_contribution_url( [
                    'revenue'     => $allRevenues[ $key ],
                    'campaign_id' => $campaign_id,
                    'title'       => $campaignName,
                    'url'         => $current_url
                ] );

                $isSelected                                    = absint( $selectedAmount ) === $key + 1;
                $currentAmount                                 = (float) $allAmounts[ $key ] * 100;
                $payment_config['amounts'][ $key ]['amount']   = $currentAmount;
                $payment_config['amounts'][ $key ]['revenue']  = $allRevenues[ $key ];
                $payment_config['amounts'][ $key ]['selected'] = $isSelected;
                $payment_config['amounts'][ $key ]['url']      = $contribute_url . '&custom_pricing=' . $currency_config['code'] . $currentAmount;
            }

            // Handle edge case of no selected default button.
            if ( ! in_array( true, array_column( $payment_config['amounts'], 'selected' ) ) ) {
                $payment_config['amounts'][0]['selected'] = true;
            }

            // Only add custom amount if it was checked.
            if ( $allowCustomAmount ) {
                $payment_config['custom_amount'] = 500;
                // Generate contribution URL's for Pay Now and Pay Later revenue to handle custom amount.
                $contribution_urls = $client->get_contribution_urls( [
                    'campaign_id' => $campaign_id,
                    'title'       => $campaignName,
                    'url'         => $current_url
                ] );
            }
        }

        // View data for laterpay/views/frontend/partials/widget/contribution-dialog.php.
        $view_args = array(
            'symbol'             => 'USD' === $currency_config['code'] ? '$' : '€',
            'id'                 => $campaign_id,
            'type'               => $contributionType,
            'name'               => $campaignName,
            'thank_you'          => $campaignThankYouPage,
            'contribution_urls'  => $contribution_urls,
            'payment_config'     => $payment_config,
            'dialog_header'      => $dialog_header,
            'dialog_description' => $dialog_description,
        );

        // Load the contributions dialog for User.
        $this->assign( 'contribution', $view_args );
        $html = $this->get_text_view( 'frontend/partials/widget/contribution-dialog' );

        return $html;
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
        $asset_file = require $this->config->get( 'block_build_dir' ) . 'laterpay-blocks.asset.php';

        // Register main script to load all blocks.
        wp_register_script(
            'laterpay-block-editor-assets',
            $this->config->get( 'block_build_url' ) . 'laterpay-blocks.js',
            $asset_file['dependencies'],
            $asset_file['version']
        );

        $currency_config = LaterPay_Helper_Config::get_currency_config();

        // Data to be used in blocks.
        $lp_data = [
            'currency' => $currency_config,
            'locale'   => get_locale(),
            'symbol'   => ( 'USD' === $currency_config['code'] ) ? '$' : '€'
        ];

        // Pass data to block script.
        wp_localize_script(
            'laterpay-block-editor-assets',
            'laterPayBlockData',
            $lp_data
        );

        // Register main style for all blocks.
        wp_register_style(
            'laterpay-block-editor-assets',
            $this->config->get( 'css_url' ) . 'laterpay-blocks.css',
            array(),
            filemtime( $this->config->get( 'css_dir' ) . 'laterpay-blocks.css' )
        );

        // Register Subscription / Time Pass Purchase Button Block.
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

        // Register Contribution Block.
        register_block_type( 'laterpay/contribution', array(
            'style'           => 'laterpay-block-editor-assets',
            'editor_script'   => 'laterpay-block-editor-assets',
            'render_callback' => array( $this, 'contribution_render_callback' )
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

    /**
     * Initialize the controller and hook action to register routes.
     */
    public function lp_register_routes() {
        $rest_file_path = $this->config->get( 'plugin_dir_path' ) . 'application/Controller/Rest.php';
        include_once $rest_file_path;
        $restObject = new LaterPay_Controller_Admin_Rest();
        add_action( 'rest_api_init', array( $restObject, 'register_routes' ) );
    }
}
