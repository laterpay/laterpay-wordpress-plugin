<?php

/**
 * LaterPay shortcode controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Frontend_Shortcode extends LaterPay_Controller_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_shortcode_premium_download' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_premium_download_box' ),
            ),
            'laterpay_shortcode_laterpay' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_premium_download_box' ),
            ),
            'laterpay_shortcode_redeem_voucher' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_redeem_gift_code' ),
            ),
            'laterpay_shortcode_time_pass_purchase' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_time_pass_subscription_purchase', 200 ),
            ),
            'laterpay_shortcode_subsription_purchase' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_time_pass_subscription_purchase', 200 ),
            ),
            'laterpay_shortcode_check_access' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_access_manage_content', 200 ),
            ),
            'laterpay_shortcode_contribution' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'render_contribution_dialog', 200 ),
            ),
            'wp_ajax_laterpay_get_premium_shortcode_link' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_get_premium_shortcode_link' ),
            ),
            'wp_ajax_nopriv_laterpay_get_premium_shortcode_link' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'ajax_get_premium_shortcode_link' ),
            ),
        );
    }

    /**
     * Contains all settings for the plugin.
     *
     * @var LaterPay_Model_Config
     */
    protected $config;

    /**
     * Render a teaser box for selling additional (downloadable) content from the shortcode [laterpay_premium_download].
     * Shortcode [laterpay] is an alias for shortcode [laterpay_premium_download].
     *
     * The shortcode [laterpay_premium_download] accepts various parameters:
     * - target_post_title: the title of the page that contains the paid content
     * - target_post_id: the WordPress id of the page that contains the paid content
     * - heading_text: the text that should be displayed as heading in the teaser box;
     *   restricted to one line
     * - description_text: text that provides additional information on the paid content;
     *   restricted to a maximum of three lines
     * - content_type: choose between 'text', 'music', 'video', 'gallery', or 'file',
     *   to display the corresponding default teaser image provided by the plugin;
     *   can be overridden with a custom teaser image using the teaser_image_path attribute
     * - teaser_image_path: path to an image that should be used instead of the default LaterPay teaser image
     *
     * Basic example:
     * [laterpay_premium_download target_post_title="Event video footage"]
     * or:
     * [laterpay_premium_download target_post_id="734"]
     *
     * Advanced example:
     * [laterpay_premium_download target_post_id="734" heading_text="Video footage of concert"
     * description_text="Full HD video of the entire concept, including behind the scenes action."
     * teaser_image_path="/uploads/images/concert-video-still.jpg"]
     *
     * @var array $atts
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception
     *
     */
    public function render_premium_download_box( LaterPay_Core_Event $event ) {

        list( $atts ) = $event->get_arguments() + array( array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'target_post_id'    => '',
            'target_post_title' => '',
            'heading_text'      => esc_html__( 'Additional Premium Content', 'laterpay' ),
            'description_text'  => '',
            'content_type'      => '',
            'teaser_image_path' => '',
            // deprecated:
            'target_page_id'    => '',
            'target_page_title' => '',
        ), $atts );

        $error_reason = '';

        // get URL for target page
        $page = null;

        if ( $a['target_post_id'] !== '' ) {
            $page = get_post( absint( $a['target_post_id'] ) );
        }

        // target_post_id was provided, but didn't work
        if ( $page === null && $a['target_post_id'] !== '' ) {
            $error_reason = sprintf(
                esc_html__( 'We couldn\'t find a page for target_post_id="%s" on this site.', 'laterpay' ),
                absint( $a['target_post_id'] )
            );
        }

        if ( $page === null && $a['target_post_title'] !== '' ) {
            $page = LaterPay_Helper_Post::get_page_by_title( $a['target_post_title'], OBJECT, $this->config->get( 'content.enabled_post_types' ) );
        }

        // target_post_title was provided, but didn't work (no invalid target_post_id was provided)
        if ( $page === null && empty( $error_reason ) ) {
            $error_reason = sprintf(
                esc_html__( 'We couldn\'t find a page for target_post_title="%s" on this site.', 'laterpay' ),
                esc_html( $a['target_post_title'] )
            );
        }

        if ( $page === null ) {
            $error_message  = '<div class="lp_shortcode-error">';
            $error_message .= esc_html__( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            $event->set_result( $error_message );
            throw new LaterPay_Core_Exception( $error_message );
        }

        $page_id = $page->ID;

        // don't render the shortcode, if the target page has a post type for which LaterPay is disabled
        if ( ! in_array( $page->post_type, $this->config->get( 'content.enabled_post_types' ), true ) ) {
            $error_reason   = esc_html__( 'Laterpay has been disabled for the post type of the target page.', 'laterpay' );
            $error_message  = '<div class="lp_shortcode-error">';
            $error_message .= esc_html__( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            $event->set_result( $error_message );
            throw new LaterPay_Core_Exception( $error_message );
        }

        // check, if page has a custom post type
        $custom_post_types   = get_post_types( array( '_builtin' => false ) );
        $custom_types        = array_keys( $custom_post_types );
        $is_custom_post_type = ! empty( $custom_types ) && in_array( $page->post_type, $custom_types, true );

        // get the URL of the target page
        if ( $is_custom_post_type ) {
            // getting the permalink of a custom post type requires get_post_permalink instead of get_permalink
            $page_url = get_post_permalink( $page_id );
        } else {
            $page_url = get_permalink( $page_id );
        }

        // Supported content data types.
        $content_types = array( 'file', 'gallery', 'audio', 'video', 'text', 'music', 'link' );

        if ( empty( $a['content_type'] ) ) {
            // determine $content_type from MIME type of files attached to post
            $page_mime_type = get_post_mime_type( $page_id );
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
        } elseif ( in_array( $a['content_type'], $content_types, true ) ) {
            $content_type = $a['content_type'];
        } else {
            $content_type = 'text';
        }

        $heading     = $a['heading_text'];
        $description = $a['description_text'];

        if ( 'link' === $content_type ) {
            // Build anchor text for premium link.
            $anchor_text = empty( $description ) ? $heading : sprintf( '%s - %s', $heading, $description );

            $html = '<a class="lp_js_premium-file-box lp_premium_link lp_premium_link_anchor" title="'
                    . esc_html__( 'Buy now with Laterpay', 'laterpay' )
                    . '" data-content-type="'
                    . esc_attr( $content_type )
                    . '" data-post-id="'
                    . esc_attr( $page_id )
                    .'" data-page-url="'
                    . esc_url( $page_url )
                    . '">';
            $html .= esc_html( $anchor_text ) . '</a>';

        } else {
            $image_path  = $a['teaser_image_path'];


            // build the HTML for the teaser box
            if ( ! empty( $image_path ) ) {
                $html = '<div class="lp_js_premium-file-box lp_premium-file-box" '
                        . 'style="background-image:url(' . esc_url( $image_path ) . ')'
                        . '" data-post-id="' . esc_attr( $page_id )
                        . '" data-content-type="' . esc_attr( $content_type )
                        . '" data-page-url="' . esc_url ( $page_url )
                        . '">';
            } else {
                $html = '<div class="lp_js_premium-file-box lp_premium-file-box lp_is-' . esc_attr( $content_type )
                        . '" data-post-id="' . esc_attr( $page_id )
                        . '" data-content-type="' . esc_attr( $content_type )
                        . '" data-page-url="' . esc_url( $page_url )
                        . '">';
            }

            // create a premium box
            $html .= '<div class="lp_premium-file-box__details">';
            $html .= '<h3 class="lp_premium-file-box__title">' . esc_attr( $heading ) . '</h3>';

            if ( ! empty( $description ) ) {
                $html .= '<p class="lp_premium-file-box__text">' . esc_attr( $description ) . '</p>';
            }

            $html .= '</div>';
            $html .= '</div>';
        }
        $event->set_result( $html );
    }

    /**
     * Get premium shortcode link
     *
     * @hook wp_ajax_laterpay_get_premium_content_url, wp_ajax_nopriv_laterpay_get_premium_content_url
     * @param LaterPay_Core_Event $event
     * @throws LaterPay_Core_Exception_InvalidIncomingData
     * @throws LaterPay_Core_Exception_PostNotFound
     *
     * @return string
     */
    public function ajax_get_premium_shortcode_link( LaterPay_Core_Event $event ) {
        if ( ! isset( $_GET['action'] ) || sanitize_text_field( $_GET['action'] ) !== 'laterpay_get_premium_shortcode_link' ) { // phpcs:ignore
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'action' );
        }

        if ( ! isset( $_GET['ids'] ) ) { // phpcs:ignore
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'ids' );
        }

        if ( ! isset( $_GET['types'] ) ) { // phpcs:ignore
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'types' );
        }

        if ( ! isset( $_GET['parent_pid'] ) ) { // phpcs:ignore
            throw new LaterPay_Core_Exception_InvalidIncomingData( 'parent_pid' );
        }

        $current_post_id = absint( $_GET['parent_pid'] ); // phpcs:ignore
        if ( ! get_post( $current_post_id ) ) {
            throw new LaterPay_Core_Exception_PostNotFound( $current_post_id );
        }

        $ids    = array_map( 'sanitize_text_field', $_GET['ids'] ); // phpcs:ignore
        $types  = array_map( 'sanitize_text_field', $_GET['types'] ); // phpcs:ignore
        $result = array();

        foreach ( $ids as $key => $id ) {
            $post = get_post( absint( $id ) );
            if ( ! $post ) {
                continue;
            }

            $is_purchasable = LaterPay_Helper_Pricing::is_purchasable( $id );
            $content_type   = $types[ $key ];
            $is_attachment  = $post->post_type === 'attachment';

            $access = LaterPay_Helper_Post::has_access_to_post( $post, $is_attachment, $current_post_id );

            if ( $access || ! $is_purchasable ) {
                // the user has already purchased the item
                switch ( $content_type ) {
                    case 'file':
                        $button_label = __( 'Download now', 'laterpay' );
                        break;

                    case 'video':
                        $button_label = __( 'Watch now', 'laterpay' );
                        break;

                    case 'gallery':
                        $button_label = __( 'View now', 'laterpay' );
                        break;

                    case 'music':
                    case 'audio':
                        $button_label = __( 'Listen now', 'laterpay' );
                        break;

                    default:
                        $button_label = __( 'Read now', 'laterpay' );
                        break;
                };

                if ( $is_attachment && $is_purchasable ) {
                    // render link to purchased attachment
                    $button_page_url = LaterPay_Helper_File::get_encrypted_resource_url(
                        $post->ID,
                        wp_get_attachment_url( $post->ID ),
                        $access,
                        'attachment'
                    );
                } else {
                    if ( $is_attachment ) {
                        // render link to attachment
                        $button_page_url = wp_get_attachment_url( $post->ID );
                    } else {
                        // render link to purchased post
                        $button_page_url = get_permalink( $post );
                    }
                }

                $html_button = '<a href="' . esc_url( $button_page_url ) . '" ' .
                    'class="lp_js_purchaseLink lp_purchase-button lp_purchase-button--shortcode" ' .
                    'rel="prefetch">' .
                    esc_html( $button_label ) .
                    '</a>';
            } else {
                if ( 'link' !== $content_type ) {
                    // the user has not purchased the item yet
                    $button_event = new LaterPay_Core_Event();
                    $button_event->set_echo( false );
                    $button_event->set_argument( 'post', $post );
                    $button_event->set_argument( 'current_post', $current_post_id );
                    $button_event->set_argument( 'attributes', array(
                        'class' => 'lp_js_doPurchase lp_purchase-button lp_purchase-link--shortcode',
                    ) );
                    laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_button', $button_event );
                    $html_button = $button_event->get_result();
                    if ( empty( $html_button ) ) {
                        $view_args = array(
                            'url' => get_permalink( $post->ID ),
                        );
                        $this->assign( 'laterpay', $view_args );
                        $html_button = $this->get_text_view( 'frontend/partials/post/shortcode-purchase-link' );
                    }
                } else {
                    $link_event = new LaterPay_Core_Event();
                    $link_event->set_echo( false );
                    $link_event->set_argument( 'post', $post );
                    $link_event->set_argument( 'attributes', array(
                        'class' => 'lp_js_premium-file-box lp_premium_link lp_premium_link_anchor',
                    ) );
                    laterpay_event_dispatcher()->dispatch( 'laterpay_purchase_link_shortcode', $link_event );
                    $html_button = $link_event->get_result();
                }
            }

            $result[ $id ] = $html_button;
        }

        $event->set_result(
            array(
                'success'   => true,
                'data'      => $result,
            )
        );
    }

    /**
     * Render a form to redeem a gift code from shortcode [laterpay_redeem_voucher].
     * The shortcode renders an input and a button.
     * If the user enters his gift code and clicks the 'Redeem' button, a purchase dialog is opened,
     * where the user has to confirm the purchase of the associated time pass for a price of 0.00 Euro.
     * This step is done to ensure that this user accepts the LaterPay terms of use.
     *
     * Parameters
     * - id   : (Optional) Time Pass ID or Subscription ID. When passed, it will accept voucher code only for that Time Pass or Subscription.
     * - type : (Optional) Type of voucher code to be accepted. Expected values "timepass", "subscription".
     *
     * Basic example:
     * - [laterpay_redeem_voucher]
     * - [laterpay_redeem_voucher type="subscription"]
     *
     * Advanced example:
     * - [laterpay_redeem_voucher id="1" type="timepass"]
     * - [laterpay_redeem_voucher id="2" type="subscription"]
     *
     * @param LaterPay_Core_Event $event
     *
     * @throws LaterPay_Core_Exception
     *
     * @return string
     */
    public function render_redeem_gift_code( LaterPay_Core_Event $event ) {

        list( $atts ) = $event->get_arguments() + array( array() );

        $data = shortcode_atts( array(
            'id'   => null,
            'type' => '',
        ), $atts );

        // If ID is not empty and type is empty then default type should be 'timepass'.
        if ( ! empty( $data['id'] ) && empty( $data['type'] ) ) {
            $data['type'] = 'timepass';
        }

        $allowed_types = [ 'timepass', 'subscription' ];
        $data['type']  = strtolower( trim( $data['type'] ) );
        $data['type']  = in_array( $data['type'], $allowed_types, true ) ? $data['type'] : '';

        $pass_data = array();

        // Get a specific time pass, if an ID was provided; otherwise get all time passes.
        if ( $data['id'] ) {

            if ( 'subscription' === $data['type'] ) {
                $pass_data = LaterPay_Helper_Subscription::get_subscription_by_id( $data['id'], true );
            } else {
                $pass_data = LaterPay_Helper_TimePass::get_time_pass_by_id( $data['id'], true );
            }

            if ( ! $pass_data ) {

                if ( 'subscription' === $data['type'] ) {
                    $error_message = esc_html__( 'Wrong Subscription ID.', 'laterpay' );
                } else {
                    $error_message = esc_html__( 'Wrong Time Pass ID.', 'laterpay' );
                }

                $error_message = LaterPay_Helper_View::get_error_message( $error_message, $atts );
                $event->set_result( $error_message );
                throw new LaterPay_Core_Exception( $error_message );
            }
        }

        $view_args = array(
            'pass_data'               => $pass_data,
            'type'                    => $data['type'],
            'standard_currency'       => $this->config->get( 'currency.code' ),
            'preview_post_as_visitor' => LaterPay_Helper_User::preview_post_as_visitor( get_post() ),
        );

        $this->assign( 'laterpay', $view_args );

        $html = $this->get_text_view( 'frontend/partials/post/gift/gift-redeem' );

        $event->set_result( $html );
    }

    /**
     * Render gift card.
     *
     * @param array $gift_pass
     * @param bool  $show_redeem
     *
     * @return string
     */
    public function render_gift_pass( $gift_pass, $show_redeem = false ) {
        // check if gift_pass is not empty and is array
        if ( ! $gift_pass || ! is_array( $gift_pass ) ) {
            return '';
        }

        $view_args = array(
            'gift_pass'   => $gift_pass,
            'show_redeem' => $show_redeem,
        );
        $this->assign( 'laterpay_gift', $view_args );

        $this->render( 'frontend/partials/post/gift/gift-pass', null, true );

    }

    /**
     * Render redeem gift card form.
     *
     * @return void
     */
    public function render_redeem_form() {

        $this->render( 'frontend/partials/post/gift/redeem-form', null, true );
    }

    /**
     * Add voucher codes to time passes.
     *
     * @param array $time_passes list of time passes
     *
     * @return array
     */
    protected function add_free_codes_to_passes( $time_passes, $link = null ) {
        if ( is_array( $time_passes ) ) {
            foreach ( $time_passes as $id => $time_pass ) {
                // create URL with the generated voucher code
                $data = array(
                    'voucher' => LaterPay_Helper_Voucher::generate_voucher_code(),
                    'link'    => $link ? $link : get_permalink(),
                );

                $time_pass['url']   = LaterPay_Helper_TimePass::get_laterpay_purchase_link( $time_pass['pass_id'], $data, true );
                $time_passes[ $id ] = $time_pass;
            }
        }

        return $time_passes;
    }

    /**
     * Render a purchase button for selling Time Pass / Subscription using the shortcode [laterpay_time_pass_purchase] or [laterpay_subscription_purchase].
     *
     * The shortcode [laterpay_time_pass_purchase] / [laterpay_subscription_purchase] accepts various parameters:
     * - id: The ID of Time Pass / Subscription.
     * - button_text: Text to be displayed on purchase button, defaults to revenue type value.
     * - button_background_color: Background color of the button.
     * - button_text_color: Text color of the Button.
     * - custom_image_path: Image to be displayed in place of button text. Overrides button text and color values.
     *
     * Basic example:
     * [laterpay_time_pass_purchase id="3" ]
     * or:
     * [laterpay_subscription_purchase id="4" ]
     *
     * Advanced example:
     * [laterpay_subscription_purchase id="5" button_background_color="blue" button_text_color="black" button_text="Purchase Now!"]
     * or:
     * [laterpay_subscription_purchase id="6" custom_image_path="http://example.com/images/Subscribe.png"]
     *
     * @param  LaterPay_Core_Event $event
     *
     * @throws LaterPay_Core_Exception
     */
    public function render_time_pass_subscription_purchase( LaterPay_Core_Event $event ) {

        // Check whether the shortcode was asked for Time Pass or Subscription.
        $is_subscription = $event->get_name() === 'laterpay_shortcode_subsription_purchase' ? true : false;

        // Get all the attributes.
        list( $attributes ) = $event->get_arguments() + array( array() );

        // Provide default values for empty shortcode attributes.
        $shortcode_atts = shortcode_atts( array(
            'id'                      => '',
            'button_text'             => '',
            'button_background_color' => get_option( 'laterpay_main_color', '#01a99d' ),
            'button_text_color'       => '#ffffff',
            'custom_image_path'       => '',
        ), $attributes );

        $error_message = '';

        // ID of Time Pass / Subscription.
        $entity_id = $shortcode_atts['id'];

        // Get Time Pass / Subscription data according to ID.
        if ( ! empty( $entity_id ) ) {
            $entity = $is_subscription ? LaterPay_Helper_Subscription::get_subscription_by_id( $entity_id, true ) : LaterPay_Helper_TimePass::get_time_pass_by_id( $entity_id, true );
        }

        // Template for error message.
        $template = '<div class="lp_shortcode-error">%s</div>';

        // ID was provided, but didn't work.
        if ( empty( $entity ) ) {

            if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
                $error_message = sprintf(
                    $template,
                    sprintf( esc_html__( 'We couldn\'t find a %s with id="%s" on this site.', 'laterpay' ), $is_subscription ? __( 'Subscription', 'laterpay' ) : __( 'Time Pass', 'laterpay' ), $entity_id )
                );
            }

            $event->set_result( $error_message );

            return;
        }

        // Update purchase button text.
        if ( empty( $shortcode_atts['button_text'] ) && ! $is_subscription ) {
            if ( 'ppu' === $entity['revenue_model'] ) {
                $shortcode_atts['button_text'] = esc_html__( 'Buy Now, Pay Later', 'laterpay' );
            } else {
                $shortcode_atts['button_text'] = esc_html__( 'Buy Now', 'laterpay' );
            }
        }

        // Override purchase button text value if subscription.
        if ( empty( $shortcode_atts['button_text'] ) && $is_subscription ) {
            $shortcode_atts['button_text'] = esc_html__( 'Subscribe Now', 'laterpay' );
        }

        // Get the purchase URL.
        $shortcode_atts['url'] = $is_subscription ? LaterPay_Helper_Subscription::get_subscription_purchase_link( $entity_id ) : LaterPay_Helper_TimePass::get_laterpay_purchase_link( $entity_id );

        // Assign data for view.
        $this->assign( 'laterpay', $shortcode_atts );

        // Get markup with all data.
        $html_button = $this->get_text_view( 'frontend/partials/post/shortcode-purchase-button' );

        // Set final result.
        $event->set_result( $html_button );

    }

    /**
     * Wrap your Ads or content around these shortcode so that it's hidden to premium users those who have access to certain,
     * time pass /  subscription / current post.
     *
     * The shortcode [laterpay_check_access] accepts these parameters:
     * - timepasses: A comma separated string containing ID of time passes.
     * - subscriptions: A comma separated string containing ID of subscriptions.
     *
     * Basic example:
     * [laterpay_check_access timepasses="47" ]Ads![/laterpay_check_access]
     * or:
     * [laterpay_check_access subscriptions="18" ]AdS![/laterpay_check_access]
     * or:
     * [laterpay_check_access timepasses="47" subscriptions="18"]Ads![/laterpay_check_access]
     * or:
     * [laterpay_check_access timepasses="all"]Ads![/laterpay_check_access]
     * or:
     * [laterpay_check_access subscriptions="all"]Ads![/laterpay_check_access]
     *
     * @param  LaterPay_Core_Event $event
     *
     * @throws LaterPay_Core_Exception
     */
    public function laterpay_access_manage_content( LaterPay_Core_Event $event ) {

        // Get all the attributes.
        list( $attributes, $content ) = $event->get_arguments() + array( array(), '' );

        // Check if user has access to current page/post, if so hide content.
        if ( true === LaterPay_Helper_Pricing::check_current_post_access() ) {
            $event->set_result( '' );

            return;
        }

        // Provide default values for empty shortcode attributes.
        $shortcode_atts = shortcode_atts( array(
            'timepasses'    => '',
            'subscriptions' => '',
        ), $attributes );

        // ID of Time Passes and Subscriptions.
        $time_pass_ids    = $shortcode_atts['timepasses'];
        $subscription_ids = $shortcode_atts['subscriptions'];

        // Check if all is set for both time pass and subscription.
        if ( 'all' === $time_pass_ids && 'all' === $subscription_ids ) {
            // Template for error message.
            $template = '<div class="lp_shortcode-error">%s</div>';

            if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
                $error_message = sprintf(
                    $template,
                    sprintf( esc_html__( '%1$s"all"%2$s cannot be used for both %1$s"timepasses"%2$s and %1$s"subscriptions"%2$s at the same time.', 'laterpay' ), '<code>', '</code>' )
                );
            }

            $event->set_result( $error_message );

            return;
        }

        if ( true === LaterPay_Helper_Pricing::check_time_pass_subscription_access( $time_pass_ids, $subscription_ids ) ) {
            $event->set_result( '' );

            return;
        }

        // If user has no access then show content.
        $event->set_result( $content );
    }

    /**
     * Generate the shortcode string based on provide attributes and their values.
     *
     * @param $config_array array Shortcode attribute and value data.
     *
     * @return mixed
     */
    private static function  get_shortcode_string( $config_array ) {
        return array_reduce(
            array_keys( $config_array ),
            function ( $carry, $key ) use ( $config_array ) {
                $value = $config_array[ $key ];
                if ( in_array( $key, ['all_amounts', 'all_revenues'], true ) ) {
                    $value = implode( ',', $config_array[ $key ] );
                }
                return $carry . ' ' . $key . '="' . $value . '"';
            },
            ''
        );
    }

    /**
     * Generate shortcode based on provided config.
     *
     * @param $type         string Type of shortcode.
     * @param $config_array array  Shortcode configuration data.
     *
     * @return array|bool
     */
    public static function generator( $type, $config_array ) {
        // Handle contribution shortcode generation.
        if ( 'contribution' === $type ) {
            // Validate the configuration.
            $result = self::is_contribution_config_valid( $config_array );
            if ( false === $result['success'] ) {
                return $result;
            } else {
                if ( 'multiple' === $config_array['type'] && 'none' === $config_array['custom_amount'] ) {
                    unset( $config_array['custom_amount'] );
                }
                // Create the shortcode string.
                $built_shortcode = sprintf( '[laterpay_contribution %s]', self::get_shortcode_string( $config_array ) );
                return [
                    'success' => true,
                    'code'    => $built_shortcode
                ];
            }
        }

        return [
            'success' => false,
            'message' => esc_html__( 'Something went wrong.', 'laterpay' )
        ];
    }

    /**
     * Check if the provided shortcode configuration for Contribution is valid or now.
     *
     * @param $config_array array Contribution configuration data.
     *
     * @return array|bool
     */
    private static function is_contribution_config_valid( $config_array ) {

        // Check if campaign name is set.
        if ( empty( $config_array['name'] ) ) {
            return [
                'success' => false,
                'message' => esc_html__( 'Please enter a Campaign Name above.', 'laterpay' ),
            ];
        }

        // Check if campaign amount is empty.
        if ( 'single' === $config_array['type'] ) {
            if ( floatval( $config_array['single_amount'] ) === floatval(0.0) ) {
                return [
                    'success' => false,
                    'message' => esc_html__( 'Please enter a valid contribution amount above.', 'laterpay' ),
                ];
            }
            return true;
        }

        return true;
    }

    /**
     * Display Contribution dialog for multiple amounts and Contribution amount button for single amount shortcode.
     *
     * The shortcode [laterpay_contribution] accepts these parameters:
     * - type: Type of the Contribution, i.e Single / Multiple.
     * - name: Name of the Campaign.
     * - thank_you: URL to which the user has to be redirected to, if empty redirect to shortcode page.
     * - single_amount: Amount of Contribution, value in cents..
     * - single_revenue: Revenue of the single amount, i.e Pay Now / Pay Later.
     * - custom_amount: Custom Amount for Contribution dialog, if set amount will be pre-filled else empty.
     * - all_amounts: A comma separated string containing configured amounts.
     * - all_revenues: A comma separated string containing configured revenues.
     * - selected_amount: Indicates default selected amount in the Contribution Dialog for Multiple Contributions.
     *
     * Basic example:
     * [laterpay_contribution  name="Kerala Floods Relief" thank_you="" type="single" single_amount="400" single_revenue="ppu"]
     * or:
     * [laterpay_contribution  name="Dharamsala Animal Rescue" thank_you="" type="multiple" all_amounts="300,500,800" all_revenues="ppu,sis,sis" selected_amount="1"]
     * or:
     * [laterpay_contribution  name="Dharamsala Animal Rescue" thank_you="https://dharamsalaanimalrescue.org/" type="multiple" custom_amount="1000" all_amounts="300,500" all_revenues="ppu,sis" selected_amount="1"]
     *
     * @param LaterPay_Core_Event $event
     */
    public function render_contribution_dialog( LaterPay_Core_Event $event ) {
        list( $atts) = $event->get_arguments() + array( array() );

        $config_data = shortcode_atts( array(
            'type'               => 'multiple',
            'name'               => null,
            'dialog_header'      => __( 'Support the author', 'laterpay' ),
            'dialog_description' => __( 'How much would you like to contribute?', 'laterpay' ),
            'thank_you'          => null,
            'single_amount'      => null,
            'single_revenue'     => null,
            'custom_amount'      => null,
            'all_amounts'        => null,
            'all_revenues'       => null,
            'selected_amount'    => null,
        ), $atts );

        // Show error to current user?
        $show_error = is_user_logged_in() && current_user_can( 'manage_options' );

        // Template for error message.
        $template = '<div class="lp_shortcode-error">%s</div>';

        // Validate shortcode attributes.
        $validation_result = self::is_contribution_config_valid( $config_data );

        // Display error if something went wrong.
        if ( $show_error && false === $validation_result['success'] ) {
            $error_message = sprintf(
                $template,
                sprintf( esc_html__( '%1$s', 'laterpay' ), $validation_result['message'] )
            );
            $event->set_result( $error_message );
            return;
        }

        // Set redirect URL, if empty use current page where shortcode resides.
        if ( ! empty( $config_data['thank_you'] ) ) {
            $current_url = $config_data['thank_you'];
        } else {
            global $wp;
            $current_url = trailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
        }

        // Configure contribution values.
        $payment_config    = [];
        $contribution_urls = '';
        $currency_config   = LaterPay_Helper_Config::get_currency_config();
        $campaign_name     = $config_data['name'];
        $campaign_id       = str_replace( ' ', '-', strtolower( $campaign_name ) ) . '-' . (string) time();
        $client_options    = LaterPay_Helper_Config::get_php_client_options();
        $client            = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        if ( 'single' === $config_data['type'] ) {
            // Configure single amount contribution.
            $lp_revenue     = empty( $config_data['single_revenue'] ) ? 'ppu' : $config_data['single_revenue'];
            $payment_config = [
                'amount'  => $config_data['single_amount'],
                'revenue' => $lp_revenue,
                'url'     => $client->get_single_contribution_url( [
                    'revenue'        => $lp_revenue,
                    'campaign_id'    => $campaign_id,
                    'title'          => $campaign_name,
                    'url'            => $current_url,
                ] )
            ];
        } else {
            // Get all amounts and revenues from shortcode.
            $multiple_amounts  = explode( ',', $config_data['all_amounts'] );
            $multiple_revenues = explode( ',', $config_data['all_revenues'] );

            // Loop through each amount  and configure amount attributes.
            foreach ( $multiple_amounts as $key => $value ) {
                $contribute_url = $client->get_single_contribution_url( [
                    'revenue'     => $multiple_revenues[ $key ],
                    'campaign_id' => $campaign_id,
                    'title'       => $campaign_name,
                    'url'         => $current_url
                ] );

                $payment_config['amounts'][ $key ]['amount']   = $multiple_amounts[ $key ];
                $payment_config['amounts'][ $key ]['revenue']  = $multiple_revenues[ $key ];
                $payment_config['amounts'][ $key ]['selected'] = absint( $config_data['selected_amount'] ) === $key + 1;
                $payment_config['amounts'][ $key ]['url']      = $contribute_url . '&custom_pricing=' . $currency_config['code'] .  $multiple_amounts[ $key ];
            }

            // Only add custom amount if it was checked in backend.
            if ( isset( $config_data['custom_amount'] ) ) {
                $payment_config['custom_amount'] = $config_data['custom_amount'];

                // Generate contribution URL's for Pay Now and Pay Later revenue to handle custom amount.
                $contribution_urls = $client->get_contribution_urls( [
                    'campaign_id' => $campaign_id,
                    'title'       => $campaign_name,
                    'url'         => $current_url
                ] );
            }
        }

        // View data for laterpay/views/frontend/partials/widget/contribution-dialog.php.
        $view_args = array(
            'symbol'             => 'USD' === $currency_config['code'] ? '$' : '€',
            'id'                 => $campaign_id,
            'dialog_header'      => $config_data['dialog_header'],
            'dialog_description' => $config_data['dialog_description'],
            'type'               => $config_data['type'],
            'name'               => $campaign_name,
            'thank_you'          => empty( $config_data['thank_you'] ) ? '' : $config_data['thank_you'],
            'contribution_urls'  => $contribution_urls,
            'payment_config'     => $payment_config,
        );

        // Load the contributions dialog for User.
        $this->assign( 'contribution', $view_args );
        $html = $this->get_text_view( 'frontend/partials/widget/contribution-dialog' );
        $event->set_result( $html );
    }
}
